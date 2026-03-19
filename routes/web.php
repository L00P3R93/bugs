<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

// Redirect authenticated users to admin panel, or verification if unverified
Route::middleware(['auth'])->get('/dashboard', function () {
    if (! auth()->user()->hasVerifiedEmail()) {
        return redirect()->route('verification.notice');
    }

    return redirect('/admin');
})->name('dashboard');

require __DIR__.'/settings.php';
