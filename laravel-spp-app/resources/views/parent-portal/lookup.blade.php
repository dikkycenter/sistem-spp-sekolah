<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Orang Tua - Cek Tagihan SPP</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-50 to-indigo-50 flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-8">
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-indigo-100 rounded-full mb-3">
                <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-slate-800">Cek Tagihan SPP</h1>
            <p class="text-sm text-slate-500 mt-1">Masukkan data siswa untuk melihat status pembayaran</p>
        </div>

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm mb-4">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('portal.verify') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">NISN Siswa</label>
                <input type="text" name="nisn" value="{{ old('nisn') }}" required
                    class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Tanggal Lahir</label>
                <input type="date" name="birth_date" value="{{ old('birth_date') }}" required
                    class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none">
            </div>
            <button type="submit"
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 rounded-lg transition">
                Lihat Tagihan
            </button>
        </form>

        <p class="text-xs text-center text-slate-400 mt-6">
            Kesulitan akses? Hubungi Tata Usaha sekolah.
        </p>
    </div>
</body>
</html>
