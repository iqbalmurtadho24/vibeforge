# CLAUDE.md - Vibeforge Constitution

Dokumen ini adalah rujukan wajib untuk setiap sesi Claude Code CLI di project
ini. Baca lengkap sebelum audit atau eksekusi kode apapun.

> **VIBEFORGE TEMPLATE**: Project ini adalah template Vibeforge untuk membangun
> aplikasi website apapun. Branding, warna, dan fitur spesifik aplikasi didefinisikan
> di `docs/prd.md` dan `docs/branding.md`. Semua kode harus mendukung
> konfigurasi ulang tanpa harus rewrite.

---

## 1. Prinsip Kerja
- Audit dulu secara komprehensif dalam satu prompt, baru eksekusi one-shot
  setelah direview manual oleh project owner.
- JANGAN mengubah `core/` atau file ini (`CLAUDE.md`) dari dalam `modules/`
  tanpa persetujuan eksplisit tertulis dari project owner di prompt yang sama.
- Tidak ada Git. Deploy manual via FTP drag-drop. Jangan asumsikan versioning
  otomatis, jangan sarankan command git.
- Single-phase execution per sesi: selesaikan satu tahap penuh, laporkan,
  tunggu approval sebelum lanjut tahap berikutnya.
- Klaim "sudah selesai"/"sudah diuji" dari CLI tidak dianggap valid tanpa
  bukti konkret (path file, isi fungsi, atau hasil test manual via
  browser). CLI tidak punya browser/HTTP client, tidak bisa membuktikan
  klaim fungsional sendiri.

### Audit Prompts
Pilih audit prompt yang sesuai dengan fase project:
- **docs/audit_protocol.txt** - Basic audit untuk Step 1 (shell kosong/konfigurasi dasar)
- **docs/audit_conformance_addendum.txt** - Comprehensive audit setelah fitur bisnis mulai
  diisi ke shell (manajemen/admin/client). Output: docs/STANDARD_AUDIT.md

## 2. Stack Teknis
- Native PHP (tanpa framework), Tailwind CSS (CDN atau build lokal),
  Alpine.js untuk interaktivitas ringan, vanilla JS untuk sisanya.
- Laragon untuk development lokal, FTP untuk deploy production.
- Database dual-mode: JSON (`data/*.json`) untuk dummy/testing tanpa DB,
  MySQL untuk production. Ditentukan oleh `DB_MODE` di `.env`.
- Autoloading: PSR-4 manual via `spl_autoload_register()` (tidak pakai
  Composer), memetakan namespace ke path folder sesuai konvensi PSR-4.
- Coding style: PSR-12.
- Versioning: SemVer, dicatat di `CHANGELOG.md`.
- Dokumentasi API (jika ada endpoint publik nanti): OpenAPI 3.0 di
  `docs/openapi.yaml`.
- Keamanan: OWASP ASVS Level 1-2 sebagai baseline minimum.

## 3. Arsitektur - Vibeforge SPA Shell Architecture

### 3a. Shell Architecture
Setiap folder clean-URL (`login/`, `register/`, `manajemen/`, `admin/`,
`client/`) berisi shell tipis (`index.php`) yang di-load Apache otomatis
saat folder diakses tanpa nama file, TANPA mod_rewrite.

Di dalam satu shell, semua perpindahan state/tab TIDAK boleh full-page
reload. Gunakan AJAX ke `core/router.php` untuk load konten module.
Reload penuh hanya terjadi saat pindah ANTAR shell (misal login -> client).

**Konsep Aplikasi**: Tipe aplikasi (e-commerce, dashboard, media player, dll)
ditentukan di `docs/prd.md`. Struktur modul dan fitur menyesuaikan.

`login.php`/`register.php`/`logout.php` BUKAN file mandiri, melainkan
module AJAX di bawah `modules/auth/`, dipanggil dari dalam shell
`login/index.php` melalui `core/router.php`.

### 3b. Auth-State Lintas Shell
- session PHP server-side adalah SATU-SATUNYA sumber kebenaran untuk status
  login dan role.
- Setiap shell (`manajemen/index.php`, `admin/index.php`, `client/index.php`,
  juga `public/index.php`) WAJIB memanggil `session_start()` dan validasi
  ulang `user_id`+`role` dari server SENDIRI saat pertama kali dimuat.
- Kalau validasi gagal, redirect paksa ke `/login/`.

### 3c. Role & Shell Mapping

| Shell Folder     | Allowed Role | Akses Level                    | Template Reference             |
|------------------|-------------|--------------------------------|-------------------------------|
| manajemen/       | manajemen   | Super Admin (full access)     | `references/modul_manajemen.html` |
| admin/           | admin       | Creator (produksi)      | `references/modul_admin.html`     |
| client/          | client      | Client             | `references/modul_client.html`    |

**Catatan Penting:**
- Role `manajemen` = Super Admin (dashboard overview, approve kreator, moderasi
  konten/audit, manajemen user, keuangan — lihat `docs/prd.md` Section D)
- Role `admin` = Creator (upload karya, analitik performa, manajemen
  royalti & penarikan dana — untuk Artis/Pendakwah/Podcaster/Munsyid
  terverifikasi, lihat `docs/prd.md` Section C). Nama role tetap `admin`
  secara teknis (folder shell, kolom `role` di `data/users.json`, dst) —
  yang berubah di sini hanyalah definisi konseptualnya, bukan penamaan kode.
- Role `client` = Pendengar (Free & Premium) — lihat `docs/prd.md` Section B.
  Moderasi konten dan manajemen user BUKAN tanggung jawab role `admin`;
  keduanya sepenuhnya berada di bawah role `manajemen` sesuai `docs/prd.md`
  Section D, konsisten dengan yang sudah dijelaskan di atas.

### 3d. Branding Dinamis
Nama aplikasi, logo, warna diambil dari `docs/prd.md`
dan dikonfigurasi di `.env`. Tidak ada hardcode nama aplikasi di kode.

### 3e. File Reference Template

Setiap shell di `public/xxx/index.php` HARUS mengikuti struktur dan styling
SAMA PERSIS yang ditulis di `references/*.html` yang sesuai:

| Shell File                              | Template Reference                  |
|-----------------------------------------|-------------------------------------|
| `public/index.php`                      | `references/landingpage.html`         |
| `public/login/index.php`                | `references/login.html`               |
| `public/register/index.php`             | `references/register.html`            |
| `public/manajemen/index.php`           | `references/modul_manajemen.html`     |
| `public/admin/index.php`                | `references/modul_admin.html`         |
| `public/client/index.php`               | `references/modul_client.html`        |

## 3f. Router Proxy Pattern (Document Root Architecture)

Document root Apache adalah `public/`, sehingga `core/router.php` yang ada di
project root tidak dapat diakses langsung oleh browser. Framework menggunakan
**router proxy pattern** untuk解决这个问题:

```
Request Flow (AJAX):
+---------------------------------------------------------------------+
|  Browser JS                                                         |
|  fetch('/core/router.php')                                         |
|              +                                                      |
|  Apache document root = public/                                      |
|              +                                                      |
|  public/core/router.php (PROXY FILE) - WAJIB ADA!                  |
|              +                                                      |
|  require_once '../core/router.php'                                  |
|              +                                                      |
|  project-root/core/router.php (ACTUAL)                              |
+---------------------------------------------------------------------+
```

**File WAJIB yang harus dibuat:**
- `public/core/router.php` - Router proxy (wajib ada, bukan optional!)

**Template `public/core/router.php`:**
```php
<?php
/**
 * App - Router Proxy
 *
 * Proxies AJAX requests to the actual router in parent directory.
 * Created because Apache document root is set to public/.
 */
define('APP_ENTRY', true);

// Include the actual router (go up 2 levels: public/core -> public -> project root)
require_once dirname(__DIR__, 2) . '/core/router.php';
```

> **KRITIS**: Jika router proxy tidak ada, semua AJAX request (login, logout,
> register, dll) akan mengembalikan 404 dan login TIDAK AKAN BEKERJA.

> Error yang muncul: "Server returned invalid response" atau "Invalid JSON response"
> di console browser.

## 4. Struktur Folder

project-root/
+-- CLAUDE.md
+-- README.md
+-- CHANGELOG.md
+-- LICENSE                  <- Apache-2.0
+-- SECURITY.md
+-- .env
+-- .env.example
+-- .gitignore
+-- public/                  <- document root
|   +-- index.php            <- landing page
|   +-- login/index.php
|   +-- register/index.php
|   +-- manajemen/index.php
|   +-- admin/index.php
|   +-- client/index.php
|   +-- core/
|   |   +-- router.php       <- WAJIB: Router proxy (lihat Section 3f)
|   +-- assets/
|   |   +-- css/branding.css <- CSS variables (sumber warna utama)
|   |   +-- flags/           <- Flag images untuk i18n (id.png, en.png, ar.png)
|   +-- uploads/
|   +-- .htaccess            <- Options -Indexes, blok akses .env/.log/.sql/.md,
|                               CSP untuk icons/fonts (lihat Section 8b)
+-- core/                    <- router.php, auth, session, Repo (dual-mode)
+-- include/                 <- config.php, helper.php
+-- modules/                 <- modul AJAX per role, tidak boleh ubah core/
|   +-- auth/                <- login.php, register.php, logout.php
+-- data/                    <- JSON dummy (users.json, dst)
+-- cache/                   <- debug.log (lihat Section 7)
+-- locales/                 <- File terjemahan i18n
|   +-- id.json             <- Indonesia
|   +-- en.json             <- English
|   +-- ar.json             <- Arabic
+-- docs/
|   +-- prd.md               <- DEFINISI APLIKASI SPESIFIK
|   +-- branding.md          <- WARNA, TYPOGRAPHY, LOGO APLIKASI
|   +-- install.md
|   +-- audit_protocol.txt
|   +-- audit_conformance_addendum.txt
|   +-- openapi.yaml
+-- references/              <- template shell (golden reference), lihat Section 3e/12c
|   +-- landingpage.html       <- template shell landing page
|   +-- login.html             <- template shell login
|   +-- register.html          <- template shell register
|   +-- modul_manajemen.html
|   +-- modul_admin.html
|   +-- modul_client.html
+-- migrations/               <- SQL, dieksekusi hanya saat final/production
+-- .htaccess                <- Require all denied (proteksi kedua jika
                                 document root salah arah)

## 5. Session, Role, dan Redirect Landing Page
- Session menyimpan `user_id` dan `role` (`manajemen` | `admin` | `client`).
- `public/index.php` WAJIB cek session di server-side (`session_start()` di
  awal file, sebelum HTML), bukan JS client-side, untuk hindari flicker dan
  untuk keamanan.
- Mapping role -> shell:
  ```php
  $role_to_shell = [
      'manajemen' => '/manajemen/',
      'admin'     => '/admin/',
      'client'    => '/client/',
  ];
  ```
- Semua CTA di landing page (navbar, hero, CTA tengah halaman, footer) HARUS
  konsisten: kalau `$is_logged_in`, tombol Daftar/Masuk diganti satu tombol
  "Ke Dashboard" mengarah ke `$dashboard_url` sesuai role. Gunakan satu
  variabel di awal file, jangan cek session berulang per section.
- **Konfigurasi Nama Aplikasi**: Nama yang ditampilkan diambil dari variabel
  `APP_DISPLAY_NAME` di `.env`, bukan hardcode di kode.

## 6. Environment & Fitur Development-Only
- `APP_ENV` wajib eksplisit di `.env`: `development` | `staging` | `production`.
  Jangan biarkan variabel ini kosong/tidak ada — environment-gate akan gagal
  diam-diam jika demikian.
- Tombol quick-fill login (isi otomatis email+password di form untuk
  mempercepat preview) HANYA tampil jika `APP_ENV != production`. Validasi
  ini WAJIB juga di server-side kalau ada endpoint backend terkait, tidak
  cukup disembunyikan lewat CSS/JS di client. Implementasi Wajib:
  a) Di HTML/JS: cek `<?= (defined('APP_ENV') && APP_ENV !== 'production') ?>`
     untuk menampilkan tombol
  b) Tombol memanggil fungsi JavaScript (`quickLoginFill(email, password)`)
     untuk mengisi field form, BUKAN langsung submit
  c) Demo password default: `password123`
  d) Server-side WAJIB validasi: tolak login demo di production
- **`DB_MODE=json` + `APP_ENV=production`**:
  - Di `development`/`staging`: kombinasi ini SAH dan tidak diblokir,
    log warning saja ke `cache/debug.log` — dibutuhkan untuk fleksibilitas
    testing sebelum migrasi MySQL selesai.
  - Di server production sungguhan (bukan lokal/staging — dideteksi lewat
    domain/host, bukan hanya nilai `APP_ENV`): kombinasi ini WAJIB diblokir
    keras (halt + pesan error jelas ke log, jangan render aplikasi),
    karena riwayat project menunjukkan `APP_ENV=production` pernah
    ke-set tanpa sadar di lingkungan yang bukan production sungguhan.
    Jangan ulangi kesalahan itu dengan cara sebaliknya (production asli
    jalan diam-diam pakai data dummy JSON).

## 6b. Demo Users & Development Data

Framework menyediakan default users untuk testing. Data ada di `data/users.json`:

| Role      | Email Default      | Password    |
|-----------|--------------------|-------------|
| Manajemen | admin@[app].com    | password123 |
| Admin     | admin@[app].id     | password123 |
| Client    | client@[app].com   | password123 |

**Generate Argon2ID Hash (WAJIB untuk semua user di JSON):**
```bash
# Command untuk generate hash (Laragon PHP):
php -r "echo password_hash('password123', PASSWORD_ARGON2ID);"

# Atau dengan path lengkap (Windows Laragon):
D:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe -r "echo password_hash('password123', PASSWORD_ARGON2ID);"
```

> **KRITIS**: Password hash di `data/users.json` HARUS valid (di-generate dengan
> command di atas). Hash placeholder/invalid akan menyebabkan login gagal.

**Demo Users Template (SALIN KE `data/users.json`):**
```json
[
    {
        "id": "usr_001",
        "name": "Super Admin",
        "email": "admin@[app].com",
        "password_hash": "$argon2id$v=19$m=65536,t=4,p=1$...",
        "role": "manajemen",
        "theme_preference": "dark",
        "created_at": "2024-01-01T00:00:00Z",
        "status": "active"
    }
]
```

**Aturan Demo Mode:**
- Demo users SELALU menggunakan Argon2ID hashed password di JSON
- Login module WAJIB accept `password123` sebagai valid password saat `APP_ENV === 'development'`
- Di production, hanya Argon2ID hash yang valid
- Jangan gunakan password plain text di `data/users.json`

## 7. Logging - Dua Jenis, Jangan Dicampur
- **`cache/debug.log`**: khusus error development (PHP notice/warning/fatal,
  exception, query gagal). Aktif hanya jika `APP_DEBUG=true`. BOLEH dihapus
  manual atau via script setelah error diperbaiki. Ini yang dibaca Claude
  Code CLI untuk troubleshooting saat preview.
- **Audit trail** (`data/audit_trail.json` atau tabel `audit_log`):
  mencatat semua aksi POST (login, ubah data, dst), APPEND-ONLY, tidak
  pernah dihapus manual, berjalan terus tanpa syarat `APP_DEBUG`. Ini bukan
  debug log — JANGAN pernah menyarankan atau mengeksekusi penghapusan
  isinya kecuali diminta eksplisit dengan alasan bisnis jelas.

## 8. Keamanan Baseline (wajib di semua modul baru)
- Password: Argon2ID (`password_hash()` dengan `PASSWORD_ARGON2ID`).
- CSRF: token wajib di-generate dan diverifikasi dengan `hash_equals()`,
  terpusat di `core/router.php`, bukan per-file.
- Remember-me: selector+validator token, per-device logout, invalidate
  semua token saat password berubah.
- Rate limiting progresif berbasis IP+username.
- Re-authentication middleware untuk aksi sensitif.
- `requireRole()` guard di setiap file module.
- Prepared statements via PDO wrapper (mode MySQL); JSON mode tetap
  divalidasi input-nya meski tanpa SQL injection risk langsung.

### Entry Guard - Dua Pola, Jangan Tertukar

Entry guard adalah mekanisme mencegah akses langsung ke file yang seharusnya
hanya di-include/run via entry point. Ada **dua pola** yang HARUS
dipilih sesuai peran file. Perbedaan intinya: siapa yang **mendefinisikan**
konstanta `APP_ENTRY`, dan siapa yang **cuma memeriksanya**. Kalau kedua
pola dicampur (file yang seharusnya hanya memeriksa ikut mendefinisikan
sendiri), proteksinya jadi self-satisfying dan mati total tanpa gejala
visual apapun — lihat riwayat #2 di bawah.

**Pola 1 - Entry-point file (dirancang untuk diakses langsung via browser):**
```
public/index.php
public/login/index.php
public/register/index.php
public/manajemen/index.php
public/admin/index.php
public/client/index.php
public/core/router.php      <- WAJIB: router proxy (bukan core/router.php)
core/router.php             <- Actual router (di-include oleh proxy)
```

File Pola 1 adalah satu-satunya yang boleh MENDEFINISIKAN konstanta ini:
```php
defined('APP_ENTRY') or define('APP_ENTRY', true);
```

> `define()` - TIDAK pakai `die()`. Entry point mendefinisikan constanta
> ini agar file Pola 2 yang di-include-nya (config.php, helper, module,
> dsb) lolos dari cek murni di bawah.

**Pola 2 - Include/module file (TIDAK dirancang untuk diakses langsung):**
```
include/config.php
include/helper.php
core/auth.php
core/session.php
modules/auth/login.php
modules/*/...
```

File Pola 2 **DILARANG mendefinisikan konstanta ini sendiri.** Gunakan
HANYA pemeriksaan murni, tanpa `define()`:
```php
if (!defined('APP_ENTRY')) {
    http_response_code(403);
    exit('Direct access forbidden');
}
```

> **Kenapa bukan `defined('APP_ENTRY') or define(...)` untuk Pola 2:**
> kalau file Pola 2 ikut men-define, kondisi pemeriksaannya sudah
> terpenuhi oleh dirinya sendiri SEBELUM sempat dicek — jadi blok
> "tolak akses langsung" tidak akan pernah bisa gagal, baik file itu
> di-include lewat router MAUPUN diakses langsung lewat URL. Proteksi
> anti-akses-langsung untuk file Pola 2 jadi tidak pernah aktif di
> manapun, meski kodenya terlihat sudah "ada guard".

**Contoh alur yang benar:**

```
# public/core/router.php (Pola 1, entry point)
defined('APP_ENTRY') or define('APP_ENTRY', true);
require_once ...;   // include modules/auth/login.php

# modules/auth/login.php (Pola 2, module) - diakses via router
if (!defined('APP_ENTRY')) {          // APP_ENTRY sudah true dari router.php
    http_response_code(403);          // jadi baris ini TIDAK dieksekusi,
    exit('Direct access forbidden');  // login.php lanjut jalan normal
}

# modules/auth/login.php diakses LANGSUNG via URL (tanpa lewat router)
if (!defined('APP_ENTRY')) {          // APP_ENTRY belum pernah di-define
    http_response_code(403);          // baris ini DIEKSEKUSI,
    exit('Direct access forbidden');  // akses langsung berhasil diblokir
}
```

> **Kesalahan fatal yang pernah terjadi (riwayat #1):** entry-point Pola 1
> pakai `die()` tanpa `define()`. Akibatnya: halaman blank "Direct access
> forbidden" untuk semua user, termasuk akses normal via browser. SOLUSI:
> entry-point Pola 1 GUNAKAN `define()`, BUKAN `die()`.
>
> **Kesalahan fatal yang pernah terjadi (riwayat #2):** perbaikan riwayat #1
> di atas sempat diterapkan juga ke file Pola 2 (include/module), sehingga
> file tersebut ikut men-define `APP_ENTRY` sendiri alih-alih hanya
> memeriksanya. Efeknya proteksi anti-akses-langsung di SEMUA file Pola 2
> mati diam-diam tanpa gejala visual — baru ketahuan saat diaudit langsung
> kode per file. SOLUSI: Pola 1 tetap pakai `define()`, Pola 2 WAJIB pakai
> cek murni TANPA `define()` seperti contoh di atas. Jangan pernah
> menyamakan solusi kedua riwayat ini — akar masalahnya berbeda kategori
> file.

### Content Security Policy (CSP) Configuration

Framework menggunakan resource eksternal:
- **Phosphor Icons**: `https://unpkg.com/@phosphor-icons/web`
- **Tailwind CSS**: `https://cdn.tailwindcss.com`
- **Google Fonts**: `https://fonts.googleapis.com`

CSP harus mengizinkan resource ini. Update `public/.htaccess`:

**CSP untuk Development (`public/.htaccess`):**
```apache
<IfModule mod_headers.c>
    Header always set Content-Security-Policy "default-src 'self' 'unsafe-inline' 'unsafe-eval' https: data: blob:; script-src 'self' 'unsafe-inline' 'unsafe-eval' https: blob:; style-src 'self' 'unsafe-inline' 'unsafe-eval' https: blob: data:; font-src 'self' https: data: blob: https://unpkg.com https://fonts.gstatic.com https://fonts.googleapis.com; img-src 'self' data: https: blob:; connect-src 'self' https:; frame-ancestors 'self';"
</IfModule>
```

**Troubleshooting CSP:**
1. Clear browser cache (Settings -> Privacy -> Clear browsing data)
2. Hard refresh: `Ctrl + Shift + R`
3. Restart Apache di Laragon
4. Check console browser untuk CSP violation errors
5. Jika error: pastikan CSP di `.htaccess` sudah benar dan Apache sudah di-restart

**Catatan Production:**
- Di production, gunakan Tailwind CSS build lokal (bukan CDN)
- Hosting Phosphor Icons sendiri atau gunakan CSP Report-Only mode
- CSP yang lebih ketat harus dikonfigurasi sesuai kebutuhan aplikasi

### Module Loading di Router

`core/router.php` memuat module dengan path:
```
modules/{module}/{action}.php
```

Contoh:
- `module=auth` + `action=login` -> `modules/auth/login.php`
- `module=auth` + `action=register` -> `modules/auth/register.php`

Module adalah file Pola 2 (lihat "Entry Guard - Dua Pola" di atas). Module
WAJIB memiliki:
1. Cek murni TANPA `define()` di baris pertama:
   `if (!defined('APP_ENTRY')) { http_response_code(403); exit('Direct access forbidden'); }`
   — JANGAN memakai `defined('APP_ENTRY') or define(...)` di module, karena
   itu membuat module men-define dirinya sendiri sebelum sempat diperiksa,
   sehingga proteksi anti-akses-langsungnya tidak pernah bisa aktif.
2. Include `config.php` dan `helper.php` dengan path yang benar:
   ```php
   if (!defined('ROOT_PATH')) {
       define('ROOT_PATH', dirname(__DIR__, 2));
   }
   require_once ROOT_PATH . '/include/config.php';
   require_once ROOT_PATH . '/include/helper.php';
   ```

## 9. Tema Dark/Light
- CSS variable di `public/assets/css/branding.css`, satu sumber warna untuk
  seluruh landing page dan modul login/register (lihat `docs/branding.md`
  untuk daftar lengkap token warna).
- Selector: `<html data-theme="dark|light">`.
- Preference disimpan di `data/users.json` per user (kolom
  `theme_preference`, default `"dark"`), dibaca saat load shell — BUKAN
  hanya localStorage, supaya konsisten lintas device.
- **Branding Variables**: Semua warna, logo, dan elemen branding dikonfigurasi
  melalui `docs/branding.md` dan CSS variables di `branding.css`.

## 10. Navigasi Responsif
- Desktop: sidebar (nav-item vertikal).
- Mobile: bottom nav (mobile-nav-item horizontal).
- Breakpoint dan struktur item navigasi: lihat `docs/prd.md` Section
  UI Component Taxonomy.
- **Template visual**: `references/modul_*.html` berisi contoh lengkap struktur
  sidebar desktop + bottom nav mobile per role. Gunakan sebagai referensi
  saat membangun atau memodifikasi navigasi.

## 11. Integrasi Universal
- **API External**: Framework mendukung integrasi dengan API manapun.
  Konfigurasi API keys dan endpoints di `.env`.
- **Webhooks**: Sistem webhook generik untuk integrasi dengan layanan pihak ketiga.
- **OAuth/SSO**: Dukungan untuk integrasi OAuth 2.0 (Google, GitHub, dll).
- **Payment Gateway**: Arsitektur siap integrasi payment gateway (Midtrans,
  Xendit, Stripe, dll) sesuai kebutuhan aplikasi di `docs/prd.md`.
- **Push Notifications**: Arsitektur siap untuk OneSignal atau Firebase Cloud Messaging.
- **File Storage**: Mendukung storage lokal atau cloud (AWS S3, GCS, dll).

## 12. Governance
- Perubahan pada dokumen ini (`CLAUDE.md`) hanya di sesi terpisah khusus
  untuk itu, dengan persetujuan eksplisit project owner, tidak dicampur
  dengan sesi audit/eksekusi fitur.
- Scope creep (menambah fitur/endpoint yang tidak diminta di prompt) adalah
  pelanggaran. Kalau CLI menemukan sesuatu yang perlu perbaikan di luar
  scope prompt, laporkan sebagai rekomendasi, JANGAN eksekusi tanpa
  persetujuan eksplisit dalam prompt terpisah.

## 12b. Troubleshooting Guide

### Login Gagal - "Server returned invalid response" / "Invalid JSON response"

**Penyebab:** Router proxy `public/core/router.php` tidak ada atau salah path.

**Cek dan Solusi:**
1. Pastikan file `public/core/router.php` ada
2. Cek isi file:
   ```php
   <?php
   define('APP_ENTRY', true);
   require_once dirname(__DIR__, 2) . '/core/router.php';
   ```
3. Pastikan path `dirname(__DIR__, 2)` benar (harus naik 2 level: public/core -> public -> root)
4. Restart Apache di Laragon
5. Clear browser cache
6. Test login lagi

### Icons Tidak Muncul (Phosphor Icons Error)

**Penyebab:** CSP (Content Security Policy) memblokir `unpkg.com`

**Cek di browser console:**
```
Loading the script 'https://unpkg.com/@phosphor-icons/web' violates CSP directive
Loading the font '<URL>' violates CSP directive
```

**Solusi:**
1. Clear browser cache (Settings -> Privacy -> Clear browsing data)
2. Hard refresh: `Ctrl + Shift + R`
3. Cek `public/.htaccess` — pastikan CSP mengizinkan `https://unpkg.com`
4. Restart Apache di Laragon
5. Jika masih error, cek CSP section di Section 8b dokumen ini

### Password Hash Invalid / Login Selalu Gagal

**Penyebab:** Hash password di `data/users.json` tidak valid atau placeholder.

**Cek dan Solusi:**
1. Buka `data/users.json`
2. Pastikan password hash formatnya valid (dimulai dengan `$argon2id$v=19$...`)
3. Jika hash terlihat random/tidak valid, regenerate:
   ```bash
   php -r "echo password_hash('password123', PASSWORD_ARGON2ID);"
   ```
4. Update `users.json` dengan hash baru
5. Test login dengan `password123`

### Halaman Blank Putih (White Screen of Death)

**Penyebab:**
- Entry guard error (`die()` di entry point)
- PHP fatal error yang tidak ditampilkan

**Solusi:**
1. Cek `public/.htaccess` — pastikan tidak ada `php_flag display_errors Off`
2. Cek entry point files — Pastikan pakai `define()`, BUKAN `die()`
3. Cek `include/config.php` dan `include/helper.php` — Pastikan pakai Pola 2 (`die()`)
4. Enable error reporting sementara:
   ```php
   error_reporting(E_ALL);
   ini_set('display_errors', 1);
   ```

### AJAX Request Return 404

**Penyebab:**
- Root `.htaccess` memblokir akses ke `core/`
- Document root tidak punya router proxy

**Solusi:**
1. Cek root `.htaccess` — pastikan `core/` tidak diblokir
2. Pastikan `public/core/router.php` ada (router proxy)
3. Restart Apache

### Demo Login Button Tidak Muncul

**Penyebab:**
- `APP_ENV=production` di `.env`
- Quick fill button di-hide oleh CSS

**Solusi:**
1. Buka `.env`
2. Set `APP_ENV=development`
3. Pastikan kondisi di HTML:
   ```php
   <?php if (defined('APP_ENV') && APP_ENV !== 'production'): ?>
       <!-- Quick login buttons -->
   <?php endif; ?>
   ```

### Session Tidak Persisten / Auto Logout

**Penyebab:**
- Cookie session tidak disimpan
- Session lifetime terlalu pendek

**Solusi:**
1. Cek `include/config.php` — pastikan `SESSION_LIFETIME` cukup besar
2. Cek browser — pastikan cookies diizinkan
3. Cek HTTPS setting:
   ```php
   ini_set('session.cookie_secure', isProduction() ? '1' : '0');
   ```

## 12c. Source of Truth - references/*.html Files

Setiap halaman shell WAJIB mengikuti template referensi yang sesuai dengan
exact match pada struktur dan styling. `references/*.html` adalah BASELINE
STRUKTURAL MINIMUM dan referensi visual/layout — bukan sumber teks final,
dan bukan langit-langit yang membatasi elemen fungsional yang disyaratkan
section lain di dokumen ini (mis. §6 demo quick-login, §12d i18n).

### Aturan Golden:

1. **SEMUA elemen UI struktural** (warna, ikon, layout, spacing, hierarki
   section) HARUS sama persis dengan `references/*.html` yang sesuai.

2. **YANG BOLEH berbeda** antara referensi dan implementasi:
   - Data dinamis (nama user, email, dsb)
   - Login state (authenticated vs anonymous)
   - Theme preference (dark vs light)
   - Kata-kata literal teks (lihat poin 4 — teks WAJIB lewat i18n, sehingga
     hasil akhirnya boleh berbeda dari string mentah di references/*.html, asal
     makna/fungsi label tetap setara)
   - Elemen fungsional tambahan yang disyaratkan section lain meski tidak
     digambarkan di references/*.html (contoh: tombol quick-login dev-only §6,
     language selector §12d) — TAMBAHKAN elemen ini, jangan menunggu
     references/*.html diupdate dulu

3. **YANG TIDAK BOLEH berbeda**:
   - Struktur HTML (semua section yang digambarkan di references/*.html tetap
     harus ada — section BOLEH ditambah sesuai poin 2, TIDAK BOLEH
     dikurangi)
   - CSS classes dan pendekatan styling
   - Ikon dan gambar
   - Layout dan spacing

4. **Teks statis WAJIB lewat sistem i18n** (`t('key')`, lihat §12d), bukan
   hardcode — termasuk teks yang di-inject via JavaScript (lihat subsection
   "Cakupan Wajib i18n" di §12d). Teks di dalam `references/*.html`
   diperlakukan sebagai DRAFT yang wajib diekstrak jadi key di
   `locales/*.json` (nilai default berbahasa Indonesia masuk ke
   `locales/id.json`), BUKAN disalin literal ke kode PHP. Kalau poin 1
   (kesamaan visual) dan poin 4 (i18n) tampak berbenturan pada satu elemen
   teks, poin 4 yang menang — golden template mengatur STRUKTUR dan
   TAMPILAN, bukan string literal per bahasa.

### Checklist Saat Membuat/Memperbaiki Halaman:

```
SAAT MEMBUAT HALAMAN BARU:
1. Copy `references/*.html` yang sesuai sebagai base
2. Tambahkan PHP header (session, config, helper)
3. GANTI semua static text dengan <?= t('key') ?>
4. GANTI hardcoded values dengan PHP variables
5. VERIFIKASI visual match dengan referensi

SAAT AUDIT HALAMAN:
1. Bandingkan struktur HTML dengan references/*.html
2. Cek semua section yang ada di referensi
3. Cek semua teks menggunakan t() function
4. Pastikan tidak ada hardcoded branding
```

### Mapping Template Reference ke Shell:

| Shell File                  | Template Reference                  | Fungsi                         |
|-----------------------------|--------------------------------------|--------------------------------|
| `public/index.php`          | `references/landingpage.html`         | Landing page publik            |
| `public/login/index.php`    | `references/login.html`               | Halaman login                  |
| `public/register/index.php` | `references/register.html`            | Halaman registrasi             |
| `public/manajemen/index.php` | `references/modul_manajemen.html`   | Super Admin Dashboard          |
| `public/admin/index.php`   | `references/modul_admin.html`         | Creator  Dashboard       |
| `public/client/index.php`  | `references/modul_client.html`        | Client Player    |
| `public/assets/css/branding.css`  | `docs/branding.md`        | Style Brand Identity    |

## 12d. Internationalization (i18n) System

Framework mendukung multi-bahasa dengan IP-based detection dan manual selection.

### Struktur File Terjemahan

```
locales/
+-- languages.json  # WAJIB - manifest: daftar bahasa yang tersedia
+-- id.json          # Indonesia (default untuk ID, MY, SG, BN IP)
+-- en.json          # English (default untuk US, GB, AU, dll IP)
+-- ar.json          # العربية (default untuk SA, AE, EG, dll IP)
```

`locales/languages.json` adalah SATU-SATUNYA sumber kebenaran untuk daftar
bahasa yang tersedia di selector UI — kode bahasa, nama tampilan, path ikon
bendera, dan arah RTL. Menambah bahasa baru = tambah satu entry di file ini
+ buat `locales/xx.json` yang sesuai, TANPA mengubah kode PHP apapun.

**Format `locales/languages.json`:**
```json
{
    "id": { "name": "Bahasa Indonesia", "flag": "/assets/flags/id.svg", "rtl": false },
    "en": { "name": "English", "flag": "/assets/flags/en.svg", "rtl": false },
    "ar": { "name": "العربية", "flag": "/assets/flags/ar.svg", "rtl": true }
}
```

### Format JSON Terjemahan

```json
{
    "app_name": "[Nama Aplikasi]",
    "tagline": "Audio Islami. Temani kamu di setiap momen.",
    "nav.home": "Beranda",
    "nav.categories": "Kategori",
    "nav.popular": "Populer",
    "nav.premium": "Premium",
    "hero.title": "Dengarkan Suara <br><span>Ketenangan Hati.</span>",
    "hero.subtitle": "[Nama Aplikasi] menemani setiap momenmu dengan ribuan nasyid...",
    "hero.cta.listen": "Mulai Mendengarkan",
    "hero.cta.explore": "Jelajahi Kategori",
    "social_proof.active_listeners": "Pendengar aktif setiap hari.",
    "categories.title": "Jelajahi Kategori",
    "categories.nasyid": "Nasyid",
    "categories.quran": "Quran",
    "categories.kajian": "Kajian",
    "categories.podcast": "Podcast",
    "categories.dzikir": "Dzikir",
    "popular.title": "Paling Banyak Didengarkan",
    "premium.banner.title": "Tanpa Batas, Tanpa Iklan",
    "premium.banner.subtitle": "Dengarkan ribuan konten Islami dengan kualitas tinggi...",
    "premium.banner.cta": "Coba Gratis 30 Hari",
    "features.title": "Mengapa Mendengarkan di [Nama Aplikasi]?",
    "features.hd_quality": "Kualitas Audio HD",
    "features.hd_quality.desc": "Nikmati lantunan ayat suci dan nasyid dengan kejernihan maksimal...",
    "features.offline": "Dengarkan Offline",
    "features.offline.desc": "Unduh audio favoritmu dan dengarkan kapan saja di mana saja...",
    "features.personal": "Kurasi Personal",
    "features.personal.desc": "Dapatkan rekomendasi playlist harian yang disesuaikan...",
    "features.no_ads": "100% Bebas Iklan",
    "features.no_ads.desc": "Fokus beribadah, merenung, dan mencari inspirasi...",
    "testimonials.title": "Apa Kata Sahabat [Nama Aplikasi]?",
    "faq.title": "Pertanyaan Seputar [Nama Aplikasi]",
    "faq.paid.title": "Apakah aplikasi [Nama Aplikasi] berbayar?",
    "faq.paid.content": "Anda dapat menikmati sebagian besar konten [Nama Aplikasi] secara gratis...",
    "faq.offline.title": "Bagaimana cara mengaktifkan Mode Offline?",
    "faq.offline.content": "Mode offline eksklusif untuk pengguna Premium...",
    "faq.creator.title": "Apakah saya bisa menjadi podcaster/kreator di [Nama Aplikasi]?",
    "faq.creator.content": "Tentu! Kami selalu mencari suara-suara inspiratif baru...",
    "cta.title": "Siap Menemukan Ketenangan Hati?",
    "cta.subtitle": "Bergabunglah dengan ribuan pendengar lainnya...",
    "cta.button": "Daftar Sekarang Secara Gratis",
    "footer.company": "Perusahaan",
    "footer.about": "Tentang Kami",
    "footer.careers": "Karir",
    "footer.community": "Komunitas",
    "footer.legal": "Legal",
    "footer.privacy": "Pusat Privasi",
    "footer.copyright": "2026 [Nama Aplikasi]. All rights reserved.",
    "auth.login": "Masuk",
    "auth.register": "Daftar",
    "auth.logout": "Keluar",
    "auth.email": "Email",
    "auth.password": "Password",
    "auth.dashboard": "Dashboard",
    "common.see_all": "Lihat semua",
    "common.loading": "Memuat..."
}
```

### Helper Functions (Tambahkan ke include/helper.php)

```php
if (!defined('LOCALES_PATH')) {
    define('LOCALES_PATH', dirname(__DIR__) . '/locales');
}

if (!defined('LANGUAGES_MANIFEST')) {
    define('LANGUAGES_MANIFEST', LOCALES_PATH . '/languages.json');
}

if (!function_exists('escape')) {
    function escape(?string $value): string {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Get translation for a key
 */
function t(string $key, string $fallback = ''): string {
    static $translations = null;
    static $currentLang = null;

    if ($currentLang === null) {
        $currentLang = $_SESSION['language'] ?? detectLanguage();
    }

    if ($translations === null || !isset($translations[$currentLang])) {
        $file = LOCALES_PATH . '/' . $currentLang . '.json';

        if (file_exists($file)) {
            $translations[$currentLang] = json_decode(file_get_contents($file), true) ?? [];
        } else {
            // Fallback ke Indonesia
            $fallbackFile = LOCALES_PATH . '/id.json';
            if (file_exists($fallbackFile)) {
                $translations[$currentLang] = json_decode(file_get_contents($fallbackFile), true) ?? [];
            } else {
                $translations[$currentLang] = [];
            }
        }
    }

    return $translations[$currentLang][$key] ?? ($fallback ?: $key);
}

/**
 * Detect language based on IP and preference.
 *
 * Bahasa default MENGIKUTI negara asal IP visitor (bukan hardcode 'id').
 * Kalau kode negaranya terdeteksi tapi TIDAK ADA di $countryToLang (belum
 * punya mapping bahasa), catat ke cache/debug.log supaya project owner bisa
 * menyiapkan locale/mapping untuk negara tsb, lalu fallback ke English
 * ('en') sebagai default universal — BUKAN ke 'id'.
 */
function detectLanguage(): string {
    // 1. Cek session/cookie dulu
    if (!empty($_SESSION['language'])) {
        return $_SESSION['language'];
    }

    // 2. Cek parameter URL - validasi terhadap locales/languages.json,
    //    BUKAN daftar hardcoded, supaya bahasa baru otomatis diterima.
    if (!empty($_GET['lang']) && in_array($_GET['lang'], getAvailableLocaleCodes(), true)) {
        $_SESSION['language'] = $_GET['lang'];
        return $_GET['lang'];
    }

    // 3. IP-based detection (simplified - gunakan API untuk production)
    $ip = getClientIp();

    // Untuk demo/testing, gunakan simple geo detection
    // Production: gunakan ip-api.com atau ipinfo.io
    $countryCode = getCountryCodeFromIP($ip);

    $countryToLang = [
        // ASEAN (default Indonesia)
        'ID' => 'id', 'MY' => 'id', 'SG' => 'id', 'BN' => 'id', 'TL' => 'id',
        // English speaking countries
        'US' => 'en', 'GB' => 'en', 'AU' => 'en', 'NZ' => 'en', 'CA' => 'en',
        // Arabic speaking countries
        'SA' => 'ar', 'AE' => 'ar', 'EG' => 'ar', 'IQ' => 'ar', 'JO' => 'ar',
        'MA' => 'ar', 'DZ' => 'ar', 'KW' => 'ar', 'QA' => 'ar', 'BH' => 'ar',
        'OM' => 'ar', 'YE' => 'ar', 'SY' => 'ar', 'LB' => 'ar', 'SD' => 'ar',
    ];

    // Negara terdeteksi tapi belum ada mapping bahasa -> catat ke cache/debug.log
    // (lihat Section 7, guard APP_DEBUG sama seperti error log development
    // lainnya) supaya owner tahu negara mana yang perlu ditambahkan bahasanya,
    // lalu fallback ke English.
    if (!isset($countryToLang[$countryCode])) {
        if (defined('APP_DEBUG') && APP_DEBUG) {
            $logLine = sprintf(
                "[%s] [i18n] Negara \"%s\" (IP: %s) belum punya mapping bahasa di detectLanguage(). "
                . "Fallback ke \"en\". Tambahkan mapping negara ini + locales/xx.json jika perlu.\n",
                date('Y-m-d H:i:s'),
                $countryCode,
                $ip
            );
            @file_put_contents(ROOT_PATH . '/cache/debug.log', $logLine, FILE_APPEND);
        }
        return 'en';
    }

    return $countryToLang[$countryCode];
}

/**
 * Get country code from IP (simplified for demo)
 * Production: gunakan API seperti ip-api.com/json/{IP}
 */
function getCountryCodeFromIP(string $ip): string {
    // Untuk demo - return berdasarkan IP range
    // Ganti dengan implementasi nyata untuk production

    // Localhost/testing
    if ($ip === '127.0.0.1' || $ip === '::1' || strpos($ip, '192.168.') === 0) {
        return 'ID'; // Default ke Indonesia untuk dev
    }

    // Untuk demo purposes, assume Indonesia
    // Real implementation would call a GeoIP API
    return 'ID';
}

/**
 * Get available languages - dibaca dinamis dari locales/languages.json,
 * BUKAN hardcoded. Hanya kode yang punya file locales/xx.json yang
 * ditampilkan.
 */
function getAvailableLanguages(): array {
    static $languages = null;

    if ($languages === null) {
        $languages = [];
        $raw = file_exists(LANGUAGES_MANIFEST) ? file_get_contents(LANGUAGES_MANIFEST) : false;
        $manifest = $raw !== false ? (json_decode($raw, true) ?? []) : [];

        foreach ($manifest as $code => $meta) {
            if (!file_exists(LOCALES_PATH . "/{$code}.json")) {
                continue;
            }
            $languages[$code] = [
                'code' => $code,
                'name' => $meta['name'] ?? strtoupper($code),
                'flag' => $meta['flag'] ?? "/assets/flags/{$code}.svg",
                'rtl'  => (bool) ($meta['rtl'] ?? false),
            ];
        }
    }

    return $languages;
}

/**
 * Language codes that have both a manifest entry and a translation file.
 */
function getAvailableLocaleCodes(): array {
    return array_keys(getAvailableLanguages());
}

/**
 * Check if current language is RTL
 */
function isRtlLanguage(): bool {
    $lang = $_SESSION['language'] ?? 'id';
    $languages = getAvailableLanguages();
    return $languages[$lang]['rtl'] ?? false;
}
```

### Language Selector UI

Di semua header/navbar, tambahkan language selector di SAMPING tombol
theme toggle. Template:

```html
<!-- Language Selector (di Header) -->
<div class="relative group" x-data="{ open: false }">
    <button
        @click="open = !open"
        @click.away="open = false"
        class="flex items-center gap-1.5 px-2 py-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
        aria-label="Change Language"
    >
        <?php $currentLang = $_SESSION['language'] ?? detectLanguage(); ?>
        <img
            src="<?= escape(getAvailableLanguages()[$currentLang]['flag'] ?? '/assets/flags/_default.svg') ?>"
            onerror="this.onerror=null;this.src='/assets/flags/_default.svg';"
            alt="<?= $currentLang ?>"
            class="w-5 h-3.5 rounded-sm shadow-sm"
        >
        <i class="ph ph-caret-down text-xs text-gray-500"></i>
    </button>

    <div
        x-show="open"
        x-transition
        class="absolute right-0 mt-1 bg-white dark:bg-gray-900 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 py-1 min-w-[140px] z-50"
    >
        <?php foreach (getAvailableLanguages() as $code => $lang): ?>
        <a href="?lang=<?= $code ?>" class="flex items-center gap-2 px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors <?= ($_SESSION['language'] ?? 'id') === $code ? 'text-brand-gold' : 'text-gray-700 dark:text-gray-300' ?>">
            <img src="<?= escape($lang['flag']) ?>" onerror="this.onerror=null;this.src='/assets/flags/_default.svg';" class="w-5 h-3.5 rounded-sm"> <?= escape($lang['name']) ?>
        </a>
        <?php endforeach; ?>
    </div>
</div>
```

> **JANGAN** hardcode daftar `<a href="?lang=...">` satu-per-satu di HTML —
> selalu `foreach (getAvailableLanguages() as $code => $lang)` supaya
> selector otomatis bertambah/berkurang mengikuti `locales/languages.json`
> tanpa menyentuh kode shell.

### RTL Support untuk Bahasa Arab

Tambahkan di `<html>` tag:
```php
<html lang="<?= $_SESSION['language'] ?? 'id' ?>"
      dir="<?= isRtlLanguage() ? 'rtl' : 'ltr' ?>"
      class="<?= $themePreference === 'light' ? '' : 'dark' ?>">
```

Tambahkan CSS untuk RTL:
```css
[dir="rtl"] {
    direction: rtl;
    text-align: right;
}

[dir="rtl"] .ms-auto { margin-left: auto; margin-right: 0; }
[dir="rtl"] .me-auto { margin-right: auto; margin-left: 0; }
/* dst - adjust spacing utilities as needed */
```

### Flag Assets

Download flag PNG files dan simpan di `public/assets/flags/`:
- `id.png` - Flag Indonesia (150x100px recommended)
- `en.png` - Flag United Kingdom / US
- `ar.png` - Flag Saudi Arabia / Arab

Gunakan format 150x100px, 24-bit PNG dengan background transparan.

### Cakupan Wajib i18n: Termasuk Konten yang Di-inject via JavaScript

Section 3a mewajibkan arsitektur SPA — perpindahan state/tab TANPA
full-page reload, konten dimuat/diganti lewat JavaScript. Aturan `t()` di
atas TIDAK cukup hanya diterapkan pada teks yang dirender PHP langsung ke
HTML awal saat page load — WAJIB juga diterapkan pada SEMUA teks yang
di-inject ke DOM lewat JavaScript setelah page load, termasuk:
- Template literal (backtick string) di dalam `<script>` yang di-assign ke
  `innerHTML`/`textContent`.
- Fragment HTML hasil AJAX (baik dari `core/router.php` maupun endpoint
  lain).
- Pesan dinamis dari logic JS murni (contoh: greeting berdasarkan jam,
  pesan sukses/error, label yang dihasilkan kondisional di JS).

**Dua cara yang SAH untuk memenuhi ini:**
1. Interpolasi `<?= t('key') ?>` langsung di dalam template literal JS, di
   blok `<script>` yang dirender PHP — dieksekusi sekali saat shell
   dimuat, nilai sudah final sebelum sampai ke browser.
2. Objek JS yang di-populate dari PHP sekali di awal shell (contoh:
   `window._i18n = { greetingMorning: <?= json_encode(t('...')) ?>, ... };`),
   lalu direferensikan dari variabel itu di seluruh logic JS murni. JANGAN
   hardcode string bahasa apapun langsung di JavaScript.

> **Kesalahan yang pernah terjadi:** shell chrome (sidebar/header/nav)
> sudah memakai `t()` dengan benar, sementara konten dinamis (isi tab/
> halaman yang di-inject via JS) 100% hardcode satu bahasa. Ganti bahasa
> lewat selector terlihat berfungsi (chrome berubah), padahal isi halaman
> sama sekali tidak berubah. Audit visual sekilas TIDAK cukup untuk
> menangkap ini — WAJIB grep manual string bahasa non-`t()` di dalam blok
> `<script>` setiap shell sebelum menyatakan i18n selesai.

## 13. Multi-Project Template
- File ini adalah template Vibeforge yang bisa di-clone/reuse untuk project
  baru. Setiap reuse:
  1. Baca `docs/prd.md` untuk mendefinisikan aplikasi spesifik
  2. Baca `docs/branding.md` untuk mengkonfigurasi branding
  3. Update `.env` dengan konfigurasi spesifik project
  4. Update `references/*.html` template sesuai kebutuhan aplikasi
  5. **WAJIB**: Buat `public/core/router.php` sebagai router proxy (lihat Section 3f)
  6. **WAJIB**: Setup CSP di `public/.htaccess` (lihat Section 8b)
  7. **WAJIB**: Buat `locales/*.json` dengan terjemahan untuk i18n
  8. **WAJIB**: Download flag assets ke `public/assets/flags/`
- Section berikut GENERIK dan berlaku untuk semua project: 1, 2, 3, 3a-3f, 4,
  5, 6, 6b, 7, 8, 8b, 9, 10, 11, 12, 12a-12d, 13.
- Section berikut SPESIFIK-FITUR (abaikan jika tidak relevan): 3 (konsep
  aplikasi), 6, 9, 10, 11.
- Saat reuse: rename `APP_` prefix jika ingin namespacing khusus per project
  (opsional, tapi seringk kali disarankan untuk menghindari konflik).

## 12e. Path Resolution Reference (WAJIB untuk Shell Baru)

Setiap shell di `public/` memiliki depth berbeda. Gunakan template ini:

| Shell Location                 | Path ke include/          | Path ke core/              |
|-------------------------------|--------------------------|---------------------------|
| `public/index.php`            | `__DIR__ . '/../include/'` | `__DIR__ . '/../core/'`    |
| `public/login/index.php`       | `__DIR__ . '/../../include/' | `__DIR__ . '/../../core/` |
| `public/logout/index.php`      | `__DIR__ . '/../../include/' | `__DIR__ . '/../../core/` |
| `public/client/index.php`      | `__DIR__ . '/../../include/' | `__DIR__ . '/../../core/` |
| `public/admin/index.php`       | `__DIR__ . '/../../include/' | `__DIR__ . '/../../core/` |
| `modules/auth/*.php`          | `ROOT_PATH . '/include/'    | `ROOT_PATH . '/core/'      |

**Template Standar Shell Baru:**
```php
<?php
defined('APP_ENTRY') or define('APP_ENTRY', true);

require_once __DIR__ . '/../../include/config.php';
require_once __DIR__ . '/../../include/helper.php';

initSession();
// ... shell logic ...
```

## 12f. Logout Flow Pattern (WAJIB)

Logout HANYA berfungsi sebagai redirector session destroyer.

**Alur yang Benar:**
1. User klik link `/logout/`
2. Shell langsung proses: `session_destroy()` + `header('Location: /')` + `exit`
3. Redirect ke landing page TANPA konten HTML

**Contoh `public/logout/index.php yang BENAR:**
```php
<?php
defined('APP_ENTRY', true);

require_once __DIR__ . '/../../include/config.php';
require_once __DIR__ . '/../../include/helper.php';

initSession();
$_SESSION = [];
session_destroy();

header('Location: /');
exit;
```

**PERATURAN:**
- TIDAK ada konten HTML/CSS/JS di logout shell
- WAJIB destruksi session SEBELUM redirect
- Validasi auth state di landing page via PHP session check

## 12g. Scroll Spy untuk Mobile Navigation

Mobile bottom nav WAJIB memiliki scroll spy agar menu aktif saat section visible.

**HTML dengan class marker:**
```html
<a href="#kategori" class="mobile-nav-item text-gray-400">...</a>
<section id="kategori">...</section>
```

**JavaScript Scroll Spy:**
```javascript
const sectionIds = ['kategori', 'terbaru', 'faq', 'testimoni'];

function updateNavHighlight(activeId) {
    document.querySelectorAll('.mobile-nav-item').forEach(item => {
        item.classList.remove('text-brand-gold');
        item.classList.add('text-gray-400');
    });
    const active = document.querySelector(`.mobile-nav-item[href="#${activeId}"]`);
    if (active) {
        active.classList.add('text-brand-gold');
        active.classList.remove('text-gray-400');
    }
}

const observer = new IntersectionObserver((entries) => {
    entries.forEach(e => { if (e.isIntersecting) updateNavHighlight(e.target.id); });
}, { rootMargin: '-40% 0px -40% 0px', threshold: 0 });

sectionIds.forEach(id => {
    const s = document.getElementById(id);
    if (s) observer.observe(s);
});
```

## 12h. PHP Syntax Validation Checklist

SEBELUM deploy/review, WAJIB validasi manual di CLI:

```bash
php -l public/logout/index.php
php -l public/client/index.php
php -l include/config.php
php -l include/helper.php
```

**Enforcement:**
1. Error syntax WAJIB diperbaiki terlebih dahulu
2. BARU test di browser
3. Verifikasi auth flow (login -> logout -> login)

## 12i. Auth State Konsistensi Desktop & Mobile

Auth buttons (Login/Dashboard/Logout) WAJIB konsisten di SEMUA lokasi:

| Lokasi      | Posisi Auth Button           |
|-------------|----------------------------|
| Desktop Nav | Kanan header                |
| Mobile Nav  | TENGAH bottom nav, elevated  |
| Hero CTA    | Bawah heading               |
| Footer      | Tidak untuk auth (optional)  |

**Auth Button Rules:**
- Desktop: kanan header dengan inline styling
- Mobile: tengah bottom nav dengan floating design
- Login state: tampilkan Dashboard + Logout
- Logout state: tampilkan Masuk + Daftar
- Auth state di-trigger PHP session, BUKAN JavaScript toggle

**Checklist Konsistensi:**
- [ ] Auth buttons di desktop header
- [ ] Auth buttons di mobile bottom nav
- [ ] Hero CTA sesuai auth state
- [ ] Scroll spy berfungsi di mobile nav



