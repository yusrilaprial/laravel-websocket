<?php

use App\Events\BidPlacedEvent;
use App\Events\Testing;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/bid', function (Request $request) {
    BidPlacedEvent::dispatch($request->name, $request->price);
})->withoutMiddleware(VerifyCsrfToken::class);
