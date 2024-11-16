<?php

use App\Events\BidPlacedEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\StreamedResponse; 
use Hhxsv5\SSE\SSE;
use Hhxsv5\SSE\Event;
use Hhxsv5\SSE\StopSSEException;

// WS Reverb - Laravel Broadcast
Route::get('/', function () {
    return view('welcome');
});

Route::post('/bid', function (Request $request) {
    BidPlacedEvent::dispatch($request->name, $request->price);
})->withoutMiddleware(VerifyCsrfToken::class);

// SSE - Server Sent Event
Route::get('/sse', function () {
    Cache::put('has_new_bid', true);
    return view('welcome-sse');
});

Route::post('/sse/bid', function (Request $request) {
    $request->validate([
        'name' => 'required|string',
        'price' => 'required|numeric',
    ]);

    // Save bids on cache
    $bids = Cache::get('bids', []);
    $newBid = [
        'name' => $request->input('name'),
        'price' => $request->input('price'),
    ];
    $bids[] = $newBid;
    Cache::put('bids', $bids);

    Cache::put('has_new_bid', true); // new event

    // Trigger broadcast via SSE
    return response()->json(['status' => 'Bid submitted']);
})->withoutMiddleware(VerifyCsrfToken::class);

Route::get('/sse/stream', function (Request $request) {
    $response = new StreamedResponse();
    $response->headers->set('Content-Type', 'text/event-stream');
    $response->headers->set('Cache-Control', 'no-cache');
    $response->headers->set('Connection', 'keep-alive');
    $response->headers->set('X-Accel-Buffering', 'no'); // Nginx: unbuffered responses suitable for Comet and HTTP streaming applications
    $response->setCallback(function () {
        $callback = function () {            
            if (!Cache::get('has_new_bid', false)) {
                return false; // Return false if no new messages
            }

            // $shouldStop = false; // Stop if something happens or to clear connection, browser will retry
            // if ($shouldStop) {
            //     throw new StopSSEException();
            // }

            $bids = Cache::get('bids', []);
            Cache::put('has_new_bid', false); // Reset new event

            return json_encode($bids);
        };
        (new SSE(new Event($callback)))->start();
    });

    return $response;
});
