# Dokumentasi Instalasi & Protocol Eksekusi AI - vibeforge

> **FILE INI ADALAH TEMPLATE STATIC.**
> Konfigurasi aktif tersimpan di `data/install_config.json` oleh Setup Wizard.
> Edit file ini manual untuk perubahan permanen.

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

**Jika `referencesCount = 0`:**
- AI WAJIB generate `references/*.html` secara otomatis saat TAHAP 2
- File HTML ini menjadi golden template untuk styling dan struktur

**Jika `referencesCount > 0`:**
- File referensi sudah di-upload via wizard
- AI audit referensi -> generate `docs/prd.md` dan `docs/branding.md`

---

## 4. File yang Mungkin Di-Generate Otomatis

### 4.1 Branding (`docs/branding.md`)

- **Auto**: Jika `brandingMode = "auto"` di `data/install_config.json`
- Generate: Nama, Tagline, Value Proposition, Target Audience, Tone, Palet Warna, Typography

### 4.2 PRD (`docs/prd.md`)

- **Auto**: Jika `prdMode = "auto"` di `data/install_config.json`
- Generate 7 bagian: Problem Statement, Goals, Target User, User Stories, FR, NFR, Scope

### 4.3 References HTML (`references/`)

- **Auto**: Jika `referencesCount = 0` di `data/install_config.json`
- Generate sesuai `pageStructure` di `data/install_config.json`

---

## 5. Protokol Pembangunan AI (Build Protocol)

Setiap AI Coding Assistant WAJIB mengikuti 3 Tahap secara linear:

### TAHAP 1 — AUDIT & RENCANA (Read-Only)

1. Baca `CLAUDE.md`, `data/install_config.json`
2. Baca `docs/prd.md` dan `docs/branding.md` (jika ada)
3. Baca `references/*.html` (jika ada)
4. Audit struktur file core:
   - `include/config.php`, `include/helper.php`
   - `core/router.php`, `core/session.php`, `core/csrf.php`, `core/Repo.php`
   - `public/core/router.php` (router proxy - WAJIB)
   - `modules/auth/`
   - `.env`, `.env.example`
   - `data/users.json`
5. Jalankan **Audit Protocol** sesuai `docs/audit_protocol.md`
6. Buat `docs/build_plan.md`
7. **BERHENTI & TUNGGU persetujuan owner**

---

### TAHAP 2 — BUILD (Eksekusi)

1. Buat file sesuai `build_plan.md`
2. Ikuti arsitektur di CLAUDE.md:
   - Entry Guard Pattern (§8)
   - Router Proxy Pattern (§3f)
   - Repo Pattern Dual-Mode (§3g)
   - SPA Shell Architecture (§3a)
3. Generate demo users dengan Argon2ID (CLAUDE.md §6b)
4. Setup i18n files
5. **CRUD harus berfungsi nyata**

---

### TAHAP 3 — VERIFY & PREVIEW

1. Validasi syntax PHP: `php -l` pada semua file
2. Konfigurasi virtual host (manual via Laragon)
3. URL preview: `http://<project>.test/`

**Checklist Manual (owner verifikasi di browser):**
- [ ] Landing page sesuai `references/landingpage.html`
- [ ] Quick-login demo berfungsi
- [ ] i18n berfungsi
- [ ] Logout redirect ke landing page
- [ ] Auth state konsisten

---

## 6. Keamanan & Demo Users

**Security Baseline (CLAUDE.md §8):**
- Password: Argon2ID
- CSRF Token Validation
- IP+Username Rate Limiting
- Prepared Statements

**Demo Users (CLAUDE.md §6b):**

| Role | Email | Password |
|------|-------|----------|
| Manajemen | `admin@<app>.com` | `password123` |
| Admin | `admin@<app>.id` | `password123` |
| Client | `client@<app>.com` | `password123` |

---

## 7. Referensi Dokumen

| File | Scope |
|------|-------|
| `CLAUDE.md` | Konstitusi teknis utama |
| `docs/document.md` | Decision guide |
| `docs/prd.md` | Definisi produk |
| `docs/branding.md` | Identitas visual |
| `docs/audit_protocol.md` | Audit dasar |
| `data/install_config.json` | Konfigurasi aktif wizard |

---

**Template static - edit manual jika perlu**
