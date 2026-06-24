<?php

use App\Livewire\InvoiceImporter;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route(auth()->check() ? 'import' : 'login');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

Route::get('/import', InvoiceImporter::class)
    ->middleware('auth')
    ->name('import');

require __DIR__.'/settings.php';
