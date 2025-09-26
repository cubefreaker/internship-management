<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// DUDI Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('dudi', App\Http\Controllers\DudiController::class);
    Route::post('dudi/{dudi}/restore', [App\Http\Controllers\DudiController::class, 'restore'])->name('dudi.restore');
    Route::get('dudi/{dudi}/siswa-magang', [App\Http\Controllers\DudiController::class, 'getSiswaMagang'])->name('dudi.siswa-magang');
});

// User Management Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('users', App\Http\Controllers\UserController::class);
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
