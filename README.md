# Digital Sports CRM

[![License: Apache 2.0](https://img.shields.io/badge/License-Apache_2.0-blue.svg)](LICENSE)
[![Latest release](https://img.shields.io/github/v/release/DigitalFederation/DigitalSportsCrm?label=latest%20release)](https://github.com/DigitalFederation/DigitalSportsCrm/releases/latest)

Digital Sports CRM is an open-source **Laravel 11 + Vite** platform for federation management. It
provides operational workflows for members, entities, certifications, licenses, events, documents,
payments, and public directories.

It is provided as source code for **self-hosting and adaptation**. The maintainers do not provide
guaranteed hosting, support, maintenance, or service-level commitments.

## Documentation

📖 **[Read the documentation »](https://digitalfederation.github.io/DigitalSportsCrm/)**

The full guide (also browsable in [`docs/`](docs/)) covers installation, configuration, architecture,
access control, and every feature. Start here:

- **[Getting Started](https://digitalfederation.github.io/DigitalSportsCrm/guides/getting-started)** — install, first admin login, and production deployment.
- **[Configuring Committees](https://digitalfederation.github.io/DigitalSportsCrm/guides/configuring-committees)** — define your federation's committees (the main customization point).
- **[Navigation & Menus](https://digitalfederation.github.io/DigitalSportsCrm/guides/navigation-and-menus)** — how the sidebar is built and customized.
- **[Architecture](https://digitalfederation.github.io/DigitalSportsCrm/architecture/01-overview)** and **[Access Control](https://digitalfederation.github.io/DigitalSportsCrm/access-control/role-management)**.

## Features

- **Members, entities & federations** with role- and permission-based access control.
- **Certifications & licenses** — purchase, attribution, and validation flows.
- **Config-driven committees** — model your federation's areas of activity (and their purchase,
  listing, and menu wiring) in `config/committees.php`, with no code changes.
- **Events** — events, applications, and enrollment.
- **Documents, payments & invoicing** — with an optional EasyPay gateway and Moloni invoicing.
- **Public directories** and a configurable public map.
- **Deployment-agnostic branding** via `config/branding.php`.

## Requirements

- PHP 8.2 or newer
- Composer
- Node.js and npm
- MySQL or a compatible database
- Redis, queues, object storage, mail, payment, Sentry, and invoicing integrations are optional and
  configured through environment variables

## Quick Start

```bash
composer install
npm ci
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
```

Run it locally:

```bash
php artisan serve   # http://127.0.0.1:8000
npm run dev         # Vite dev server (or use `npm run build` for static assets)
```

A fresh `migrate --seed` does **not** create a login account. To seed a first admin, set
`SEED_DEFAULT_ADMIN=true` and `DEFAULT_ADMIN_PASSWORD` in `.env` before seeding — see
**[Getting Started](https://digitalfederation.github.io/DigitalSportsCrm/guides/getting-started)**
for the full guide, including the required scheduler (cron) and queue worker for production.

## Versions & Updates

Releases follow [Semantic Versioning](https://semver.org/) and are published as
[GitHub releases](https://github.com/DigitalFederation/DigitalSportsCrm/releases), each with
notes describing what changed. `CHANGELOG.md` in this repository carries the same history.

**Am I running the latest version?**

```bash
git describe --tags                     # e.g. v1.2.0
```

The version is also shown in the app's sidebar footer and on its **Version & Changelog** page.
Compare it with the
[latest release](https://github.com/DigitalFederation/DigitalSportsCrm/releases/latest).

> A bare tag (`v1.2.0`) means you are on that release. Something like `v1.2.0-5-g168f401` means
> you are 5 commits past it — a development snapshot of `main`, not a release.

**Update to the latest release:**

```bash
git fetch --tags
git checkout $(git tag -l 'v*' --sort=-v:refname | head -n1)
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan optimize:clear && php artisan optimize
php artisan queue:restart
```

**Back up your database and `storage/` first**, and read the notes for every release between
yours and the target — the *Changed* sections are where manual steps appear. The full
procedure, including rollback, is in the
**[Upgrading guide](https://digitalfederation.github.io/DigitalSportsCrm/guides/upgrading)**.

To be notified of new versions, choose **Watch → Custom → Releases** on this repository. The
application itself never phones home to check for updates.

## Configuration

All secrets and deployment-specific values belong in `.env`, never in committed files. Start from
`.env.example` and configure application/database/cache/queue/mail settings, branding
(`FEDERATION_*` / `INTERNATIONAL_FEDERATION_*`), the public map (`PUBLIC_MAP_*`), and the optional
EasyPay, Moloni, Sentry, and object-storage integrations. Your committees are defined in
`config/committees.php`. Locale, timezone, default country, and the install-time geography package
are controlled by `APP_LOCALE`, `APP_TIMEZONE`, `DEFAULT_COUNTRY_CODE`, and
`GEOGRAPHY_DATASET`; see [Localization and Geography](docs/guides/localization-and-geography.md).
See the
[configuration reference](https://digitalfederation.github.io/DigitalSportsCrm/guides/getting-started#configuration).

Deployment-specific configuration, branding, logos, uploaded files, production data, business
documents, and credentials must stay **outside** the repository.

## Testing & Quality

```bash
php artisan test                                      # or ./vendor/bin/pest
./vendor/bin/phpstan analyse -c phpstan.neon
./vendor/bin/pint
npm run build
bash scripts/validate-no-generated-artifacts.sh --all
```

## Contributing

Contributions are welcome — please read [`CONTRIBUTING.md`](.github/CONTRIBUTING.md) (and
[`CODE_OF_CONDUCT.md`](.github/CODE_OF_CONDUCT.md)) before opening an issue or pull request. Keep private
deployment details out of issues, commits, and pull requests.

## Security

Please report vulnerabilities privately. See [`SECURITY.md`](.github/SECURITY.md).

## License

Licensed under the Apache License, Version 2.0. See [`LICENSE`](LICENSE).
