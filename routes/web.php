<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// DUDI Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('dudi', App\Http\Controllers\DudiController::class);
    Route::post('dudi/{dudi}/restore', [App\Http\Controllers\DudiController::class, 'restore'])->name('dudi.restore');
    Route::get('dudi/{dudi}/siswa-magang', [App\Http\Controllers\DudiController::class, 'getSiswaMagang'])->name('dudi.siswa-magang');
    Route::post('dudi/{dudi}/apply', [App\Http\Controllers\DudiController::class, 'apply'])->name('dudi.apply');

    // Magang Routes
    Route::resource('magang', App\Http\Controllers\MagangController::class);
});

// Logbook Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('logbook', App\Http\Controllers\LogbookController::class);
    Route::post('logbook/{id}/verify', [App\Http\Controllers\LogbookController::class, 'verify'])->name('logbook.verify');
});


// User Management Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('users', App\Http\Controllers\UserController::class);
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
