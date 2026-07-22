# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Initial Vibeforge template setup
- SPA shell architecture with AJAX-based module loading
- Dark/Light theme system
- `core/Repo.php` - data access layer terpusat, auto-switch SQL/JSON
  per entitas (`DB_MODE=auto|json|mysql`), CRUD (`all/find/where/insert/
  update/delete`) dengan file-locking terpisah + atomic write (temp+rename)
  untuk mode JSON (lihat CLAUDE.md Section 3g)
- `core/session.php`, `core/csrf.php`, `core/remember.php`,
  `core/ratelimit.php`, `core/router.php` - auth core: session bootstrap
  + guard role, CSRF synchronizer token (hash_equals), remember-me
  selector+validator, rate limiting fixed-window IP+email, dispatcher
  module/action terpusat
- `modules/auth/login.php`, `modules/auth/register.php` - login & register
  penuh lewat `Repo` (bukan akses file langsung), menulis audit trail
  append-only tiap aksi
- Multi-role authentication system (manajemen, admin, client) - login,
  role guard per shell, dan logout teruji end-to-end lewat HTTP asli
- Dual-mode database support (JSON/MySQL) via `Repo`
- CSRF protection dengan hash_equals verification, terpusat di
  `core/router.php`
- Rate limiting dasar (IP + email, fixed-window)
- Remember-me dengan selector+validator pattern
- Audit trail append-only (`data/audit_trail.json`)
- `data/users.json` (live demo data, 3 role) dengan hash Argon2ID valid
- OWASP ASVS Level 1-2 security baseline

### Changed
- `.env.example`: `DB_MODE` default `json` -> `auto` (deteksi otomatis
  SQL/JSON per tabel, bukan flag manual)
- `public/admin/index.php`, `public/client/index.php`,
  `public/manajemen/index.php`, `public/index.php`: hapus akses
  `loadJsonFile('users.json')` langsung (melanggar Section 3g), ganti
  pakai `$user['theme_preference']` yang sudah datang dari `Repo` via
  `getCurrentUser()`
- `public/login/index.php`: tombol quick-login demo sumber emailnya dari
  `Repo::table('users')->all()`, bukan hardcoded literal per role
- `.gitignore`: tambah `data/audit_trail.json`, `data/login_attempts.json`,
  `data/remember_tokens.json`, `data/*.lock` (artefak runtime, jangan
  ke-commit)
- `CLAUDE.md` Section 2: klaim "autoloading PSR-4 via
  `spl_autoload_register()`" diganti mencerminkan pendekatan yang benar-
  benar dipakai (`require_once` eksplisit per file, tanpa namespace)
- `docs/install.md` TAHAP 2: tambah langkah 7 - validasi fungsional via
  `php -S` + `curl` ke `/` dan `/login/` (WAJIB HTTP 200 sebelum lanjut
  Tahap 3), karena `php -l` di langkah 6 hanya menangkap parse error,
  bukan "fungsi belum di-require" yang fatal saat runtime

### Deprecated
- (List of deprecated features)

### Removed
- (List of removed features)

### Fixed
- Semua shell (`public/index.php`, `login/`, `register/`, `admin/`,
  `client/`, `manajemen/`, `logout/`) fatal error karena memanggil fungsi
  core yang belum pernah didefinisikan di manapun (`initSession()`,
  `isLoggedIn()`, `getCurrentUser()`, `getDashboardUrl()`,
  `generateCsrfToken()`, `redirect()`, `clearRememberToken()`) - sekarang
  seluruhnya terhubung ke `core/session.php`/`core/csrf.php`/
  `core/remember.php`
- `CLAUDE.md` Section 12e "Template Standar Shell Baru" dan
  `docs/install.md` TAHAP 2 langkah 5b: template yang didokumentasikan
  sebelumnya hanya mencontohkan `require config.php + helper.php` sebelum
  memanggil `initSession()`, padahal fungsi itu ada di
  `core/session.php`/`core/csrf.php` yang tidak pernah disebut - kalau
  diikuti apa adanya untuk membangun shell baru, ini akan mereproduksi
  persis bug fatal error di atas pada project turunan manapun

### Security
- (Security-related changes)

---

## [1.0.0] - 2026-07-14

### Added
- Initial release
- Landing page with session-based authentication
- Login/Register modules
- Manajemen dashboard shell
- Admin dashboard shell
- Client dashboard shell
- Vibeforge branding template system
- OpenAPI documentation template

---

## Versioning

This project uses [SemVer](https://semver.org/) for versioning.

- **MAJOR** version: Incompatible changes to the framework architecture
- **MINOR** version: New functionality in a backwards compatible manner
- **PATCH** version: Backwards compatible bug fixes

## Release Schedule

There is no fixed release schedule. Releases are made as needed when:
- Major security patches are required
- Significant new features are added
- Breaking changes must be deployed

---

## How to Update This File

When making changes to the project:

1. Add entries under `[Unreleased]` section
2. Use these prefixes:
   - `Added` for new features
   - `Changed` for changes in existing functionality
   - `Deprecated` for soon-to-be removed features
   - `Removed` for removed features
   - `Fixed` for any bug fixes
   - `Security` for vulnerability fixes

3. When releasing a new version:
   - Move all `[Unreleased]` changes to a new version section
   - Add release date in ISO 8601 format
   - Create a new empty `[Unreleased]` section

Example:
```markdown
## [1.1.0] - 2026-07-15

### Added
- New API endpoint for user profile updates
- Export data feature in CSV format

### Fixed
- Session timeout not working correctly
- CSS variable not applied on theme switch
```
