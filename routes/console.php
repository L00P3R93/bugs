<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('payouts:process-daily')
    ->dailyAt('00:05')
    ->timezone('Africa/Nairobi')
    ->withoutOverlapping()
    ->onFailure(fn () => Log::error('Daily payout job failed'));

Schedule::command('withdrawals:process-pending')
    ->everyMinute()
    ->withoutOverlapping()
    ->onFailure(fn () => Log::channel('mpesa')->error('B2C processing job failed'));
