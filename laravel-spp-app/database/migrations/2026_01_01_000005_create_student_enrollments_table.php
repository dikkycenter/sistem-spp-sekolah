<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Riwayat Kelas & SPP per Tahun Ajaran.
 * Satu siswa punya banyak baris di sini (setiap tahun ajaran satu baris).
 * Menangani kasus siswa naik/tinggal kelas + SPP berubah tiap TA.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->string('grade');       // ex: "1", "7", "10" (SD/SMP/SMA)
            $table->string('class_name');  // ex: "1A", "7B", "10 IPA 1"
            $table->decimal('base_spp_amount', 12, 2)->default(0);
            $table->boolean('is_active')->default(true); // enrollment aktif (untuk auto-generate)
            $table->timestamps();

            $table->unique(['student_id', 'academic_year_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_enrollments');
    }
};
