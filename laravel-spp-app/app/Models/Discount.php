<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Discount extends Model
{
    protected $fillable = [
        'student_id', 'name', 'amount',
        'start_month', 'start_year', 'end_month', 'end_year',
        'notes', 'is_active',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Cek apakah diskon aktif pada bulan/tahun tertentu.
     */
    public function isActiveFor(int $month, int $year): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $target = Carbon::create($year, $month, 1)->startOfMonth();
        $start = Carbon::create($this->start_year, $this->start_month, 1)->startOfMonth();
        $end = Carbon::create($this->end_year, $this->end_month, 1)->endOfMonth();

        return $target->between($start, $end);
    }
}
