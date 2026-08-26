<?php

namespace App\Console\Commands;

use App\Services\InvoiceGeneratorService;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Auto-generate invoice bulanan.
 * Scheduled: setiap tanggal 1 pukul 00:00 (lihat routes/console.php).
 *
 * Manual run:
 *   php artisan spp:generate-invoices
 *   php artisan spp:generate-invoices --month=3 --year=2026
 */
class GenerateMonthlyInvoicesCommand extends Command
{
    protected $signature = 'spp:generate-invoices
                            {--month= : Bulan target (1-12). Default: bulan berjalan.}
                            {--year=  : Tahun target. Default: tahun berjalan.}';

    protected $description = 'Generate invoice SPP bulanan untuk semua siswa aktif.';

    public function handle(InvoiceGeneratorService $service): int
    {
        $now = Carbon::now();
        $month = (int) ($this->option('month') ?? $now->month);
        $year = (int) ($this->option('year') ?? $now->year);

        if ($month < 1 || $month > 12) {
            $this->error('Bulan tidak valid.');
            return self::FAILURE;
        }

        $this->info("Generating invoices untuk {$month}/{$year} ...");

        $stats = $service->generateForMonth($month, $year);

        $this->table(
            ['Created', 'Skipped', 'Errors'],
            [[$stats['created'], $stats['skipped'], $stats['errors']]]
        );

        return self::SUCCESS;
    }
}
