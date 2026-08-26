<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Pivot: alokasi nominal dari satu pembayaran ke satu invoice.
 * Digunakan untuk FIFO (Case 1) - 1 payment bisa terdistribusi ke beberapa invoice.
 */
class PaymentAllocation extends Model
{
    protected $fillable = ['payment_id', 'invoice_id', 'amount'];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
