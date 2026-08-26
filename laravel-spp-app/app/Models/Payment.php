<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Payment extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'receipt_number', 'student_id', 'invoice_id',
        'amount_paid', 'method', 'payment_date',
        'notes', 'recorded_by',
    ];

    protected $casts = [
        'amount_paid' => 'decimal:2',
        'payment_date' => 'date',
        'method' => PaymentMethod::class,
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'recorded_by');
    }

    /**
     * Media collection untuk bukti pembayaran (wajib untuk transfer).
     * Disk mengikuti FILESYSTEM_DISK -> ganti ke 's3' untuk Cloudflare R2.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('proof')
            ->useDisk(config('filesystems.default'))
            ->singleFile()
            ->acceptsMimeTypes(['image/png', 'image/jpeg', 'application/pdf']);
    }

    public function getProofUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('proof') ?: null;
    }
}
