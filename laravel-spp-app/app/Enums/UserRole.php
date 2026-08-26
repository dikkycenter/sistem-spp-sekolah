<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Role user untuk gating akses.
 * - Admin: full access (CRUD semua, hapus pembayaran, ubah invoice).
 * - Bendahara: catat pembayaran + lihat data; TIDAK bisa hapus payment atau ubah invoice.
 */
enum UserRole: string implements HasLabel, HasColor
{
    case Admin = 'admin';
    case Bendahara = 'bendahara';

    public function getLabel(): string
    {
        return match ($this) {
            self::Admin => 'Administrator',
            self::Bendahara => 'Bendahara',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Admin => 'danger',
            self::Bendahara => 'info',
        };
    }
}
