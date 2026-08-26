<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tagihan {{ $student->name }} - {{ $school->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-50">
    <header class="bg-white border-b border-slate-200">
        <div class="max-w-4xl mx-auto px-4 py-4 flex items-center justify-between">
            <div>
                <div class="text-lg font-bold text-slate-800">{{ $school->name }}</div>
                <div class="text-xs text-slate-500">Portal Orang Tua</div>
            </div>
            <form action="{{ route('portal.logout') }}" method="POST">
                @csrf
                <button class="text-sm text-red-600 hover:text-red-700 font-medium">Keluar</button>
            </form>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 py-6 space-y-6">

        <!-- Info Siswa -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="text-xs text-slate-500 uppercase tracking-wide">Data Siswa</div>
            <h1 class="text-2xl font-bold text-slate-800 mt-1">{{ $student->name }}</h1>
            <div class="mt-2 grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div>
                    <div class="text-slate-500">NISN</div>
                    <div class="font-semibold">{{ $student->nisn }}</div>
                </div>
                <div>
                    <div class="text-slate-500">Kelas</div>
                    <div class="font-semibold">{{ optional($student->activeEnrollment())->class_name ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-slate-500">Orang Tua</div>
                    <div class="font-semibold">{{ $student->parent->name ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-slate-500">Total Tunggakan</div>
                    <div class="font-bold {{ $totalTunggakan > 0 ? 'text-red-600' : 'text-emerald-600' }}">
                        Rp {{ number_format($totalTunggakan, 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Rekening Sekolah -->
        @if($totalTunggakan > 0)
        <div class="bg-indigo-50 border-l-4 border-indigo-500 rounded-lg p-5">
            <div class="text-xs font-semibold text-indigo-700 uppercase mb-1">Transfer ke Rekening</div>
            <div class="text-sm text-slate-800">
                <strong>{{ $school->bank_name }}</strong> - {{ $school->bank_account_number }}<br>
                a.n. <strong>{{ $school->bank_account_name }}</strong>
            </div>
        </div>
        @endif

        <!-- List Tagihan -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200">
                <h2 class="font-semibold text-slate-800">Riwayat Tagihan</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-xs text-slate-600 uppercase">
                        <tr>
                            <th class="px-6 py-3 text-left">Periode</th>
                            <th class="px-6 py-3 text-left">No. Invoice</th>
                            <th class="px-6 py-3 text-right">Total</th>
                            <th class="px-6 py-3 text-right">Dibayar</th>
                            <th class="px-6 py-3 text-right">Sisa</th>
                            <th class="px-6 py-3 text-center">Status</th>
                            <th class="px-6 py-3 text-center">PDF</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($student->invoices as $inv)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-3 font-medium">{{ $inv->month_name }} {{ $inv->year }}</td>
                                <td class="px-6 py-3 text-slate-500 text-xs">{{ $inv->invoice_number }}</td>
                                <td class="px-6 py-3 text-right">Rp {{ number_format($inv->total_due, 0, ',', '.') }}</td>
                                <td class="px-6 py-3 text-right text-emerald-600">Rp {{ number_format($inv->total_paid, 0, ',', '.') }}</td>
                                <td class="px-6 py-3 text-right {{ $inv->remaining_balance > 0 ? 'text-red-600 font-semibold' : 'text-slate-400' }}">
                                    Rp {{ number_format($inv->remaining_balance, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-3 text-center">
                                    @php
                                        $badge = match($inv->status->value) {
                                            'paid' => 'bg-emerald-100 text-emerald-700',
                                            'partial' => 'bg-amber-100 text-amber-700',
                                            default => 'bg-red-100 text-red-700',
                                        };
                                    @endphp
                                    <span class="inline-block px-2.5 py-1 rounded-full text-xs font-medium {{ $badge }}">
                                        {{ $inv->status->getLabel() }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-center">
                                    <a href="{{ route('portal.invoice.pdf', $inv) }}" target="_blank"
                                        class="text-indigo-600 hover:text-indigo-800 font-medium">Download</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-slate-400">Belum ada tagihan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</body>
</html>
