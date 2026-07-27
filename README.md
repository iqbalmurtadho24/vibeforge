# 🔥 Vibeforge — Wujudkan Aplikasi dari Dokumen ke Kode Jadi

> **Template starter PHP (Native) untuk membangun aplikasi web modern dengan pendekatan *vibe coding*: Anda menjelaskan aplikasi lewat dokumen (`docs/prd.md`, `docs/branding.md`), AI Coding Assistant (Claude Code, Cursor, Copilot CLI) yang mewujudkannya jadi kode fungsional lengkap.**

[![License: Apache-2.0](https://img.shields.io/badge/License-Apache%202.0-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/PHP-8.1%2B-8892BF.svg?logo=php)](https://php.net)
[![AI Assisted](https://img.shields.io/badge/AI%20Assisted-Vibe%20Coding-FF6B35.svg?logo=anthropic)](#)
[![No Framework](https://img.shields.io/badge/No%20Framework-Native%20PHP-000000.svg?logo=php)](https://php.net)
[![Security](https://img.shields.io/badge/Security-OWASP%20ASVS%20L1--2-green.svg)](#-security)
[![i18n](https://img.shields.io/badge/i18n-ID%20%7C%20EN%20%7C%20AR-blue.svg)](#-internationalization-i18n)

---

## 🎯 Apa Itu Vibeforge?

**Vibeforge bukan framework** — ini **template + protokol kerja** yang memungkinkan Anda:

| Cara Tradisional | Vibeforge (Vibe Coding) |
|------------------|-------------------------|
| Tulis kode manual file per file | Tulis **konsep** di `docs/prd.md` & `docs/branding.md` |
| Setup auth, routing, DB dari nol | **Sudah siap**: Auth, Repo dual-mode (JSON/MySQL), i18n, Theme, CSP |
| Cek error di browser | Validasi otomatis: `php -l`, cek sintaks, preview lokal |
| Desain UI acak | **6 template HTML referensi** (`references/*.html`) untuk konsistensi visual |

> **Filosofi**: *AI coding assistant bukan pengganti Anda — ia adalah mitra arsitek yang mengeksekusi rancangan Anda dengan presisi.*

---

## ✨ Fitur Utama

### 🏗️ Arsitektur Siap Pakai (Shell Architecture)
- **Landing Page** (`public/index.php`) — Hero, fitur, demo, install guide interaktif
- **Auth Shells**: `/login/`, `/register/`, `/logout/` — SPA via AJAX ke `core/router.php`
- **Dashboard Shells** (Role-based):
  - `/manajemen/` — **Super Admin** (overview, users, system, audit)
  - `/admin/` — **Creator** (upload karya, analitik, royalti)
  - `/client/` — **Client** (eksplorasi konten, player)
- **Router Proxy Pattern** — Document root `public/`, AJAX ke `public/core/router.php` → `core/router.php`

### 🔐 Auth & Security (Production-Ready)
- **Password**: Argon2ID (`PASSWORD_ARGON2ID`)
- **CSRF**: Token terpusat di `core/router.php` + `hash_equals()`
- **Rate Limiting**: IP + username, fixed-window (`core/ratelimit.php`)
- **Remember Me**: Selector + validator, per-device logout, invalidate on password change
- **Re-auth Middleware**: Untuk aksi sensitif
- **Prepared Statements**: PDO wrapper (mode MySQL)

### 🌍 Multi-Bahasa (i18n) Lengkap
- **Deteksi Otomatis**: IP-based (ID/US/SA dll) → fallback ke English
- **Manual Selector**: Flag dropdown di header (desktop) & bottom nav (mobile)
- **RTL Support**: Bahasa Arab (`dir="rtl"`, CSS logical properties)
- **Coverage**: Semua teks PHP + JS-injected content via `t('key')` / `window._i18n`
- **File**: `locales/id.json`, `en.json`, `ar.json` + `languages.json` manifest

### 🌓 Dark/Light Theme
- CSS Variables di `public/assets/css/branding.css` (single source of truth)
- Persistensi: `localStorage` + `users.json` kolom `theme_preference`
- Selector di header (desktop) & bottom nav (mobile)

### 📱 Responsive & Mobile-First
- **Desktop**: Sidebar navigation vertikal
- **Mobile**: Bottom nav horizontal + scroll spy (IntersectionObserver)
- **Breakpoint**: `md: 768px` konsisten di semua shell
- **No Horizontal Scroll**: `overflow-x-auto` pada tabel/code, `flex-wrap` pada toolbar

### 💾 Data Access Layer — Repo Pattern (Auto-Switch SQL/JSON)
```php
Repo::table('users')->all();
Repo::table('users')->find($id);
Repo::table('users')->where([...]);
Repo::table('users')->insert([...]);
Repo::table('users')->update($id, [...]);
Repo::table('users')->delete($id);
```
- **Mode `auto` (default)**: Deteksi per-entitas (MySQL kalau tabel ada, JSON kalau belum)
- **Mode `json`**: Force JSON untuk demo/testing
- **Mode `mysql`**: Force MySQL, gagal keras kalau koneksi/tabel tidak ada
- **Atomic JSON Write**: File lock terpisah (`.lock`), `rename()` atomic, tidak corrupt

### ⚡ Setup Wizard Interaktif (`/install/`) & Deployment Console
- **Automated Setup Script (`scripts/setup-project.ps1`)**:
  - Auto-detection hak Administrator: jika dijalankan dari terminal non-admin, tampilkan countdown 3 detik lalu otomatis alihkan ke jendela PowerShell Administrator (`RunAs`).
  - Wizard interaktif pilih Local Disk (C/D/E/dll), Server (Laragon/XAMPP), dan Nama Aplikasi.
  - Otomatis `npx degit`, pembuatan Virtual Host (`nama_app.test`), penulisan Windows `hosts`, restart service/proses Apache, dan flush DNS.
- **Automated Deployment Console (Landing Page `public/index.php`)**:
  - Form interaktif langsung di landing page untuk memilih Disk & Server, sanitasi nama app, jalankan setup terminal, dan redirect ke Wizard.
- **Setup Wizard Dual-Mode (`public/install/`)**:
  | Mode | Langkah | Deskripsi |
  |------|---------|-----------|
  | **Aplikasi Baru (Greenfield)** | 12 langkah | Overview → PRD → Branding → Logo → 6 HTML References → Config → Path |
  | **Redesain (Refit)** | 5 langkah | Overview → Upload/Kelola `references/` (Codebase Lama) → Logo → Target Server → AI Re-architecting |
- **Monaco Editor Integrasi**: Text editor browser profesional untuk Markdown (`prd.md`, `branding.md`) dan HTML (`references/*.html`) dengan fallback automatic textarea.
- **Instant Role Demo Portals**:
  - Login 1-klik via AJAX di landing page untuk menguji RBAC tanpa ngetik kredensial:
    - **Manajemen** (Super Admin): `admin@app.com`
    - **Admin** (Creator): `admin@app.id`
    - **Client** (Pendengar): `client@app.com`

---

## 🚀 Quick Start (3 Menit)

### Prasyarat
- **Node.js** (hanya untuk `npx degit` — bukan runtime aplikasi)
- **XAMPP** (`htdocs/`) **atau** **Laragon** (`www/`)
- **AI Coding CLI**: [Claude Code](https://code.claude.com) / Cursor / GitHub Copilot CLI
- **VS Code** (disarankan: terminal + editor terintegrasi)

### 1. Download Template & Setup Virtual Host (Interaktif)

> Windows PowerShell (Jalankan langsung di terminal PowerShell / CMD):
```bash
irm https://raw.githubusercontent.com/iqbalmurtadho24/vibeforge/main/scripts/setup-project.ps1 | iex
```

> 💡 **Auto Admin Elevation**: Jika terminal yang Anda gunakan **bukan Administrator**, script secara otomatis menampilkan loading/countdown 3 detik dan langsung mengalihkan eksekusi ke jendela PowerShell Administrator baru (`RunAs`).

> Script akan memandu Anda:
> 1. **Pilih Local Disk** (C: / D: / E: / dst)
> 2. **Pilih Server** (ketik `l` untuk Laragon atau `x` untuk XAMPP lalu Enter)
> 3. **Masukkan Nama Aplikasi** (tanpa spasi, gunakan `_` atau `-`)
> 4. Lalu otomatis: Download template via `npx degit`, buat Virtual Host + update Windows Hosts (`nama_app.test`), restart service Apache, flush DNS, dan membuka browser.

---

**Opsi B: Manual (jika tidak ingin pakai script)**

```bash
# Laragon
cd C:\laragon\www

# atau XAMPP
cd C:\xampp\htdocs

# Unduh Vibeforge — GANTI nama_project_anda (tanpa spasi, gunakan _ atau -)
npx -y degit iqbalmurtadho24/vibeforge nama_project_anda
cd nama_project_anda

# Lalu setup virtual host manual:
# Laragon: Menu Laragon → Apache → Sites Enabled → Add `nama_project_anda.test` → Reload Apache
# XAMPP: Edit C:\xampp\apache\conf\extra\httpd-vhosts.conf → tambah VirtualHost → restart Apache
```

### 2. Buka Setup Wizard (Opsional tapi Disarankan)
```
# Laragon (Auto Virtual Host)
http://nama_project_anda.test/install/

# XAMPP
http://localhost/nama_project_anda/public/install/
```
Atau gunakan **Installer Interaktif di Landing Page**:
```
http://nama_project_anda.test/  →  Pilih Server, Disk, Nama App  →  "Proses"  →  "Unduh & Buka Terminal Otomatis"
```

### 3. Isi Konsep Aplikasi (WAJIB Sebelum Menjalankan AI)
**Mode Aplikasi Baru (12 langkah)**:
1. **Overview**: Pemilihan mode & penjelasan arsitektur.
2. **PRD** (`docs/prd.md`): Spesifikasi aplikasi, aktor, & alur fitur.
3. **Branding** (`docs/branding.md`): Token warna, font, logo, tone.
4. **Logo**: Upload asset logo (`docs/logo.png`).
5. **Template Landing**: `references/landingpage.html`.
6. **Form Auth**: `references/login.html` & `references/register.html`.
7. **Modul Roles**: `modul_manajemen.html`, `modul_admin.html`, `modul_client.html`.
8. **Config & Environment**: Variabel `.env` & koneksi data/DB.
9. **AI Execution**: Buka CLI dan eksekusi `baca @docs/install.md`.

**Mode Redesain / Refit (5 langkah)**:
1. **Overview**: Pilih Mode Redesain.
2. **Upload References**: Masukkan codebase lama ke `references/`.
3. **Logo Assets**: Upload asset logo baru.
4. **Target Host**: Tentukan web server lokal (Laragon/XAMPP).
5. **AI Re-architecting**: Jalankan AI CLI untuk auto-generate PRD & menyerap struktur lama.

> ⚠️ **Jangan lewati langkah ini.** Kalau `prd.md`/`branding.md` kosong, AI akan berhenti dan minta Anda mengisi — ini disengaja supaya AI tidak menebak-nebak konsep aplikasi Anda.

### 4. Jalankan AI Coding Assistant
```bash
# Di terminal (masih di folder project)
claude
```
Kemudian ketik:
```
Baca dan jalankan docs/install.md
```

### 5. Ikuti 3 Tahap Eksekusi AI
| Tahap | AI Lakukan | Anda Lakukan |
|-------|------------|--------------|
| **1. Audit & Rencana** | Baca `CLAUDE.md`, `prd.md`, `branding.md`, `references/`, cek struktur core → tulis `docs/build_plan.md` | **Review** `build_plan.md`, approve sebelum lanjut |
| **2. Eksekusi Kode** | Buat `.env`, `data/users.json` (Argon2ID), seluruh shell PHP, jalankan `php -l` | **Cek** hasil validasi `php -l`, approve sebelum lanjut |
| **3. Preview Lokal** | Hanya memberi instruksi — tidak bisa eksekusi GUI | **Wajib manual**: buka Laragon/XAMPP, aktifkan Auto Virtual Host, restart Apache, arahkan document root ke `public/`, cek di browser |

> 💡 **Tips**: Kurangi gangguan approval per-file (opsional):
> ```bash
> claude --permission-mode acceptEdits
> ```
> atau tekan `Shift+Tab` di dalam sesi. Ini hanya mengurangi prompt per-file — tetap lakukan review Anda di setiap *batas tahap* seperti tabel di atas.

---

## 📁 Struktur Project

```
vibeforge/
├── CLAUDE.md                    # Konstitusi project (wajib baca AI)
├── README.md                    # File ini
├── LICENSE                      # Apache-2.0
├── SECURITY.md                  # Kebijakan keamanan
├── CHANGELOG.md                 # SemVer changelog
├── .env                         # Environment config (generate dari .env.example)
├── .env.example                 # Template env
├── .gitignore
├── public/                      # DOCUMENT ROOT (Apache/Nginx)
│   ├── index.php                # Landing page
│   ├── login/index.php          # Halaman login
│   ├── register/index.php       # Halaman register
│   ├── logout/index.php         # Logout (redirect only)
│   ├── manajemen/index.php      # Super Admin dashboard
│   ├── admin/index.php          # Creator dashboard
│   ├── client/index.php         # Client dashboard
│   ├── core/router.php          # Router proxy (WAJIB ADA!)
│   ├── assets/css/branding.css  # CSS variables (warna utama)
│   ├── assets/flags/            # Flag images untuk i18n
│   ├── uploads/                 # User uploads
│   ├── install/                 # Setup Wizard shell
│   └── .htaccess                # Security headers, CSP, block sensitive files
├── core/                        # Core library (router, auth, session, Repo, CSRF, ratelimit)
├── include/                     # config.php, helper.php (t(), escape(), dsb)
├── modules/                     # Modul AJAX per role (auth/, install/, dll)
├── data/                        # JSON dummy (users.json, dll)
├── cache/                       # debug.log (APP_DEBUG only)
├── locales/                     # i18n translations
│   ├── languages.json           # Manifest bahasa (manifest tunggal!)
│   ├── id.json                  # Indonesia
│   ├── en.json                  # English
│   └── ar.json                  # العربية (RTL)
├── docs/                        # Dokumentasi aplikasi SPESIFIK
│   ├── prd.md                   # WAJIB diisi (konsep aplikasi)
│   ├── branding.md              # WAJIB diisi (identitas visual)
│   ├── install.md               # Protokol AI (3 tahap, auto-generated)
│   ├── audit_protocol.md        # Audit template
│   └── openapi.yaml             # API spec (jika ada endpoint publik)
├── references/                  # Template visual (acuan struktur)
│   ├── landingpage.html
│   ├── login.html
│   ├── register.html
│   ├── modul_manajemen.html
│   ├── modul_admin.html
│   └── modul_client.html
├── migrations/                  # SQL (production only)
└── .htaccess                    # Proteksi kedua (document root salah arah)
```

---

## 🔧 Konfigurasi Environment

### `.env` (Contoh Production)
```env
# App Identity
APP_DISPLAY_NAME="Nama Aplikasi Anda"
APP_TAGLINE="Tagline aplikasi"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domainanda.com

# Cryptographic Keys (generate fresh per deployment!)
APP_KEY=                    # 64 hex chars
CSRF_KEY=                   # 64 hex chars
REMEMBER_ME_SECRET=         # 128 hex chars

# Database — FORCE MySQL in production
DB_MODE=mysql
DB_HOST=127.0.0.1
DB_NAME=vibeforge
DB_USER=app_user
DB_PASSWORD=strong_random_password

# Session
SESSION_LIFETIME=3600
SESSION_SECURE=true
SESSION_HTTPONLY=true
SESSION_SAMESITE=lax

# Rate Limiting
RATE_LIMIT_MAX_ATTEMPTS=10
RATE_LIMIT_WINDOW=300
```

### Generate Keys
```bash
php -r "echo bin2hex(random_bytes(32)).PHP_EOL;"   # APP_KEY / CSRF_KEY
php -r "echo bin2hex(random_bytes(64)).PHP_EOL;"   # REMEMBER_ME_SECRET
```

### Development Override
```env
APP_ENV=development
APP_DEBUG=true
DB_MODE=auto          # atau json
SESSION_SECURE=false
```

---

## 🌐 Internationalization (i18n)

### Tambah Bahasa Baru
1. Tambah entry di `locales/languages.json`:
```json
{
  "id": { "name": "Bahasa Indonesia", "flag": "/assets/flags/id.svg", "rtl": false },
  "en": { "name": "English", "flag": "/assets/flags/en.svg", "rtl": false },
  "ar": { "name": "العربية", "flag": "/assets/flags/ar.svg", "rtl": true },
  "jp": { "name": "日本語", "flag": "/assets/flags/jp.svg", "rtl": false }
}
```
2. Buat `locales/jp.json` dengan key-value yang sama struktur.
3. Selesai — selector otomatis muncul, tidak perlu ubah kode PHP.

### Gunakan di Kode
```php
// PHP
<?= t('hero.title') ?>
<?= t('auth.login', 'Login') ?>  // fallback

// JavaScript (di shell PHP)
window._i18n = {
  greetingMorning: '<?= json_encode(t("greeting.morning")) ?>',
  // ...
};
// Lalu di JS murni:
element.textContent = window._i18n.greetingMorning;
```

---

## 🛡️ Security

Lihat **[SECURITY.md](SECURITY.md)** untuk:
- Vulnerability reporting (private disclosure)
- Security checklist untuk developer & deployment
- Implemented protections table
- Required `.env` configuration
- Standards compliance (OWASP ASVS L1-2, Top 10, CWE)

**Ringkasan Cepat**:
- Password: Argon2ID ✅
- CSRF: Centralized token + `hash_equals()` ✅
- SQLi: Prepared statements only ✅
- XSS: `escape()` / `t()` + CSP ✅
- Rate Limit: IP + username fixed-window ✅
- Session: Regenerate ID, secure cookies ✅

---

## 📦 Deployment Checklist

Sebelum production:
- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] `DB_MODE=mysql` (bukan `auto`/`json`)
- [ ] Generate fresh `APP_KEY`, `CSRF_KEY`, `REMEMBER_ME_SECRET`
- [ ] Database MySQL ready + user dengan privileges minimal
- [ ] HTTPS enforced (valid TLS cert)
- [ ] Document root → `public/`
- [ ] `.htaccess` security rules active (CSP, block `.env`/`*.log`/`*.sql`)
- [ ] Directory listing disabled
- [ ] Rate limiting tuned untuk traffic Anda
- [ ] Backup strategy untuk `data/` + database

---

## 🤝 Kontribusi

1. Fork repo
2. Buat branch: `git checkout -b feature/nama-fitur`
3. Commit: `git commit -m "feat: deskripsi singkat"`
4. Push: `git push origin feature/nama-fitur`
5. Open Pull Request

**Style**: PSR-12, Conventional Commits, SemVer.

---

## 📄 Lisensi

**Apache License 2.0** — lihat [LICENSE](LICENSE).

```
Copyright 2026 Vibeforge Project Contributors

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
```

---

## 🙏 Acknowledgments

- **Tailwind CSS** — Utility-first styling (CDN)
- **Alpine.js** — Reactive UI components (CDN)
- **Phosphor Icons** — Beautiful icon set (CDN)
- **Monaco Editor** — Code editing in Setup Wizard (CDN)
- **degit** — Git repo downloader for template bootstrap

---

## 📞 Support & Community

- **GitHub Issues**: Bug reports, feature requests
- **GitHub Discussions**: Questions, showcase, general chat
- **Security**: [SECURITY.md](SECURITY.md) — private disclosure only

---

*Selamat membangun aplikasi Anda dengan Vibeforge.* 🔥

**Vibeforge Team** — [github.com/iqbalmurtadho24/vibeforge](https://github.com/iqbalmurtadho24/vibeforge)