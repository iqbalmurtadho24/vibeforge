# Protocol Audit Dasar (Basic Technical Audit) - Vibeforge

## KONTEKS & TUJUAN
Dokumen ini digunakan oleh AI Auditor (Claude Code CLI / auditor teknis) untuk melakukan audit teknis secara komprehensif pada aplikasi berbasis **Vibeforge Template** — baik saat struktur shell masih awal maupun setelah fitur bisnis dibangun.

Audit ini bertujuan untuk mengidentifikasi celah struktural, keamanan, dan kepatuhan arsitektur — bukan audit kelayakan bisnis.

> **CATATAN AUDIT (READ-ONLY):**
> Di sesi audit ini, AUDITOR DILARANG mengubah kode atau memperbaiki file (kecuali menjalankan command validasi read-only seperti `php -l`).
> Output audit ditulis ke file: `docs/AUDIT_BASIC.md`.
> Jika audit membutuhkan analisis mendalam atau kesesuaian bisnis multi-mode, lanjutkan dengan `docs/audit_conformance_addendum.md`.

---

## URUTAN BACAAN WAJIB (LINEAR)
1. `CLAUDE.md` — Konstitusi teknis & aturan arsitektur utama project
2. `docs/prd.md` — Definisi spesifikasi produk, fitur, dan role pengguna
3. `docs/branding.md` — Identitas visual, warna, font, dan CSS variables
4. `references/*.html` — Golden reference template untuk setiap shell
5. `locales/*.json` — Manifest dan dictionary i18n (`id.json`, `en.json`, `ar.json`, `languages.json`)
6. `.env` dan `.env.example` — Konfigurasi environment aktif vs template
7. Listing struktur folder aktual (mengabaikan `vendor/`, `node_modules/`, `.git/`, `cache/`, `.claude/`)

---

## STANDAR RUJUKAN
- **PSR-12**: Coding Style Standard
- **12-Factor App**: Config, Statelessness, Environment Separation
- **OWASP ASVS Level 1-2**: Baseline Keamanan Aplikasi Web
- **Document Root**: `public/` sebagai satu-satunya web root publik Apache/Nginx
- **SPA Shell Architecture**: Navigasi AJAX tanpa full-page reload di dalam satu shell

---

## AUDIT CHECKLIST

### A. Entry Guard Consistency (CLAUDE.md §8)
- [ ] Apakah SEMUA file entry-point (`public/index.php`, `public/login/index.php`, `public/register/index.php`, `public/manajemen/index.php`, `public/admin/index.php`, `public/client/index.php`, `public/core/router.php`, `core/router.php`) menggunakan `defined('APP_ENTRY') or define('APP_ENTRY', true);`?
- [ ] Apakah SEMUA file include/module (`include/*.php`, `core/*.php` selain `router.php`, `modules/**/*.php`) menggunakan cek murni TANPA `define()`:
  ```php
  if (!defined('APP_ENTRY')) {
      http_response_code(403);
      exit('Direct access forbidden');
  }
  ```
- [ ] CEK KHUSUS: Pastikan TIDAK ADA file entry-point (Pola 1) yang memakai `die()` — ini dapat menyebabkan blank screen "Direct access forbidden" bagi pengguna normal.

### B. Router Proxy Pattern (CLAUDE.md §3f) — KRITIS
- [ ] Apakah `public/core/router.php` ADA? (Tanpa proxy file ini, seluruh request AJAX login/logout/register akan 404).
- [ ] Apakah isinya sesuai standar: `define('APP_ENTRY', true);` dan `require_once dirname(__DIR__, 2) . '/core/router.php';`?
- [ ] Apakah `core/router.php` di project root ada dan menjadi pusat logika routing?
- [ ] Apakah root `.htaccess` tidak memblokir akses proxy ke `core/`?

### C. Path Resolution Correctness (CLAUDE.md §12e)
- [ ] Verifikasi require_once ke `include/` dan `core/` di setiap shell `public/` menggunakan depth path `../` yang benar (misal level-1 `__DIR__ . '/../include/'`, level-2 `__DIR__ . '/../../include/'`).

### D. Document Root Leakage & Access Control
- [ ] Apakah folder internal (`core/`, `include/`, `data/`, `modules/`, `cache/`) terlindung dari akses HTTP langsung?
- [ ] Apakah `.htaccess` di root dan `public/` sudah terkonfigurasi sebagai perlindungan berlapis?
- [ ] Pastikan `.env`, `include/config.php`, dan file `data/*.json` tidak dapat diunduh langsung via URL browser.

### E. Sensitive File Exposure
- [ ] Pastikan file sensitif (`.env`, `.log`, `.sql`, `.md`, `.bak`, `.old`) diblokir oleh `public/.htaccess`.
- [ ] Cek apakah ada file temporary atau backup sensitif yang tertinggal di folder `public/`.

### F. Session Security & Cross-Shell Auth State (CLAUDE.md §3b, §5)
- [ ] Apakah SELURUH shell (`public/index.php`, `login/`, `register/`, `manajemen/`, `admin/`, `client/`) memanggil `session_start()` / `initSession()` di baris awal sebelum output HTML?
- [ ] Apakah status login dan role divalidasi ulang di server-side pada setiap shell?
- [ ] Apakah shell role-restricted (`manajemen/`, `admin/`, `client/`) melakukan redirect ke `/login/` jika session tidak valid atau role mismatch?

### G. Logout Flow Pattern (CLAUDE.md §12f)
- [ ] Apakah `public/logout/index.php` HANYA memproses `session_destroy()`, header redirect `Location: /`, dan `exit;` TANPA merender output HTML/CSS/JS?

### H. Environment Configuration & Production Safety (CLAUDE.md §6)
- [ ] Apakah `APP_ENV` di `.env` terisi eksplisit (`development`, `staging`, atau `production`)?
- [ ] Apakah `.env.example` mencakup seluruh variabel yang digunakan aplikasi?
- [ ] Apakah tombol quick-fill login demo HANYA tampil jika `APP_ENV !== 'production'` dan divalidasi di server-side?
- [ ] Apakah kombinasi `DB_MODE=json` + `APP_ENV=production` pada server production sungguhan diblokir keras (halt + log error)?

### I. Demo Users & Password Hash Validity (CLAUDE.md §6b)
- [ ] Apakah password hash di `data/users.json` menggunakan format Argon2ID yang valid (`$argon2id$v=19$...`)?
- [ ] Apakah login module menerima `password123` HANYA saat `APP_ENV === 'development'`?

### J. Landing Page & Auth Button Consistency (CLAUDE.md §5, §12i)
- [ ] Apakah semua CTA landing page konsisten memakai variabel `$is_logged_in` server-side?
- [ ] Apakah tombol Masuk/Daftar berubah menjadi "Ke Dashboard" yang mengarah ke dashboard role sesuai saat user telah login?

### K. Golden Template Conformance (CLAUDE.md §12c, §3e) — KRITIS
Bandingkan setiap shell `public/` dengan template di `references/`:
- `public/index.php` vs `references/landingpage.html`
- `public/login/index.php` vs `references/login.html`
- `public/register/index.php` vs `references/register.html`
- `public/manajemen/index.php` vs `references/modul_manajemen.html`
- `public/admin/index.php` vs `references/modul_admin.html`
- `public/client/index.php` vs `references/modul_client.html`

### L. Internationalization (i18n) Completeness (CLAUDE.md §12d) — KRITIS
- [ ] Apakah `locales/languages.json`, `id.json`, `en.json`, `ar.json` ada dan berformat JSON valid?
- [ ] Apakah setiap key di `id.json` juga ada di `en.json` dan `ar.json`?
- [ ] Apakah SEMUA teks statis di HTML dan yang di-inject via JavaScript (template literals, AJAX fragment) menggunakan fungsi `t('key')` / `window._i18n`?
- [ ] Apakah `dir="rtl"` dan styling RTL diterapkan saat bahasa Arab (`ar`) dipilih?

### M. Theme System Dark/Light (CLAUDE.md §9)
- [ ] Apakah CSS variables dari `public/assets/css/branding.css` digunakan secara terpusat?
- [ ] Apakah preference tema disinkronkan ke field `theme_preference` user di `data/users.json`?

### N. Security Headers & CSP (CLAUDE.md §8b)
- [ ] Apakah `public/.htaccess` mengatur Content-Security-Policy (CSP) yang mengizinkan resource eksternal aktif (`unpkg.com`, `fonts.googleapis.com`, `cdn.tailwindcss.com`)?

### O. Data Access Layer & Dual-Mode Repo (CLAUDE.md §3g)
- [ ] Apakah seluruh modul menggunakan `Repo::table('nama_entitas')` tanpa query SQL / file read-write JSON manual langsung?
- [ ] Pada mode JSON: apakah penulisan data aman (atomic write via temp file + `.lock`)?
- [ ] Pada mode SQL: apakah query menggunakan PDO prepared statements?

### P. Setup Wizard & Instalasi (`public/install/`)
- [ ] Apakah setup wizard di `public/install/index.php` berjalan normal tanpa error JavaScript?
- [ ] Apakah tombol "Buka Folder" di Manajemen References membuka folder `references/` via AJAX backend?
- [ ] Apakah disk lokal terdeteksi otomatis dan dapat dikonfigurasi?
- [ ] Apakah penuntasan wizard berhasil menggenerate ulang `docs/install.md` sesuai mode (`new` vs `redesign`)?

### Q. PHP Syntax Validation (CLAUDE.md §12h) — ZERO TOLERANCE
- [ ] Jalankan `php -l` pada SELURUH file `.php` di `public/`, `core/`, `include/`, `modules/`.
- [ ] Pastikan 0 parse error.

---

## FORMAT OUTPUT AUDIT
Hasil audit WAJIB ditulis ke file **`docs/AUDIT_BASIC.md`** dengan struktur:

```markdown
# Laporan Audit Teknis Dasar (Basic Audit Report)

## [CHECKLIST-ID] Judul Checklist
**Lokasi:** path/file.php:line
**Kondisi Aktual:** ...
**Kondisi Standar:** ...
**Status:** ✅ OK / ❌ GAGAL / ⚠️ PERHATIAN
**Prioritas:** 🔴 Kritis / 🟠 Tinggi / 🟡 Sedang / 🟢 Rendah
**Rekomendasi Perbaikan:** ...

---

## RINGKASAN AUDIT
- Total Checklist: X
- ✅ Passed: X | ❌ Failed: X | ⚠️ Warnings: X
- Breakdown Prioritas: 🔴 X | 🟠 X | 🟡 X | 🟢 X

## TEMUAN KRITIS (BLOCKER)
1. ...

## REKOMENDASI PRIORITAS KERJA
1. ...
```
