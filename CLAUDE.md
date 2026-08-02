# CLAUDE.md - Vibeforge Constitution (13 Pilar Software Architecture)

Dokumen ini adalah rujukan wajib untuk setiap sesi Claude Code CLI di project Vibeforge. Semua audit, pengembangan, dan pemeliharaan kode WAJIB mematuhi arsitektur **13 Pilar Software (6 Lapisan)** dan ketentuan teknis di bawah ini.

> **VIBEFORGE TEMPLATE**: Project ini adalah template PHP Native SPA untuk membangun aplikasi website berbasis *vibe coding*. 
> Konfigurasi alur instalasi: `public/install/` → `data/install_config.json` → `docs/install.md` → `docs/prd.md` & `docs/branding.md` → `references/` (Golden Template / Aplikasi Referensi) → `public/*.php` (SPA Shell).

---

## Peta Arsitektur: 13 Pilar dalam 6 Lapisan

| Lapisan | Pilar | Implementasi Vibeforge Framework |
|---|---|---|
| **1. Inti Aplikasi** | **1. Frontend** | SPA Shell Architecture (`public/*/index.php`), Golden HTML Templates (`references/*.html`), Dynamic Branding (`branding.css`), i18n System (GeoIP + Arab/English Fallback + Persistence), Responsive Nav & Scroll Spy. |
| | **2. API & Backend Logic** | Router Proxy (`public/core/router.php` → `core/router.php`), AJAX Module Controllers (`modules/*/*.php`), Helper (`include/helper.php`), JS i18n payload (`window._i18n`). |
| | **3. Database & Storage** | Data Access Layer Terpusat (`core/Repo.php` Auto-Switch SQL/JSON), Mutex Lock (`.lock`) + Atomic Write (temp+rename), Language Preference Storage (`language_preference` in `users.json`/MySQL), File Storage (`public/uploads/`). |
| **2. Keamanan** | **4. Authentication & Session** | Password Hashing Argon2ID, Session Core (`core/session.php`), Remember-Me Selector+Validator (`core/remember.php`), Re-authentication Middleware. |
| | **5. Role-Based Access (RBAC)** | Role-to-Shell Mapping (`manajemen`, `admin`, `client`), Guard `requireRole()`, Dual-Pattern Entry Guard (Pola 1 Entry vs Pola 2 Module). |
| **3. Tempat & Tenaga** | **6. Hosting & Deployment** | Document Root Apache `public/`, Laragon/XAMPP Vhost, Manual FTP Drag-Drop Deploy, Header Security (`public/.htaccess`). |
| | **7. Cloud Compute** | PHP 8.1+ Native Runtime, Strict Environment Isolation (`APP_ENV=development\|staging\|production`). |
| **4. Alur Kerja Aman** | **8. CI/CD & Version Control** | Semantic Versioning (`CHANGELOG.md`), Syntax Validation (`php -l`), 3-Tahap Build Protocol (Audit → Build → Verify), Non-Git FTP Deployment Rule. |
| **5. Performa & Skala** | **9. Rate Limiting** | IP + Username Fixed-Window Rate Limiter (`core/ratelimit.php`). |
| | **10. Cache & CDN** | Asset CDN Rules (Tailwind, Phosphor Icons, Google Fonts), CSP Header, In-Memory Translation Cache (`t()`). |
| | **11. Load Balancer & Scaling** | Auto-Switch Dual Mode (`json` → `mysql` per-entitas), Prepared Statements PDO, Stateless Server Session. |
| **6. Keandalan** | **12. Error Tracking & Logging** | Debug Log (`cache/debug.log` saat `APP_DEBUG=true`) vs Audit Trail (`data/audit_trail.json` append-only, permanen). |
| | **13. Availability & Recovery** | Atomic JSON Writes, Production Protection Guard (`DB_MODE=json` + Prod Block), Session Lifespan & Cookie Protection. |

---

## 1. Prinsip Kerja & Governance
- **Audit Dulu Baru Eksekusi**: Lakukan audit komprehensif dalam satu prompt, lalu eksekusi setelah direview manual oleh project owner.
  - Basic Audit (Step 1): `docs/audit_protocol.md` → Output: `docs/AUDIT_BASIC.md`
  - Conformance Audit (Step 2+): `docs/audit_conformance_addendum.md` → Output: `docs/AUDIT_CONFORMANCE.md`
- **Proteksi File Inti**: JANGAN mengubah `core/` atau `CLAUDE.md` dari dalam `modules/` tanpa izin tertulis dari project owner.
- **Single-Phase Execution**: Selesaikan satu tahap penuh, laporkan, dan tunggu persetujuan sebelum lanjut ke tahap berikutnya.
- **Validasi Bukti Konkret**: Klaim "selesai/diuji" dari CLI wajib disertai bukti nyata (path file, isi fungsi, atau hasil test `php -l`/`curl`). CLI tidak punya browser visual.
- **Perubahan Dokumen**: Perubahan pada `CLAUDE.md` hanya dilakukan di sesi terpisah khusus dengan persetujuan eksplisit owner.

---

## 2. LAPISAN 1 — Inti Aplikasi

### 2a. Pilar 1 — Frontend (SPA Shell, Golden References & Algoritma Deteksi i18n)
- **SPA Shell Architecture**: Clean-URL folders (`login/`, `register/`, `manajemen`, `admin`, `client`) berisi shell tipis (`index.php`) yang dimuat Apache tanpa `mod_rewrite`.
  - `public/index.php` berfungsi sebagai landing page atau redirect ke `/login/` tergantung konfigurasi Tahap 3B wizard.
  - Jika Landing Page tidak dicentang di wizard, `public/index.php` WAJIB berisi redirect PHP (`header('Location: /login/'); exit;`).
  - Jika Landing Page dicentang:
    - Jika Login & Register dicentang → Landing Page **WAJIB** menampilkan tombol "Masuk" (Login) dan "Daftar" (Register).
    - Jika hanya Login yang dicentang → Landing Page menampilkan tombol "Masuk" saja (tanpa "Daftar").
    - Jika Login TIDAK dicentang (hanya Landing Page) → Landing Page **TIDAK PERLU** menampilkan tombol Masuk/Daftar (halaman landing page publik tanpa auth).
  - **Aturan Landing ↔ Login (Mutlak)**: Minimal salah satu dari Landing Page atau Login harus aktif. Jika Login aktif, minimal satu role dari {manajemen, admin, client} WAJIB ada.
- **AJAX Navigation**: Perpindahan tab/state dalam 1 shell TIDAK BENTUK full-page reload. Gunakan AJAX ke `core/router.php`. Reload penuh hanya terjadi saat berpindah ANTAR shell.
- **Golden References (`references/*.html`)**: Setiap shell `public/xxx/index.php` HARUS mengikuti struktur HTML, styling CSS, dan komponen visual SAMA PERSIS dengan template di `references/*.html`:
  - `public/index.php` → `references/landingpage.html` (atau `references/index.html`/`index.php` yang di-convert ke SPA Shell)
  - `public/login/index.php` → `references/login.html` (atau file login referensi)
  - `public/register/index.php` → `references/register.html` (atau file register referensi)
  - `public/manajemen/index.php` → `references/modul_manajemen.html`
  - `public/admin/index.php` → `references/modul_admin.html`
  - `public/client/index.php` → `references/modul_client.html`
  *PENTING (Proses Analisis & Transformasi Referensi ke Public SPA Shell)*:
  1. **Multi-Asset & Multi-File Support**: Folder `references/` dapat berisi file HTML, PHP, CSS, JS, gambar (PNG/SVG/JPG), video (MP4/WebM), font, JSON, maupun seluruh struktur folder aplikasi lawas/legacy.
  2. **Audit & pemetaan Alur Lengkap**: AI WAJIB membaca dan memetakan alur komprehensif dari seluruh file referensi — menelusuri entry-point (`index.html`/`index.php`), relasi navigasi, penanganan aset (pindah ke `public/assets/`), hingga alur logika bisnis dan data.
  3. **Generasi Aplikasi Baru Berbasis Referensi**: Output aplikasi di `public/` WAJIB menjadi aplikasi baru yang bersih, modern, dan berfungsi penuh dengan alur bisnis yang SAMA PERSIS dengan referensi di `references/`, tetapi sepenuhnya menggunakan nama aplikasi, logo, palet warna, dan identitas visual dari `docs/branding.md` & `docs/prd.md` (DILARANG mempertahankan landing page atau branding bawaan Vibeforge Framework).
  5. **Mode Manual vs Auto**:
     - Cek keberadaan file di `references/`. Jika kosong, AI WAJIB men-generate file HTML referensi terlebih dahulu di `references/` berdasarkan konsep di `docs/prd.md` & `docs/branding.md`.
     - Jika `references/` berisi file/folder, AI WAJIB mengganti seluruh branding dan UI bawaan Vibeforge di `public/` (`public/index.php`, `public/login/`, dll) serta memperbarui file root (`.env`, `.env.example`, `README.md`, `LICENSE`, `CHANGELOG.md`) dengan nama aplikasi, tagline, dan konfigurasi baru.
  6. **Deteksi Otomatis Skema Database (SQL vs JSON)**:
     - AI WAJIB menelusuri SELURUH file di `references/` untuk mencari ketersediaan file/query/skema database SQL (`.sql` atau DDL/query SQL di file `.php`).
     - **Jika Ditemukan SQL/Query Database di `references/`**: DILARANG KERAS menggunakan skema/konsep JSON database (`data/*.json`). AI WAJIB menghapus seluruh konsep JSON, menyusun migrasi SQL di `migrations/`, mengkonfigurasi `.env` dengan `DB_MODE=mysql` (atau `auto`), dan langsung menampilkan/memproses data dari MySQL via `Repo::table()` sesuai query yang tertulis di `references/`. Seluruh fitur CRUD berjalan secara nyata terhadap database MySQL.
     - **Jika Tidak Ditemukan SQL/Query Database Sama Sekali di `references/`**: AI WAJIB mengkonfigurasi `DB_MODE=json`, menghapus seluruh konsep/koneksi SQL/MySQL, dan membuat file database JSON selengkap-lengkapnya di `data/*.json` yang mendukung seluruh fitur CRUD secara nyata dengan file locking & atomic write (`.lock` + `.tmp` → `rename()`).
  7. **Auto-Generate PRD & Branding (Wizard Auto-Mode)**:
     - Jika `prdMode = "auto"` dan/atau `brandingMode = "auto"` di `data/install_config.json` (dari Wizard `/install/`), AI WAJIB menganalisis seluruh aset di `references/` (HTML, PHP, media, alur aplikasi, skema SQL) untuk memunculkan `docs/prd.md` dan `docs/branding.md` secara otomatis.
     - Penulisan `docs/prd.md` WAJIB mempertahankan format standar 7 bagian (Problem Statement, Goals, Target User, User Stories, FR, NFR, Scope) dan `docs/branding.md` (6 bagian: Nama/Tagline, Value Prop, Target/Tone, Palet Warna, Typography, Logo) sesuai template wizard.
- **Branding & Theme Dinamis**: Warna, logo, dan font didefinisikan via `docs/branding.md` dan CSS variables di `public/assets/css/branding.css`. Nama aplikasi diambil dari `APP_DISPLAY_NAME` di `.env`. Theme dark/light dikontrol via `<html data-theme="dark|light">` dan disinkronkan ke `users.json` (`theme_preference`).
- **Navigasi Responsif & Scroll Spy**: Sidebar vertikal di Desktop, Bottom Nav di Mobile. Mobile bottom nav WAJIB menggunakan `IntersectionObserver` scroll spy untuk meng-highlight menu aktif secara otomatis.
- **Algoritma Deteksi Bahasa i18n & Persistensi Data (WAJIB)**:
  - Manifest terpusat di `locales/languages.json` (`id`, `en`, `ar`, `ja`, dsb).
  - **Urutan Prioritas Penentuan Bahasa (`detectLanguage()`)**:
    1. **Parameter URL (`?lang=xx`)**: Prioritas utama. Mengubah `$_SESSION['language']` dan secara otomatis mengupdate kolom `language_preference` pada entitas pengguna di database (`data/users.json` / MySQL via `Repo`).
    2. **Session Aktif (`$_SESSION['language']`)**: Menggunakan preferensi sesi jika pengguna sudah mengubah bahasa pada request sebelumnya.
    3. **Database Preference**: Jika user sudah login dan `language_preference` tersimpan di database, gunakan nilai tersebut.
    4. **GeoIP / IP Country Code Detection**:
       - Jika negara asal IP termasuk Liga Arab (SA, AE, EG, IQ, JO, MA, DZ, KW, QA, BH, OM, YE, SY, LB, SD, LY, TN, MR, PS, SO, DJ, KM) → gunakan Bahasa Arab (`'ar'`).
       - Jika negara terdaftar pada mapping manifest (ID, JP, US, GB, dsb) → gunakan bahasa sesuai mapping.
       - Jika kode negara IP tidak ada di `locales/languages.json`: jika terdeteksi negara Arab → fallback ke `'ar'`, untuk negara lainnya → fallback ke Bahasa Inggris (`'en'`).
  - **Aturan Bebas Hardcode Teks UI**: Seluruh string UI wajib menggunakan `<?= t('key.path') ?>` untuk PHP, dan `window._i18n = { key: <?= json_encode(t('key.path')) ?> }` untuk JavaScript.
  - **RTL Auto Layout**: Tag `<html>` otomatis diset `dir="rtl"` jika bahasa yang aktif memiliki properti `"rtl": true` di `locales/languages.json`.

### 2b. Pilar 2 — API & Backend Logic (Router Proxy & Controller Modules)
- **Document Root Architecture**: Document root Apache diset ke `public/`.
- **Router Proxy Pattern (WAJIB)**:
  `Browser fetch('/core/router.php')` → `public/core/router.php` (Proxy File) → `require_once dirname(__DIR__, 2) . '/core/router.php'`.
  *Tanpa proxy `public/core/router.php`, semua AJAX request akan return 404.*
- **AJAX Module Controller**: Modules diletakkan di `modules/{module}/{action}.php` (mis. `modules/auth/login.php`).
- **Autoloading Rule**: TIDAK ADA autoloader atau namespace. Include class/fungsi di `core/` dan `include/` secara eksplisit menggunakan `require_once`.

### 2c. Pilar 3 — Database & Storage (Repo Pattern Dual-Mode)
- **Data Access Layer Terpusat (`core/Repo.php`)**: Semua modul WAJIB mengakses data via `Repo::table('entitas')`. Dilarang query PDO atau baca/tulis JSON secara langsung di controller/module.
- **Mode Auto-Switch (`DB_MODE`)**:
  - `auto` (Default): Deteksi otomatis PER ENTITAS. Coba PDO ke MySQL; jika tabel ada → Pakai **SQL**, jika tabel tidak ada / connection error → Fallback ke **JSON** (`data/{entitas}.json`).
  - `json` (Force): Semua entitas paksa pakai JSON (gunakan saat TIDAK ada SQL/query database di `references/`).
  - `mysql` (Force): Semua entitas WAJIB SQL (gunakan saat ADA SQL/query database di `references/`). Halt + log error jika koneksi/tabel tidak ada.
- **Aturan Mutlak SQL vs JSON**: Jika `references/` berisi file/query SQL/database, AI WAJIB menghapus seluruh konsep JSON dan menggunakan `DB_MODE=mysql`/`auto` dengan migrasi SQL di `migrations/`. Jika `references/` tidak berisi SQL sama sekali, AI WAJIB membuat database JSON selengkap mungkin di `data/*.json` dengan file locking & atomic write.
- **User Preference Schema**: Kolom `theme_preference` dan `language_preference` tersimpan di `data/users.json` / tabel `users` MySQL.
- **JSON Write Safety**: Mutex via file lock `{entitas}.json.lock` + Atomic Write (`.tmp` → `rename()`).

---

## 3. LAPISAN 2 — Keamanan

### 3a. Pilar 4 — Authentication & Session Management
- **Password Hashing**: Argon2ID (`password_hash($pass, PASSWORD_ARGON2ID)`).
- **Session Security**: Session PHP server-side adalah satu-satunya sumber kebenaran status auth. Panggil `initSession()` di awal setiap shell. Cookie session WAJIB `HttpOnly`, `SameSite=Lax`, dan `Secure` (di production).
- **Remember-Me**: Selector + Validator token per device (`core/remember.php`), di-invalidate saat password berubah.
- **Re-authentication**: Prompt konfirmasi password untuk tindakan sensitif.

### 3b. Pilar 5 — Role-Based Access Control (RBAC) & Entry Guards
- **Role & Shell Mapping**:
  - Role `manajemen` → Super Admin / Administrator Utama (`public/manajemen/`)
  - Role `admin` → Creator / Admin Biasa (`public/admin/`)
  - Role `client` → Client / Consumer (`public/client/`)
  Setiap shell WAJIB memvalidasi role via `requireRole('role_name')`.
- **Dual-Pattern Entry Guard System**:
  - **Pola 1 — Entry-Point Files**: `defined('APP_ENTRY') or define('APP_ENTRY', true);`
  - **Pola 2 — Include & Module Files**: `if (!defined('APP_ENTRY')) { http_response_code(403); exit('Direct access forbidden'); }`

---

## 4. LAPISAN 3 — Tempat & Tenaga

### 4a. Pilar 6 — Hosting & Deployment
- **Hosting Target**: Laragon (Local Development) & FTP Shared Hosting / VPS (Production).
- **No Git Deployment**: Deploy dilakukan manual via FTP drag-drop.
- **Security Headers (`public/.htaccess`)**: Options -Indexes, proteksi file sensitif, & Content Security Policy (CSP).

### 4b. Pilar 7 — Cloud Compute & Environment Isolation
- **Runtime Target**: PHP 8.1+ Native.
- **Environment State (`APP_ENV`)**: `development` | `staging` | `production`.
- **Production Guard**: `DB_MODE=json` + Real Production Host WAJIB diblokir keras.

---

## 5. LAPISAN 4 — Alur Kerja Aman

### 5a. Pilar 8 — CI/CD & Version Control (Build Protocol)
- **Versioning**: SemVer di `CHANGELOG.md`.
- **PHP Syntax Validation**: Wajib linting manual `php -l`.
- **Linear 3-Tahap Build Protocol**: Audit → Build → Verify.

---

## 6. LAPISAN 5 — Performa & Skala

### 6a. Pilar 9 — Rate Limiting
- **Fixed-Window Limiter (`core/ratelimit.php`)**: Rate limiting IP + Username.

### 6b. Pilar 10 — Cache & CDN
- **In-Memory Locale Cache**: Static cache untuk `t()` dan `getAvailableLanguages()`.

### 6c. Pilar 11 — Scaling & Database Mode
- Prepared Statements PDO & Stateless Server Session.

---

## 7. LAPISAN 6 — Keandalan

### 7a. Pilar 12 — Error Tracking & Logging
- `cache/debug.log` (dev) vs `data/audit_trail.json` (append-only audit log).

### 7b. Pilar 13 — Availability & Recovery
- Atomic file operations + Logout Flow Pattern.
