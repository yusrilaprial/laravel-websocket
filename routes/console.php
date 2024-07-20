<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Artisan::command('user:add', function () {
//     User::factory(1)->create();
// })->purpose('add user')->everySecond();

// Schedule::command('backup:clean')->everySecond();
// Schedule::command('backup:run')->everySecond();
