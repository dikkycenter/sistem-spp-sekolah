# AI AGENT PROGRESS STATE

## COMPLETED STEPS

### ✅ Step 1: Docker & Environment Setup
- `docker-compose.yml` — services: app (PHP 8.3), nginx, postgres 16, redis 7, scheduler (loop), queue worker
- `Dockerfile` — PHP 8.3-fpm-alpine + ext (pdo_pgsql, gd, zip, intl, redis, opcache) + composer
- `docker/nginx/default.conf` — nginx vhost → php-fpm:9000
- `docker/php/php.ini` — timezone Asia/Jakarta, upload 20M, opcache
- `.env.example` — pgsql + redis + FILESYSTEM_DISK toggle (local ↔ s3/R2)
- `composer.json` — Laravel 13 + Filament 5 + Livewire 4 + Spatie MediaLibrary + barryvdh/laravel-dompdf + predis

### ✅ Step 2: Migrations + Models
- Migrations: users, sessions, school_profiles, academic_years, parents_data, students, student_enrollments, discounts, invoices, payments, payment_allocations
- Models: `SchoolProfile` (HasMedia, `singleFile` logo), `AcademicYear` (auto-deactivate others on save), `ParentModel`, `Student`, `StudentEnrollment` (pivot Student×AcademicYear with `base_spp_amount`), `Discount` (`isActiveFor($month, $year)`), `Invoice` (uses `InvoiceStatus` enum, `recalculate()` from allocations), `Payment` (HasMedia `proof` collection on configurable disk), `PaymentAllocation` (FIFO pivot), `User` (Filament `canAccessPanel`)
- Native Enums: `InvoiceStatus` (Unpaid/Partial/Paid, implements HasColor+HasLabel), `PaymentMethod` (Cash/Transfer)

### ✅ Step 3: Auto-Generate Command
- `App\Services\InvoiceGeneratorService::generateForMonth($month, $year)` — factors in **active Discounts** for that specific month/year (Case 3)
- `App\Console\Commands\GenerateMonthlyInvoicesCommand` (signature `spp:generate-invoices --month --year`)
- `routes/console.php` uses native Laravel 13 `Schedule::` facade: monthly on day 1 @ 00:00 Asia/Jakarta, `withoutOverlapping()->onOneServer()`

### ✅ Step 4: Payment Logic (Case 1 FIFO + Case 2 Partial)
- `App\Services\PaymentService::recordPayment()` wraps everything in `DB::transaction` + `lockForUpdate()`
- FIFO: fetches all invoices for student with `remaining_balance > 0` ordered by year/month/id, applies `min(remaining, invoice.remaining_balance)` to each until amount exhausted
- Partial: after each allocation, calls `Invoice::recalculate()` which recomputes `total_paid`, `remaining_balance`, and sets status to `Partial` when paid > 0 but balance > 0
- Overpayment: any leftover is logged into payment `notes` as "Kelebihan bayar: Rp X"
- Auto receipt number: `RCP/YYYY/MM/000001`

### ✅ Step 5: Filament Resources
- `AdminPanelProvider` (path=/admin, brand=SPP Sekolah, indigo palette)
- Resources: `SchoolProfilePage` (singleton), `AcademicYearResource`, `ParentResource`, `StudentResource` (with 3 RelationManagers: Enrollments, Discounts, Invoices), `InvoiceResource` (with **Pay Action** including `SpatieMediaLibraryFileUpload` proof, required only when method=Transfer — Case 4), `PaymentResource` (view + Kwitansi PDF button)
- Header action `Generate Invoice Bulan Ini` on invoice list (manual trigger of scheduler command)
- Download actions on Invoice + Payment tables → `route('invoices.pdf')` / `route('payments.pdf')` open in new tab (WhatsApp-friendly)

### ✅ Step 6: PDF Blade Templates
- `resources/views/pdf/invoice.blade.php` — A4, letterhead (logo + name + address + contact from SchoolProfile), meta table, item table with discount line, totals box, **bank info box** (bank name + account number + account name — Case 4 transfer instructions), signature footer
- `resources/views/pdf/receipt.blade.php` — A4, letterhead, allocation table showing each invoice paid via FIFO with "LUNAS" / "Sisa" indicator per row (Case 2 explicit), big "JUMLAH DIBAYAR" box with **terbilang** (via `App\Support\Terbilang` helper), "Catatan Sisa Tagihan" warning box listing all remaining balances per invoice, dual signature footer
- `PdfService` → `barryvdh/laravel-dompdf` A4 portrait, filename uses invoice/receipt number

### ✅ Extras
- `DatabaseSeeder` — creates admin user (admin@sekolah.test / password), sample school profile with bank info, active academic year 2026/2027, sample parent+student+enrollment for testing
- `PdfController` + `routes/web.php` (auth middleware)
- `App\Support\Terbilang` (number → Indonesian words, used in receipt)

## NEXT STEP: NONE — All 6 required steps complete.

## HOW TO RUN
```bash
cd /app/laravel-spp-app
cp .env.example .env
docker compose up -d --build
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan storage:link
```
→ Open http://localhost:8080/admin  (login: admin@sekolah.test / password)

## FILE TREE
```
/app/laravel-spp-app/
├── docker-compose.yml
├── Dockerfile
├── composer.json
├── .env.example
├── README.md
├── docker/{nginx,php}/...
├── app/
│   ├── Enums/{InvoiceStatus,PaymentMethod}.php
│   ├── Models/{SchoolProfile,AcademicYear,ParentModel,Student,
│   │           StudentEnrollment,Discount,Invoice,Payment,
│   │           PaymentAllocation,User}.php
│   ├── Services/{InvoiceGeneratorService,PaymentService,PdfService}.php
│   ├── Console/Commands/GenerateMonthlyInvoicesCommand.php
│   ├── Http/Controllers/{Controller,PdfController}.php
│   ├── Filament/
│   │   ├── Pages/SchoolProfilePage.php
│   │   └── Resources/{Student,Invoice,Payment,AcademicYear,Parent}Resource(+Pages,+RelationManagers)
│   ├── Providers/Filament/AdminPanelProvider.php
│   └── Support/Terbilang.php
├── database/
│   ├── migrations/2026_01_01_00000{0-8}_*.php
│   └── seeders/DatabaseSeeder.php
├── resources/views/
│   ├── pdf/{invoice,receipt}.blade.php
│   └── filament/pages/school-profile.blade.php
└── routes/{web,console}.php
```
