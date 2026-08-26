<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('name'); // ex: "Juara Olimpiade", "Anak Yatim"
            $table->decimal('amount', 12, 2)->default(0); // nominal potongan per bulan
            $table->unsignedTinyInteger('start_month'); // 1-12
            $table->unsignedSmallInteger('start_year');
            $table->unsignedTinyInteger('end_month');   // 1-12
            $table->unsignedSmallInteger('end_year');
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
