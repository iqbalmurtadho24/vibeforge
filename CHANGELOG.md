# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Initial Vibeforge template setup
- Multi-role authentication system (manajemen, admin, client)
- SPA shell architecture with AJAX-based module loading
- Dual-mode database support (JSON/MySQL)
- Dark/Light theme system
- CSRF protection with hash_equals verification
- Rate limiting (IP + username based)
- Remember-me with selector+validator pattern
- Comprehensive audit system (append-only)
- OWASP ASVS Level 1-2 security baseline

### Changed
- (List of changed files with brief description)

### Deprecated
- (List of deprecated features)

### Removed
- (List of removed features)

### Fixed
- (List of bug fixes)

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
