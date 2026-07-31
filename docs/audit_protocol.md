# Protocol Audit Dasar (Basic Technical Audit) - Vibeforge

## KONTEKS & TUJUAN

Dokumen ini digunakan oleh AI Auditor (assistant CLI / auditor teknis) untuk melakukan audit teknis secara komprehensif pada aplikasi berbasis **Vibeforge Template** — mematuhi arsitektur **13 Pilar Software (6 Lapisan)** di `CLAUDE.md`, baik saat struktur shell masih awal maupun setelah fitur bisnis dibangun.

Audit ini bertujuan untuk mengidentifikasi celah struktural, keamanan, dan kepatuhan arsitektur — bukan audit kelayakan bisnis.

> **CATATAN AUDIT (READ-ONLY):**
> - Di sesi audit ini, AUDITOR DILARANG mengubah kode atau memperbaiki file (kecuali menjalankan command validasi read-only seperti `php -l`).
> - Output audit ditulis ke file: `docs/AUDIT_BASIC.md`.
> - Jika audit membutuhkan analisis mendalam atau kesesuaian bisnis multi-mode, lanjutkan dengan `docs/audit_conformance_addendum.md`.

---

## URUTAN BACAAN WAJIB (LINEAR)

1. `CLAUDE.md` — Konstitusi teknis & arsitektur 13 Pilar Software (6 Lapisan)
2. `docs/prd.md` — Definisi spesifikasi produk, fitur, dan role pengguna
3. `docs/branding.md` — Identitas visual, warna, font, dan CSS variables
4. `references/*.html` — Golden reference template untuk setiap shell
5. `locales/*.json` — Manifest dan dictionary i18n (`id.json`, `en.json`, `ar.json`, `ja.json`, `languages.json`)
6. `.env` dan `.env.example` — Konfigurasi environment aktif vs template
7. Listing struktur folder aktual (mengabaikan `vendor/`, `node_modules/`, `.git/`, `cache/`, `.claude/`)

---

## STANDAR RUJUKAN

- **13 Pilar Software Architecture (@dwicuan / Vibeforge Alignment)**: 6 Lapisan (Inti Aplikasi, Keamanan, Tempat & Tenaga, Alur Kerja Aman, Performa & Skala, Keandalan)
- **PSR-12**: Coding Style Standard
- **12-Factor App**: Config, Statelessness, Environment Separation
- **OWASP ASVS Level 1-2**: Baseline Keamanan Aplikasi Web
- **Document Root Architecture**: `public/` sebagai satu-satunya web root publik Apache/Nginx
- **SPA Shell Architecture**: Navigasi AJAX tanpa full-page reload di dalam satu shell

---

## AUDIT CHECKLIST

### A. Entry Guard Consistency (CLAUDE.md §3b - Pilar 5)

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

- [ ] CEK KHUSUS: Pastikan TIDAK ADA file entry-point yang memakai `die()` — dapat menyebabkan blank screen WSOD.

---

### B. Router Proxy Pattern (CLAUDE.md §2b - Pilar 2) — KRITIS

- [ ] Apakah `public/core/router.php` ADA? (Router proxy - WAJIB)
- [ ] Apakah isinya benar:
  ```php
  <?php
  define('APP_ENTRY', true);
  require_once dirname(__DIR__, 2) . '/core/router.php';
  ```
- [ ] Apakah `core/router.php` (actual router) menggunakan Entry Guard yang benar?

---

### C. Dual-Mode Repo Pattern (CLAUDE.md §2c - Pilar 3 & §6c - Pilar 11)

- [ ] Apakah `core/Repo.php` ada dan mengimplementasikan:
  - `all()`, `find()`, `where()`, `insert()`, `update()`, `delete()`
  - Auto-detection per-entitas (SQL vs JSON)
  - Prepared statements untuk mode SQL
  - Mutex lock (`.lock`) + atomic write (`.tmp` → `rename()`) untuk mode JSON
  - Persistensi preferensi pengguna (`language_preference`, `theme_preference`)
- [ ] Apakah `DB_MODE` di `.env` valid: `auto`, `json`, atau `mysql`?

---

### D. Session & Auth State (CLAUDE.md §3a - Pilar 4 & §3b - Pilar 5)

- [ ] Apakah SEMUA shell memanggil `initSession()` di awal?
- [ ] Apakah role mapping konsisten:
  - `manajemen` -> `/manajemen/`
  - `admin` -> `/admin/`
  - `client` -> `/client/`
- [ ] Apakah landing page (`public/index.php`) melakukan validasi session SERVER-SIDE?

---

### E. Security Baseline (CLAUDE.md §3a - Pilar 4, §3b - Pilar 5, §6a - Pilar 9)

- [ ] Password: Argon2ID (`PASSWORD_ARGON2ID`)
- [ ] CSRF: Token validation dengan `hash_equals()` terpusat di `core/router.php`
- [ ] Rate Limiting: Fixed-window IP+Username (`core/ratelimit.php`)
- [ ] Prepared Statements: PDO untuk mode SQL
- [ ] Cookies: `HttpOnly`, `SameSite=Lax`, dan `Secure` (di production)

---

### F. i18n System & Multi-Bahasa (CLAUDE.md §2a - Pilar 1 & §6b - Pilar 10)

- [ ] Apakah `locales/languages.json` ada dan valid (`id`, `en`, `ar`, `ja`)?
- [ ] Apakah `locales/id.json`, `locales/en.json`, `locales/ar.json`, `locales/ja.json` ada dan selaras?
- [ ] Apakah flag assets ada di `public/assets/flags/`?
- [ ] Apakah algoritma `detectLanguage()` di `include/helper.php` mengimplementasikan 4-tier detection:
  1. Parameter URL (`?lang=xx`) + simpan ke DB (`language_preference` via `Repo`)
  2. Sesi Aktif (`$_SESSION['language']`)
  3. Database Preference User Logged-in
  4. IP-based GeoIP Detection (Liga Arab -> `ar`, mapped countries -> lang, fallback Arab -> `ar`, fallback lain -> `en`)
- [ ] Apakah DILARANG hardcode teks bahasa di file `public/*.php` atau `modules/*/*.php`?
- [ ] Apakah teks statis di PHP menggunakan `<?= t('key') ?>`?
- [ ] Apakah teks dinamis di JS menggunakan payload `window._i18n = { key: <?= json_encode(t('key')) ?> }`?
- [ ] Apakah language selector di header dikelilingi loop dinamis `foreach (getAvailableLanguages() as $code => $lang)`?
- [ ] Apakah layout RTL (`dir="rtl"`) diaktifkan otomatis untuk Bahasa Arab (`"rtl": true`)?

---

### G. Theme System (CLAUDE.md §2a - Pilar 1)

- [ ] Apakah `public/assets/css/branding.css` ada dengan CSS variables?
- [ ] Apakah theme toggle berfungsi dan sinkron dengan `<html data-theme="dark|light">`?
- [ ] Apakah theme preference disimpan di database (`theme_preference` column via `Repo`)?

---

### H. Logout Flow (CLAUDE.md §7b - Pilar 13)

- [ ] Apakah `public/logout/index.php` ada?
- [ ] Apakah logout TANPA konten HTML (langsung `session_destroy()` + redirect `header('Location: /')`)?

---

### I. PHP Syntax Validation (CLAUDE.md §5a - Pilar 8) — ZERO TOLERANCE

- [ ] Jalankan `php -l` pada SELURUH file `.php` di `public/`, `core/`, `include/`, `modules/`.
- [ ] Pastikan 0 parse error.

---

### J. Reference Template Compliance (CLAUDE.md §2a - Pilar 1)

- [ ] Apakah setiap shell mengikuti struktur `references/*.html` yang sesuai?
- [ ] Apakah SEMUA teks statis dan dinamis menggunakan sistem i18n (`t()` / `window._i18n`)?

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
