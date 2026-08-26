<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Invoice {{ $invoice->invoice_number }}</title>
<style>
    @page { size: A4 portrait; margin: 20mm 15mm; }
    * { box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
    .letterhead { border-bottom: 3px solid #1e3a8a; padding-bottom: 10px; margin-bottom: 20px; }
    .letterhead table { width: 100%; }
    .letterhead .logo { width: 80px; vertical-align: top; }
    .letterhead .logo img { max-width: 75px; max-height: 75px; }
    .letterhead .school-name { font-size: 20px; font-weight: bold; color: #1e3a8a; letter-spacing: 0.5px; }
    .letterhead .school-meta { font-size: 10px; color: #555; line-height: 1.4; }
    h1.title { text-align: center; font-size: 16px; margin: 15px 0; letter-spacing: 4px; }
    .meta-table { width: 100%; margin-bottom: 15px; }
    .meta-table td { padding: 3px 0; }
    .meta-table .label { width: 130px; font-weight: 600; }
    table.items { width: 100%; border-collapse: collapse; margin-top: 15px; }
    table.items th, table.items td { border: 1px solid #999; padding: 8px; text-align: left; }
    table.items th { background: #f1f5f9; font-weight: 600; }
    table.items td.num { text-align: right; }
    .totals { margin-top: 10px; float: right; width: 45%; }
    .totals table { width: 100%; }
    .totals td { padding: 5px 8px; }
    .totals tr.total td { font-weight: bold; border-top: 2px solid #333; font-size: 13px; color: #1e3a8a; }
    .bank-info { clear: both; margin-top: 40px; padding: 12px; background: #f8fafc;
                 border-left: 4px solid #1e3a8a; font-size: 10.5px; }
    .bank-info strong { color: #1e3a8a; }
    .footer-sign { margin-top: 60px; text-align: right; padding-right: 30px; font-size: 11px; }
    .footer-sign .sign-space { height: 60px; }
    .status-badge { display: inline-block; padding: 3px 10px; border-radius: 4px;
                    font-size: 10px; font-weight: bold; color: white; }
    .status-unpaid { background: #dc2626; }
    .status-partial { background: #f59e0b; }
    .status-paid { background: #16a34a; }
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
                    @if($school->website) | Web: {{ $school->website }} @endif
                </div>
            </td>
        </tr>
    </table>
</div>

<h1 class="title">TAGIHAN SPP</h1>

<table class="meta-table">
    <tr>
        <td class="label">No. Invoice</td>
        <td>: <strong>{{ $invoice->invoice_number }}</strong></td>
        <td class="label">Status</td>
        <td>:
            <span class="status-badge status-{{ $invoice->status->value }}">
                {{ $invoice->status->getLabel() }}
            </span>
        </td>
    </tr>
    <tr>
        <td class="label">Nama Siswa</td>
        <td>: {{ $invoice->student->name }}</td>
        <td class="label">NISN</td>
        <td>: {{ $invoice->student->nisn }}</td>
    </tr>
    <tr>
        <td class="label">Kelas</td>
        <td>: {{ $invoice->enrollment->class_name ?? '-' }}</td>
        <td class="label">Tahun Ajaran</td>
        <td>: {{ $invoice->academicYear->name }}</td>
    </tr>
    <tr>
        <td class="label">Periode Tagihan</td>
        <td>: {{ $invoice->month_name }} {{ $invoice->year }}</td>
        <td class="label">Jatuh Tempo</td>
        <td>: {{ optional($invoice->due_date)->format('d M Y') ?? '-' }}</td>
    </tr>
    <tr>
        <td class="label">Orang Tua/Wali</td>
        <td>: {{ $invoice->student->parent->name ?? '-' }}</td>
        <td class="label">WhatsApp</td>
        <td>: {{ $invoice->student->parent->phone ?? '-' }}</td>
    </tr>
</table>

<table class="items">
    <thead>
    <tr>
        <th style="width:40px">No</th>
        <th>Uraian</th>
        <th style="width:150px" class="num">Jumlah (Rp)</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td>1</td>
        <td>SPP {{ $invoice->month_name }} {{ $invoice->year }} - Kelas {{ $invoice->enrollment->class_name ?? '' }}</td>
        <td class="num">{{ number_format($invoice->base_amount, 0, ',', '.') }}</td>
    </tr>
    @if($invoice->discount_amount > 0)
    <tr>
        <td>2</td>
        <td>Potongan / Beasiswa</td>
        <td class="num">- {{ number_format($invoice->discount_amount, 0, ',', '.') }}</td>
    </tr>
    @endif
    </tbody>
</table>

<div class="totals">
    <table>
        <tr>
            <td>Subtotal</td>
            <td class="num" style="text-align:right">Rp {{ number_format($invoice->base_amount, 0, ',', '.') }}</td>
        </tr>
        @if($invoice->discount_amount > 0)
        <tr>
            <td>Diskon</td>
            <td class="num" style="text-align:right">Rp {{ number_format($invoice->discount_amount, 0, ',', '.') }}</td>
        </tr>
        @endif
        <tr>
            <td>Sudah Dibayar</td>
            <td class="num" style="text-align:right">Rp {{ number_format($invoice->total_paid, 0, ',', '.') }}</td>
        </tr>
        <tr class="total">
            <td>SISA TAGIHAN</td>
            <td class="num" style="text-align:right">Rp {{ number_format($invoice->remaining_balance, 0, ',', '.') }}</td>
        </tr>
    </table>
</div>

<div style="clear:both"></div>

<div class="bank-info">
    <strong>PEMBAYARAN VIA TRANSFER</strong><br>
    Silakan transfer ke rekening berikut, lalu upload bukti transfer ke admin/tata usaha:<br><br>
    <strong>Bank:</strong> {{ $school->bank_name ?? '-' }}<br>
    <strong>No. Rekening:</strong> {{ $school->bank_account_number ?? '-' }}<br>
    <strong>Atas Nama:</strong> {{ $school->bank_account_name ?? '-' }}<br><br>
    <em>Pembayaran tunai dapat dilakukan langsung di kantor Tata Usaha sekolah.</em>
</div>

<div class="footer-sign">
    <div>{{ now()->translatedFormat('d F Y') }}</div>
    <div>Tata Usaha,</div>
    <div class="sign-space"></div>
    <div><strong>_________________________</strong></div>
</div>

</body>
</html>
