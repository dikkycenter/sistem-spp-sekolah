# SPP Payment System - Laravel 13 + Filament 5

Sistem pembayaran SPP (Sumbangan Pembinaan Pendidikan) untuk sekolah SD/SMP/SMA.

## Stack
- **PHP 8.3 / Laravel 13**
- **Filament 5** (Admin Panel)
- **Livewire 4**
- **PostgreSQL 16**
- **Redis 7** (cache + queue)
- **Spatie Media Library** (upload bukti bayar)
- **barryvdh/laravel-dompdf** (PDF invoice & kwitansi)
- **Docker Compose** (nginx + php-fpm + postgres + redis)

## Cara Menjalankan

```bash
# 1. Clone / copy folder ini ke server Anda
cp .env.example .env

# 2. Build & start containers
docker compose up -d --build

# 3. Install dependencies
docker compose exec app composer install

# 4. Generate app key
docker compose exec app php artisan key:generate

# 5. Run migrations & seed
docker compose exec app php artisan migrate --seed

# 6. Buat user admin Filament
docker compose exec app php artisan make:filament-user

# 7. Link storage (untuk media library)
docker compose exec app php artisan storage:link

# 8. Setup scheduler (crontab di host atau supervisor)
# * * * * * cd /path-to-project && docker compose exec -T app php artisan schedule:run >> /dev/null 2>&1
```

Akses Admin Panel: http://localhost:8080/admin

## Fitur Utama
- CRUD Profil Sekolah, Tahun Ajaran, Orang Tua, Siswa
- Riwayat Kelas & SPP per Tahun Ajaran (satu siswa bisa naik/tinggal kelas dengan biaya berbeda)
- Diskon/Beasiswa dengan periode berlaku (start-end month/year)
- **Auto-generate invoice** setiap tanggal 1 pukul 00:00 (Cron)
- **FIFO Arrears**: pembayaran otomatis melunasi tagihan tertua dulu
- **Partial Payment**: dukung cicilan, sisa tagihan tercatat
- **PDF Invoice** & **PDF Kwitansi** A4 dengan kop surat + rekening sekolah
- Upload Bukti Bayar (wajib jika metode = Transfer) via Spatie Media Library
- Storage bisa switch `local` <-> `s3/R2` hanya dengan mengubah `FILESYSTEM_DISK`

## Struktur Folder
Lihat file & folder di project ini (mengikuti struktur Laravel standar).

## Switch ke Cloudflare R2
Ubah di `.env`:
```
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=xxx
AWS_SECRET_ACCESS_KEY=xxx
AWS_DEFAULT_REGION=auto
AWS_BUCKET=spp-uploads
AWS_ENDPOINT=https://<accountid>.r2.cloudflarestorage.com
AWS_USE_PATH_STYLE_ENDPOINT=true
```
