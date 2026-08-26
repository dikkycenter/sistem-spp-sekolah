<?php

namespace App\Exports;

use App\Models\Payment;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PaymentsExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    public function __construct(
        protected ?int $month = null,
        protected ?int $year = null,
    ) {}

    public function query()
    {
        $q = Payment::query()
            ->with(['student.parent', 'allocations.invoice'])
            ->orderBy('payment_date');

        if ($this->month) {
            $q->whereMonth('payment_date', $this->month);
        }
        if ($this->year) {
            $q->whereYear('payment_date', $this->year);
        }

        return $q;
    }

    public function headings(): array
    {
        return [
            'No. Kwitansi', 'Tanggal', 'NISN', 'Nama Siswa',
            'Orang Tua', 'Metode', 'Nominal', 'Alokasi Invoice', 'Catatan',
        ];
    }

    public function map($payment): array
    {
        $alloc = $payment->allocations->map(fn ($a) =>
            sprintf('%s (Rp %s)', $a->invoice->invoice_number ?? '-', number_format($a->amount, 0, ',', '.'))
        )->implode('; ');

        return [
            $payment->receipt_number,
            $payment->payment_date?->format('d/m/Y'),
            $payment->student->nisn,
            $payment->student->name,
            $payment->student->parent->name ?? '-',
            $payment->method->getLabel(),
            (float) $payment->amount_paid,
            $alloc,
            $payment->notes,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => [
                'fillType' => 'solid',
                'startColor' => ['rgb' => 'D1FAE5'],
            ]],
        ];
    }

    public function title(): string
    {
        $t = 'Pembayaran';
        if ($this->month && $this->year) {
            $t .= sprintf(' %02d-%d', $this->month, $this->year);
        }
        return $t;
    }
}
