# Agent Guidelines

This file is the neutral contributor and coding-agent guide for Digital Sports CRM. Follow it when making changes in this repository.

## Project Overview

Digital Sports CRM is a Laravel 11 and Vite application for federation operations: members, entities, licenses, certifications, events, documents, payments, and public directories.

The project is published as open source under Apache License 2.0. Keep repository content generic and safe for public distribution. Deployment-specific branding, logos, secrets, production data, organization names, and commercial documents must stay outside the repo.

## Repository Layout

- `app/`: Laravel application layer, including HTTP controllers, console commands, events, notifications, and framework integrations.
- `src/Domain/`: domain modules and business actions.
- `src/Support/`: shared support utilities.
- `packages/`: local packages maintained with this app.
- `routes/`: route definitions grouped by namespace or feature.
- `resources/`: Blade views, JavaScript, CSS, and frontend source assets.
- `database/`: migrations, factories, and seeders.
- `tests/`: Pest/PHPUnit feature and unit tests.
- `config/`: Laravel and application configuration.
- `public/`: web root. Do not commit generated build output, uploaded files, private branding, or local media.
- `docs/`: public technical documentation only. Do not store business proposals, contracts, private runbooks, or client-specific documents here.

## Development Setup

Use the standard Laravel setup unless a task provides a more specific environment.

```bash
composer install
npm ci
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
```

For local development:

```bash
php artisan serve
npm run dev
```

## Quality Commands

Run the smallest useful validation for the change, then broaden when touching shared behavior.

```bash
php artisan test
./vendor/bin/pest
./vendor/bin/phpstan analyse -c phpstan.neon
./vendor/bin/pint
npm run build
npm run format
bash scripts/validate-no-generated-artifacts.sh --all
```

If local services are unavailable, report exactly which validation could not run and why.

## Coding Standards

- Follow PSR-12 for PHP and the existing Laravel conventions in nearby files.
- Prefer domain actions and services over placing business logic directly in controllers.
- Keep controllers thin: validate, authorize, delegate to actions/services, return responses.
- Use Eloquent relationships, scopes, casts, policies, requests, resources, and enums where they fit existing patterns.
- Avoid hardcoded status strings, role names, route fragments, and payment states when an enum, config value, or existing constant is available.
- Preserve backward compatibility for existing deployments unless the task explicitly asks for a breaking migration.
- Keep migration changes reversible where practical and document operational steps when data migration is required.
- Use factories and seeders for test data. Never commit real personal, client, athlete, federation, invoice, certificate, or uploaded production data.

## Open-Source Safety Rules

- Never commit `.env` files, credentials, API tokens, webhook secrets, SSH keys, private URLs, production database dumps, screenshots with real data, or browser test artifacts.
- Keep private logos and deployment-specific branding in ignored paths such as `public/private-branding/`.
- Use `example.test`, `example.com`, placeholder phone numbers, and clearly fake names in docs and tests.
- Do not add business proposals, pricing sheets, contracts, private legal documents, or client endorsement language.
- Do not imply guaranteed support, hosting, maintenance, SLA, warranty, or liability acceptance.
- Before release, run a full working-tree and history secret scan from a clean clone.

## Frontend Guidelines

- Use Vite, Tailwind, Blade, Alpine/Livewire patterns, and the existing component structure.
- Prefer small, cohesive frontend files and avoid one-off styling that conflicts with shared components.
- Keep interfaces dense, operational, and predictable. This is an admin/operations platform, not a marketing site.
- Check responsive behavior when changing shared layouts, tables, forms, navigation, or public pages.

## Testing Guidelines

- Pest is preferred; PHPUnit-compatible tests are acceptable where already used.
- Feature tests live in `tests/Feature`; unit tests live in `tests/Unit`.
- Use factories and explicit fake data. Avoid realistic personal data unless it is obviously synthetic and uses reserved domains.
- For payment, webhook, file upload, and privileged-access changes, include negative-path tests.
- Do not commit Playwright, Cypress, browser screenshots, traces, videos, test reports, or generated browser caches.

## Commit And PR Guidelines

- Use Conventional Commits, for example `fix: guard admin restore command`.
- Keep commits focused. Avoid mixing refactors with behavior changes unless the refactor is required.
- PRs should include summary, risk notes, migration steps, env changes, and validation performed.
- UI changes should include screenshots only when they do not contain private or personal data.

## Security Notes

- Treat all production data as private.
- Privileged commands, seeders, and admin access recovery flows must require explicit operator intent.
- Payment, invoicing, mail, object storage, Sentry, analytics, and webhook integrations must default to disabled or sandbox-safe behavior unless configured.
- Report vulnerabilities privately using the process in `.github/SECURITY.md`.
