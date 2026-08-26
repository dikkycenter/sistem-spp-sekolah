<?php

namespace App\Support;

/**
 * Helper konversi angka ke terbilang bahasa Indonesia (untuk kwitansi PDF).
 */
class Terbilang
{
    public static function make(int $number): string
    {
        $number = abs($number);
        $words = ['', 'satu', 'dua', 'tiga', 'empat', 'lima',
                  'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];

        if ($number < 12) {
            return $words[$number] ?: 'nol';
        }
        if ($number < 20) {
            return self::make($number - 10).' belas';
        }
        if ($number < 100) {
            return self::make(intdiv($number, 10)).' puluh '.self::make($number % 10);
        }
        if ($number < 200) {
            return 'seratus '.self::make($number - 100);
        }
        if ($number < 1000) {
            return self::make(intdiv($number, 100)).' ratus '.self::make($number % 100);
        }
        if ($number < 2000) {
            return 'seribu '.self::make($number - 1000);
        }
        if ($number < 1_000_000) {
            return self::make(intdiv($number, 1000)).' ribu '.self::make($number % 1000);
        }
        if ($number < 1_000_000_000) {
            return self::make(intdiv($number, 1_000_000)).' juta '.self::make($number % 1_000_000);
        }
        return self::make(intdiv($number, 1_000_000_000)).' miliar '.self::make($number % 1_000_000_000);
    }
}
