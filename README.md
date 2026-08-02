# Vibeforge — Template Web App SPA Native PHP (vibe coding)

> **Template PHP Native untuk *vibe coding* berbasis Arsitektur 13 Pilar Software (6 Lapisan): Tulis konsep di `docs/prd.md` & `docs/branding.md`, AI Coding Assistant mewujudkannya menjadi aplikasi web fungsional.**

[![License](https://img.shields.io/badge/Apache%202.0-blue.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-8.1+-8892BF.svg?logo=php)](https://php.net)
[![AI Assisted](https://img.shields.io/badge/Vibe%20Coding-FF6B35.svg)](#)
[![No Framework](https://img.shields.io/badge/Native%20PHP-000000.svg?logo=php)](https://php.net)
[![Security](https://img.shields.io/badge/OWASP%20ASVS%20L1--2-green.svg)](SECURITY.md)
[![13 Pilar Software](https://img.shields.io/badge/Architecture-13%20Pilar%20Software-orange.svg)](CLAUDE.md)
[![i18n](https://img.shields.io/badge/i18n-ID%20%7C%20EN%20%7C%20AR%20%7C%20JA-blue.svg)](#-internationalization-i18n)

---

## Daftar Isi

- [Apa Itu Vibeforge?](#-apa-itu-vibeforge)
- [Peta Arsitektur 13 Pilar Software](#-peta-arsitektur-13-pilar-software)
- [Fitur Utama](#-fitur-utama)
- [Quick Start (3 Menit)](#-quick-start-3-menit)
- [Struktur Project](#-struktur-project)
- [Konfigurasi Environment](#-konfigurasi-environment)
- [Internationalization (i18n)](#-internationalization-i18n)
- [Keamanan (Security)](#-keamanan-security)
- [Deployment Checklist](#-deployment-checklist)
- [Kontribusi & Lisensi](#-kontribusi--lisensi)

---

## Apa Itu Vibeforge?

**Vibeforge bukan framework** — ini **template + protokol kerja** berstandar arsitektur industri.

| Tradisional | Vibeforge |
|---|---|
| Tulis kode manual file per file | Tulis **konsep** di `docs/prd.md` & `docs/branding.md` |
| Setup auth, routing, DB dari nol | **Sudah siap**: Auth Argon2ID, Repo dual-mode, i18n, Theme, CSP |
| Cek error manual di browser | Validasi otomatis: `php -l`, 3-Tahap Build Protocol |
| Desain UI acak | **Golden HTML Templates** (`references/*.html`) |

---

## Peta Arsitektur 13 Pilar Software

Vibeforge memetakan 13 Pilar Software (6 Lapisan) secara tepat ke dalam struktur native PHP:

| Lapisan | Pilar | Implementasi Vibeforge Framework |
|---|---|---|
| **1. Inti Aplikasi** | **1. Frontend** | SPA Shell (`public/*/index.php`), Golden HTML References (`references/*.html`), Dynamic Branding (`branding.css`), i18n GeoIP + Fallback + DB Preference, Responsive Nav & Scroll Spy |
| | **2. API & Backend Logic** | Router Proxy (`public/core/router.php` → `core/router.php`), Controllers (`modules/*/*.php`), Helper (`include/helper.php`) |
| | **3. Database & Storage** | Data Access Layer Terpusat (`core/Repo.php` Auto-Switch SQL/JSON), Mutex Lock (`.lock`) + Atomic Write (temp+rename), Language Preference Storage (`language_preference` in `users.json`/MySQL), File Storage (`public/uploads/`). **Aturan Mutlak**: Jika `references/` berisi SQL → `DB_MODE=mysql`, hapus konsep JSON. Jika tidak ada SQL → `DB_MODE=json`, buat JSON DB selengkapnya. |
| **2. Keamanan** | **4. Authentication & Session** | Password Hashing Argon2ID, Session Core (`core/session.php`), Remember-Me Selector+Validator (`core/remember.php`), Re-auth Middleware |
| | **5. Role-Based Access (RBAC)** | Role-to-Shell Mapping (`manajemen`, `admin`, `client`), Guard `requireRole()`, Dual-Pattern Entry Guard (Pola 1 Entry vs Pola 2 Module) |
| **3. Tempat & Tenaga** | **6. Hosting & Deployment** | Document Root Apache `public/`, Laragon/XAMPP Vhost, Manual FTP Drag-Drop Deploy, Header Security (`public/.htaccess`) |
| | **7. Cloud Compute** | PHP 8.1+ Native Runtime, Environment Isolation (`APP_ENV=development\|staging\|production`) |
| **4. Alur Kerja Aman** | **8. CI/CD & Version Control** | Semantic Versioning (`CHANGELOG.md`), Syntax Validation (`php -l`), 3-Tahap Build Protocol (Audit → Build → Verify) |
| **5. Performa & Skala** | **9. Rate Limiting** | IP + Username Fixed-Window Rate Limiter (`core/ratelimit.php`) |
| | **10. Cache & CDN** | Asset CDN Rules (Tailwind, Phosphor Icons, Google Fonts), CSP Header, In-Memory Locale Cache (`t()`) |
| | **11. Load Balancer & Scaling** | Auto-Switch Dual Mode (`json` → `mysql` per-entitas), Prepared Statements PDO, Stateless Server Session |
| **6. Keandalan** | **12. Error Tracking & Logging** | Debug Log (`cache/debug.log` saat `APP_DEBUG=true`) vs Audit Trail (`data/audit_trail.json` append-only, permanen) |
| | **13. Availability & Recovery** | Atomic JSON Writes (temp+rename), Production Guard (`DB_MODE=json` + Prod Block), Session Lifespan & Cookie Protection. **Strict SQL Mode**: Jika `references/` berisi SQL, `DB_MODE=mysql` wajib dan JSON DB dilarang. |

---

## Fitur Utama

### SPA Shell Architecture
- **Landing Page** (`public/index.php`) — Hero, fitur, demo, install guide. Jika Landing Page dicentang TANPA Login, halaman ini adalah landing page publik tanpa tombol Masuk/Daftar.
- **Auth Shells** — `/login/`, `/register/`, `/logout/` (SPA via AJAX). **Aturan Index Login**: Jika hanya Login dan 1 halaman lain yang dicentang (tanpa Landing Page), login TETAP berada di `public/index.php` — **JANGAN redirect ke `/login/`**.
- **Dashboard Shells (Dinamis berbasis Referensi)**:
  - Nama folder di `public/` dan `references/` HARUS mengikuti struktur yang ada di `references/` (misal `/pendaftar/`, `/peserta/`, `/manajemen/`, `/admin/`, `/client/`). Jangan memaksa nama tetap.
- **Penghapusan Halaman Tidak Dicentang**: Halaman yang TIDAK dicentang di Tahap 3B wizard akan secara otomatis dihapus file/foldernya dari `public/` tanpa menyisakan route bocor.
- **Auto-Delete Install Wizard**: Folder `public/install/` akan secara otomatis dihapus setelah proses vibe coding/build AI berjalan agar wizard tidak dapat diakses kembali.
- **Router Proxy Pattern** — Doc root `public/`, AJAX ke `public/core/router.php` → `core/router.php`
- **Aturan Landing ↔ Login**: Minimal salah satu dari Landing Page atau Login harus aktif. Jika Login aktif, minimal satu role/halaman dashboard wajib ada. Jika hanya Landing Page yang aktif, role halaman tidak wajib dan tombol Auth disembunyikan.

### Internationalization (i18n) Tingkat Lanjut
- **GeoIP & Smart Fallback**: Deteksi otomatis negara IP pengunjung (Negara Liga Arab → Bahasa Arab `ar`, Mapped Countries → `id`/`ja`/`en`). IP negara yang tidak terdaftar akan otomatis diarahkan ke Bahasa Arab (`ar`) jika berasal dari kawasan Arab, atau Bahasa Inggris (`en`) sebagai standar universal.
- **Persistensi Preferensi Bahasa**: Pilihan bahasa pengguna yang diubah melalui URL parameter (`?lang=xx`) atau dropdown UI akan secara otomatis disinkronkan dan disimpan ke database (`data/users.json` / MySQL) via `Repo` untuk pengguna yang sedang login.

---

## Quick Start (3 Menit)

### 1. Download & Setup Virtual Host
```powershell
irm https://raw.githubusercontent.com/iqbalmurtadho24/vibeforge/main/scripts/setup-project.ps1 | iex
```

### 2. Buka Setup Wizard
Akses `http://nama_project_anda.test/install/` di browser untuk mengonfigurasi `data/install_config.json`, `docs/prd.md`, dan `docs/branding.md`.

### 3. Jalankan AI Coding Assistant
```bash
claude
```
Lalu berikan instruksi:
```
Baca dan jalankan docs/install.md
```

---

### Aturan Database (SQL vs JSON)
- **Jika `references/` berisi file/query SQL**: AI WAJIB menggunakan `DB_MODE=mysql`/`auto`, membuat migrasi di `migrations/`, dan menampilkan data langsung dari MySQL via `Repo::table()`. Konsep JSON database (`data/*.json`) **dilarang keras**.
- **Jika `references/` TIDAK berisi SQL sama sekali**: AI WAJIB menggunakan `DB_MODE=json`, membuat file database JSON selengkap-lengkapnya di `data/*.json` dengan dukungan penuh CRUD, file locking, dan atomic write.

---

## Struktur Project

```
vibeforge/
├── CLAUDE.md                    # Konstitusi project & 13 Pilar Software (wajib baca AI)
├── README.md
├── LICENSE                      # Apache-2.0
├── SECURITY.md
├── CHANGELOG.md
├── .env / .env.example
├── .gitignore
│
├── public/                      # DOCUMENT ROOT APACHE
│   ├── index.php                # Landing page (atau redirect ke /login/ jika Landing Page tidak dicentang)
│   ├── login/index.php          # Login shell (wajib jika Landing Page tidak dicentang)
│   ├── register/index.php
│   ├── logout/index.php
│   ├── manajemen/index.php      # Super Admin (wajib jika Login dicentang)
│   ├── admin/index.php          # Creator (wajib jika Login dicentang)
│   ├── client/index.php         # Client (wajib jika Login dicentang)
│   ├── core/router.php          # Router proxy (WAJIB!)
│   ├── install/                 # Setup Wizard
│   ├── assets/css/branding.css  # CSS variables
│   ├── assets/flags/            # Flag images (i18n)
│   ├── uploads/                 # File storage
│   └── .htaccess                # Security headers, CSP
│
├── core/                        # Router, auth, session, Repo, CSRF, ratelimit
├── include/                     # config.php, helper.php (t(), escape(), detectLanguage())
├── modules/                     # Modul AJAX per role (auth/, install/, dll)
├── data/                        # JSON dummy (users.json dengan language_preference) & audit_trail.json
│   └── *.json                   # Database JSON (DB_MODE=json, hanya jika TIDAK ada SQL di references/)
├── cache/                       # debug.log (APP_DEBUG only)
├── locales/                     # i18n manifest & translation files
│   ├── languages.json           # Manifest bahasa
│   ├── id.json / en.json / ar.json / ja.json
├── docs/                        # Dokumentasi spesifik aplikasi (prd.md, branding.md, install.md)
├── references/                  # Template visual (golden reference references/*.html) & opsional skema SQL
│   └── *.sql                    # Skema/query database SQL (jika ada → DB_MODE=mysql wajib, JSON DB dilarang)
└── migrations/                  # SQL (production only, jika DB_MODE=mysql)
```

---

## Lisensi

**Apache License 2.0** — lihat [LICENSE](LICENSE).

Copyright 2026 Vibeforge Project Contributors — [github.com/iqbalmurtadho24/vibeforge](https://github.com/iqbalmurtadho24/vibeforge)
