<?php

namespace App\Exports;

use App\Models\Invoice;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Export rekap tagihan SPP per bulan/tahun ke Excel.
 * Digunakan untuk laporan yayasan.
 */
class InvoicesExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    public function __construct(
        protected ?int $month = null,
        protected ?int $year = null,
        protected ?string $status = null,
    ) {}

    public function query()
    {
        $q = Invoice::query()
            ->with(['student.parent', 'academicYear', 'enrollment'])
            ->orderBy('year')->orderBy('month')->orderBy('id');

        if ($this->month) {
            $q->where('month', $this->month);
        }
        if ($this->year) {
            $q->where('year', $this->year);
        }
        if ($this->status) {
            $q->where('status', $this->status);
        }

        return $q;
    }

    public function headings(): array
    {
        return [
            'No. Invoice', 'Periode', 'Tahun Ajaran', 'NISN', 'Nama Siswa',
            'Kelas', 'Orang Tua', 'No. WA', 'Base SPP', 'Diskon',
            'Total Tagihan', 'Dibayar', 'Sisa', 'Status', 'Dibuat',
        ];
    }

    public function map($invoice): array
    {
        return [
            $invoice->invoice_number,
            sprintf('%02d/%d', $invoice->month, $invoice->year),
            $invoice->academicYear->name ?? '-',
            $invoice->student->nisn,
            $invoice->student->name,
            $invoice->enrollment->class_name ?? '-',
            $invoice->student->parent->name ?? '-',
            $invoice->student->parent->phone ?? '-',
            (float) $invoice->base_amount,
            (float) $invoice->discount_amount,
            (float) $invoice->total_due,
            (float) $invoice->total_paid,
            (float) $invoice->remaining_balance,
            $invoice->status->getLabel(),
            $invoice->created_at?->format('d/m/Y H:i'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => [
                'fillType' => 'solid',
                'startColor' => ['rgb' => 'E0E7FF'],
            ]],
        ];
    }

    public function title(): string
    {
        $t = 'Tagihan SPP';
        if ($this->month && $this->year) {
            $t .= sprintf(' %02d-%d', $this->month, $this->year);
        }
        return $t;
    }
}
