# Panduan Setup Workflow N8N — Sistem SPP Sekolah

---

## 1. Cara Import Workflow ke N8N

1. Buka n8n → klik **"+"** atau **"New Workflow"**
2. Klik menu **⋮** (tiga titik) di pojok kanan atas
3. Pilih **"Import from file"**
4. Upload file `spp-sekolah-workflow.json`
5. Klik **Save** (Ctrl+S)

---

## 2. Setup Credential — Wajib Dilakukan Sebelum Jalankan

### Untuk Google Gemini (Default)

1. Buka **Settings → Credentials → Add Credential**
2. Cari **"HTTP Query Auth"**
3. Isi:
   - **Name:** `Google Gemini API` ← harus sama persis
   - **Name (query param):** `key`
   - **Value:** `API_KEY_GEMINI_KAMU`
4. Klik **Save**

Dapatkan API key Gemini di: https://aistudio.google.com/app/apikey

---

## 3. Cara Mengganti Provider LLM

Semua konfigurasi provider ada di satu tempat: **node "Config & Init"**.

Buka node tersebut dan ubah bagian `CONFIG`:

```javascript
const CONFIG = {
  // Ganti nilai ini untuk switch provider SEMUA agent sekaligus
  activeProvider: 'googleGemini',   // ← UBAH INI
```

### Opsi Provider yang Tersedia

| Provider | Nilai `activeProvider` | Model Default |
|---|---|---|
| Google Gemini | `'googleGemini'` | `models/gemini-2.0-flash` |
| OpenAI GPT | `'openAi'` | `gpt-4o` |
| Anthropic Claude | `'anthropic'` | `claude-sonnet-4-5` |
| Ollama (lokal) | `'ollama'` | `llama3.1` |

### Ganti Model Spesifik

```javascript
models: {
  googleGemini: 'models/gemini-2.0-flash',  // bisa ganti ke gemini-2.0-pro, dll
  openAi: 'gpt-4o',                          // bisa ganti ke gpt-4-turbo, dll
  anthropic: 'claude-sonnet-4-5',            // bisa ganti ke claude-opus-4-5, dll
  ollama: 'llama3.1'                         // sesuaikan model yang terinstall
},
```

---

## 4. Setup Credential untuk Provider Lain

Jika ingin ganti provider, buat credential sesuai tabel ini:

### OpenAI

1. Settings → Credentials → Add → **"HTTP Header Auth"**
2. Name: `OpenAI API`
3. Header Name: `Authorization`
4. Header Value: `Bearer sk-xxxx`

**Catatan:** Jika pakai OpenAI, kamu perlu mengubah URL di tiap agent node dari endpoint Gemini ke:
```
https://api.openai.com/v1/chat/completions
```
Dan ubah format body request ke format OpenAI. *(Cara paling mudah: gunakan Gemini atau tools n8n built-in OpenAI node)*

### Anthropic Claude

1. Settings → Credentials → Add → **"HTTP Header Auth"**
2. Name: `Anthropic API`
3. Header Name: `x-api-key`
4. Header Value: `sk-ant-xxxx`

### Cara Paling Mudah Ganti Provider: Gunakan n8n Built-in AI Nodes

Jika kamu ingin ganti provider tanpa ubah URL manual, di n8n tersedia node bawaan:
- **OpenAI Chat Model**
- **Google Gemini Chat Model**
- **Anthropic Chat Model**

Kamu bisa replace node "HTTP Request" di setiap agent dengan node AI bawaan tersebut, lalu isi credential-nya. Format system prompt tetap sama.

---

## 5. Cara Mengirim Request ke Workflow

Workflow berjalan via **HTTP POST** ke webhook URL.

### URL Webhook

Setelah import dan aktifkan workflow, copy URL dari node **"Webhook Trigger"**.
Formatnya: `https://[n8n-host]/webhook/spp-sekolah`

### Format Request Body (JSON)

```json
{
  "request": "Buat fitur CRUD Murid beserta migration dan service class-nya"
}
```

### Contoh Request Lain

```json
{
  "request": "Implementasi PaymentService dengan logika FIFO sesuai PRD"
}
```

```json
{
  "request": "Buat Dashboard Widget untuk menampilkan total tunggakan dan murid menunggak"
}
```

```json
{
  "request": "Buat BillingService untuk generate tagihan otomatis setiap tanggal 1"
}
```

### Contoh dengan cURL

```bash
curl -X POST https://[n8n-host]/webhook/spp-sekolah \
  -H "Content-Type: application/json" \
  -d '{"request": "Buat semua migration database sesuai PRD"}'
```

---

## 6. Memahami Output Response

Response adalah JSON dengan struktur:

```json
{
  "status": "SUCCESS",
  "total_files_generated": 8,
  "files": [
    {
      "filename": "CreateMuridsTable.php",
      "path": "database/migrations/",
      "content": "<?php ...",
      "agent": "Database Designer"
    }
  ],
  "pipeline_summary": {
    "project_manager": { "task_id": "PM-001", "total_tasks": 4 },
    "qa_reviewer": { "verdict": "APPROVED", "issues_count": 0 }
  },
  "definition_of_done": {
    "migrations_ready": true,
    "qa_approved": true,
    "fifo_logic_verified": true,
    ...
  }
}
```

Jika QA tidak approve, status akan `REVISION_NEEDED` dengan daftar issues.

---

## 7. Setup GitHub Commit (Opsional — Untuk Masa Depan)

Saat ini GitHub commit **dinonaktifkan**. Untuk mengaktifkan:

### Langkah 1 — Buat GitHub Token

1. Buka GitHub → Settings → Developer Settings
2. Personal Access Tokens → Fine-grained tokens → Generate new token
3. Permissions yang dibutuhkan:
   - **Contents:** Read and write
   - **Metadata:** Read-only
4. Copy token yang dihasilkan

### Langkah 2 — Buat Credential di N8N

1. Settings → Credentials → Add → **"HTTP Header Auth"**
2. Name: `GitHub API`
3. Header Name: `Authorization`
4. Header Value: `Bearer ghp_xxxxxxxxxxxx`

### Langkah 3 — Aktifkan di Config

Di node **"Config & Init"**, ubah:

```javascript
github: {
  enabled: true,              // ← ubah dari false ke true
  credentialName: 'GitHub API',
  owner: 'dikkycenter',       // ← username GitHub kamu
  repo: 'spp-sekolah',       // ← nama repo
  branch: 'main'
}
```

### Langkah 4 — Tambah HTTP Request Node untuk Commit

Node "GitHub Commit (Setup Ready)" sudah menyiapkan payload. Untuk commit penuh, tambahkan HTTP Request node dengan:
- URL: `https://api.github.com/repos/{{owner}}/{{repo}}/contents/{{path}}`
- Method: PUT
- Auth: Credential GitHub
- Body: file content dalam format base64

---

## 8. Alur Pipeline AI Agents

```
User Request (HTTP POST)
        ↓
  [Config & Init]
  Inisialisasi provider, model, config
        ↓
  [Project Manager]
  Breakdown task, tentukan priority & dependency
        ↓
  [Database Designer]
  Generate migration Laravel + index + constraint
        ↓
  [Backend Developer]
  Generate Service Classes (PaymentService, BillingService, DiscountService)
  + Models + FIFO Engine
        ↓
  [Frontend Developer]
  Generate Filament Resources + Forms + Tables + Widgets
  + WhatsApp Action + Dashboard
        ↓
  [QA Reviewer]
  Audit security, syntax, logic, scope compliance
        ↓
  ┌─── APPROVED? ───┐
  │ YES             │ NO
  ↓                 ↓
[Technical Writer]  [Revision Report]
Generate commit msg  → Response 422
  ↓
[GitHub Gate]
Cek apakah GitHub enabled
  ↓
[Compile Output]
Kumpulkan semua file + summary
  ↓
Response 200 (semua file + metadata)
```

---

## 9. Troubleshooting

### Error: "Request kosong"
Pastikan body POST berisi field `request`.

### Error: "Credential not found"
Pastikan nama credential di n8n **sama persis** dengan yang ada di CONFIG:
- `Google Gemini API` (bukan "Gemini" atau "gemini api")

### Agent mengembalikan teks bukan JSON
Semua agent sudah di-prompt untuk output JSON murni. Jika masih terjadi, kemungkinan model terlalu panjang responsnya. Coba kurangi scope request menjadi lebih spesifik.

### QA selalu REVISION_NEEDED
Coba request yang lebih spesifik dan terfokus pada satu fitur, misalnya:
```json
{"request": "Buat PaymentService dengan logika FIFO saja"}
```

### Timeout di n8n
Untuk Gemini Flash, biasanya cepat. Jika timeout, buka Settings → n8n → Execution Timeout dan naikkan nilainya.

---

## 10. Ringkasan File yang Dihasilkan per Request

| Agent | File yang Dihasilkan |
|---|---|
| Database Designer | Migration files (`.php`) |
| Backend Developer | `PaymentService.php`, `BillingService.php`, `DiscountService.php`, Models |
| Frontend Developer | `MuridResource.php`, `PaketSPPResource.php`, `TagihanResource.php`, dll + Widgets |
| Technical Writer | Conventional commit message |

---

*Dokumen ini mengikuti PRD `sistem-spp-sekolah.md` v1.0*
