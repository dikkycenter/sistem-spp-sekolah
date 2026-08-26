# AI AGENT PROGRESS STATE

## COMPLETED STEPS

### Phase 1 - Core MVP (Steps 1-6)
- Docker stack, migrations, models, InvoiceGeneratorService, PaymentService (FIFO+Partial), Filament resources, PDF Blade templates.
- See git history + earlier version of this doc for details.

### Phase 2 - Add-on Features (2026-02)

#### ✅ 1. Dashboard Widgets
- `app/Filament/Widgets/FinancialStatsWidget.php` — 3 stats: Pemasukan Bulan Ini (dengan sparkline 7 hari), Total Tunggakan, Siswa Aktif; polling 60s
- `app/Filament/Widgets/SiswaTunggakanWidget.php` — Top-10 siswa tunggakan tertinggi (Postgres `HAVING` + `groupBy`) dengan NISN, nama, WA, jumlah invoice belum lunas, total tunggakan
- `app/Filament/Widgets/PemasukanChartWidget.php` — Line chart 6 bulan terakhir
- `AdminPanelProvider` diperbarui: register 3 widget di dashboard

#### ✅ 2. Parent Portal (Public, No Login)
- `app/Http/Controllers/ParentPortalController.php` — lookup (NISN + tanggal lahir) → session-based → dashboard
- `resources/views/parent-portal/lookup.blade.php` — form login sederhana (Tailwind CDN, gradient indigo)
- `resources/views/parent-portal/dashboard.blade.php` — kartu info siswa + rekening sekolah + tabel semua invoice dengan status badge + link download PDF
- Routes prefix `/portal/*` di `routes/web.php`: `portal.lookup`, `portal.verify`, `portal.dashboard`, `portal.invoice.pdf` (ownership check), `portal.logout`
- Landing `/` di-redirect ke portal
- PDF invoice bisa didownload dari portal (ownership divalidasi: `$invoice->student_id === session('portal_student_id')`)

#### ✅ 3. Role Permissions (Admin vs Bendahara)
- `app/Enums/UserRole.php` — enum Admin | Bendahara dengan HasLabel + HasColor
- Migration `2026_02_01_000001_add_role_to_users_table.php` — kolom `role` di tabel users (default: admin)
- `User` model diperbarui: cast `role` ke enum, method `isAdmin()`/`isBendahara()`, `canAccessPanel()` cek keanggotaan role
- `app/Policies/InvoicePolicy.php` — Admin CRUD full, Bendahara view only (tidak boleh update/delete invoice)
- `app/Policies/PaymentPolicy.php` — Admin CRUD full, Bendahara boleh create+view TAPI tidak boleh update/delete payment
- `app/Providers/AuthServiceProvider.php` — register 2 policies
- Header action `generate_now` di InvoiceResource sekarang `visible(fn () => auth()->user()?->isAdmin())`
- Seeder membuat 2 user: `admin@sekolah.test` (Admin) + `bendahara@sekolah.test` (Bendahara), password sama: `password`

#### ✅ 4. Excel Export
- Added `maatwebsite/excel: ^3.1` ke composer.json
- `app/Exports/InvoicesExport.php` — implements FromQuery + WithHeadings + WithMapping + WithStyles + WithTitle + ShouldAutoSize; filter by month/year/status; 15 kolom
- `app/Exports/PaymentsExport.php` — similar, dengan kolom alokasi FIFO (list semua invoice yang dibayar dari 1 payment)
- Header action di `ListInvoices` + `ListPayments` — dialog pilih bulan+tahun → download `.xlsx` (nama file: `Rekap-Tagihan-SPP-MM-YYYY.xlsx` / `Rekap-Pembayaran-MM-YYYY.xlsx`)

## POST-INSTALL STEPS (setelah `composer install`)
```bash
docker compose exec app php artisan migrate            # jalankan migration role
docker compose exec app php artisan db:seed --class=DatabaseSeeder
docker compose exec app php artisan vendor:publish --provider="Maatwebsite\Excel\ExcelServiceProvider" --tag=config
```

## LOGIN CREDENTIALS
- Admin: `admin@sekolah.test` / `password` — full akses (CRUD invoice, hapus payment, generate invoice)
- Bendahara: `bendahara@sekolah.test` / `password` — hanya bisa input pembayaran, tidak bisa hapus atau ubah invoice
- Portal Orang Tua: `http://localhost:8080/portal` — NISN `0071234567` + tanggal lahir siswa

## NEW FILES (Phase 2)
```
app/Enums/UserRole.php
app/Exports/InvoicesExport.php
app/Exports/PaymentsExport.php
app/Filament/Widgets/FinancialStatsWidget.php
app/Filament/Widgets/PemasukanChartWidget.php
app/Filament/Widgets/SiswaTunggakanWidget.php
app/Http/Controllers/ParentPortalController.php
app/Policies/InvoicePolicy.php
app/Policies/PaymentPolicy.php
app/Providers/AuthServiceProvider.php
database/migrations/2026_02_01_000001_add_role_to_users_table.php
resources/views/parent-portal/dashboard.blade.php
resources/views/parent-portal/lookup.blade.php
```

## MODIFIED FILES (Phase 2)
```
app/Models/User.php                                     (+ role, canAccessPanel, isAdmin/isBendahara)
app/Providers/Filament/AdminPanelProvider.php           (+ 3 widget)
app/Filament/Resources/InvoiceResource/Pages/ListInvoices.php  (+ export action, admin-only generate)
app/Filament/Resources/PaymentResource/Pages/ListPayments.php  (+ export action)
composer.json                                           (+ maatwebsite/excel)
database/seeders/DatabaseSeeder.php                     (+ bendahara user)
routes/web.php                                          (+ portal routes)
```

## NEXT STEP: NONE — all 4 requested add-ons complete.

## REMINDER: AuthServiceProvider Registration
Add ke `bootstrap/providers.php` (Laravel 13) atau `config/app.php`:
```php
App\Providers\AuthServiceProvider::class,
```
