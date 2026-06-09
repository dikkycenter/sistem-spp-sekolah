# sistem-spp-sekolah.md

# Product Requirements Document (PRD)

# Sistem SPP Sekolah

Version: 1.0

---

# 1. Ringkasan Produk

## 1.1 Tujuan Sistem

Sistem SPP Sekolah adalah aplikasi administrasi pembayaran SPP berbasis web yang digunakan untuk:

* Mengelola data murid.
* Mengelola paket SPP.
* Membuat tagihan bulanan secara otomatis.
* Mencatat pembayaran SPP.
* Menghitung pelunasan tagihan menggunakan metode FIFO (First In First Out).
* Mengelola diskon SPP.
* Memantau tunggakan melalui dashboard.
* Membantu petugas melakukan penagihan melalui WhatsApp.

Sistem harus sederhana, mudah digunakan oleh operator sekolah, dan memiliki struktur yang jelas agar dapat dikembangkan menggunakan AI Coding Agent tanpa menghasilkan implementasi yang ambigu.

---

# 2. Ruang Lingkup Produk

## 2.1 Fitur Dalam Scope

### Master Data

* Data Murid
* Paket SPP
* Diskon SPP
* Tahun Ajaran

### Operasional

* Generate tagihan bulanan otomatis
* Input pembayaran
* Pelunasan tagihan FIFO
* Pembayaran parsial
* Riwayat pembayaran

### Monitoring

* Dashboard tunggakan
* Statistik pembayaran
* Daftar murid menunggak

### Utilitas

* Generate pesan WhatsApp
* Export PDF bukti pembayaran

---

## 2.2 Fitur Di Luar Scope (Versi Awal)

Fitur berikut tidak boleh dibuat pada fase pertama:

* Payment Gateway
* Virtual Account
* QRIS
* Integrasi Bank
* Multi Sekolah
* Multi Cabang
* Multi Mata Uang
* Akuntansi Jurnal
* Approval Workflow Berjenjang

AI Agent tidak boleh menambahkan fitur di luar scope tanpa instruksi eksplisit.

---

# 3. Tech Stack

| Komponen        | Teknologi              |
| --------------- | ---------------------- |
| Framework       | Laravel 13             |
| Admin Panel     | Filament v5            |
| Frontend        | Livewire 4 + Alpine.js |
| Styling         | Tailwind CSS           |
| Database        | PostgreSQL 16          |
| Queue           | Database Queue         |
| Scheduler       | Laravel Scheduler      |
| AI Workflow     | n8n                    |
| AI Router       | 9Router                |
| Version Control | GitHub                 |

Repository:

dikkycenter/spp-sekolah

---

# 4. Arsitektur Sistem

## 4.1 Prinsip Utama

Seluruh logika bisnis wajib ditempatkan pada Service Class.

Filament Resource hanya bertugas:

* Menampilkan data
* Menerima input
* Memanggil Service

Resource tidak boleh berisi logika bisnis utama.

---

## 4.2 Layer Arsitektur

Presentation Layer

* Filament Resources
* Filament Widgets
* Filament Actions

Business Layer

* PaymentService
* BillingService
* DiscountService

Data Layer

* Eloquent Models
* PostgreSQL

---

# 5. Database Design

## 5.1 Tabel Murid

### murids

| Kolom           | Tipe      |
| --------------- | --------- |
| id              | bigint    |
| nisn            | string    |
| nama            | string    |
| status          | string    |
| paket_spp_id    | foreignId |
| tahun_ajaran_id | foreignId |
| created_at      | timestamp |
| updated_at      | timestamp |

### Index

* unique(nisn)
* index(status)
* index(paket_spp_id)
* index(tahun_ajaran_id)

---

## 5.2 Tabel Paket SPP

### paket_spp

| Kolom         | Tipe          |
| ------------- | ------------- |
| id            | bigint        |
| nama_paket    | string        |
| nominal_dasar | decimal(10,2) |
| created_at    | timestamp     |
| updated_at    | timestamp     |

---

## 5.3 Tabel Tahun Ajaran

### tahun_ajaran

| Kolom      | Tipe      |
| ---------- | --------- |
| id         | bigint    |
| nama       | string    |
| aktif      | boolean   |
| created_at | timestamp |
| updated_at | timestamp |

Contoh:

* 2025/2026
* 2026/2027

Hanya satu tahun ajaran boleh aktif.

---

## 5.4 Tabel Diskon SPP

### diskon_spp

| Kolom           | Tipe          |
| --------------- | ------------- |
| id              | bigint        |
| murid_id        | foreignId     |
| jenis_diskon    | string        |
| nilai           | decimal(10,2) |
| tanggal_mulai   | date          |
| tanggal_selesai | date          |
| aktif           | boolean       |

Contoh:

* Beasiswa
* Anak Guru
* Prestasi

---

## 5.5 Tabel Tagihan SPP

### tagihan_spp

| Kolom            | Tipe          |
| ---------------- | ------------- |
| id               | bigint        |
| murid_id         | foreignId     |
| bulan_tagihan    | string(7)     |
| nominal_tagihan  | decimal(10,2) |
| nominal_terbayar | decimal(10,2) |
| sisa_tagihan     | decimal(10,2) |
| status_bayar     | string        |
| created_at       | timestamp     |
| updated_at       | timestamp     |

### Status

* belum_bayar
* sebagian
* lunas

### Index

* index(status_bayar)
* index(murid_id)
* index(bulan_tagihan)

### Constraint

Setiap murid hanya boleh memiliki satu tagihan pada satu bulan.

Unique:

(murid_id, bulan_tagihan)

---

## 5.6 Tabel Pembayaran

### pembayaran

| Kolom        | Tipe          |
| ------------ | ------------- |
| id           | bigint        |
| murid_id     | foreignId     |
| nominal      | decimal(10,2) |
| metode_bayar | string        |
| tgl_bayar    | timestamp     |
| catatan      | text nullable |
| created_by   | foreignId     |
| created_at   | timestamp     |
| updated_at   | timestamp     |

Contoh metode bayar:

* Tunai
* Transfer
* QRIS

---

# 6. Aturan Integritas Data

Aturan berikut wajib dipatuhi oleh seluruh AI Agent.

## Rule 1

Satu murid hanya boleh memiliki satu tagihan per bulan.

---

## Rule 2

Seluruh pembayaran wajib diproses melalui PaymentService.

Tidak boleh mengubah status tagihan langsung dari Resource.

---

## Rule 3

Tagihan lunas tidak boleh diedit.

---

## Rule 4

Nominal pembayaran tidak boleh:

* Nol
* Negatif

---

## Rule 5

Status tagihan harus konsisten.

Jika:

sisa_tagihan = 0

Maka:

status_bayar = lunas

---

## Rule 6

Status pembayaran harus mengikuti:

belum_bayar

Jika:

nominal_terbayar = 0

sebagian

Jika:

0 < nominal_terbayar < nominal_tagihan

lunas

Jika:

nominal_terbayar = nominal_tagihan

---

# 7. Logika Bisnis

## 7.1 Pembuatan Tagihan Bulanan

### Trigger

Laravel Scheduler

### Jadwal

Tanggal 1 setiap bulan

### Proses

1. Ambil seluruh murid aktif.
2. Ambil paket SPP aktif.
3. Hitung diskon aktif.
4. Buat tagihan bulan berjalan.
5. Simpan ke tagihan_spp.

### Validasi

Sistem wajib memeriksa apakah tagihan bulan tersebut sudah ada.

Jika sudah ada:

Jangan membuat tagihan baru.

---

## 7.2 Perhitungan Diskon

Formula:

nominal_tagihan = nominal_dasar - diskon

Diskon hanya berlaku jika:

* aktif = true
* tanggal saat ini berada dalam periode diskon

---

## 7.3 Pembayaran FIFO

### Tujuan

Pembayaran selalu melunasi tagihan paling lama terlebih dahulu.

### Proses

1. Mulai database transaction.
2. Ambil seluruh tagihan belum lunas.
3. Gunakan lockForUpdate().
4. Urutkan berdasarkan bulan_tagihan ASC.
5. Alokasikan pembayaran ke tagihan tertua.
6. Update nominal_terbayar.
7. Update sisa_tagihan.
8. Update status_bayar.
9. Commit transaction.

---

### Contoh

Tagihan:

Januari = 300.000

Februari = 300.000

Maret = 300.000

Total tunggakan = 900.000

Pembayaran:

500.000

Hasil:

Januari = lunas

Februari = sebagian

Maret = belum_bayar

---

# 8. Dashboard

## KPI 1

Total Nominal Tunggakan Aktif

Definisi:

Jumlah seluruh sisa_tagihan yang belum lunas.

---

## KPI 2

Jumlah Murid Menunggak

Definisi:

Jumlah murid yang memiliki minimal satu tagihan belum lunas.

---

## KPI 3

Pembayaran Bulan Ini

Definisi:

Total nominal pembayaran pada bulan berjalan.

---

## Widget

### Stats Overview

* Total Tunggakan
* Murid Menunggak
* Pembayaran Bulan Ini

### Table Widget

Daftar murid yang memiliki tunggakan.

---

# 9. WhatsApp Reminder

## Template Pesan

Halo Bapak/Ibu {{nama_wali}}

Kami menginformasikan bahwa saat ini terdapat tagihan SPP atas nama:

{{nama_murid}}

Total tunggakan:
Rp {{total_tunggakan}}

Mohon dapat dilakukan pembayaran sesuai ketentuan sekolah.

Terima kasih.

---

## Action

Filament akan menghasilkan URL:

wa.me

dengan pesan yang sudah terisi otomatis.

---

# 10. Filament Resources

## MuridResource

Fungsi:

* CRUD Murid

---

## PaketSPPResource

Fungsi:

* CRUD Paket SPP

---

## DiskonSPPResource

Fungsi:

* CRUD Diskon

---

## TahunAjaranResource

Fungsi:

* CRUD Tahun Ajaran

---

## TagihanResource

Fungsi:

* Monitoring Tagihan

Tidak boleh edit status tagihan secara manual.

---

## PembayaranResource

Fungsi:

* Input Pembayaran
* Riwayat Pembayaran

---

# 11. AI Agent Responsibilities

## Project Manager

Tugas:

* Membagi pekerjaan
* Menentukan dependency
* Menyusun task teknis

Output:

JSON

---

## Database Designer

Tugas:

* Migration
* Foreign Key
* Index
* Constraint

Output:

Migration Laravel

---

## Backend Developer

Tugas:

* Service Class
* FIFO Engine
* Billing Engine
* Discount Engine

Output:

PHP Service Classes

---

## Frontend Developer

Tugas:

* Filament Resources
* Forms
* Tables
* Widgets

Output:

Filament Components

---

## QA Reviewer

Tugas:

* Audit keamanan
* Audit sintaks
* Audit logic

Output:

APPROVED atau daftar perbaikan

---

## Technical Writer

Tugas:

* Conventional Commit

Output:

Git Commit Message

---

# 12. Coding Standards

Seluruh AI Agent wajib mengikuti aturan berikut.

## Wajib

* Type Hinting
* Service Class Pattern
* Laravel Best Practices
* Filament Best Practices
* PostgreSQL Compatible

---

## Dilarang

* Raw SQL tanpa alasan kuat
* Logic bisnis di Resource
* Logic bisnis di Blade
* Hardcode nominal
* Mengubah status tagihan secara langsung

---

# 13. Workflow n8n

Flow:

User Request

↓

Project Manager

↓

Database Designer

↓

Backend Developer

↓

Frontend Developer

↓

QA Reviewer

↓

Technical Writer

↓

GitHub Commit

---

# 14. Definition of Done

Fitur dianggap selesai jika:

* Migration berhasil dijalankan.
* Tidak ada error sintaks.
* QA memberikan APPROVED.
* FIFO berjalan sesuai spesifikasi.
* Tagihan bulanan tidak duplikat.
* Dashboard menampilkan data valid.
* Resource Filament dapat digunakan tanpa error.
* Seluruh test lulus.

---

# 15. Referensi Implementasi Awal

## Instalasi Laravel

composer create-project laravel/laravel spp-sekolah

## Instalasi Filament

composer require filament/filament:"^5.0-dev" -W

## Database

DB_CONNECTION=pgsql

## Menjalankan Scheduler

php artisan schedule:work

## Menjalankan Queue

php artisan queue:work

---

Dokumen ini menjadi sumber kebenaran utama (Single Source of Truth) untuk seluruh AI Agent, workflow n8n, dan proses pengembangan sistem SPP Sekolah.
