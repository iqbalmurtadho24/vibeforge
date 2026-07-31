# Security Policy

## 🔒 Security Commitment & Architecture

Vibeforge takes security seriously. We follow **OWASP ASVS Level 1-2** as our minimum security baseline and strictly implement **Lapisan 2 (Keamanan)** & **Lapisan 6 (Keandalan)** dari **13 Pilar Software**:
1. **Authentication & Session** (Argon2ID, session regeneration, remember-me selector+validator)
2. **Role-Based Access Control / RBAC** (Role-to-shell mapping, `requireRole()`, Dual-Pattern Entry Guard)
3. **Rate Limiting** (Fixed-window IP + Username)
4. **Error Tracking & Audit Trail** (`cache/debug.log` dev-only vs `data/audit_trail.json` append-only)
5. **Availability & Data Recovery** (Atomic JSON write via temp file + `rename()`, Production Guard)

---

## 🚨 Reporting Security Vulnerabilities

**DO NOT create public GitHub issues for security vulnerabilities.**

Instead, please report via:

| Channel | Details |
|---------|---------|
| **Email** | security@vibeforge.dev (or project maintainer) |
| **PGP** | Available on request — encrypt sensitive details |

### Report Format
Please include:
- **Type**: XSS, SQLi, CSRF, Auth Bypass, IDOR, RCE, etc.
- **Location**: File path + line numbers (e.g., `core/router.php:142`)
- **Reproduction**: Step-by-step instructions + PoC code
- **Impact**: What an attacker could achieve
- **Suggested Fix**: Optional but appreciated

### Response Timeline
| Timeframe | Action |
|-----------|--------|
| **24 hours** | Acknowledge receipt |
| **72 hours** | Initial triage + severity assessment |
| **7 days** | Fix timeline estimate |
| **30 days** | Patch release (critical: sooner) |
| **Release** | CVE request (if applicable) + public advisory |

---

## ✅ Security Checklist

### For Developers (Code Contributions)

#### Authentication & Session (Pilar 4)
- [ ] Password hashing: **Argon2ID** only (`password_hash(..., PASSWORD_ARGON2ID)`)
- [ ] Session regeneration on login (`session_regenerate_id(true)`)
- [ ] Secure cookie flags: `HttpOnly`, `Secure` (production), `SameSite=Lax`
- [ ] Remember-me: selector + validator, per-device, invalidated on password change
- [ ] Rate limiting on auth endpoints (IP + username, `core/ratelimit.php`)

#### Input Validation & Output Encoding (Pilar 1)
- [ ] **Never** trust `$_GET`, `$_POST`, `$_REQUEST`, `$_COOKIE` directly
- [ ] Validate **server-side** (client-side is UX only)
- [ ] Escape output: `htmlspecialchars($var, ENT_QUOTES, 'UTF-8')` → `escape()` helper
- [ ] JSON context: `json_encode()` with `JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP`
- [ ] URL context: `rawurlencode()`, `http_build_query()`

#### Database & SQL Injection Prevention (Pilar 3 & 11)
- [ ] **Prepared statements only** — `Repo` layer enforces this in SQL mode
- [ ] No string interpolation in queries
- [ ] JSON mode: atomic write (temp + rename) & mutex file lock (`{entitas}.json.lock`)

#### Role Access & Entry Guards (Pilar 5)
- [ ] **Pola 1 (Entry Point)**: `defined('APP_ENTRY') or define('APP_ENTRY', true);`
- [ ] **Pola 2 (Module/Include)**: `if (!defined('APP_ENTRY')) { http_response_code(403); exit('Direct access forbidden'); }` *(Tanpa define!)*
- [ ] Role Guard: `requireRole('manajemen'|'admin'|'client')` di setiap module/shell.

#### CSRF Protection (Pilar 4)
- [ ] Token on **every** state-changing form (POST/PUT/DELETE)
- [ ] Verify with `hash_equals()` (timing-safe)
- [ ] Centralized in `core/router.php` + `core/csrf.php`

#### Security Headers & CSP (Pilar 6 & 10)
- [ ] CSP: `default-src 'self'`, allow Tailwind CDN, Phosphor Icons (`unpkg.com`), Google Fonts (`fonts.googleapis.com`)
- [ ] `X-Frame-Options: SAMEORIGIN`
- [ ] `X-Content-Type-Options: nosniff`
- [ ] `Referrer-Policy: strict-origin-when-cross-origin`

### For Deployments (Production Guard - Pilar 7 & 13)

| Setting | Required Value |
|---------|----------------|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `DB_MODE` | `mysql` (Hard block jika `DB_MODE=json` di production server) |
| `APP_KEY` | 64-char hex (cryptographic) |
| `CSRF_KEY` | 64-char hex |
| `REMEMBER_ME_SECRET` | 128-char hex |
| `SESSION_LIFETIME` | `3600` (1 hour) |
| HTTPS | Enforced (`HttpOnly` + `Secure` cookies) |
| `.htaccess` | Directory listing disabled, `.env`/`*.log`/`*.sql` blocked |

---

## 🛡️ Implemented Protections

| Feature | Status | Implementation |
|---------|--------|----------------|
| Password Hashing | ✅ | Argon2ID (`core/auth.php`) |
| CSRF Tokens | ✅ | Centralized (`core/csrf.php` + `core/router.php`) |
| SQL Injection | ✅ | Prepared statements only (`core/Repo.php`) |
| XSS (Output) | ✅ | `escape()` / `t()` helpers, CSP |
| Session Hijacking | ✅ | Regenerate ID, secure cookies |
| Rate Limiting | ✅ | Fixed-window IP+user (`core/ratelimit.php`) |
| Entry Guards | ✅ | Dual-Pattern System (Pola 1 vs Pola 2) |
| Remember-Me | ✅ | Selector/Validator, per-device |
| Re-auth Middleware | ✅ | Sensitive actions require current password |
| Clickjacking | ✅ | CSP `frame-ancestors 'self'` (`.htaccess`) |

---

## 📞 Contact

- **Security Email**: security@vibeforge.dev
- **Maintainer**: [@iqbalmurtadho24](https://github.com/iqbalmurtadho24)

---

*Last updated: 2026-07-31 | Version: 3.3.0 (13 Pilar Software Architecture Alignment)*
