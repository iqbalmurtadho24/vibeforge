# Security Policy

## 🔒 Security Commitment

Vibeforge takes security seriously. We follow **OWASP ASVS Level 1-2** as our minimum security baseline and implement defense-in-depth across all layers.

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

#### Authentication & Session
- [ ] Password hashing: **Argon2ID** only (`password_hash(..., PASSWORD_ARGON2ID)`)
- [ ] Session regeneration on login (`session_regenerate_id(true)`)
- [ ] Secure cookie flags: `HttpOnly`, `Secure` (production), `SameSite=Lax/Strict`
- [ ] Remember-me: selector + validator, per-device, invalidated on password change
- [ ] Rate limiting on auth endpoints (IP + username, `core/ratelimit.php`)

#### Input Validation & Output Encoding
- [ ] **Never** trust `$_GET`, `$_POST`, `$_REQUEST`, `$_COOKIE` directly
- [ ] Validate **server-side** (client-side is UX only)
- [ ] Escape output: `htmlspecialchars($var, ENT_QUOTES, 'UTF-8')` → `escape()` helper
- [ ] JSON context: `json_encode()` with `JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP`
- [ ] URL context: `rawurlencode()`, `http_build_query()`

#### Database (SQL Injection Prevention)
- [ ] **Prepared statements only** — `Repo` layer enforces this
- [ ] No string interpolation in queries (even for "safe" values)
- [ ] JSON mode: validate/sanitize before write (no direct SQL risk but integrity matters)

#### CSRF Protection
- [ ] Token on **every** state-changing form (POST/PUT/DELETE)
- [ ] Verify with `hash_equals()` (timing-safe)
- [ ] Centralized in `core/router.php` + `core/csrf.php` — not per-file

#### XSS Prevention
- [ ] CSP header via `.htaccess` (see `public/.htaccess`)
- [ ] `escape()` / `t()` for all dynamic content
- [ ] No `innerHTML` with untrusted data — use `textContent` or framework escaping

#### File Uploads
- [ ] MIME validation (`finfo_file()`, not extension)
- [ ] Size limit (2MB default for logos)
- [ ] Store outside document root (`docs/logo.png` is exception — read-only served)
- [ ] Randomized filenames if user content

#### Security Headers
- [ ] CSP: `default-src 'self'`, allow CDN fonts/icons explicitly
- [ ] `X-Frame-Options: SAMEORIGIN` (or `DENY`)
- [ ] `X-Content-Type-Options: nosniff`
- [ ] `Referrer-Policy: strict-origin-when-cross-origin`
- [ ] `Permissions-Policy` (restrict camera/mic/geolocation)

### For Deployments (Production)

| Setting | Required Value |
|---------|----------------|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `DB_MODE` | `mysql` (hard fail if missing) |
| `APP_KEY` | 64-char hex (cryptographic) |
| `CSRF_KEY` | 64-char hex |
| `REMEMBER_ME_SECRET` | 128-char hex |
| `SESSION_LIFETIME` | `3600` (1 hour) |
| HTTPS | Enforced (HttpOnly + Secure cookies) |
| `.htaccess` | Directory listing disabled, `.env`/`*.log`/`*.sql` blocked |

**Generate keys:**
```bash
php -r "echo bin2hex(random_bytes(32)).PHP_EOL;"   # APP_KEY / CSRF_KEY
php -r "echo bin2hex(random_bytes(64)).PHP_EOL;"   # REMEMBER_ME_SECRET
```

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
| Remember-Me | ✅ | Selector/Validator, per-device |
| Re-auth Middleware | ✅ | Sensitive actions require current password |
| Clickjacking | ⚠️ | CSP `frame-ancestors 'self'` (`.htaccess`) |
| Subresource Integrity | ❌ | Not yet (CDN resources) |

---

## 🔑 Security Configuration

### Required `.env` Variables (Production)
```env
# Cryptographic (generate fresh per deployment)
APP_KEY=                    # 64 hex chars
CSRF_KEY=                   # 64 hex chars
REMEMBER_ME_SECRET=         # 128 hex chars

# Environment
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database — force MySQL in production
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

### Development Overrides (`.env.local` or `.env` when `APP_ENV=development`)
```env
APP_ENV=development
APP_DEBUG=true
DB_MODE=auto        # or json
SESSION_SECURE=false
```

---

## 📋 Security Standards Compliance

- **OWASP Top 10 2021** — Addressed (A01-A10)
- **OWASP ASVS Level 1-2** — Baseline implemented
- **CWE** — Common weakness patterns mitigated
- **NIST SSDF** — Secure development practices

---

## 🔄 Security Updates

Security patches are released as:
- **Patch version** (e.g., `v1.2.1`) for fixes
- **CHANGELOG.md** — Security section lists CVEs/fixes
- **GitHub Security Advisories** — For coordinated disclosure

Subscribe to releases or watch the repo for notifications.

---

## 📞 Contact

- **Security Email**: security@vibeforge.dev
- **Maintainer**: [@iqbalmurtadho24](https://github.com/iqbalmurtadho24)
- **PGP Key**: On request

> **Disclaimer**: While we implement industry-standard protections, no software is 100% secure. Deployers are responsible for: keeping dependencies updated, configuring servers correctly, monitoring logs, and following security best practices for their environment.

---

*Last updated: 2026-07-26 | Version: 1.0*