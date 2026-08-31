<?php

use App\Http\Controllers\B2CBalanceResultController;
use App\Http\Controllers\B2CBalanceTimeoutController;
use App\Http\Controllers\C2BBalanceResultController;
use App\Http\Controllers\C2BBalanceTimeoutController;
use App\Http\Controllers\WithdrawResultController;
use App\Http\Controllers\WithdrawTimeoutController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

// Redirect authenticated users to admin panel, or verification if unverified
Route::middleware(['auth'])->get('/dashboard', function () {
    if (! auth()->user()->hasVerifiedEmail()) {
        return redirect()->route('verification.notice');
    }

    return redirect('/console');
})->name('dashboard');

Route::post('/result', WithdrawResultController::class);
Route::post('/timeout', WithdrawTimeoutController::class);

Route::post('/b2c/balance/result', B2CBalanceResultController::class);
Route::post('/b2c/balance/timeout', B2CBalanceTimeoutController::class);
Route::post('/c2b/balance/result', C2BBalanceResultController::class);
Route::post('/c2b/balance/timeout', C2BBalanceTimeoutController::class);

require __DIR__.'/settings.php';
