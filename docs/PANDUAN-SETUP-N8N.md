# 📋 Panduan Setup Workflow n8n — Sistem SPP Sekolah
> Panduan ini dirancang untuk pemula yang belum pernah menggunakan n8n sebelumnya.

---

## 🗂️ File yang Perlu Kamu Miliki

| File | Keterangan |
|------|------------|
| `spp-sekolah-n8n-workflow.json` | File workflow utama yang diimport ke n8n |
| `PANDUAN-SETUP-N8N.md` | File ini — panduan lengkap |

---

## ⚡ LANGKAH 1: Install n8n (Pilih Salah Satu)

### Opsi A — Docker (Direkomendasikan untuk Pemula)
```bash
docker run -it --rm \
  --name n8n \
  -p 5678:5678 \
  -v ~/.n8n:/home/node/.n8n \
  n8nio/n8n
```
Akses: http://localhost:5678

### Opsi B — NPM (Jika ada Node.js)
```bash
npm install n8n -g
n8n start
```

### Opsi C — Docker Compose (Termasuk PostgreSQL untuk proyek SPP)
Buat file `docker-compose.yml`:
```yaml
version: "3.8"
services:
  n8n:
    image: n8nio/n8n
    ports:
      - "5678:5678"
    volumes:
      - n8n_data:/home/node/.n8n
    environment:
      - N8N_BASIC_AUTH_ACTIVE=true
      - N8N_BASIC_AUTH_USER=admin
      - N8N_BASIC_AUTH_PASSWORD=admin123
    restart: unless-stopped

  postgres:
    image: postgres:16
    ports:
      - "5432:5432"
    environment:
      POSTGRES_DB: spp_sekolah
      POSTGRES_USER: spp_user
      POSTGRES_PASSWORD: ganti_password_ini
    volumes:
      - postgres_data:/var/lib/postgresql/data
    restart: unless-stopped

volumes:
  n8n_data:
  postgres_data:
```

Jalankan:
```bash
docker-compose up -d
```

---

## 🔑 LANGKAH 2: Dapatkan API Key Gemini

1. Buka https://aistudio.google.com/app/apikey
2. Klik **Create API Key**
3. Salin API key yang muncul (simpan, hanya tampil sekali)
4. **Gratis** untuk penggunaan standar

---

## 🔧 LANGKAH 3: Setup Credential Gemini di n8n

1. Buka n8n di browser (http://localhost:5678)
2. Klik menu **Settings** (ikon gear) → **Credentials**
3. Klik **Add Credential**
4. Cari dan pilih: **Google Gemini(PaLM) Api**
5. Isi:
   - **API Key**: paste API key dari langkah 2
6. Klik **Save**
7. **Catat Credential ID** yang muncul (terlihat di URL atau nama credential)

---

## 📥 LANGKAH 4: Import Workflow

1. Di n8n, klik menu **Workflows** (sidebar kiri)
2. Klik tombol **Import from File**
3. Pilih file `spp-sekolah-n8n-workflow.json`
4. Workflow akan terbuka otomatis

---

## 🔗 LANGKAH 5: Hubungkan Credential ke Semua Agent

Setelah import, semua node AI Agent akan menampilkan tanda peringatan merah karena credential belum terhubung.

**Lakukan untuk setiap node berikut (ada 8 node AI):**

| Node | Nama |
|------|------|
| 🧠 PM Agent | Project Manager |
| 🔍 Code Analyzer Agent | Code Analyzer |
| 🗄️ DB Engineer Agent | Database Engineer |
| ⚙️ Backend Developer Agent | Backend Developer |
| 🎨 Frontend Developer Agent | Frontend Developer |
| 🔬 QA Agent | Quality Assurance |
| 🚢 DevOps Agent | DevOps |
| 📝 Tech Writer Agent | Technical Writer |
| 🔄 PM Revision Loop | Revision Handler |

**Caranya:**
1. Klik node yang bermasalah (ikon merah)
2. Pada bagian **Credential**, klik dropdown
3. Pilih credential Gemini yang sudah kamu buat
4. Klik **Save**
5. Ulangi untuk semua node di atas

---

## ▶️ LANGKAH 6: Test Workflow

### Cara Mudah (Manual Trigger):
1. Buka workflow
2. Klik tombol **Test Workflow** (pojok kanan atas)
3. Pilih node **🚀 Webhook Trigger**
4. Klik **Test Step**
5. Di panel kanan, klik **Send Test Data**
6. Isi body:
```json
{
  "message": "Mulai pembangunan Sistem SPP Sekolah fase pertama: Database Schema"
}
```
7. Klik **Execute**

### Lihat Hasilnya:
- Setiap node akan menampilkan data input/output
- Warna hijau = sukses
- Warna merah = error (klik node untuk lihat detail)

---

## 🔄 CARA MENGGANTI MODEL AI (Sangat Mudah!)

### Ganti Satu Node:
1. Klik node yang ingin diganti
2. Ubah field **Model** di bagian Parameters
3. Untuk Gemini: pilih `gemini-2.0-flash`, `gemini-1.5-pro`, dll

### Ganti ke OpenAI (GPT):
1. Hapus node Gemini
2. Tambah node baru: **OpenAI Chat Model**
3. Hubungkan ke node sebelum/sesudahnya
4. Masukkan credential OpenAI (dari https://platform.openai.com/api-keys)
5. Pilih model: `gpt-4o`, `gpt-4o-mini`, dll

### Ganti ke Anthropic (Claude):
1. Hapus node Gemini
2. Tambah node baru: **Anthropic Chat Model**
3. Masukkan credential Anthropic API key
4. Pilih model: `claude-opus-4-6`, `claude-sonnet-4-6`, dll

### Tabel Rekomendasi Model per Agent:

| Agent | Rekomendasi Gratis | Rekomendasi Berbayar |
|-------|-------------------|---------------------|
| PM Agent | Gemini 2.0 Flash | Claude Sonnet 4.6 |
| Code Analyzer | Gemini 2.0 Flash | GPT-4o-mini |
| DB Engineer | Gemini 1.5 Pro | Claude Sonnet 4.6 |
| Backend Developer | Gemini 1.5 Pro | Claude Sonnet 4.6 |
| Frontend Developer | Gemini 2.0 Flash | GPT-4o |
| QA Agent | Gemini 1.5 Pro | Claude Sonnet 4.6 |
| DevOps Agent | Gemini 2.0 Flash | GPT-4o-mini |
| Tech Writer | Gemini 2.0 Flash | Claude Sonnet 4.6 |

> 💡 **Rekomendasi untuk Pemula:** Mulai dengan Gemini 2.0 Flash (gratis, cepat). Upgrade ke Claude Sonnet untuk agent DB Engineer dan Backend jika butuh output lebih presisi.

---

## 🌐 LANGKAH 7: Pastikan Koneksi Internet

Workflow ini membutuhkan koneksi internet untuk:
- Memanggil Gemini API
- Agent mendownload package Laravel/Composer

**Cek koneksi n8n:**
1. Di n8n, tambah node **HTTP Request** (untuk testing)
2. URL: `https://api.google.com`
3. Jika status 200 → internet terhubung ✅

**Jika menggunakan Docker WSL:**
```bash
# Test koneksi dari dalam container
docker exec -it n8n ping google.com

# Jika tidak bisa, tambahkan DNS ke docker-compose.yml:
# dns:
#   - 8.8.8.8
#   - 8.8.4.4
```

---

## 📦 SETUP GITHUB COMMIT (Opsional - Aktifkan Jika Dibutuhkan)

### 1. Buat Personal Access Token GitHub:
1. GitHub → Settings → Developer Settings → Personal Access Tokens
2. Generate new token (classic)
3. Centang: `repo` (full control)
4. Salin token

### 2. Tambah Credential GitHub di n8n:
1. n8n → Settings → Credentials → Add
2. Pilih: **GitHub API**
3. Isi token

### 3. Tambah Node GitHub setelah Tech Writer:
- Node: **GitHub** → **File** → **Create or Update**
- Repository: `dikkycenter/spp-sekolah`
- Branch: `main`
- File path: sesuai file yang digenerate

### Format Conventional Commit:
```
feat(billing): add FIFO payment allocation engine
fix(tagihan): resolve duplicate billing on scheduler
refactor(services): extract payment logic to PaymentService
docs(readme): add installation guide for deployment
test(fifo): add edge case tests for partial payment
chore(deps): update filament to v5.x
```

---

## 🐛 TROUBLESHOOTING UMUM

### Error: "Credential not found"
→ Pastikan credential Gemini sudah dibuat dan dipilih di semua node AI

### Error: "Rate limit exceeded" (Gemini)
→ Gemini free tier punya limit. Tambahkan node **Wait** 2-3 detik antara agent jika perlu:
   - Tambah node **Wait** di antara Consolidate → DB/BE/FE Agent
   - Set: 2000ms (2 detik)

### Error: "JSON parse error" di node Code
→ Output AI kadang tidak tepat JSON. Node Code sudah ada try-catch, tapi jika terus gagal:
   - Buka node yang error
   - Cek `raw_output` di output node
   - Adjust prompt agent tersebut agar selalu output JSON valid

### Agent menghasilkan output terpotong
→ Naikkan `maxOutputTokens` di node agent tersebut (max 8192 untuk Gemini Flash)

### DB/BE/FE Agent berjalan tidak paralel
→ Node `Consolidate PM + CA` sudah dikonfigurasi untuk memicu ketiganya sekaligus. Jika masih sequential, cek connections di n8n:
   - Klik node Consolidate
   - Pastikan output terhubung ke DB, BE, dan FE dengan 3 garis terpisah

---

## 📁 STRUKTUR OUTPUT YANG DIHASILKAN WORKFLOW

Setelah workflow selesai, node **📊 Final Report** akan berisi:

```
full_outputs:
├── db_engineer     → Migration files, Models, Seeders, Factories
├── backend         → Services (PaymentService, BillingService, DiscountService), Tests
├── frontend        → Filament Resources, Widgets, Panel Provider
├── qa_report       → Laporan audit APPROVED/REJECTED
├── devops          → docker-compose.yml, .env.example, deploy.sh
└── tech_writer     → CHANGELOG.md, DEPLOYMENT.md, USER_GUIDE.md
```

---

## 💡 TIPS UNTUK PEMULA

1. **Mulai kecil**: Test satu node dulu (misal hanya PM Agent) sebelum jalankan full workflow
2. **Simpan output**: Copy hasil dari node Final Report ke file teks sebelum keluar
3. **Iterasi**: Kalau hasil kurang memuaskan, edit prompt di node yang bersangkutan
4. **Monitor executions**: n8n menyimpan history di menu Executions — bisa dilihat ulang

---

*Dibuat untuk proyek: Sistem SPP Sekolah | Tech Stack: Laravel 13 + Filament v5 + PostgreSQL 16*
