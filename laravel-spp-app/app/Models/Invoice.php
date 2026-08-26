<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number', 'student_id', 'academic_year_id',
        'student_enrollment_id', 'month', 'year',
        'base_amount', 'discount_amount', 'total_due',
        'total_paid', 'remaining_balance', 'status',
        'due_date', 'notes',
    ];

    protected $casts = [
        'base_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_due' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
        'due_date' => 'date',
        'status' => InvoiceStatus::class,
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(StudentEnrollment::class, 'student_enrollment_id');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    /**
     * Recalculate total_paid, remaining_balance & status berdasarkan alokasi.
     * Dipanggil setiap kali ada payment allocation berubah.
     */
    public function recalculate(): void
    {
        $paid = (float) $this->allocations()->sum('amount');
        $this->total_paid = $paid;
        $this->remaining_balance = max(0, (float) $this->total_due - $paid);

        $this->status = match (true) {
            $this->remaining_balance <= 0 => InvoiceStatus::Paid,
            $paid > 0 => InvoiceStatus::Partial,
            default => InvoiceStatus::Unpaid,
        };

        $this->save();
    }

    public function getMonthNameAttribute(): string
    {
        return \Carbon\Carbon::create()->month($this->month)
            ->locale('id')->translatedFormat('F');
    }
}
