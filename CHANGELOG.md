# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- **13 Pilar Software Architecture Alignment**: Restructured core constitution (`CLAUDE.md`), `README.md`, and `SECURITY.md` to map directly to the 6 Layers & 13 Software Pillars (Frontend, API & Backend Logic, Database & Storage, Auth & Session, RBAC, Hosting & Deployment, Cloud Compute, CI/CD & Version Control, Rate Limiting, Cache & CDN, Scaling, Error Tracking & Logging, Availability & Recovery).
- **Integrated Execution Flow Specification**: Formally linked installation workflow (`public/install/` → `data/install_config.json` → `docs/install.md` → `docs/prd.md` & `docs/branding.md` → `references/*.html` → `public/*.php`) to eliminate architecture gaps and redundant checks.

### Changed
- **`CLAUDE.md`**: Re-architected as the official technical constitution implementing the 13 Pillars, with explicit path resolution matrix, Dual-Pattern Entry Guard rules, and Repo dual-mode constraints.
- **`README.md`**: Updated documentation to highlight the 13 Pillars mapping table, SPA Shell Architecture, and Setup Wizard 4-Step flow.
- **`SECURITY.md`**: Updated OWASP ASVS baseline security policy mapped to Layer 2 (Keamanan) & Layer 6 (Keandalan).

### Fixed
- Integration misalignments between `docs/install.md`, `CLAUDE.md`, and template generation steps.

---

## [1.0.0] - 2026-07-14

### Added
- Initial release of Vibeforge template system
- SPA shell architecture with AJAX-based module loading
- Dark/Light theme system
- `core/Repo.php` - central data access layer, auto-switch SQL/JSON
-OWASP ASVS Level 1-2 security baseline
