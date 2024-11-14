<?php

use App\Events\BidPlacedEvent;
use App\Events\Testing;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

// WS Reverb
Route::get('/', function () {
    return view('welcome');
});

Route::post('/bid', function (Request $request) {
    BidPlacedEvent::dispatch($request->name, $request->price);
})->withoutMiddleware(VerifyCsrfToken::class);

// SSE
Route::get('/sse', function () {
    Cache::put('has_new_bid', true);
    return view('welcome-sse');
});

Route::post('/sse/bid', function (Request $request) {
    // Validasi request untuk bid
    $request->validate([
        'name' => 'required|string',
        'price' => 'required|numeric',
    ]);

    // Simpan bid dalam cache (atau gunakan mekanisme penyimpanan lain seperti DB)
    $bids = Cache::get('bids', []);
    $newBid = [
        'name' => $request->input('name'),
        'price' => $request->input('price'),
    ];
    $bids[] = $newBid;
    Cache::put('bids', $bids);

    Cache::put('has_new_bid', true); // Tandai bahwa ada event baru

    // Trigger broadcast via SSE (akan dijelaskan di step streaming)
    return response()->json(['status' => 'Bid submitted']);
});

Route::get('/sse/stream', function (Request $request) {
    return response()->stream(function () {
        $lastEventId = Cache::get('last_event_id', 0);

        while (true) {
            if (Cache::get('has_new_bid', false)) {
                // Ambil bid terbaru dari cache
                $bids = Cache::get('bids', []);
                
                // Kirim semua bid dalam stream
                echo "id: " . ($lastEventId + 1) . "\n";
                echo "data: " . json_encode($bids) . "\n\n";
                ob_flush(); // Kirim data segera ke client
                flush();

                // Update ID event terakhir yang dikirim
                Cache::put('last_event_id', $lastEventId + 1);
                Cache::put('has_new_bid', false); // Reset event baru
            }

            // Beri jeda 2 detik sebelum stream ulang (agar tidak terlalu intensif)
            sleep(2);
        }
    }, 200, [
        'Content-Type' => 'text/event-stream',
        'Cache-Control' => 'no-cache',
        'Connection' => 'keep-alive',
        'X-Accel-Buffering' => 'no', // Jika menggunakan Nginx
    ]);
});
