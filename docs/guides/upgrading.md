---
title: Upgrading
description: Check which version you are running and update a Digital Sports CRM deployment safely
---

# Upgrading

This page answers two questions: **which version am I running?** and **how do I move to the
latest one?**

## Which version am I running?

Three ways, in order of convenience:

1. **In the app** — the version is shown in the sidebar footer, and on the
   **Version & Changelog** page (`/changelog`), which also renders the full changelog.
2. **On the server** — `php artisan tinker --execute="echo config('app.version');"`
3. **From git** — `git describe --tags`

::: tip Reading `git describe`
A clean release prints the tag alone (`v1.2.0`). Anything else — for example
`v1.2.0-5-g168f401` — means you are running 5 commits *past* the v1.2.0 release, i.e. a
development snapshot of `main` rather than a release.
:::

## Is that the latest?

Compare it with the
[latest release](https://github.com/DigitalFederation/DigitalSportsCrm/releases/latest) on
GitHub. Every release there lists what changed, and the same notes are in
[`CHANGELOG.md`](https://github.com/DigitalFederation/DigitalSportsCrm/blob/main/CHANGELOG.md).

To be told about new releases instead of checking, open the
[repository](https://github.com/DigitalFederation/DigitalSportsCrm) and choose
**Watch → Custom → Releases**. GitHub then emails you on each new version.

The application never contacts GitHub on its own — a self-hosted instance makes no outbound
calls to check for updates.

## What the version number means

Releases follow [Semantic Versioning](https://semver.org/): given `MAJOR.MINOR.PATCH`,

| Change | Means | Upgrade effort |
| --- | --- | --- |
| **PATCH** (1.2.0 → 1.2.1) | Bug fixes only. | Drop-in. |
| **MINOR** (1.2.0 → 1.3.0) | New features, backward-compatible. | Drop-in; may add migrations or optional config. |
| **MAJOR** (1.2.0 → 2.0.0) | Breaking changes. | Read the release notes first — manual steps likely. |

Upgrade one minor version at a time rather than jumping several, so that each release's
migrations run in order.

## Before you upgrade

1. **Back up the database and the `storage/` directory.** Migrations are not reversible in
   practice once new data is written.
2. **Read the release notes** for every version between yours and the target, paying attention
   to the **Changed** sections — those are where manual steps appear.
3. **Check your local modifications.** If you have adapted the source, `git status` and
   `git stash` before pulling. Deployment-specific values belong in `.env` and files outside
   the repository, never in committed files.
4. **Put the site in maintenance mode** if the upgrade is more than a patch:
   `php artisan down --render="errors::503"`.

## Upgrading

```bash
git fetch --tags
git checkout v1.2.0                # or the release you are moving to
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan optimize:clear && php artisan optimize
php artisan queue:restart
php artisan up                     # if you ran `php artisan down`
```

::: warning `queue:restart` is not optional
Queue workers hold the old code in memory until they are restarted. Skipping this step leaves
jobs running against the previous release.
:::

Tracking `main` instead of releases? Substitute `git pull` for the fetch/checkout — but you
are then on unreleased code, and `git describe --tags` will report a development snapshot.

## Verifying the upgrade

```bash
php artisan about        # environment summary
```

Then load the app and confirm the sidebar footer shows the new version. If it still shows the
old one, the config cache is stale — run `php artisan optimize:clear` again.

## If something breaks

- `php artisan optimize:clear` resolves most post-upgrade oddities (stale config, route, view,
  and event caches).
- Check `storage/logs/laravel.log`.
- To roll back, restore the database backup **and** check out the previous tag. Restoring only
  the code leaves migrations applied that the old code does not expect.
- Report reproducible problems as a
  [GitHub issue](https://github.com/DigitalFederation/DigitalSportsCrm/issues), including the
  output of `git describe --tags` and `php artisan about`. Keep private deployment details out
  of issues.

## For maintainers: cutting a release

1. Add the `## [X.Y.Z] — YYYY-MM-DD` section to `CHANGELOG.md`, moving anything under
   `## [Unreleased]` into it.
2. Bump `version` in `config/app.php` to `X.Y.Z`. CI fails if it disagrees with either the
   changelog or the tag.
3. Merge, then `git tag vX.Y.Z && git push origin vX.Y.Z`.

The **Release** workflow publishes the GitHub release automatically, using that changelog
section as the release notes.
