<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Profil Sekolah (single record, digunakan sebagai kop surat pada PDF).
 * Menggunakan Spatie Media Library untuk menyimpan logo.
 */
class SchoolProfile extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'name', 'address', 'phone', 'email', 'website',
        'logo_path', 'bank_name', 'bank_account_name',
        'bank_account_number', 'headmaster_name',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')
            ->singleFile()
            ->useDisk(config('filesystems.default'))
            ->acceptsMimeTypes(['image/png', 'image/jpeg', 'image/svg+xml']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(200)->height(200)
            ->nonQueued();
    }

    /** Helper untuk mendapatkan single profile (dibuat satu saja). */
    public static function current(): self
    {
        return static::query()->firstOrCreate(
            ['id' => 1],
            ['name' => 'Nama Sekolah', 'address' => '-']
        );
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('logo') ?: null;
    }
}
