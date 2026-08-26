<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicYear extends Model
{
    protected $fillable = ['name', 'start_date', 'end_date', 'is_active'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function enrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public static function active(): ?self
    {
        return static::where('is_active', true)->first();
    }

    /**
     * Pastikan hanya 1 tahun ajaran aktif.
     */
    protected static function booted(): void
    {
        static::saving(function (self $model) {
            if ($model->is_active) {
                static::where('id', '!=', $model->id)
                    ->where('is_active', true)
                    ->update(['is_active' => false]);
            }
        });
    }
}
