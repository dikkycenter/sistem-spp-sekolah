<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Services\PdfService;
use Illuminate\Http\Request;

/**
 * Portal orang tua (public, tanpa akun).
 * Login pakai NISN + tanggal lahir siswa.
 * Session-based: setelah verifikasi, student_id disimpan di session.
 */
class ParentPortalController extends Controller
{
    public function __construct(protected PdfService $pdf) {}

    /** Halaman lookup awal (form NISN + tanggal lahir). */
    public function showLookup()
    {
        return view('parent-portal.lookup');
    }

    /** Verifikasi kredensial siswa. */
    public function verify(Request $request)
    {
        $data = $request->validate([
            'nisn' => 'required|string',
            'birth_date' => 'required|date',
        ]);

        $student = Student::query()
            ->where('nisn', $data['nisn'])
            ->whereDate('birth_date', $data['birth_date'])
            ->first();

        if (! $student) {
            return back()->withErrors([
                'nisn' => 'NISN atau tanggal lahir tidak cocok. Silakan cek kembali.',
            ])->withInput();
        }

        session(['portal_student_id' => $student->id]);

        return redirect()->route('portal.dashboard');
    }

    /** Dashboard: list invoice & pembayaran siswa. */
    public function dashboard()
    {
        $studentId = session('portal_student_id');
        if (! $studentId) {
            return redirect()->route('portal.lookup');
        }

        $student = Student::with([
            'parent',
            'invoices' => fn ($q) => $q->orderByDesc('year')->orderByDesc('month'),
            'invoices.enrollment',
            'payments' => fn ($q) => $q->latest('payment_date')->limit(10),
        ])->find($studentId);

        if (! $student) {
            session()->forget('portal_student_id');
            return redirect()->route('portal.lookup');
        }

        $totalTunggakan = $student->invoices->sum('remaining_balance');
        $school = \App\Models\SchoolProfile::current();

        return view('parent-portal.dashboard', compact('student', 'totalTunggakan', 'school'));
    }

    /** Download invoice PDF dari portal (validasi ownership). */
    public function downloadInvoice(\App\Models\Invoice $invoice)
    {
        $studentId = session('portal_student_id');
        abort_unless($studentId && $invoice->student_id === $studentId, 403);

        return $this->pdf->invoice($invoice);
    }

    /** Logout dari portal. */
    public function logout()
    {
        session()->forget('portal_student_id');
        return redirect()->route('portal.lookup');
    }
}
