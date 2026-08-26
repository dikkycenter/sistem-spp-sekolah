<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

/**
 * Line chart pemasukan 6 bulan terakhir.
 */
class PemasukanChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Pemasukan 6 Bulan Terakhir';
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $labels = [];
        $data = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $labels[] = $month->translatedFormat('M Y');

            $total = (float) Payment::whereYear('payment_date', $month->year)
                ->whereMonth('payment_date', $month->month)
                ->sum('amount_paid');

            $data[] = $total;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pemasukan SPP (Rp)',
                    'data' => $data,
                    'borderColor' => '#059669',
                    'backgroundColor' => 'rgba(5, 150, 105, 0.1)',
                    'fill' => true,
                    'tension' => 0.35,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
