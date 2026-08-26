<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Models\AcademicYear;
use App\Models\Discount;
use App\Models\Invoice;
use App\Models\StudentEnrollment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service untuk generate invoice SPP bulanan secara otomatis.
 * Case 3 (Discount) di-handle di sini: cek diskon aktif untuk bulan/tahun target.
 */
class InvoiceGeneratorService
{
    /**
     * Generate invoice untuk semua enrollment aktif pada bulan & tahun yang diberikan.
     *
     * @return array{created:int, skipped:int, errors:int}
     */
    public function generateForMonth(int $month, int $year): array
    {
        $stats = ['created' => 0, 'skipped' => 0, 'errors' => 0];

        $activeYear = AcademicYear::active();
        if (! $activeYear) {
            Log::warning('[InvoiceGenerator] Tidak ada tahun ajaran aktif.');
            return $stats;
        }

        $enrollments = StudentEnrollment::query()
            ->where('academic_year_id', $activeYear->id)
            ->where('is_active', true)
            ->with('student')
            ->get();

        foreach ($enrollments as $enrollment) {
            if (! $enrollment->student || ! $enrollment->student->is_active) {
                $stats['skipped']++;
                continue;
            }

            // Skip jika invoice sudah ada untuk siswa/bulan/tahun tsb
            $exists = Invoice::query()
                ->where('student_id', $enrollment->student_id)
                ->where('month', $month)
                ->where('year', $year)
                ->exists();

            if ($exists) {
                $stats['skipped']++;
                continue;
            }

            try {
                DB::transaction(function () use ($enrollment, $month, $year, $activeYear) {
                    $baseAmount = (float) $enrollment->base_spp_amount;
                    $discountAmount = $this->calculateDiscount(
                        $enrollment->student_id, $month, $year
                    );
                    // Diskon tidak boleh melebihi base amount
                    $discountAmount = min($discountAmount, $baseAmount);
                    $totalDue = max(0, $baseAmount - $discountAmount);

                    Invoice::create([
                        'invoice_number' => $this->nextInvoiceNumber($year, $month),
                        'student_id' => $enrollment->student_id,
                        'academic_year_id' => $activeYear->id,
                        'student_enrollment_id' => $enrollment->id,
                        'month' => $month,
                        'year' => $year,
                        'base_amount' => $baseAmount,
                        'discount_amount' => $discountAmount,
                        'total_due' => $totalDue,
                        'total_paid' => 0,
                        'remaining_balance' => $totalDue,
                        'status' => $totalDue > 0 ? InvoiceStatus::Unpaid : InvoiceStatus::Paid,
                        'due_date' => Carbon::create($year, $month, 10),
                    ]);
                });
                $stats['created']++;
            } catch (\Throwable $e) {
                $stats['errors']++;
                Log::error('[InvoiceGenerator] Gagal generate invoice', [
                    'enrollment_id' => $enrollment->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $stats;
    }

    /**
     * Hitung total diskon aktif untuk siswa pada bulan/tahun tertentu.
     * Bisa ada >1 diskon berlaku bersamaan (dijumlahkan).
     */
    public function calculateDiscount(int $studentId, int $month, int $year): float
    {
        $discounts = Discount::query()
            ->where('student_id', $studentId)
            ->where('is_active', true)
            ->get();

        $total = 0.0;
        foreach ($discounts as $d) {
            if ($d->isActiveFor($month, $year)) {
                $total += (float) $d->amount;
            }
        }

        return $total;
    }

    /**
     * Format: INV/YYYY/MM/000001 (running number per bulan).
     */
    protected function nextInvoiceNumber(int $year, int $month): string
    {
        $prefix = sprintf('INV/%04d/%02d/', $year, $month);
        $last = Invoice::where('invoice_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('invoice_number');

        $next = $last
            ? ((int) substr($last, -6)) + 1
            : 1;

        return $prefix.str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }
}
