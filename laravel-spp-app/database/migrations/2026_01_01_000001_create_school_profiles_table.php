<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name');            // Nama Sekolah
            $table->text('address');           // Alamat
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('logo_path')->nullable(); // fallback jika tidak pakai media library
            $table->string('bank_name')->nullable();          // Contoh: BCA / BRI
            $table->string('bank_account_name')->nullable(); // Nama pemilik rekening
            $table->string('bank_account_number')->nullable();
            $table->string('headmaster_name')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_profiles');
    }
};
