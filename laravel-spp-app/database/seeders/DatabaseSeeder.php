<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\ParentModel;
use App\Models\SchoolProfile;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin & Bendahara user
        User::updateOrCreate(
            ['email' => 'admin@sekolah.test'],
            ['name' => 'Admin Sekolah', 'password' => Hash::make('password'), 'role' => \App\Enums\UserRole::Admin]
        );
        User::updateOrCreate(
            ['email' => 'bendahara@sekolah.test'],
            ['name' => 'Bendahara Sekolah', 'password' => Hash::make('password'), 'role' => \App\Enums\UserRole::Bendahara]
        );

        // 2. School profile
        SchoolProfile::query()->updateOrCreate(['id' => 1], [
            'name' => 'SMP Harapan Bangsa',
            'address' => 'Jl. Pendidikan No. 1, Jakarta Selatan',
            'phone' => '021-1234567',
            'email' => 'info@smpharapan.sch.id',
            'website' => 'www.smpharapan.sch.id',
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_name' => 'Yayasan Harapan Bangsa',
            'headmaster_name' => 'Drs. Budi Santoso, M.Pd',
        ]);

        // 3. Academic year
        $ta = AcademicYear::firstOrCreate(
            ['name' => '2026/2027'],
            ['start_date' => '2026-07-01', 'end_date' => '2027-06-30', 'is_active' => true]
        );

        // 4. Sample parent + student + enrollment
        $parent = ParentModel::firstOrCreate(
            ['name' => 'Bapak Ahmad'],
            ['phone' => '081234567890', 'address' => 'Jl. Melati No. 5']
        );

        $student = Student::firstOrCreate(
            ['nisn' => '0071234567'],
            ['name' => 'Rizky Ahmad', 'gender' => 'L', 'parent_id' => $parent->id]
        );

        StudentEnrollment::firstOrCreate(
            ['student_id' => $student->id, 'academic_year_id' => $ta->id],
            ['grade' => '7', 'class_name' => '7A', 'base_spp_amount' => 500000, 'is_active' => true]
        );
    }
}
