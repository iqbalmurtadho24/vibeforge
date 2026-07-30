# Protocol Audit Dasar (Basic Technical Audit) - Vibeforge

## KONTEKS & TUJUAN

Dokumen ini digunakan oleh AI Auditor (the assistant CLI / auditor teknis) untuk melakukan audit teknis secara komprehensif pada aplikasi berbasis **Vibeforge Template** — baik saat struktur shell masih awal maupun setelah fitur bisnis dibangun.

Audit ini bertujuan untuk mengidentifikasi celah struktural, keamanan, dan kepatuhan arsitektur — bukan audit kelayakan bisnis.

> **CATATAN AUDIT (READ-ONLY):**
> - Di sesi audit ini, AUDITOR DILARANG mengubah kode atau memperbaiki file (kecuali menjalankan command validasi read-only seperti `php -l`).
> - Output audit ditulis ke file: `docs/AUDIT_BASIC.md`.
> - Jika audit membutuhkan analisis mendalam atau kesesuaian bisnis multi-mode, lanjutkan dengan `docs/audit_conformance_addendum.md`.

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

- [ ] Apakah SEMUA file entry-point menggunakan `defined('APP_ENTRY') or define('APP_ENTRY', true);`?
  - `public/index.php`
  - `public/login/index.php`
  - `public/register/index.php`
  - `public/manajemen/index.php`
  - `public/admin/index.php`
  - `public/client/index.php`
  - `public/core/router.php`
  - `core/router.php`

- [ ] Apakah SEMUA file include/module menggunakan cek murni TANPA `define()`:
  ```php
  if (!defined('APP_ENTRY')) {
      http_response_code(403);
      exit('Direct access forbidden');
  }
  ```
  - `include/*.php`
  - `core/*.php` (selain `router.php`)
  - `modules/**/*.php`

- [ ] CEK KHUSUS: Pastikan TIDAK ADA file entry-point yang memakai `die()` — dapat menyebabkan blank screen.

---

### B. Router Proxy Pattern (CLAUDE.md §3f) — KRITIS

- [ ] Apakah `public/core/router.php` ADA? (Router proxy - WAJIB)
- [ ] Apakah isinya benar:
  ```php
  <?php
  define('APP_ENTRY', true);
  require_once dirname(__DIR__, 2) . '/core/router.php';
  ```
- [ ] Apakah `core/router.php` (actual router) menggunakan Entry Guard yang benar?

---

### C. Dual-Mode Repo Pattern (CLAUDE.md §3g)

- [ ] Apakah `core/Repo.php` ada dan mengimplementasikan:
  - `all()`, `find()`, `where()`, `insert()`, `update()`, `delete()`
  - Auto-detection per-entitas (SQL vs JSON)
  - Prepared statements untuk mode SQL
  - File lock + atomic write untuk mode JSON

- [ ] Apakah `DB_MODE` di `.env` valid: `auto`, `json`, atau `mysql`?

---

### D. Session & Auth State (CLAUDE.md §5)

- [ ] Apakah SEMUA shell memanggil `session_start()` di awal?
- [ ] Apakah role mapping konsisten:
  - `manajemen` -> `/manajemen/`
  - `admin` -> `/admin/`
  - `client` -> `/client/`

- [ ] Apakah landing page (`public/index.php`) melakukan validasi session SERVER-SIDE?

---

### E. Security Baseline (CLAUDE.md §8)

- [ ] Password: Argon2ID (`PASSWORD_ARGON2ID`)
- [ ] CSRF: Token validation dengan `hash_equals()`
- [ ] Rate Limiting: IP+Username based
- [ ] Prepared Statements: PDO untuk mode SQL

---

### F. i18n System (CLAUDE.md §12d)

- [ ] Apakah `locales/languages.json` ada dan valid?
- [ ] Apakah `locales/id.json`, `locales/en.json`, `locales/ar.json` ada?
- [ ] Apakah flag assets ada di `public/assets/flags/`?
- [ ] Apakah helper functions `t()`, `detectLanguage()`, `getAvailableLanguages()` ada di `include/helper.php`?
- [ ] Apakah language selector ada di header/navbar?

---

### G. Theme System (CLAUDE.md §9)

- [ ] Apakah `public/assets/css/branding.css` ada dengan CSS variables?
- [ ] Apakah theme toggle berfungsi?
- [ ] Apakah theme preference disimpan di database (`theme_preference` column)?

---

### H. Logout Flow (CLAUDE.md §12f)

- [ ] Apakah `public/logout/index.php` ada?
- [ ] Apakah logout TANPA konten HTML (langsung `session_destroy()` + redirect)?

---

### I. PHP Syntax Validation (CLAUDE.md §12h) — ZERO TOLERANCE

- [ ] Jalankan `php -l` pada SELURUH file `.php` di `public/`, `core/`, `include/`, `modules/`.
- [ ] Pastikan 0 parse error.

---

### J. Reference Template Compliance (CLAUDE.md §12c)

- [ ] Apakah setiap shell mengikuti struktur `references/*.html` yang sesuai?
- [ ] Apakah SEMUA teks statis menggunakan `t()` function?

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

---

**Untuk audit lanjutan (multi-mode, governance, integrasi pihak ketiga), gunakan `docs/audit_conformance_addendum.md`.**
