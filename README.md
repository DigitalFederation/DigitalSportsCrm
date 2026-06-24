# Digital Sports CRM

[![License: Apache 2.0](https://img.shields.io/badge/License-Apache_2.0-blue.svg)](LICENSE)

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

## Configuration

All secrets and deployment-specific values belong in `.env`, never in committed files. Start from
`.env.example` and configure application/database/cache/queue/mail settings, branding
(`FEDERATION_*` / `INTERNATIONAL_FEDERATION_*`), the public map (`PUBLIC_MAP_*`), and the optional
EasyPay, Moloni, Sentry, and object-storage integrations. Your committees are defined in
`config/committees.php`. See the
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
