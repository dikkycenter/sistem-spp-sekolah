<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Kwitansi {{ $payment->receipt_number }}</title>
<style>
    @page { size: A4 portrait; margin: 20mm 15mm; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
    .letterhead { border-bottom: 3px solid #059669; padding-bottom: 10px; margin-bottom: 20px; }
    .letterhead table { width: 100%; }
    .letterhead .logo { width: 80px; vertical-align: top; }
    .letterhead .logo img { max-width: 75px; max-height: 75px; }
    .letterhead .school-name { font-size: 20px; font-weight: bold; color: #059669; }
    .letterhead .school-meta { font-size: 10px; color: #555; line-height: 1.4; }
    h1.title { text-align: center; font-size: 18px; margin: 15px 0 5px; letter-spacing: 4px; color: #059669; }
    .receipt-no { text-align: center; font-size: 11px; color: #666; margin-bottom: 20px; }
    .meta-table { width: 100%; margin-bottom: 15px; }
    .meta-table td { padding: 3px 0; }
    .meta-table .label { width: 130px; font-weight: 600; }
    table.alloc { width: 100%; border-collapse: collapse; margin-top: 10px; }
    table.alloc th, table.alloc td { border: 1px solid #999; padding: 7px; }
    table.alloc th { background: #ecfdf5; font-weight: 600; }
    table.alloc td.num { text-align: right; }
    .amount-paid-box { margin-top: 20px; padding: 15px; background: #f0fdf4;
                       border: 2px solid #059669; border-radius: 4px; text-align: center; }
    .amount-paid-box .amount { font-size: 22px; font-weight: bold; color: #059669; }
    .amount-paid-box .label { font-size: 10px; color: #666; letter-spacing: 1px; }
    .remaining-warn { margin-top: 12px; padding: 10px; background: #fef3c7;
                      border-left: 4px solid #f59e0b; font-size: 10.5px; }
    .footer-sign { margin-top: 50px; }
    .footer-sign table { width: 100%; }
    .footer-sign .sign-space { height: 60px; }
    .terbilang { margin-top: 10px; font-style: italic; }
</style>
</head>
<body>

<div class="letterhead">
    <table>
        <tr>
            <td class="logo">
                @if($school->logo_url)
                    <img src="{{ $school->logo_url }}" alt="Logo">
                @endif
            </td>
            <td>
                <div class="school-name">{{ strtoupper($school->name) }}</div>
                <div class="school-meta">
                    {{ $school->address }}<br>
                    @if($school->phone) Telp: {{ $school->phone }} @endif
                    @if($school->email) | Email: {{ $school->email }} @endif
                </div>
            </td>
        </tr>
    </table>
</div>

<h1 class="title">KWITANSI PEMBAYARAN</h1>
<div class="receipt-no">No: <strong>{{ $payment->receipt_number }}</strong></div>

<table class="meta-table">
    <tr>
        <td class="label">Telah diterima dari</td>
        <td>: <strong>{{ $payment->student->parent->name ?? $payment->student->name }}</strong></td>
    </tr>
    <tr>
        <td class="label">Untuk Siswa</td>
        <td>: {{ $payment->student->name }} (NISN: {{ $payment->student->nisn }})</td>
    </tr>
    <tr>
        <td class="label">Metode Pembayaran</td>
        <td>: {{ $payment->method->getLabel() }}</td>
    </tr>
    <tr>
        <td class="label">Tanggal Bayar</td>
        <td>: {{ $payment->payment_date->translatedFormat('d F Y') }}</td>
    </tr>
</table>

<table class="alloc">
    <thead>
    <tr>
        <th style="width:35px">No</th>
        <th>Untuk Pembayaran (Alokasi FIFO)</th>
        <th style="width:130px" class="num">Nominal (Rp)</th>
    </tr>
    </thead>
    <tbody>
    @php $i = 1; $totalAllocated = 0; @endphp
    @forelse($payment->allocations as $alloc)
        @php $totalAllocated += (float) $alloc->amount; @endphp
        <tr>
            <td>{{ $i++ }}</td>
            <td>
                SPP {{ $alloc->invoice->month_name }} {{ $alloc->invoice->year }}
                <span style="color:#666">({{ $alloc->invoice->invoice_number }})</span>
                @if($alloc->invoice->remaining_balance > 0)
                    <br><span style="color:#f59e0b; font-size:10px">
                        Sisa tagihan bulan ini: Rp {{ number_format($alloc->invoice->remaining_balance, 0, ',', '.') }}
                    </span>
                @else
                    <br><span style="color:#059669; font-size:10px">✓ LUNAS</span>
                @endif
            </td>
            <td class="num">{{ number_format($alloc->amount, 0, ',', '.') }}</td>
        </tr>
    @empty
        <tr><td colspan="3" style="text-align:center; color:#888">Belum ada alokasi</td></tr>
    @endforelse
    </tbody>
</table>

<div class="amount-paid-box">
    <div class="label">JUMLAH DIBAYAR</div>
    <div class="amount">Rp {{ number_format($payment->amount_paid, 0, ',', '.') }}</div>
    <div class="terbilang">
        Terbilang: <em>{{ ucwords(\App\Support\Terbilang::make((int) $payment->amount_paid)) }} Rupiah</em>
    </div>
</div>

@php
    $stillOwed = $payment->allocations
        ->filter(fn($a) => $a->invoice && $a->invoice->remaining_balance > 0);
@endphp

@if($stillOwed->count() > 0)
<div class="remaining-warn">
    <strong>Catatan Sisa Tagihan:</strong><br>
    @foreach($stillOwed as $a)
        - {{ $a->invoice->month_name }} {{ $a->invoice->year }}: <strong>Rp {{ number_format($a->invoice->remaining_balance, 0, ',', '.') }}</strong><br>
    @endforeach
</div>
@endif

@if($payment->notes)
<p style="margin-top: 15px; font-size: 10.5px;"><strong>Catatan:</strong> {{ $payment->notes }}</p>
@endif

<div class="footer-sign">
    <table>
        <tr>
            <td style="width:50%">
                <em>Penerima,</em>
                <div class="sign-space"></div>
                <strong>_________________________</strong><br>
                <span style="font-size:10px">Tata Usaha</span>
            </td>
            <td style="width:50%; text-align:right">
                {{ $payment->payment_date->translatedFormat('d F Y') }}<br>
                <em>Pembayar,</em>
                <div class="sign-space"></div>
                <strong>_________________________</strong>
            </td>
        </tr>
    </table>
</div>

</body>
</html>
