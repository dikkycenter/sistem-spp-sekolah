# PRD - SPP Payment System (Laravel/Filament Reference Code)

## Problem Statement
Build a School Tuition Fee (SPP) Payment Web App for SD/SMP/SMA using Laravel 13, Filament 5, PHP 8.3, PostgreSQL 16, Redis, Docker, Spatie Media Library, barryvdh/laravel-dompdf.

## User Decision
User chose **"Hanya hasilkan kode Laravel/Filament sebagai file teks"** — no runtime in this Emergent environment. Deliverable is a full production-ready Laravel project at `/app/laravel-spp-app/` that the user copies to their own Docker/PHP host.

## What's Implemented (2026-02)
1. **Docker stack**: `docker-compose.yml` (app php-fpm, nginx, postgres 16, redis 7, scheduler loop, queue worker) + `Dockerfile` (PHP 8.3-alpine, pdo_pgsql, gd, redis, opcache)
2. **10 migrations + 10 models** with relationships (School Profile, Academic Year, Parent, Student, Enrollment pivot, Discount, Invoice, Payment, PaymentAllocation, User) + native PHP Enums (`InvoiceStatus`, `PaymentMethod`)
3. **Auto-generate Command** (`spp:generate-invoices`) scheduled monthly-on-day-1 @ 00:00 Asia/Jakarta via native `Schedule::` facade in `routes/console.php`. Handles discounts per month/year.
4. **PaymentService** with FIFO allocation (`lockForUpdate`, ordered by year/month/id) + partial support via `Invoice::recalculate()` + overpayment logged in notes
5. **Filament resources**: SchoolProfile singleton page, AcademicYear, Parent, Student (with 3 RelationManagers), Invoice (with Pay action using `SpatieMediaLibraryFileUpload` — required only when method=Transfer), Payment
6. **PDF templates (A4)**: `invoice.blade.php` with letterhead + bank info box for transfer; `receipt.blade.php` with per-invoice FIFO allocation table, terbilang, remaining-balance warnings
7. **Extras**: DatabaseSeeder (admin@sekolah.test / password), `Terbilang` helper, PdfController+routes

## Business Cases Coverage
- Case 1 (FIFO Arrears): `PaymentService::applyFifoAllocation()` ✓
- Case 2 (Partial): `Invoice::recalculate()` sets `Partial` status; receipt PDF shows "JUMLAH DIBAYAR" + remaining balance box ✓
- Case 3 (Discount per month/year): `InvoiceGeneratorService::calculateDiscount()` + `Discount::isActiveFor()` ✓
- Case 4 (Transfer requires proof + invoice shows bank): SpatieMediaLibraryFileUpload conditional `required()` + PDF bank-info block ✓

## Not Implemented / Deferred
- Composer.lock, actual Laravel skeleton files (public/index.php, bootstrap/, config/*) — user must scaffold with `composer create-project laravel/laravel` and drop these files in, OR run `composer install` inside container which will pull vendor + generate autoloader
- Test suite (PHPUnit)
- Role-based permissions (currently all users can access panel)
- Email notifications for overdue invoices
- Public parent-facing portal

## Backlog (P1)
- Dashboard widgets: total tunggakan, pemasukan bulan ini, chart
- Bulk invoice export
- WhatsApp gateway integration (auto-send invoice PDF)
- Multi-role (Admin, Bendahara, Kepala Sekolah)

## File Tree
See `/app/laravel-spp-app/PROGRESS.md` for full structure.
