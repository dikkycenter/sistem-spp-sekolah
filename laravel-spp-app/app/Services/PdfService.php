<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\SchoolProfile;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

/**
 * Generator PDF Invoice & Kwitansi (A4) dengan letterhead dari SchoolProfile.
 */
class PdfService
{
    public function invoice(Invoice $invoice): Response
    {
        $invoice->load(['student.parent', 'academicYear', 'enrollment', 'allocations.payment']);
        $school = SchoolProfile::current();

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'school' => $school,
        ])->setPaper('a4', 'portrait');

        $filename = 'Invoice-'.str_replace('/', '_', $invoice->invoice_number).'.pdf';

        return $pdf->download($filename);
    }

    public function receipt(Payment $payment): Response
    {
        $payment->load(['student.parent', 'allocations.invoice', 'invoice']);
        $school = SchoolProfile::current();

        $pdf = Pdf::loadView('pdf.receipt', [
            'payment' => $payment,
            'school' => $school,
        ])->setPaper('a4', 'portrait');

        $filename = 'Kwitansi-'.str_replace('/', '_', $payment->receipt_number).'.pdf';

        return $pdf->download($filename);
    }
}
