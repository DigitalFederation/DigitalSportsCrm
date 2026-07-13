---
title: Getting Started
description: Install, configure, run, and deploy Digital Sports CRM
---

# Getting Started

This guide takes you from a fresh clone to a running instance — locally first, then a
production deployment. The application is a standard Laravel 11 + Vite project, so most of
this will be familiar if you have deployed Laravel before.

> Digital Sports CRM is provided as source for self-hosting and adaptation. The maintainers
> do not provide guaranteed hosting, support, or SLAs — you operate your own deployment.

## Requirements

- **PHP 8.2** or newer (with the usual Laravel extensions: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `curl`, `gd`/`imagick`, `zip`)
- **Composer**
- **Node.js and npm**
- **MySQL** (or a compatible database)
- Optional integrations, all configured through `.env`: Redis, a queue backend, object storage, mail, the EasyPay payment gateway, Moloni invoicing, and Sentry

## Quick start (local)

```bash
git clone <your-repo-url>
cd <repo>

composer install
npm ci

cp .env.example .env
php artisan key:generate
```

Set your database connection in `.env` (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`), create
the database, then load the schema and reference data:

```bash
php artisan migrate --seed
```

`migrate` loads the squashed schema from `database/schema/mysql-schema.sql` and the single
reference-data migration; `--seed` populates roles, permissions, committees, countries, menus,
and other lookup data via `DatabaseSeeder`.

Build the frontend and run the app:

```bash
npm run build       # production assets (also prepares vendor assets like TinyMCE)
php artisan serve   # serves http://127.0.0.1:8000
npm run dev         # optional: Vite dev server with HMR (instead of npm run build)
```

## First admin login

A fresh `migrate --seed` does **not** create a login account by default. Opt in to a default
admin by setting these in `.env` **before** running `migrate --seed`:

```ini
SEED_DEFAULT_ADMIN=true
DEFAULT_ADMIN_NAME="Site Admin"
DEFAULT_ADMIN_EMAIL=admin@example.test   # defaults to admin@example.test if left blank
DEFAULT_ADMIN_PASSWORD=change-me-now      # required when SEED_DEFAULT_ADMIN=true
```

If you already seeded without these set, run the user seeder on its own (clear any cached
config first so the new `.env` values are picked up):

```bash
php artisan config:clear
php artisan db:seed --class=UserSeeder
```

The seeded user receives the `admin` role (full platform access). Use these credentials only
for first login, then create real accounts and rotate the password. Leaving
`SEED_DEFAULT_ADMIN=false` is the right choice for any shared or production environment.

## Configuration

All secrets and deployment-specific values live in `.env` — never in committed files. Start
from `.env.example`, which is grouped and commented. Key areas:

| Area | Env keys |
|------|----------|
| Application | `APP_NAME`, `APP_ENV`, `APP_KEY`, `APP_DEBUG`, `APP_URL` |
| Database | `DB_CONNECTION`, `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` |
| Branding | `FEDERATION_*` (name, short name, contact, logo) and `INTERNATIONAL_FEDERATION_*` — see `config/branding.php` |
| Committees | Not env — define your federation's committees (and their licenses-attributed, purchase, and menu wiring) in `config/committees.php`. See [Configuring Committees](/guides/configuring-committees). |
| Certification systems | `DIVING_CERTIFICATION_SYSTEMS` (comma-separated, e.g. `PADI,SSI,CMAS`; empty by default) — see `config/diving.php` |
| Public map | `PUBLIC_MAP_*` (leave `PUBLIC_MAP_COUNTRY_ID` empty unless you intend to publish map locations) |
| Cache / session / queue | `CACHE_DRIVER`, `SESSION_DRIVER`, `QUEUE_CONNECTION` (the `cache`, `sessions`, and `jobs` tables ship in the schema, so `database` works out of the box) |
| Mail | `MAIL_*` |
| Payments (optional) | `EASYPAY_*` — see [EasyPay Integration](/easypay_integration) |
| Invoicing (optional) | `MOLONI_*` |
| Error tracking (optional) | `SENTRY_LARAVEL_DSN` |
| Storage (optional) | `FILESYSTEM_DISK`, `FILESYSTEM_PUBLIC_DRIVER`, `MEDIA_DISK`, object-storage credentials |

Deployment logos and private branding assets should be stored in an ignored path (e.g.
`public/private-branding/`) and referenced by env variables — do not commit them.

## Production deployment

The steps below assume a typical PHP-FPM + Nginx/Apache host. Adapt to your platform as needed.

### 1. Environment

```ini
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.example
```

Point your web server's document root at the project's **`public/`** directory (front
controller `public/index.php`). Ensure `storage/` and `bootstrap/cache/` are writable by the
web/PHP user.

### 2. Install, migrate, build

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build

php artisan migrate --force          # --force is required in production
php artisan storage:link             # if you serve uploaded files from the local public disk
```

### 3. Cache for performance

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
# or simply: php artisan optimize
```

Re-run these (or `php artisan optimize:clear` then re-cache) after any config or code change.

### 4. Scheduler (cron) — required

The app relies on scheduled tasks for time-based maintenance: license/certification/subscription/insurance
expiry, expired-document suspension, diving license-expiration checks, event-application deadline
notifications, weekly insurance reports, and database backups (see `app/Console/Kernel.php`).
Add a single cron entry:

```bash
* * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
```

Without this, time-based workflows (e.g. expiring licenses and suspending expired documents) will not run.
Note: paid-license **activation** is not a scheduled task — it happens when payment is confirmed, via a
queued event listener (see the queue worker below).

### 5. Queue worker — required for async work

Many actions are queued (invoice generation, role synchronisation, notifications, and more —
58+ queued jobs). The default `QUEUE_CONNECTION=sync` runs them inline (fine for evaluation,
but it blocks web requests). For production, switch to a real backend and run a worker:

```ini
QUEUE_CONNECTION=database   # the jobs/failed_jobs tables ship in the schema; redis also works
```

```bash
php artisan queue:work --tries=3
```

Run the worker under a process supervisor (systemd, Supervisor, or your platform's equivalent)
so it restarts on failure and after deploys. Restart workers on each deploy:
`php artisan queue:restart`.

### 6. Operations Center

Once deployed, admins (`access settings`) can review scheduled tasks and run whitelisted
maintenance commands from the in-app Operations Center — see
[Platform Utilities](/features/platform-utilities).

## Updating an existing deployment

```bash
git pull
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan optimize:clear && php artisan optimize
php artisan queue:restart
```

## Verifying the install

```bash
php artisan about           # environment summary
php artisan test            # full test suite (or ./vendor/bin/pest)
```

## Next steps

- [Architecture Overview](/architecture/01-overview) — how the system is structured
- [Configuring Committees](/guides/configuring-committees) — define your own committees (the main deployment-customization point)
- [Access Control](/access-control/role-management) — roles, permissions, membership rules
- [Creating a Plugin](/guides/creating-a-plugin) — extend the platform without forking core
- [Development Style Guide](/guides/development-style-guide) — conventions for contributing
