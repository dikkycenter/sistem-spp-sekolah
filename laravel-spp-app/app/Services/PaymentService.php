<?php

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * PaymentService menangani:
 *   - Case 1 (FIFO): pembayaran dialokasikan ke invoice tertua yang belum lunas dulu.
 *   - Case 2 (Partial): sisa dana diterapkan sebagian; sisa invoice tersisa tetap dicatat.
 *   - Case 4: metode Transfer WAJIB ada bukti pembayaran (validasi di sisi Filament,
 *             service ini hanya menyimpan payment record + alokasi).
 *
 * Sisa nominal yang tidak terpakai (overpayment) akan disimpan sebagai payment
 * dengan invoice_id = null (deposit / kelebihan bayar untuk bulan depan).
 */
class PaymentService
{
    public function __construct(protected InvoiceGeneratorService $invoiceGenerator)
    {
    }

    /**
     * @param  array  $data  ['student_id', 'amount_paid', 'method', 'payment_date', 'notes', 'recorded_by']
     */
    public function recordPayment(array $data): Payment
    {
        if (! isset($data['student_id'], $data['amount_paid'])) {
            throw new InvalidArgumentException('student_id & amount_paid wajib diisi.');
        }

        $amount = (float) $data['amount_paid'];
        if ($amount <= 0) {
            throw new InvalidArgumentException('Jumlah pembayaran harus lebih dari 0.');
        }

        return DB::transaction(function () use ($data, $amount) {
            $payment = Payment::create([
                'receipt_number' => $this->nextReceiptNumber(),
                'student_id' => $data['student_id'],
                'invoice_id' => null, // akan diisi jika hanya 1 alokasi
                'amount_paid' => $amount,
                'method' => $data['method'] ?? PaymentMethod::Cash->value,
                'payment_date' => $data['payment_date'] ?? now()->toDateString(),
                'notes' => $data['notes'] ?? null,
                'recorded_by' => $data['recorded_by'] ?? auth()->id(),
            ]);

            $this->applyFifoAllocation($payment, $amount);

            return $payment->fresh(['allocations.invoice']);
        });
    }

    /**
     * Alokasi FIFO: ambil semua invoice belum lunas milik student, urutkan
     * (year ASC, month ASC), lalu bayar satu-per-satu sampai amount habis.
     */
    protected function applyFifoAllocation(Payment $payment, float $amount): void
    {
        $remaining = $amount;

        // Ambil semua invoice yg masih ada sisa tagihan, terurut dari yang paling lama
        $invoices = Invoice::query()
            ->where('student_id', $payment->student_id)
            ->where('remaining_balance', '>', 0)
            ->orderBy('year')
            ->orderBy('month')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        foreach ($invoices as $invoice) {
            if ($remaining <= 0) {
                break;
            }

            $applied = min($remaining, (float) $invoice->remaining_balance);

            PaymentAllocation::create([
                'payment_id' => $payment->id,
                'invoice_id' => $invoice->id,
                'amount' => $applied,
            ]);

            // Recalc total_paid, remaining_balance & status
            $invoice->recalculate();

            $remaining -= $applied;
        }

        // Kalau hanya 1 invoice terkena, set invoice_id di payment (untuk kwitansi cepat)
        $allocationCount = $payment->allocations()->count();
        if ($allocationCount === 1) {
            $payment->update([
                'invoice_id' => $payment->allocations()->value('invoice_id'),
            ]);
        }

        // Jika masih ada sisa (overpayment) -> notes
        if ($remaining > 0) {
            $payment->update([
                'notes' => trim(($payment->notes ?? '')."\n[Kelebihan bayar: Rp "
                    .number_format($remaining, 0, ',', '.').']'),
            ]);
        }
    }

    protected function nextReceiptNumber(): string
    {
        $prefix = sprintf('RCP/%04d/%02d/', now()->year, now()->month);
        $last = Payment::where('receipt_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('receipt_number');

        $next = $last
            ? ((int) substr($last, -6)) + 1
            : 1;

        return $prefix.str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }
}
