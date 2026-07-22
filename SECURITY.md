# Security Policy

## 🔒 Security Commitment

This project prioritizes security as a core requirement. We follow
[OWASP ASVS Level 1-2](https://owasp.org/www-project-application-security-verification-standard/)
as our minimum security baseline.

---

## 🚨 Reporting Security Vulnerabilities

If you discover a security vulnerability within this framework, please follow these steps:

### 1. Private Disclosure

**Do NOT** create a public GitHub issue for security vulnerabilities.
Instead, please report directly to the project maintainer via private channels.

### 2. Report Format

When reporting, please include:

- **Type** of vulnerability (XSS, SQL Injection, CSRF, etc.)
- **Full paths** of source file(s) that have the vulnerability
- **Location** of the affected source code (line numbers)
- **Step-by-step instructions** to reproduce the issue
- **Proof-of-concept** or attack code (if possible)
- **Suggested remediation** (optional)

### 3. Response Timeline

| Timeline | Action |
|----------|--------|
| Within 24 hours | Acknowledge receipt of your report |
| Within 7 days | Initial response with status update |
| Within 30 days | Detailed response with fix timeline |
| As appropriate | Public acknowledgment (with your permission) |

---

## ✅ Security Checklist

### For Developers

When contributing to this project, ensure:

- [ ] **Password Hashing**: Always use `PASSWORD_ARGON2ID`
- [ ] **CSRF Tokens**: Verify with `hash_equals()` on all POST requests
- [ ] **SQL Injection**: Use prepared statements only
- [ ] **XSS Prevention**: Escape output with `htmlspecialchars()`
- [ ] **Session Security**: Regenerate session ID on login
- [ ] **Rate Limiting**: Implement on all auth endpoints
- [ ] **Input Validation**: Validate all user input server-side

### For Deployments

Before going to production:

- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Use MySQL (`DB_MODE=mysql`)
- [ ] Generate strong cryptographic keys
- [ ] Configure HTTPS (HttpOnly, Secure cookies)
- [ ] Review `.htaccess` security rules
- [ ] Disable directory listing

---

## 🛡️ Security Features

### Implemented Protections

| Feature | Status | Implementation |
|---------|--------|----------------|
| Password Hashing | ✅ | Argon2ID |
| CSRF Protection | ✅ | Token + hash_equals() |
| SQL Injection | ✅ | Prepared Statements |
| XSS Prevention | ✅ | Output Escaping |
| Session Hijacking | ✅ | Session Regeneration |
| Rate Limiting | ✅ | IP + Username |
| Remember-Me | ✅ | Selector + Validator |
| Clickjacking | ⚠️ | To be implemented |

---

## 🔑 Security Configuration

### Required Environment Variables

```env
# Cryptographic keys (REQUIRED in production)
APP_KEY=<64-char hex>
CSRF_KEY=<64-char hex>
REMEMBER_ME_SECRET=<128-char hex>

# Environment (REQUIRED in production)
APP_ENV=production
APP_DEBUG=false
```

### Recommended Production Settings

```env
# Database
DB_MODE=mysql

# Session
SESSION_LIFETIME=3600  # 1 hour

# Rate Limiting
RATE_LIMIT_MAX=10
RATE_LIMIT_WINDOW=300  # 5 minutes
```

---

## 📋 Security Standards

This project adheres to:

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [OWASP ASVS Level 1-2](https://owasp.org/www-project-application-security-verification-standard/)
- [CWE](https://cwe.mitre.org/) (Common Weakness Enumeration)
- [CVE](https://cve.mitre.org/) (if applicable)

---

## 🔄 Security Updates

Security updates will be released as patches when vulnerabilities are discovered.
Users will be notified through:

- Version bumps in CHANGELOG.md
- Security section updates

---

## 📞 Contact

For security-related inquiries:
- **Email**: [security@example.com](mailto:security@example.com)
- **PGP Key**: [Available upon request]

---

## ⚠️ Disclaimer

While we strive to maintain the highest security standards, no software is
completely secure. Users of this framework are responsible for:

1. Keeping their deployments up to date
2. Following security best practices
3. Reporting vulnerabilities responsibly
4. Configuring security settings appropriately for their use case

The project maintainers are not liable for any damages resulting from
security vulnerabilities, whether or not they are known.
