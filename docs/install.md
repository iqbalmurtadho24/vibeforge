# Dokumentasi Instalasi & Protocol Eksekusi AI - Vibeforge

> **FILE INI ADALAH TEMPLATE STATIC.**
> Konfigurasi aktif tersimpan di `data/install_config.json` oleh Setup Wizard.
> Edit file ini manual untuk perubahan permanen.

---

## 0. VIBEFORGE AI GUIDELINES & ERROR PREVENTION PROTOCOL (MUTLAK)

Sebelum menjalankan **Build Protocol**, AI Coding Assistant WAJIB mematuhi guardrail pencegahan error berikut:

### 0.1 Mencegah File Write / Lock / Update Errors
- **Penanganan Hak Akses Folder & Environment Lock**: Sebelum memulai pembangunan, jalankan `installer_skill_claude.bat` sebagai **Administrator** di Windows. Script ini akan mengeksekusi `icacls` untuk memberikan izin penuh pada folder kerja serta mengonfigurasi plugin MCP (FileSystem Server, Sequential Thinking, Memory Server) guna mencegah *file permission lock* dan kegagalan penulisan file.
- **Single Component Execution (Stabilitas Memori & I/O)**: Bekerja secara bertahap — selesaikan **satu file/komponen pada satu waktu**. DILARANG menulis atau mengubah banyak file sekaligus di berbagai direktori dalam satu langkah eksekusi untuk mencegah file permission lock, buffer overflow, atau write collision.
- **Atomic File Write Protection**: Untuk penyimpanan JSON di `data/`, gunakan mekanisme file lock (`.lock`) dan atomic write (`.tmp` → `rename()`). Jangan menulis langsung ke file JSON utama tanpa `.tmp`.
- **Handling Permission Locks**: Jika terjadi kegagalan penulisan file, periksa status file lock / hak akses folder proyek sebelum mencoba ulang. Jalankan terminal tempat AI berada dalam mode Administrator.

### 0.2 Standar Penamaan & Konsistensi Database (Mencegah Runtime Mismatch)
- Seluruh variabel PHP/JS, kolom database, dan `id`/`name` input HTML WAJIB menggunakan format **`snake_case`**.
- **Aturan Mutlak Kolom Wilayah**: Setiap data yang berkaitan dengan alamat, kota, atau kabupaten WAJIB menggunakan nama kolom/variabel **`kota_kabupaten_rumah`**. DILARANG menggunakan nama alternatif (`city`, `kota_rumah`, `kabupaten`, dll) untuk mencegah runtime error dan ketidakcocokan query antar-modul.

### 0.3 Alur Kerja Agentic (Ruflo & Graphify Execution)
1. **Analisis Relasi**: Periksa file `core/router.php`, modul `modules/`, dan skema DB (atau Graphify jika tersedia) sebelum mengubah kode.
2. **Pecah Tugas Linear**: Pecah fitur ke sub-tahap: (1) UI Frontend SPA Shell, (2) Backend Endpoint (`modules/*/*.php`), (3) Integrasi Fetch/AJAX.
3. **Pesan Konfirmasi**: Setelah memahami seluruh aturan, AI wajib membalas di awal turn: `"Vibeforge Guidelines & Protection Protocol Diterima. Siap eksekusi."`

---

Dokumen ini adalah panduan utama instalasi dan **Build Protocol** untuk mengkonfigurasi serta memproses pembuatan aplikasi berbasis **Vibeforge Template** (PHP Single Page Application Framework).

---

## 1. Konfigurasi Server & Workspace

Konfigurasi aktif ada di `data/install_config.json`. Untuk referensi cepat:

| Parameter | Lokasi |
|-----------|--------|
| Mode Aplikasi | `data/install_config.json` → `appMode` |
| Local Disk | `data/install_config.json` → `drive` |
| Web Server | `data/install_config.json` → `serverType` |
| Folder Kerja | `data/install_config.json` → `installPath` |
| Branding Mode | `data/install_config.json` → `brandingMode` |
| PRD Mode | `data/install_config.json` → `prdMode` |

---

## 2. Struktur Halaman Aktif

Halaman yang dicentang di wizard Tahap 3B (hanya ini yang dibangun).

Lihat `data/install_config.json` → `pageStructure` untuk daftar aktual.

| Shell Folder | Allowed Role | Template Reference |
|-------------|-------------|------------------|
| `public/index.php` | - | `references/landingpage.html` |
| `public/login/index.php` | - | `references/login.html` |
| `public/register/index.php` | - | `references/register.html` |
| `public/manajemen/index.php` | `manajemen` | `references/modul_manajemen.html` |
| `public/admin/index.php` | `admin` | `references/modul_admin.html` |
| `public/client/index.php` | `client` | `references/modul_client.html` |

---

## 3. Referensi Aplikasi (`references/`)

Lihat `data/install_config.json` → `referencesCount` untuk jumlah file referensi.

**Jenis & Format Referensi:**
- File referensi di `references/` sangat beragam: file HTML, PHP, CSS, JavaScript, gambar (PNG/SVG/JPG/WebP), video (MP4/WebM), font, JSON, hingga struktur folder aplikasi lawas/legacy penuh. AI WAJIB menelusuri seluruh tipe media dan file ini secara utuh.

**Jika `referencesCount = 0`:**
- AI WAJIB generate file HTML/template referensi di `references/` secara otomatis saat TAHAP 2 berdasarkan `docs/prd.md` dan `docs/branding.md`.
- File ini menjadi golden template untuk styling dan struktur.

**Jika `referencesCount > 0`:**
- File referensi sudah di-upload via wizard.
- AI audit seluruh file referensi (termasuk alur tautan dan halaman utama seperti `index.html`/`index.php`) -> generate `docs/prd.md` dan `docs/branding.md`.

---

## 4. File yang Mungkin Di-Generate Otomatis

### 4.1 Branding (`docs/branding.md`)

- **Auto**: Jika `brandingMode = "auto"` di `data/install_config.json` (yang diatur dari Setup Wizard `/install/`).
- **Ketentuan Format**: AI WAJIB menghasilkan `docs/branding.md` yang mematuhi struktur 6 bagian bawaan template (1. Nama & Tagline, 2. Value Proposition, 3. Target Audience & Tone, 4. Palet Warna, 5. Typography, 6. Logo Guidelines).
- **Sumber Ekstraksi**: Di-generate dari analisis mendalam seluruh elemen visual di `references/` (header, CSS, skema warna, typography, dan logo pada aset referensi).

### 4.2 PRD (`docs/prd.md`)

- **Auto**: Jika `prdMode = "auto"` di `data/install_config.json` (yang diatur dari Setup Wizard `/install/`).
- **Ketentuan Format**: AI WAJIB menghasilkan `docs/prd.md` yang mematuhi struktur 7 bagian standar template (1. Problem Statement, 2. Goals, 3. Target User, 4. User Stories, 5. Functional Requirements, 6. Non-Functional Requirements, 7. Scope).
- **Sumber Ekstraksi**: Di-generate berdasarkan audit menyeluruh alur fungsional, formulir, menu navigasi, dan logika bisnis yang ditemukan pada seluruh file di `references/`.

### 4.3 References HTML (`references/`)

- **Auto**: Jika `referencesCount = 0` di `data/install_config.json`
- Generate sesuai `pageStructure` di `data/install_config.json`.

---

## 5. Protokol Pembangunan AI (Build Protocol)

Setiap AI Coding Assistant WAJIB mengikuti 3 Tahap secara linear:

### TAHAP 1 — AUDIT & RENCANA (Read-Only)

1. Baca `CLAUDE.md`, `data/install_config.json`
2. Audit keberadaan file/folder di `references/`:
   - **Jika `references/` Kosong**: AI WAJIB men-generate file template HTML di `references/` serta menyusun `docs/prd.md` & `docs/branding.md` sesuai ide/konsep aplikasi baru.
   - **Jika `references/` Berisi File/Folder**: AI WAJIB mengaudit seluruh file (HTML, PHP, CSS, JS, Gambar, Video, Font) dan alur tautan navigasi.
3. Audit skema database pada file di `references/`:
   - Cek apakah terdapat file query SQL (`.sql`) atau DDL/query SQL di dalam kode referensi.
   - **Ada SQL**: Rencanakan skema migrasi di `migrations/` & set `DB_MODE=mysql` (atau `auto`).
   - **Tidak Ada SQL**: Set `DB_MODE=json` & buat skema entitas di `data/*.json`.
4. Audit struktur file core & root:
   - `include/config.php`, `include/helper.php`
   - `core/router.php`, `core/session.php`, `core/csrf.php`, `core/Repo.php`
   - `public/core/router.php` (router proxy - WAJIB)
   - `.env`, `.env.example`, `README.md`, `LICENSE`, `CHANGELOG.md`
   - `data/users.json`
   - `locales/languages.json` dan `locales/*.json`
5. Jalankan **Audit Protocol** sesuai `docs/audit_protocol.md`
6. Buat `docs/build_plan.md`
7. **BERHENTI & TUNGGU persetujuan owner**

---

### TAHAP 2 — BUILD (Eksekusi)

1. **Transformasi Total Tampilan, Root Files & Branding (WAJIB)**:
   - GANTI total tampilan `public/index.php` dan seluruh shell `public/*.php` sesuai desain `references/`, `docs/prd.md`, dan `docs/branding.md`.
   - Update file-file konfigurasi & dokumentasi root (`.env`, `.env.example`, `README.md`, `LICENSE`, `CHANGELOG.md`) dengan nama aplikasi baru, tagline, deskripsi, dan lisensi/versi yang sesuai (DILARANG menyisakan nama "Vibeforge" atau landing page framework bawaan pada aplikasi hasil generate).
   - Periksa seluruh tautan navigasi (tombol, menu, form action). Pastikan tautan berfungsi baik di environment VirtualHost maupun non-VirtualHost (gunakan relative path atau URL helper).
2. **Kepatuhan Arsitektur 13 Pilar Software (WAJIB)**:
   - **Entry Guard Pattern (CLAUDE.md §3b)**: `defined('APP_ENTRY') or define('APP_ENTRY', true);` untuk entry point, dan `if (!defined('APP_ENTRY')) { http_response_code(403); exit('Direct access forbidden'); }` untuk file module/include.
   - **Router Proxy Pattern (CLAUDE.md §2b)**: Semua AJAX request dipicu ke `public/core/router.php` -> `core/router.php`.
   - **Repo Pattern Dual-Mode (CLAUDE.md §2c)**: Akses data terpusat via `Repo::table('entitas')` (Auto-Switch SQL/JSON).
   - **SPA Shell Architecture (CLAUDE.md §2a)**: Navigasi intra-shell via AJAX tanpa full-page reload.
   - **Standar i18n & Multi-Bahasa (CLAUDE.md §2a)**:
     - DILARANG hardcode string bahasa di file `public/*.php` atau `modules/*/*.php`.
     - Seluruh string UI wajib diekstrak ke `locales/id.json` dan disinkronkan ke `locales/*.json` (`en`, `ar`, `ja`).
     - Render statis di PHP via `<?= t('key') ?>`.
     - Render dinamis di JS via payload `window._i18n = { key: <?= json_encode(t('key')) ?> }`.
     - Dropdown bahasa wajib dikelilingi loop dinamis `getAvailableLanguages()`.
3. **Demo Users & Security Setup**:
   - Generate demo users dengan Argon2ID (`password_hash('password123', PASSWORD_ARGON2ID)`) tersimpan di `data/users.json` / MySQL (CLAUDE.md §4b).
   - Setup i18n files (`locales/languages.json` & `locales/*.json`).
4. **Implementasi Database & CRUD Nyata**:
   - Jika ditemukan file/query SQL di `references/`, bangun tabel MySQL di database dan jalankan query via `Repo::table()`.
   - Jika tidak ada SQL di `references/`, gunakan mode JSON (`data/*.json`) dengan file locking & atomic write.
   - Pastikan seluruh fitur CRUD (Create, Read, Update, Delete) berfungsi secara nyata sesuai spesifikasi di `docs/prd.md`.

---

### TAHAP 3 — VERIFY & PREVIEW

1. Validasi syntax PHP: `php -l` pada semua file
2. Konfigurasi virtual host (manual via Laragon)
3. URL preview: `http://<project>.test/`

**Checklist Manual (owner verifikasi di browser):**
- [ ] Landing page sesuai `references/landingpage.html`
- [ ] Quick-login demo berfungsi
- [ ] i18n multi-bahasa berfungsi tanpa teks hardcode & selector dinamis
- [ ] Logout redirect ke landing page
- [ ] Auth state konsisten

---

## 6. Keamanan & Demo Users

**Security Baseline (CLAUDE.md §3):**
- Password: Argon2ID
- CSRF Token Validation
- IP+Username Rate Limiting
- Prepared Statements

**Demo Users (CLAUDE.md §4b):**

| Role | Email | Password |
|------|-------|----------|
| Manajemen | `manajemen@example.com` | `password123` |
| Admin | `admin@example.com` | `password123` |
| Client | `client@example.com` | `password123` |

---

## 7. Referensi Dokumen

| File | Scope |
|------|-------|
| `CLAUDE.md` | Konstitusi teknis utama & 13 Pilar Software |
| `docs/document.md` | Decision guide |
| `docs/prd.md` | Definisi produk |
| `docs/branding.md` | Identitas visual |
| `docs/audit_protocol.md` | Audit dasar |
| `data/install_config.json` | Konfigurasi aktif wizard |

---

**Template static - edit manual jika perlu**
