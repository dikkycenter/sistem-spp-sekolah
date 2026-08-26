<?php

use App\Http\Controllers\ParentPortalController;
use App\Http\Controllers\PdfController;
use Illuminate\Support\Facades\Route;

// Admin (butuh login Filament)
Route::middleware(['auth'])->group(function () {
    Route::get('/invoices/{invoice}/pdf', [PdfController::class, 'invoice'])
        ->name('invoices.pdf');
    Route::get('/payments/{payment}/pdf', [PdfController::class, 'receipt'])
        ->name('payments.pdf');
});

// Portal Orang Tua (public, tanpa akun; verifikasi via NISN + tgl lahir)
Route::prefix('portal')->group(function () {
    Route::get('/', [ParentPortalController::class, 'showLookup'])->name('portal.lookup');
    Route::post('/verify', [ParentPortalController::class, 'verify'])->name('portal.verify');
    Route::get('/dashboard', [ParentPortalController::class, 'dashboard'])->name('portal.dashboard');
    Route::get('/invoice/{invoice}/pdf', [ParentPortalController::class, 'downloadInvoice'])
        ->name('portal.invoice.pdf');
    Route::post('/logout', [ParentPortalController::class, 'logout'])->name('portal.logout');
});

// Landing → arahkan ke portal orang tua
Route::get('/', fn () => redirect()->route('portal.lookup'));
