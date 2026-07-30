# Vibeforge — Dari Dokumen ke Kode Jadi

> **Template PHP Native untuk *vibe coding*: tulis konsep di `docs/prd.md` & `docs/branding.md`, AI Coding Assistant mewujudkannya jadi kode fungsional.**

[![License](https://img.shields.io/badge/Apache%202.0-blue.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-8.1+-8892BF.svg?logo=php)](https://php.net)
[![AI Assisted](https://img.shields.io/badge/Vibe%20Coding-FF6B35.svg)](#)
[![No Framework](https://img.shields.io/badge/Native%20PHP-000000.svg?logo=php)](https://php.net)
[![Security](https://img.shields.io/badge/OWASP%20ASVS%20L1--2-green.svg)](SECURITY.md)
[![i18n](https://img.shields.io/badge/i18n-ID%20%7C%20EN%20%7C%20AR-blue.svg)](#-internationalization-i18n)

---

## Daftar Isi

- [Apa Itu Vibeforge?](#-apa-itu-vibeforge)
- [Fitur Utama](#-fitur-utama)
- [Quick Start](#-quick-start-3-menit)
- [Struktur Project](#-struktur-project)
- [Konfigurasi Environment](#-konfigurasi-environment)
- [Internationalization](#-internationalization-i18n)
- [Security](#-security)
- [Deployment Checklist](#-deployment-checklist)
- [Kontribusi](#-kontribusi)

---

## Apa Itu Vibeforge?

**Vibeforge bukan framework** — ini **template + protokol kerja**.

| Tradisional | Vibeforge |
|---|---|
| Tulis kode manual file per file | Tulis **konsep** di `docs/prd.md` & `docs/branding.md` |
| Setup auth, routing, DB dari nol | **Sudah siap**: Auth, Repo dual-mode, i18n, Theme, CSP |
| Cek error di browser | Validasi otomatis: `php -l`, cek sintaks |
| Desain UI acak | **6 template HTML referensi** (`references/*.html`) |

> *AI coding assistant bukan pengganti Anda — ia mitra arsitek yang mengeksekusi rancangan Anda dengan presisi.*

---

## Fitur Utama

### Shell Architecture

- **Landing Page** (`public/index.php`) — Hero, fitur, demo, install guide
- **Static Showcase** (`index.html`) — GitHub Pages preview — [Live Demo](https://vibeforge-dev.netlify.app/)
- **Auth Shells** — `/login/`, `/register/`, `/logout/` (SPA via AJAX)
- **Dashboard Shells** (role-based):
  - `/manajemen/` — Super Admin (overview, users, system, audit)
  - `/admin/` — Creator (upload karya, analitik, royalti)
  - `/client/` — Client (eksplorasi konten, player)
- **Router Proxy Pattern** — Doc root `public/`, AJAX ke `public/core/router.php` → `core/router.php`

### Auth & Security

Argon2ID password hashing | CSRF token terpusat + `hash_equals()` | IP+username rate limiting | Remember-me selector+validator | Re-auth middleware | Prepared statements (PDO)

### Data Access Layer — Repo Pattern

```php
Repo::table('users')->all();                    // semua record
Repo::table('users')->find($id);                // by ID
Repo::table('users')->where(['role' => 'admin']); // filter
Repo::table('users')->insert([...]);             // return ID baru
Repo::table('users')->update($id, [...]);        // return bool
Repo::table('users')->delete($id);               // return bool
```

| Mode | Perilaku |
|---|---|
| `auto` (default) | Deteksi per-entitas: MySQL jika tabel ada, JSON jika belum |
| `json` | Force JSON — untuk demo/testing |
| `mysql` | Force MySQL — gagal keras jika koneksi/tabel tidak ada |

### Internationalization

- **Deteksi otomatis** — IP-based (ID/US/SA dll), fallback English
- **Manual selector** — Flag dropdown di header (desktop) & bottom nav (mobile)
- **RTL support** — Bahasa Arab (`dir="rtl"`, CSS logical properties)
- **Full coverage** — PHP + JS-injected content via `t('key')` / `window._i18n`

### Dark/Light Theme

CSS variables di `branding.css` (single source of truth) | `localStorage` + `users.json` per-user preference | Selector di header & bottom nav

### Responsive & Mobile-First

Desktop: sidebar vertikal | Mobile: bottom nav + scroll spy | Breakpoint `md: 768px` konsisten | No horizontal scroll

### Setup Wizard 4-Step

| Tahap | Nama | Deskripsi |
|---|---|---|
| **1** | Install (Auto-Detect) | Deteksi nama app, path, domain, PHP version — hanya tombol Mulai |
| **2** | Referensi (Opsional) | Upload HTML/CSS/JS referensi — skip jika mulai dari nol |
| **3** | Branding & Logo | Upload logo, brand identity mengikuti PRD atau isi manual |
| **4** | PRD (7 Bagian) | Generate PRD otomatis oleh AI atau paste PRD sendiri, lalu klik **Jalankan** |

**Instant Role Demo** — Login 1-klik di landing page:

| Role | Email |
|---|---|
| Manajemen (Super Admin) | `manajemen@example.com` |
| Admin (Creator) | `admin@example.com` |
| Client (Pendengar) | `client@example.com` |

---

## Quick Start (3 Menit)

### Prasyarat

- **Node.js** (hanya untuk `npx degit`)
- **Laragon** (`www/`) atau **XAMPP** (`htdocs/`)
- **AI Coding CLI**: [Claude Code](https://code.claude.com) / Cursor / GitHub Copilot CLI
- **VS Code** (disarankan)

### 1. Download & Setup Virtual Host

**Opsi A — Script Otomatis (Disarankan)**

```powershell
irm https://raw.githubusercontent.com/iqbalmurtadho24/vibeforge/main/scripts/setup-project.ps1 | iex
```

Script memandu: pilih disk → pilih server → masukkan nama app → otomatis download, buat Virtual Host, update Windows hosts, restart Apache, flush DNS, buka browser.

> Jika terminal bukan Administrator, script otomatis menampilkan countdown 3 detik lalu alihkan ke jendela PowerShell Administrator.

**Opsi B — Manual**

```bash
cd C:\laragon\www   # atau C:\xampp\htdocs
npx -y degit iqbalmurtadho24/vibeforge nama_project_anda
cd nama_project_anda
```

Lalu setup Virtual Host manual:
- **Laragon**: Menu → Apache → Sites Enabled → Add `nama_project_anda.test` → Reload
- **XAMPP**: Edit `httpd-vhosts.conf` → tambah VirtualHost → restart Apache

### 2. Buka Setup Wizard

```
# Laragon
http://nama_project_anda.test/install/

# XAMPP
http://localhost/nama_project_anda/public/install/
```

Atau langsung dari landing page: `http://nama_project_anda.test/` → klik "MULAI BUAT APLIKASIMU"

### 3. Ikuti 4 Tahap Wizard

| Tahap | Yang Terjadi |
|---|---|
| **1. Install** | Auto-detect nama app, path, domain, PHP version. Klik Mulai. |
| **2. Referensi** | Upload referensi (opsional). Skip jika mulai dari nol. |
| **3. Branding** | Upload logo. Brand identity mengikuti PRD atau isi manual. |
| **4. PRD** | Generate PRD otomatis atau paste PRD sendiri. Klik **Jalankan**. |

> Jangan lewati Tahap 4. Jika `prd.md` kosong, AI akan berhenti dan minta Anda mengisi.

### 4. Jalankan AI Coding Assistant

```bash
claude
```

Lalu ketik:

```
Baca dan jalankan docs/install.md
```

### 5. Eksekusi AI (3 Tahap)

| Tahap | AI Lakukan | Anda Lakukan |
|---|---|---|
| **1. Audit & Rencana** | Baca `CLAUDE.md`, `prd.md`, `branding.md`, `references/` → tulis `docs/build_plan.md` | Review `build_plan.md`, approve sebelum lanjut |
| **2. Eksekusi Kode** | Buat `.env`, `data/users.json`, seluruh shell PHP, jalankan `php -l` | Cek hasil validasi, approve sebelum lanjut |
| **3. Preview Lokal** | Beri instruksi saja — tidak bisa eksekusi GUI | Buka browser, cek hasil di Laragon/XAMPP |

> **Tips**: Kurangi approval per-file dengan `claude --permission-mode acceptEdits` atau tekan `Shift+Tab` di sesi. Tetap review di setiap batas tahap.

---

## Struktur Project

```
vibeforge/
├── CLAUDE.md                    # Konstitusi project (wajib baca AI)
├── README.md
├── LICENSE                      # Apache-2.0
├── SECURITY.md
├── CHANGELOG.md
├── index.html                   # Static landing (GitHub Pages)
├── .env / .env.example
├── .gitignore
│
├── public/                      # DOCUMENT ROOT
│   ├── index.php                # Landing page
│   ├── login/index.php
│   ├── register/index.php
│   ├── logout/index.php
│   ├── manajemen/index.php      # Super Admin
│   ├── admin/index.php          # Creator
│   ├── client/index.php         # Client
│   ├── core/router.php          # Router proxy (WAJIB!)
│   ├── install/                 # Setup Wizard
│   ├── assets/css/branding.css  # CSS variables
│   ├── assets/flags/            # Flag images (i18n)
│   ├── uploads/
│   └── .htaccess                # Security headers, CSP
│
├── core/                        # Router, auth, session, Repo, CSRF, ratelimit
├── include/                     # config.php, helper.php (t(), escape())
├── modules/                     # Modul AJAX per role (auth/, install/, dll)
├── data/                        # JSON dummy (users.json, dll)
├── cache/                       # debug.log (APP_DEBUG only)
├── locales/                     # i18n
│   ├── languages.json           # Manifest bahasa
│   ├── id.json / en.json / ar.json
├── docs/                        # Dokumentasi spesifik aplikasi
│   ├── prd.md                   # WAJIB diisi
│   ├── branding.md              # WAJIB diisi
│   ├── install.md               # Protokol AI (auto-generated)
│   └── audit_protocol.md
├── references/                  # Template visual (golden reference)
│   ├── landingpage.html
│   ├── login.html / register.html
│   ├── modul_manajemen.html
│   ├── modul_admin.html
│   └── modul_client.html
├── migrations/                  # SQL (production only)
└── .htaccess                    # Proteksi jika doc root salah arah
```

---

## Konfigurasi Environment

### Production

```env
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

## Internationalization (i18n)

### Tambah Bahasa Baru

1. Tambah entry di `locales/languages.json`:
   ```json
   {
     "jp": { "name": "日本語", "flag": "/assets/flags/jp.svg", "rtl": false }
   }
   ```
2. Buat `locales/jp.json` dengan key-value struktur sama.
3. Selesai — selector otomatis muncul, tanpa ubah kode PHP.

### Gunakan di Kode

```php
// PHP
<?= t('hero.title') ?>
<?= t('auth.login', 'Login') ?>  // fallback

// JavaScript (di shell PHP)
window._i18n = {
  greetingMorning: '<?= json_encode(t("greeting.morning")) ?>',
};
// JS murni:
element.textContent = window._i18n.greetingMorning;
```

---

## Security

Lihat **[SECURITY.md](SECURITY.md)** untuk detail lengkap.

| Proteksi | Implementasi |
|---|---|
| Password | Argon2ID (`PASSWORD_ARGON2ID`) |
| CSRF | Token terpusat + `hash_equals()` |
| SQL Injection | Prepared statements via PDO |
| XSS | `escape()` / `t()` + CSP headers |
| Rate Limiting | IP + username, fixed-window |
| Session | Regenerate ID, secure cookies |

---

## Deployment Checklist

- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] `DB_MODE=mysql` (bukan `auto`/`json`)
- [ ] Generate fresh `APP_KEY`, `CSRF_KEY`, `REMEMBER_ME_SECRET`
- [ ] Database MySQL ready + user minimal privileges
- [ ] HTTPS enforced (valid TLS cert)
- [ ] Document root → `public/`
- [ ] `.htaccess` security rules active (CSP, block `.env`/`*.log`/`*.sql`)
- [ ] Directory listing disabled
- [ ] Rate limiting tuned untuk traffic Anda
- [ ] Backup strategy untuk `data/` + database

---

## Kontribusi

1. Fork repo
2. Branch: `git checkout -b feature/nama-fitur`
3. Commit: `git commit -m "feat: deskripsi singkat"`
4. Push: `git push origin feature/nama-fitur`
5. Open Pull Request

**Style**: PSR-12, Conventional Commits, SemVer.

---

## Lisensi

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

## Acknowledgments

- **Tailwind CSS** — Utility-first styling (CDN)
- **Alpine.js** — Reactive UI components (CDN)
- **Phosphor Icons** — Icon set (CDN)
- **degit** — Git repo downloader for template bootstrap

---

## Support & Community

- **GitHub Issues** — Bug reports, feature requests
- **GitHub Discussions** — Questions, showcase, general chat
- **Security** — [SECURITY.md](SECURITY.md) — private disclosure only

---

*Selamat membangun aplikasi Anda dengan Vibeforge.* 🔥

**Vibeforge Team** — [github.com/iqbalmurtadho24/vibeforge](https://github.com/iqbalmurtadho24/vibeforge)
