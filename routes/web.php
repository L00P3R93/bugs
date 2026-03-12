<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

// Redirect authenticated users to admin panel
Route::middleware(['auth'])->get('/dashboard', function () {
    return redirect('/admin');
})->name('dashboard');

require __DIR__.'/settings.php';
