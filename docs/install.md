# Dokumentasi Instalasi & Protocol Eksekusi AI - Vibeforge Template

Dokumen ini adalah panduan utama instalasi dan **Build Protocol** untuk mengkonfigurasi serta memproses pembuatan aplikasi berbasis **Vibeforge Template** (PHP Single Page Application Framework).

---

## 1. Konfigurasi Server & Workspace

- **Mode Aplikasi**: `redesign` (**Redesain Aplikasi**)
- **Local Disk**: `D:`
- **Jenis Web Server**: `laragon`
- **Folder Kerja Target**: `D:\laragon\www\vibeforge`

---

## 2. Alur Kerja Setup Wizard

### Mode Aplikasi Baru (12 Langkah)
1. Overview -> 2. PRD -> 3. Branding -> 4. Logo -> 5-10. Templates HTML -> 11. Server -> 12. Path

### Mode Redesain (5 Langkah)
1. Overview -> 2. References Folder -> 3. Logo -> 4. Server -> 5. Path

---

## 3. Referensi Aplikasi Redesain (`references/`)

Pada **Mode Redesain**, AI akan menganalisa seluruh isi folder `references/` (termasuk file HTML, PHP, JS, CSS, maupun subfolder dari codebase lama).

**Daftar File Referensi Saat Ini:**

- `D:/laragon/www/vibeforge/references/landingpage.html`
- `D:/laragon/www/vibeforge/references/login.html`
- `D:/laragon/www/vibeforge/references/modul_admin.html`
- `D:/laragon/www/vibeforge/references/modul_client.html`
- `D:/laragon/www/vibeforge/references/modul_manajemen.html`
- `D:/laragon/www/vibeforge/references/register.html`

> **Instruksi Khusus AI (Mode Redesain)**:
> 1. AI WAJIB membaca SELURUH file/folder di `references/` terlebih dahulu.
> 2. Susun & tulis ulang `docs/prd.md` dan `docs/branding.md` secara utuh berdasarkan analisa dari `references/`.
> 3. Konsolidasikan referensi menjadi 6 file HTML standar di `references/*.html` (`landingpage.html`, `login.html`, `register.html`, `modul_manajemen.html`, `modul_admin.html`, `modul_client.html`).


---

## 4. Protokol Pembangunan AI (Build Protocol - `docs/document.md`)

Setiap AI Coding Assistant (Claude Code CLI) WAJIB mengikuti urutan 3 Tahap Eksekusi di bawah ini secara linear:

### TAHAP 1 — AUDIT & RENCANA (Read-Only)
1. Baca `CLAUDE.md`, `docs/prd.md`, dan `docs/branding.md`.
2. Jika **Mode Redesain**: Baca seluruh folder `references/` -> tulis `docs/prd.md` & `docs/branding.md` -> konsolidasikan `references/*.html`.
3. Audit struktur file core (`include/config.php`, `core/router.php`, `public/core/router.php`, `core/session.php`, `core/csrf.php`, `core/Repo.php`, `modules/auth/`, `.env`, `data/users.json`).
4. Buat file `docs/build_plan.md` yang memuat mapping shell, file yang belum ada, dan daftar variabel environment.
5. **BERHENTI & TUNGGU APPROVAL OWNER** sebelum lanjut ke Tahap 2.

### TAHAP 2 — EKSEKUSI ONE-SHOT
1. Salin `.env.example` ke `.env` dan sesuaikan `APP_DISPLAY_NAME` serta `APP_TAGLINE`.
2. Update CSS variables di `public/assets/css/branding.css` sesuai `docs/branding.md`.
3. Buat hash Argon2ID valid untuk demo users di `data/users.json`.
4. Untuk setiap shell (`public/index.php`, `login/`, `register/`, `manajemen/`, `admin/`, `client/`):
   - Terapkan require header 4 file: `config.php`, `helper.php`, `session.php`, `csrf.php`.
   - Ekstrak seluruh teks statis menjadi key terjemahan di `locales/id.json`, `en.json`, dan `ar.json`.
   - Ganti nama aplikasi dengan `<?= APP_DISPLAY_NAME ?>`.
5. Validasi syntax PHP dengan `php -l` pada seluruh file `.php`.
6. Validasi fungsional dengan menjalankan server lokal sementara (`php -S localhost:8099 -t public`) dan tes HTTP 200 via `curl`.
7. **BERHENTI & TUNGGU APPROVAL OWNER** sebelum lanjut ke Tahap 3.

### TAHAP 3 — PREVIEW & VERIFIKASI
1. Pastikan document root webserver mengarah ke folder `public/`.
2. Akses `http://vibeforge.test/` atau `http://localhost/vibeforge/public/`.
3. Verifikasi auth flow, tombol quick-login demo, perantian bahasa i18n, dan fungsi logout.

---

## 5. Keamanan & User Demo Default

| Role | Email Demo | Password Demo |
|------|------------|---------------|
| Super Admin (Manajemen) | `admin@vibeforge.com` | `password123` |
| Creator (Admin) | `admin@vibeforge.id` | `password123` |
| Client (Pendengar) | `client@vibeforge.com` | `password123` |

- Security Baseline: Password Argon2ID, CSRF Token Validation, IP+Username Rate Limiting, Prepared Statements (PDO Dual-Mode Repo).

---

**Dibuat otomatis oleh Vibeforge Setup Wizard**