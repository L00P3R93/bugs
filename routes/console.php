<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('payouts:process-daily')
    ->hourly()
    ->timezone('Africa/Nairobi')
    ->withoutOverlapping()
    ->onFailure(fn () => Log::error('Hourly payout job failed'));

Schedule::command('withdrawals:process-pending')
    ->everyMinute()
    ->withoutOverlapping()
    ->onFailure(fn () => Log::channel('mpesa')->error('B2C processing job failed'));

Schedule::command('mpesa:fetch-balances')
    ->hourly()
    ->withoutOverlapping()
    ->onFailure(fn () => Log::channel('mpesa')->error('M-Pesa balance fetch job failed'));
