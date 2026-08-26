<?php

namespace App\Filament\Widgets;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Widget stat kecil di dashboard: pemasukan bulan ini, total tunggakan,
 * siswa aktif, invoice belum lunas.
 */
class FinancialStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int|string|array $columnSpan = 'full';
    protected static ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $now = Carbon::now();

        $pemasukanBulanIni = (float) Payment::query()
            ->whereMonth('payment_date', $now->month)
            ->whereYear('payment_date', $now->year)
            ->sum('amount_paid');

        $totalTunggakan = (float) Invoice::query()
            ->whereIn('status', [InvoiceStatus::Unpaid->value, InvoiceStatus::Partial->value])
            ->sum('remaining_balance');

        $siswaAktif = Student::query()->where('is_active', true)->count();

        $invoicePending = Invoice::query()
            ->whereIn('status', [InvoiceStatus::Unpaid->value, InvoiceStatus::Partial->value])
            ->count();

        // Chart pemasukan 7 hari terakhir
        $chartData = collect(range(6, 0))->map(function ($daysAgo) {
            $date = Carbon::now()->subDays($daysAgo);
            return (float) Payment::whereDate('payment_date', $date)->sum('amount_paid');
        })->toArray();

        return [
            Stat::make('Pemasukan Bulan Ini', 'Rp '.number_format($pemasukanBulanIni, 0, ',', '.'))
                ->description($now->translatedFormat('F Y'))
                ->descriptionIcon('heroicon-o-arrow-trending-up')
                ->color('success')
                ->chart($chartData),

            Stat::make('Total Tunggakan', 'Rp '.number_format($totalTunggakan, 0, ',', '.'))
                ->description($invoicePending.' invoice belum lunas')
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color($totalTunggakan > 0 ? 'danger' : 'gray'),

            Stat::make('Siswa Aktif', (string) $siswaAktif)
                ->description('Terdaftar di tahun ajaran aktif')
                ->descriptionIcon('heroicon-o-academic-cap')
                ->color('primary'),
        ];
    }
}
