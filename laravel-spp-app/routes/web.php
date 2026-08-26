<?php

use App\Http\Controllers\PdfController;
use Illuminate\Support\Facades\Route;

/**
 * Route publik untuk download PDF (dilindungi auth admin).
 */
Route::middleware(['auth'])->group(function () {
    Route::get('/invoices/{invoice}/pdf', [PdfController::class, 'invoice'])
        ->name('invoices.pdf');
    Route::get('/payments/{payment}/pdf', [PdfController::class, 'receipt'])
        ->name('payments.pdf');
});
