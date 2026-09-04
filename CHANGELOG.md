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

### Added

- **`php artisan version:check`.** Compares the installed version against the latest published
  release and exits `1` when an update is available, `2` when the check itself failed, so it can
  be scheduled and monitored. It is the only thing that contacts GitHub, and only when invoked —
  the application still makes no outbound calls of its own and shows no update banner. Point it
  elsewhere with `UPDATE_CHECK_REPOSITORY`, or set that empty to disable it.

### Documentation

- The install guide now installs a **release** (`git clone --branch vX.Y.Z`) rather than `main`,
  and explains that a `main` install reports the last released version number while being ahead
  of it.
- The upgrade guide documents three ways to learn about a new release: `version:check`, GitHub's
  Watch → Releases, and the `releases.atom` feed.

## [1.2.0] — 2026-09-04

This release makes the platform deployable outside Portugal: the interface is translated into
six languages, installation defaults are country-aware, Brazilian administrative geography
ships with it, and the legal pages are edited in the app instead of being hardcoded.

> **Upgrade notes.** This release adds database migrations and new environment variables, and
> it changes the `pt` locale identifier. Read
> [Upgrading](https://digitalfederation.github.io/DigitalSportsCrm/guides/upgrading) before
> starting, and see **Changed** below for the manual steps.

### Added

- **Multi-language interface.** Six locales: Portuguese (Portugal), Brazilian Portuguese,
  English, Spanish, French, and German — Brazilian Portuguese, Spanish, French, and German
  are new (#12, #13).
- **Language selector.** A flag-based picker in the header; the choice is stored per user, so
  it persists across sessions and devices (#15).
- **Editable legal pages.** The Terms of Use, Privacy Policy, and Data Sharing Policy now live
  in the database instead of hardcoded Blade views, with a Settings screen for editing them per
  locale. Each publish freezes an immutable version and keeps the history, drafts are saved
  without affecting the live page, and submitted HTML is sanitized before validation (#30, #31).
- **Country-aware installation defaults.** `APP_LOCALE`, `APP_FALLBACK_LOCALE`, `APP_TIMEZONE`,
  `DEFAULT_COUNTRY_CODE`, and `GEOGRAPHY_DATASET` configure a deployment's country from `.env`
  rather than requiring a fork. The default country resolves by ISO code and fails explicitly
  when the configuration is missing or ambiguous, instead of silently picking a record (#19).
- **Brazilian geography dataset.** States and municipalities ship as an installation dataset,
  selectable with `GEOGRAPHY_DATASET=brazil`.
- **Territorial federation assignment policy.** One country-neutral domain policy resolves an
  individual's territorial federation — an active club takes precedence, falling back to the
  federation mapped to the individual's municipality or district. Ambiguous and unmatched cases
  are recorded as activity entries for review rather than resolved arbitrarily, and existing
  assignments are preserved.
- **Home page settings screen.** Administrators configure the public home page from the admin
  area instead of editing files (#14).

### Changed

- **The `pt` locale is now `pt_PT`** (#12). Deployments setting `APP_LOCALE=pt` **must** change
  it to `APP_LOCALE=pt_PT`; the supported locales are declared explicitly in configuration.
- **Legal page content is read from the database.** A migration imports the existing
  `lang/{locale}/legal.php` text as the first published version, so no page goes blank on
  upgrade. Installations that had edited those files should review the imported result in
  **Settings → Legal Pages** — subsequent edits belong there, not in the language files.
- The back office no longer shows the public content navigation (public map and the club,
  coach, technical-official, and diving registries) in the shared header; member portals keep
  it (#29).

### Fixed

- English no longer falls through to the group translation files, and leftover untranslated
  Portuguese strings have been translated (#11).
- The `pt_BR` locale used European Portuguese terms that are wrong in Brazil: `NIF` → `CPF`,
  `Código Postal` → `CEP`, `Localidade` → `Bairro`, `apelido` (nickname) → `sobrenome`, plus
  Brazilian identity document types and `registo` → `registro` orthography. `pt_PT` is
  unchanged.
- The **Version & Changelog** page reported a stale version. It now reads the same source of
  truth as the sidebar (`config/app.php` → `version`); the unused `config/version.php` has been
  removed, and CI fails if that constant and this changelog disagree.
- The documentation site had failed to build since 13 August — internal design notes under
  `docs/plans/` contain Blade snippets that VitePress parsed as Vue expressions. Those notes are
  now excluded from the published site.

### Documentation

- New [Upgrading](https://digitalfederation.github.io/DigitalSportsCrm/guides/upgrading) guide:
  how to tell which version you are running, what the version numbers mean, what to back up, and
  how to roll back.
- New guides for
  [Localization & Geography](https://digitalfederation.github.io/DigitalSportsCrm/guides/localization-and-geography)
  and
  [Territorial Federation Assignment](https://digitalfederation.github.io/DigitalSportsCrm/guides/territorial-federation-assignment),
  both now reachable from the sidebar.

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
