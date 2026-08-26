<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique(); // ex: INV/2026/03/000001
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years');
            $table->foreignId('student_enrollment_id')->constrained('student_enrollments');
            $table->unsignedTinyInteger('month');   // 1-12
            $table->unsignedSmallInteger('year');
            $table->decimal('base_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total_due', 12, 2)->default(0);       // base - discount
            $table->decimal('total_paid', 12, 2)->default(0);
            $table->decimal('remaining_balance', 12, 2)->default(0);
            $table->string('status')->default('unpaid'); // enum InvoiceStatus
            $table->date('due_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'month', 'year']);
            $table->index(['status', 'year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
