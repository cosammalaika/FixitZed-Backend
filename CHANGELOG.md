# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]
### Added
- Email verification flow and resend endpoint for API clients.
- Multi-factor authentication (TOTP, backup codes, trusted devices).
- Login audit logging with admin UI and rate limiting on auth endpoints.
- API caching layer with tagged cache invalidation and performance config.
- GitHub Actions CI pipeline covering Laravel and both Flutter apps.
- Infrastructure docs for HTTP/2/gzip and Horizon tuning.
- PHPStan configuration and reusable offline placeholder in apps.
- Shared connectivity banner and shimmer skeleton loaders across mobile apps.

### Changed
- Hardened CORS/Sanctum configuration defaults and .env guidance.
- Fixer, catalog, and service endpoints now leverage cache when enabled.

### Notes
- Run `composer update` to install new dev tools (Larastan).
- Update server config using `infra/nginx.conf` and `infra/horizon.md`.
