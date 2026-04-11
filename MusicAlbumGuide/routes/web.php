<?php

use App\Http\Controllers\AlbumController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/albums')->name('home');

Route::post('/albums/prefill', [AlbumController::class, 'prefill'])
    ->middleware('auth')
    ->name('albums.prefill');

Route::resource('albums', AlbumController::class)->only([
    'index', 'create', 'store', 'edit', 'update', 'destroy'
]);

Route::redirect('/dashboard', '/albums')->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
