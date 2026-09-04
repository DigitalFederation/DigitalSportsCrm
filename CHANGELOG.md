# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project follows [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

To check which version you are running, open **Version & Changelog** in the app (or read
`config/app.php` → `version`), and compare it with the
[latest release](https://github.com/DigitalFederation/DigitalSportsCrm/releases/latest).
See [Upgrading](https://digitalfederation.github.io/DigitalSportsCrm/guides/upgrading) for the
update procedure.

## [Unreleased]

_Nothing yet._

## [1.2.0] — 2026-09-04

### Added

- **Multi-language support.** The interface is now available in six locales: Portuguese
  (Portugal), Brazilian Portuguese, English, Spanish, French, and German. Brazilian
  Portuguese, Spanish, French, and German are new (#12, #13).
- **Language selector.** A flag-based language picker in the header, with the choice stored
  per user so it persists across sessions and devices (#15).
- **Home page settings screen.** Administrators can configure the public home page from the
  admin area instead of editing files (#14).

### Changed

- The `pt` locale was split into `pt_PT` (Portugal) and `pt_BR` (Brazil), and the supported
  locales are now declared explicitly in configuration (#12). Deployments that set
  `APP_LOCALE=pt` should change it to `APP_LOCALE=pt_PT`.

### Fixed

- English no longer falls through to the group translation files, and leftover untranslated
  Portuguese strings have been translated (#11).
- The version shown on the **Version & Changelog** page now reads the same source of truth as
  the sidebar (`config/app.php` → `version`). The unused `config/version.php` file, which
  reported a stale number, has been removed.

## [1.1.0] — 2026-07-14

### Added

- The federation logo is now optional — the brand name is rendered as text when no logo is
  configured (#5).
- The version shown in the sidebar is a documented release constant in `config/app.php`,
  overridable via the `APP_VERSION` environment variable for packagers (#7, #8).

### Fixed

- `php artisan migrate --seed` now creates the opt-in default admin account. Previously,
  setting `SEED_DEFAULT_ADMIN=true` and `DEFAULT_ADMIN_*` in `.env` had no effect during the
  documented install flow, leaving fresh installs with no login account (#6).

### Documentation

- New admin guide, "Administering the Platform", and menu-manager documentation (#1).
- Per-portal operator guides: Federation, Club, and Individual (#2).

### Changed

- Remaining "Digital Federation" mentions rebranded to "Digital Sports CRM" (#3).

## [1.0.0] — 2026-06-25

First public release of Digital Sports CRM, a self-hosted Laravel 11 + Vite platform for
federation management. It provides operational workflows for:

- Members, entities, and federations, with role- and permission-based access control.
- Certifications and licenses — purchase, attribution, and validation.
- Committees defined entirely in configuration (`config/committees.php`), so each deployment
  models its own areas of activity — including the license-purchase, licenses-attributed, and
  sidebar-menu wiring — without code changes.
- Events, event applications, and enrollment.
- Documents, payments (with an optional EasyPay gateway), and invoicing.
- Public directories and a configurable public map.
- Deployment-agnostic branding via `config/branding.php`.

See the documentation (`docs/`) for installation, configuration, and architecture.

[Unreleased]: https://github.com/DigitalFederation/DigitalSportsCrm/compare/v1.2.0...HEAD
[1.2.0]: https://github.com/DigitalFederation/DigitalSportsCrm/compare/v1.1.0...v1.2.0
[1.1.0]: https://github.com/DigitalFederation/DigitalSportsCrm/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/DigitalFederation/DigitalSportsCrm/releases/tag/v1.0.0
