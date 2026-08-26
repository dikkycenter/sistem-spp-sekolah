<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Services\PdfService;

class PdfController extends Controller
{
    public function __construct(protected PdfService $pdf) {}

    public function invoice(Invoice $invoice)
    {
        return $this->pdf->invoice($invoice);
    }

    public function receipt(Payment $payment)
    {
        return $this->pdf->receipt($payment);
    }
}
