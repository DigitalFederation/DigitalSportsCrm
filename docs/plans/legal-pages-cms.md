# Legal Pages CMS — Implementation Plan

Make the legal pages (Terms of Use, Privacy Policy, Data Sharing Policy) editable per
installation from the admin UI, the way any CMS/CRM does it. Today they are hardcoded
and effectively publish the original Portuguese/EU-derived text to every installation.

> **Status:** reviewed against the codebase, then audited for upgrade safety on live installs.
> Amendments are marked **[R]** (design review) and **[U]** (upgrade audit) where they changed the
> original design. **[U] The upgrade audit changed the release structure — read "Release sequence"
> before implementing anything.**

## Release sequence — read this first

This platform is already running in production for real federations. They upgrade by pulling code
and running `php artisan migrate`; **they do not run seeders**. Today, editing `lang/*/legal.php` or
the three legal blades is the *only* way an install can change its published legal text — so any
federation that has customized its terms has that text sitting in files this feature replaces.

The work therefore ships across **two releases**, and the split is not cosmetic:

| | Contents | Why |
|---|---|---|
| **Release N** | CMS core + admin CRUD + menu migration + **an import migration**. `lang/*/legal.php` and the three blades stay **in place**. | The import migration calls `Lang::get('legal.*')` per locale at migrate time, which reads *that install's on-disk lang files* — capturing any local customization automatically. This only works while the files still exist. |
| **Release N+1** | Delete `lang/*/legal.php`, the three legal blades, and the Jetstream legal surface. | By now every upgraded install's real text is safely in the database. |

**The deletion cannot share a release with the import.** Whichever deploy style an install uses —
artifact swap or `git pull` — the code tree is replaced *before* `artisan migrate` runs, so a
migration can never read files its own release deleted. Deferring the deletion one release is the
only ordering that works, and sequencing is free.

## Decisions taken

1. **[U] Delete the old sources in Release N+1, not PR 1.** `lang/*/legal.php`, the three legal blades and the Jetstream legal surface all go — one release *after* the CMS lands, so the import migration can read them first. (This supersedes the original "delete in PR 1" decision; the intent is unchanged, only the timing.)
2. **WYSIWYG editor.** Use the repo's existing TinyMCE component. Content is stored as **HTML**, not markdown.
3. **Locale defaults authored jurisdiction-neutral in all six locales** (`en`, `pt_PT`, `pt_BR`, `es`, `fr`, `de`).
4. **Explicit draft → "Publish new version" flow.** Drafts are editable in place; publishing freezes a version and bumps the number.
5. **[R] Feature is named "Legal Pages", not "Legal Documents".** `menu.admin.legal_documents` is already taken by the official-documents section (`config/menu.php:272`, translated "Legal Documents" at `lang/en/menu.php:60`). To avoid two identically-labelled sidebar sections and a silent PHP array key clash, this feature uses `legal_pages` / `legal-pages` / `LegalPage` throughout.

### Still open (safe defaults assumed, change if you disagree)

- **Permission:** reuse `access settings`. **[R] Confirmed as the right call** — it exists at `RoleAndPermissionSeeder.php:115` and is held only by the `admin` role, and reusing it needs **no re-seed** on existing installs, whereas a new permission would require a `RoleAndPermissionSeeder` re-run as an upgrade step.
- **Forced re-acceptance on republish:** plan records the accepted version (PR 3) but does *not* gate existing users behind an interstitial. That gate would be a 4th micro-PR.

## Verified facts this plan relies on

- **TinyMCE 7.5.1 is already a dependency** (`package.json`), vendored to `public/vendor/tinymce` by `scripts/copy-tinymce-assets.mjs` via the `prepare:tinymce` npm script that `dev` and `build` both run. Reusable Blade components exist: `resources/views/components/forms/tinymce-editor-static.blade.php` (plain form POST, takes `name` / `value` / `elementId` props) and a `-livewire` variant. Live usage: `resources/views/web/entity/profile/edit.blade.php:334`. **No new JS dependency is needed for the WYSIWYG.**
- **`ueberdosis/tiptap-php ^1.4` is already in `composer.json` (line 51) and currently unused in `app/`.** It parses HTML into a normalized document and re-renders it, dropping nodes and attributes outside its schema — usable as the server-side sanitizer without adding HTMLPurifier.
- The repo also carries `@editorjs/*`, `@tiptap/*` and a vendored CKEditor 5 build (`resources/vendor/ckeditor5/`). Pre-existing and out of scope; do not add a fifth editor.
- `spatie/laravel-translatable ^6.8` exists but is a JSON-column-per-attribute approach; it does not fit per-locale *versioning*, so a plain table keyed by `(type, locale, version)` is used instead.
- Jetstream's `Features::termsAndPrivacyPolicy()` is **commented out** in `config/jetstream.php:60`, so `resources/views/auth/register.blade.php:43-45` and `CreateNewUser.php:26`'s `terms` rule are dead code. The *live* terms checkboxes are in `resources/views/web/public/individual/create.blade.php:474-492`, `resources/views/web/public/entity/create.blade.php:394-415`, and `resources/views/components/entity/form_create_edit.blade.php:397-426`, posting bare `terms` / `data_sharing` booleans with no version recorded.
- Admin conventions: controllers in `app/Http/Controllers/Admin/`, routes in `routes/admin.php` (prefix `/admin`, name `admin.*` — `routes/admin.php:7-8`) inside `Route::middleware('permission:access settings')` groups (homepage-settings at `routes/admin.php:966-973`, group opens at `:948`). FormRequests in `app/Http/Requests/`, views under `resources/views/web/admin/<feature>/`.
- **[R] `MenuSeeder` is destructive.** `database/seeders/MenuSeeder.php:47` deletes all `MenuItem`s for a menu and rebuilds from `config/menu.php`; `:76` route-validates via `Route::has()` and silently skips entries whose route is missing. It is called from `DatabaseSeeder.php:33` but **not** from `FreshInstallSeeder`. Consequences are handled in Phase 2's upgrade note.
- **[R] `SetLocale` middleware** (`app/Http/Middleware/SetLocale.php:16-25`, in the `web` group at `Kernel.php:40`) resolves user preference → session → app default. Guests switch via `GET language/{locale}` (`routes/web.php:170`). The legal routes (`routes/web.php:106-109`) sit in the plain `web` group — no auth, anonymous access is fine.
- **[R] `config/app.php:85` hardcodes `'locale' => 'pt_PT'`** with no `env()`, so anonymous visitors on every install default to Portuguese.
- Tests are Pest, in `tests/Feature/` (admin ones in `tests/Feature/Admin/`), seeding `RoleAndPermissionSeeder` + `UserGroupSeeder` in `beforeEach` (pattern: `tests/Feature/Admin/HomePageSettingsTest.php`). All new files MUST end in `Test.php`.
- Locales: `pt_PT` (default), `pt_BR`, `es`, `en`, `fr`, `de` (`config/app.php:87-95`); `fallback_locale` = `en`.
- Migrations are squashed into `database/schema/mysql-schema.sql`; latest is `2026_07_15_225258_create_site_settings_table.php` — new migrations stack on top normally.
- `route:cache` is already impossible in this app (closure route at `routes/web.php:97`), and `config:cache` does not touch DB content, so neither interferes with this feature.

## Hardcoded content this feature must sweep up

Beyond the three legal blades themselves, the following are jurisdiction- or language-locked and
would otherwise survive the migration untouched. Items 1–2 are the reason the feature is not
"done" once the CMS works: they are the *entry points* to the legal pages, in Portuguese, on every
install.

| # | Location | Problem |
|---|---|---|
| 1 | `web/public/entity/create.blade.php:394-415` | Consent checkbox copy is hardcoded Portuguese passed to `__()` as JSON keys (`__('Declaro que li e concordo com os ')`, `__('Termos de Serviço')`, `__('Política de Partilha de Dados')`…). These keys exist in **no** locale JSON file, so English/French/German installs render the consent text in Portuguese. This is the exact language PR 3 records acceptance of. Fix: use the `entity.terms_confirm` family already used by the sibling component (`components/entity/form_create_edit.blade.php:397-426`, keys at `lang/en/entity.php:95-97`). |
| 2 | `web/welcome.blade.php:180-182` | Footer legal links are raw Portuguese literals not even wrapped in `__()` — `Política de Privacidade`, `Termos de Uso`, `Suporte`. This is the only footer linking the legal pages, and the data-sharing policy is not linked at all. Fix: lang keys in all six locales + add the third link. |
| 3 | `lang/en/legal.php:4,8,87-88,116` | Interpolates `config('branding.international')` (international federation name) into the data-sharing recipients list and international-transfers clause. Needs a token — see placeholder list. |
| 4 | `lang/*/legal.php:27-28` | `'dpo_department' => 'Administrative and Financial Department'` — one federation's org chart, in all six locales. Many jurisdictions don't mandate a DPO at all. Fix: seed as plain editable body text using `{{federation_email}}`/`{{federation_address}}`, no named department. |
| 5 | `lang/*/legal.php:44` | `'Tax Identification Number (NIF)'` — the Portuguese acronym persists even in German (`lang/de/legal.php:44`) and Spanish. Fix: no acronym in the stubs. |
| 6 | `lang/en/legal.php:79,81,83,111-113,~137` | GDPR **article-level** citations ("Art. 6(1)(b) GDPR", "Under the GDPR, …") pervade the legal-basis, public-disclosure and data-subject-rights sections — roughly ten cites, not just the one `gdpr_reference` key. Fix: the porting step removes *all* of them, not only the framework paragraph. |
| 7 | `resources/markdown/{terms,policy}.md`, `resources/views/{terms,policy}.blade.php`, `auth/register.blade.php:43-45` | A second, parallel legal surface. Jetstream's placeholder markdown ("Edit this file to define the terms of service…") would become an install's published terms the moment anyone uncomments `Features::termsAndPrivacyPolicy()`, bypassing the CMS entirely. Fix: delete all five, or point `terms.show`/`policy.show` at the new routes. |
| 8 | `config/app.php:85` | `'locale' => 'pt_PT'` hardcoded. Anonymous visitors on a German install get the Portuguese legal pages first. Fix: `env('APP_LOCALE', 'pt_PT')` + document in install docs. |
| 9 | `components/public-layout.blade.php:2` | `<html lang="{{ config('app.locale') }}">` ignores `app()->getLocale()`, so legal pages declare the wrong language to browsers and screen readers. Fix: `str_replace('_','-',app()->getLocale())`. |
| 10 | `shared/insurance/document-pdf.blade.php:629-630` | Hardcoded bilingual PT/EN policy disclaimer baked into every generated insurance PDF. **Out of CMS scope** — flagged as a follow-up lang-key extraction, not part of these PRs. |

Searched and clean: no CNPD/GDPR/ODR references outside `lang/*/legal.php`; no currency or country defaults in the public create forms; no legal links in email templates; `lang/en/individual.php:50-55` consent keys are already localized and jurisdiction-neutral.

## Design summary

- **Storage:** one table `legal_pages`; each row is one immutable published version (or a draft). Columns: `id`, `type` (string enum: `terms-of-service` | `privacy-policy` | `data-sharing-policy`), `locale`, `version` (int), `title`, `body` (MEDIUMTEXT, sanitized HTML), `effective_date` (date), `published_at` (nullable datetime; null = draft), `created_by` (FK users, nullable), timestamps. Unique index `(type, locale, version)`; index `(type, locale, published_at)`. "Current" = highest-version row with `published_at <= now()` per `(type, locale)`.

- **Content format: HTML, authored in TinyMCE, sanitized on write.** This is the consequence of choosing a WYSIWYG, and it moves XSS from "free" to "load-bearing" — TinyMCE's toolbar includes the `code` plugin, so an admin can paste arbitrary HTML. Defence in depth:
  1. **Sanitize on save** using `ueberdosis/tiptap-php`: parse the submitted HTML, keep only an explicit allow-list of nodes (paragraph, heading 2–4, bold, italic, bullet/ordered list, list item, link, blockquote, horizontal rule) and attributes (`href` on links only), re-render. `<script>`, `<style>`, `<iframe>`, `on*` handlers and `style` attributes are dropped structurally rather than regex-stripped.
  2. **Force safe links:** rewrite `href` values that are not `http`, `https` or `mailto` (kills `javascript:`), add `rel="noopener noreferrer"` to external links.
  3. **Store sanitized output only.** The raw submission is never persisted, so the public page renders trusted HTML with `{!! !!}`.
  4. Trim the TinyMCE toolbar on this screen to match the allow-list, so the editor cannot produce markup the sanitizer will silently discard (that mismatch is the main UX trap).

  This is a real trade-off against markdown, which was safe by construction; an allow-list has to be *correct*, hence its own test file.

- **Placeholders:** `{{federation_name}}`, `{{federation_short_name}}`, `{{portal_name}}`, `{{federation_address}}`, `{{federation_email}}`, `{{support_email}}`, `{{federation_phone}}`, `{{portal_url}}`, `{{effective_date}}`, **[R]** `{{international_federation_name}}`, `{{international_federation_short_name}}` (from `config('branding.international')` — without these the ported data-sharing and international-transfers clauses would regress installs that already configure `INTERNATIONAL_FEDERATION_NAME`). Resolved via `strtr()` at **render** time (not save time, so rebranding updates existing documents), *after* sanitization. Values come from `SiteSetting` overrides first, falling back to `config('branding.*')`, each passed through `e()` before substitution.

- **Locale fallback chain** (public page): requested locale → `config('app.fallback_locale')` (`en`) → any locale with a published version (deterministic order: `en`, `pt_PT`, rest alphabetically) → shipped default HTML stub on disk. Nothing ever 404s. **[R]** The disk stub must be passed through `LegalPageRenderer` too, or an unseeded install renders literal `{{federation_name}}` tokens.

- **Defaults:** jurisdiction-neutral HTML stubs at `database/seeders/data/legal/{locale}/{type}.html`, seeded by `LegalPageSeeder`. Idempotent: inserts version 1 only where no row exists for `(type, locale)` — never overwrites admin edits. Stubs drop CNPD, all GDPR article cites, the EEA transfers framing, the EU ODR link, the named DPO department and the NIF acronym, replacing them with neutral wording ("the supervisory authority competent in the jurisdiction where {{federation_name}} operates"). Supervisory authority / applicable law / dispute resolution stay **plain editable body text** — no structured fields — so an admin can write LGPD/ANPD or GDPR/CNPD as needed.

## Phase 1 — Model, migration, seeder, public rendering (PR 1)

**New:**

- `database/migrations/2026_08_xx_create_legal_pages_table.php`
- `app/Models/LegalPage.php` — type constants, `scopePublished`, static `current(string $type, string $locale): ?self` implementing the fallback chain, `nextVersion()`; cache per `(type, locale)` with forget-on-save, mirroring `SiteSetting`'s `rememberForever` + `flushCache` (`app/Models/SiteSetting.php:24,39-44`).
- `app/Services/LegalHtmlSanitizer.php` — tiptap-php allow-list parse/re-render + link scheme filtering.
- `app/Services/LegalPageRenderer.php` — placeholder resolution over sanitized HTML.
- `database/seeders/LegalPageSeeder.php` + `database/seeders/data/legal/{en,pt_PT,pt_BR,es,fr,de}/{terms-of-service,privacy-policy,data-sharing-policy}.html` — 18 files, ported and neutralised per the table above. **[U] For fresh installs only** — existing installs get their content from the import migration below.
- **[U] `database/migrations/2026_08_xx_import_legacy_legal_content.php` — the migration that makes this upgrade safe.** For each `(type, locale)` with no existing row, it sets the locale, reads the install's live `legal.*` translations via `Lang::get()`, and assembles version 1 of the HTML body mirroring the current blade structure (section order, headings, list items). Because `Lang::get()` resolves against `lang/{locale}/legal.php` **on that install's disk**, a federation that customized its legal text has that exact text imported — no manual step, no data loss. Published immediately (`published_at` = now, `effective_date` = today) so it is what the public pages serve. Guards: skip any `(type, locale)` that already has a row (idempotent, and safe in either order against `LegalPageSeeder`); fall back to the shipped neutral stub when `Lang::has('legal.privacy_policy_title', $locale)` is false (fresh install, or files already removed).
- `resources/views/web/public/legal/show.blade.php` — generic view: brand logo, `$page->title`, "Last update: {effective_date}" (kills the `01/02/2026` literal), body in a `prose` wrapper (`@tailwindcss/typography` already installed).

**Modified:**

- `app/Http/Controllers/LegalController.php` — three methods become thin wrappers over a shared `show($type)`; disk-stub fallback when the table is empty.
- `database/seeders/FreshInstallSeeder.php`, `database/seeders/DatabaseSeeder.php` — add `LegalPageSeeder`. **[R]** These are alternative entry points, not chained, so both need it; the seeder's idempotency covers accidental double-runs.
- **[U] `database/seeders/FreshInstallSeeder.php` — also add the missing `MenuSeeder` call**, after `CommitteeSeeder` (whose data `MenuSeeder` resolves committee ids against) and before `UserSeeder`, matching `DatabaseSeeder`'s ordering. `DatabaseSeeder:31-33` carries the comment *"Without this a fresh install renders no sidebar at all"* — but the seeder actually named for fresh installs never got the call. Low severity in practice (the documented install path is `php artisan migrate --seed` → `DatabaseSeeder`, which is correct; `FreshInstallSeeder` is undocumented and referenced only by `tests/Feature/Console/AdminAccessCommandsTest.php`), but it is one line, purely additive, and stops the two entry points drifting further apart. Fixing it also means the Legal Pages entry actually appears for anyone who does use that path.
- **[R]** `resources/views/web/welcome.blade.php:180-182` — replace Portuguese footer literals with lang keys in all six locales, add the data-sharing link.
- **[R]** `resources/views/web/public/entity/create.blade.php:394-415` — replace the hardcoded-Portuguese consent copy with the existing `entity.*` keys.
- **[R]** `config/app.php:85` — `'locale' => env('APP_LOCALE', 'pt_PT')`.
- **[R]** `resources/views/components/public-layout.blade.php:2` — emit the active locale in `<html lang>`.

**[U] Deleted: nothing.** All deletions move to Release N+1 (PR 3). `lang/*/legal.php` and the three
legal blades must survive this release as the import migration's source.

**Verify:** `php artisan migrate && php artisan db:seed --class=LegalPageSeeder`; hit the three routes in `pt_PT` and `fr`; truncate the table and confirm the disk fallback renders *with tokens resolved*; grep rendered output for `CNPD`, `Art. 6`, the ODR URL and `NIF` and confirm all absent; load the public entity registration form in `en` and confirm the consent copy is English.

**[U] Upgrade rehearsal (before merging):** restore a dump of a *live-shaped* install, hand-edit one
`lang/pt_PT/legal.php` string to simulate a federation's customization, run **only**
`php artisan migrate` (no seeders), then confirm `/privacy-policy` serves that customized text — not
the neutral stub — and that the admin grid shows 18 populated cells. Re-run `migrate` and confirm
nothing changes.

## Phase 2 — Admin CRUD + menu (PR 2)

**New:**

- `app/Http/Controllers/Admin/LegalPageController.php` — `index` (grid of 3 types × 6 locales with current version / effective date / draft badge), `edit` (loads draft, else clones current into a draft), `update` (save draft), `publish` (sanitize, stamp `published_at`, bump version, set `effective_date`), `history`, `showVersion`.
- `app/Http/Requests/LegalPageRequest.php` — validate type/locale against whitelists, `title` required, `body` required string, `effective_date` date; run the sanitizer in `passedValidation` so only clean HTML reaches the model.
- `resources/views/web/admin/legal-pages/{index,edit,history}.blade.php` — follow `homepage-settings/index.blade.php` styling. Edit view embeds `<x-forms.tinymce-editor-static name="body" :value="$page->body" />` plus a placeholder-token cheat-sheet.
- Lang keys: `admin.legal_pages_*` in `lang/*/admin.php`, and **[R]** `menu.admin.legal_pages` (**not** `legal_documents` — that key is taken by official documents at `lang/en/menu.php:60`).

**Modified:**

- `routes/admin.php` — new block inside the existing `permission:access settings` group, yielding `admin.legal-pages.*`.
- `config/menu.php` — entry in the admin Settings section (~line 296) with `'route' => ['admin.legal-pages.index']`, `'can' => 'access settings'`, **`'permissions' => ['access settings']`** (see below), and `legal-pages` added to that section's `active` array. **The route must land in the same PR** — `MenuSeeder:76` route-validates and silently skips missing routes.
- **[R] `database/seeders/MenuSeeder.php`** — honour an optional `permissions` config key instead of hardcoding `'permissions' => []` (`MenuSeeder:100`). Backward-compatible by construction: no existing `config/menu.php` entry declares `permissions`, so every current item keeps `[]` and nothing changes for existing installs.

### [R] Menu delivery: additive, no re-seed

**The original "re-run `MenuSeeder`" instruction is withdrawn.** `MenuSeeder:47` deletes every
`MenuItem` for a menu and rebuilds from config, so telling installs to re-seed would discard any
customization made through the dynamic-menu admin. A new legal-pages entry is not a good enough
reason to break an existing install's navigation. Instead the entry arrives by two independent,
non-overlapping paths:

1. **Fresh installs** — the `config/menu.php` entry, seeded normally by `MenuSeeder`. No change to how that works.
2. **Existing installs** — a dedicated idempotent **data migration** in PR 2, applied by the ordinary `php artisan migrate` step of a deploy. No seeder run, nothing deleted.

The migration must:

- **No-op when the admin menu is absent or empty** (fresh installs migrate before seeding — `MenuSeeder` will handle those).
- **No-op when an item with `route_name = 'admin.legal-pages.index'` already exists**, so it is safe to re-run and cannot double-insert on an install that got the entry from a fresh seed.
- Attach under the existing admin Settings parent (`name = 'menu.admin.settings'`, `parent_id` null, in the `admin` menu) with `order` = current max sibling + 1. **If that parent cannot be found** — the install renamed or removed it via the dynamic-menu admin — append at root level of the admin menu rather than failing the migration. A deploy must never break because someone customized their sidebar.
- Set `permissions => ['access settings']` and `visible => true`. Leave `menu_group_id` NULL — safe even on installs using menu groups, because `MenuItem::scopeInGroup()` (`app/Models/MenuItem.php:126-138`) explicitly includes ungrouped items via `orWhereNull('menu_group_id')`.
- **[U] Invalidate the menu cache before finishing** — call `app(MenuBuilderService::class)->clearAllMenuCache()` (or the existing `menu:cache-clear` command, `app/Console/Commands/MenuCacheClearCommand.php`).
- Provide a `down()` that deletes the item by `route_name`.

**[U] Why the cache invalidation is not optional.** `MenuBuilderService::build()` caches each menu for
**one hour** (`CACHE_TTL = 3600`, `app/Services/MenuBuilderService.php:14`), keyed per menu *and per
user* (`buildForUser()` injects `user_id` into the cache key). A migration that inserts the row
straight into the database does not touch that cache, so without an explicit flush the new entry
would be missing from the sidebar for up to an hour after deploy — per admin.

The danger is what an admin does next. The sidebar looks unchanged, so the natural move is to reach
for the documented menu-rebuild command — and `docs/guides/configuring-committees.md:161` and
`docs/guides/navigation-and-menus.md:80` both tell them to run
`php artisan db:seed --class=MenuSeeder`. **That wipes and rebuilds every menu item, destroying
exactly the customizations this whole design protects.** A missing cache flush therefore doesn't just
delay the feature; it walks the admin into the one destructive action we engineered the release
sequence to avoid. Flush the cache in the migration, and say in the release notes that no menu
re-seed is needed.

### [R] The menu entry must carry its own permission

`MenuSeeder:100` hardcodes `'permissions' => []` for every config item, and
`MenuBuilderService::userCanAccessItem()` (`app/Services/MenuBuilderService.php:282-316`) returns
`true` when both `permissions` and `selected_roles` are empty. So an item seeded the ordinary way
is **visible to every user regardless of the config `can` gate** — the `can` key in `config/menu.php`
is not consulted at seed time at all.

Left alone, that would put "Legal Pages" in every member's sidebar, leading them to a 403 (the route
itself is correctly gated by `permission:access settings`). Hence the `permissions` config key added
to `MenuSeeder` above, set on both delivery paths.

*Note this is a pre-existing, general issue — many current sidebar items are similarly ungated in the
DB and rely on the route's own middleware. Fixing that broadly is out of scope here; the change above
is deliberately narrow and additive so it cannot alter any existing item's visibility.*

**Verify:** Settings → Legal Pages; edit the `en` privacy policy, publish, confirm the public page updates and history shows v1 + v2; confirm a user without `access settings` gets 403 **and does not see the sidebar entry at all**; paste `<script>alert(1)</script>` and an `onclick` via the TinyMCE code view, save, confirm both are absent from the stored row and the public page; confirm only one "Legal…" section per label in the sidebar.

**[R] Upgrade rehearsal (do this before merging PR 2):** take a dump of a *customized* menu — reorder an item and rename another through the dynamic-menu admin — then run `php artisan migrate` and confirm (a) the Legal Pages entry appears, (b) every customization survives byte-for-byte, (c) running `migrate` again changes nothing.

## [U] Phase 3 — Cleanup (PR 3, Release N+1)

Only after Release N has been deployed and every install's content is in the database.

**Deleted:**

- `resources/views/web/public/legal/{terms-of-service,privacy-policy,data-sharing-policy}.blade.php` and `lang/{en,pt_PT,pt_BR,es,fr,de}/legal.php`. **[U] Audit confirmed these orphan nothing:** an exhaustive repo grep found the three legal blades to be the *only* consumers of `legal.*` translation keys — no PDF template, mail template, Livewire component, or plugin under `app/Plugins/` references them. (The other `legal` hits are `legal_name` model fields and an unrelated Spanish phrase in `lang/es/documents.php:9`.)
- `resources/views/terms.blade.php`, `resources/views/policy.blade.php`, `resources/markdown/terms.md`, `resources/markdown/policy.md`, and the `auth/register.blade.php:36-51` block — closing the Jetstream fork.
- **[U] Do NOT remove** the `"Terms of Service"` / `"Privacy Policy"` JSON keys from `lang/*.json` until that register block is gone in the same PR — `register.blade.php:44-45` still references them.

**[U] Jetstream fork safety.** `terms.show` / `policy.show` are registered *conditionally* — `vendor/laravel/jetstream/routes/livewire.php:14-16` wraps them in `Jetstream::hasTermsAndPrivacyPolicyFeature()`, and the feature is off (`config/jetstream.php:60`). So stock installs are unaffected. But a fork that enabled it would 500, because Jetstream's controller does `file_get_contents(resource_path('markdown/terms.md'))`. Mitigation: register app-level `terms.show` / `policy.show` named routes redirecting to the new pages, so any forked blade calling `route('terms.show')` keeps resolving. Plus a release note.

**Release notes for N+1:**

- Forks that enabled `Features::termsAndPrivacyPolicy()` should remove it; the CMS pages replace it.
- **The pt_PT consent wording changes** on the public entity form — from "Declaro que li e concordo com os…" to the existing `entity.terms_confirm` phrasing ("Confirmo que a entidade aceita os…"). That is a substantive change to acceptance language on a live form; federations' DPOs should be told.
- Recommend `php artisan view:clear` on deploy (compiled views for deleted blades are inert, but clearing is tidy).

## Phase 4 — Acceptance tracking (PR 4, follow-up)

- Migration `create_legal_page_acceptances_table`: `id`, `user_id` (nullable — public creation can precede a user account; also nullable `individual_id`/`entity_id`), `legal_page_id` (FK → the exact version row), `accepted_at`, `ip_address`, timestamps.
- On submit of the public create forms and registration, resolve the *current* version per accepted type/locale and insert rows in the existing create actions.
- Forced re-acceptance deliberately **not** in this PR. If wanted: `EnsureLegalPagesAccepted` middleware comparing each user's latest accepted version per type against current, plus an interstitial page.

## Tests (all Pest, all suffixed `Test.php`)

- `tests/Feature/PublicLegalPagesTest.php` — pages render seeded content; fallback chain (fr→en; pt_PT-only doc is served); empty-table disk fallback **with tokens resolved**; placeholder interpolation shows the federation name and the international federation name; unpublished draft not shown; output contains none of `CNPD`, `Art. 6`, the ODR URL, `NIF`.
- `tests/Feature/LegalHtmlSanitizerTest.php` — **security-critical.** `<script>`, `<iframe>`, `<style>`, `onclick=`, `style=`, `javascript:` hrefs all removed; allow-listed markup survives intact; external links gain `rel="noopener noreferrer"`; a hostile `SiteSetting` placeholder value is escaped, not injected.
- `tests/Feature/Admin/LegalPagesAdminTest.php` — index/edit render for admin; guest redirected; user without `access settings` gets 403; update saves a draft without publishing; publish bumps version and preserves history; validation errors.
- **[R]** `tests/Feature/Admin/LegalPagesMenuUpgradeTest.php` — the additive migration: inserts the entry under Settings on an install that already has a seeded menu; **does not delete or modify any pre-existing `MenuItem`** (assert full before/after equality on the other rows); is idempotent on re-run; no-ops when the entry already exists; falls back to root level when the Settings parent is missing; the resulting item carries `permissions => ['access settings']` so `MenuBuilderService` hides it from non-admins. **[U]** Also: warm the menu cache *before* running the migration, then assert the entry is visible to an admin immediately afterwards — this is the regression test for the cache-invalidation trap above. And assert an item with a NULL `menu_group_id` still renders on an install that uses menu groups.
- `tests/Feature/LegalPageSeederTest.php` — creates all 18 type×locale rows, idempotent, does not clobber an admin-edited row on re-seed.
- **[R]** `tests/Feature/PublicRegistrationConsentCopyTest.php` — the entity and individual create forms render consent copy in the active locale (assert the English form contains no Portuguese literal), and all three legal links resolve.
- **[U]** `tests/Feature/LegalContentImportMigrationTest.php` — **the upgrade-safety test.** Simulate a live install: `legal_pages` empty, `lang/*/legal.php` present with a locally-modified string; run the migration; assert version 1 exists for all 18 `(type, locale)` pairs, that the modified string appears in the imported body (customization preserved), that the row is published and served by `/privacy-policy`, that re-running changes nothing, and that a `(type, locale)` with a pre-existing row is left untouched.
- **[U]** `tests/Feature/WelcomeFooterLocalesTest.php` — the new footer/consent lang keys resolve in **all six** locales (a missing key renders the raw key on a public homepage).
- PR 4: `tests/Feature/LegalPageAcceptanceTest.php`.

## Proposed PR split

**Release N**

1. **PR 1 — DB-backed legal pages + import migration + public rendering + sanitizer + the hardcoded-copy sweep** (Phase 1; `PublicLegalPagesTest`, `LegalHtmlSanitizerTest`, `LegalPageSeederTest`, `LegalContentImportMigrationTest`, `PublicRegistrationConsentCopyTest`, `WelcomeFooterLocalesTest`). **Deletes nothing.**
2. **PR 2 — Admin CRUD + TinyMCE editor + menu entry** (Phase 2; `Admin/LegalPagesAdminTest`, `Admin/LegalPagesMenuUpgradeTest`).

*Upgrade command for existing installs stays exactly what it is today: pull + `php artisan migrate` + `npm run build`. No seeder run, no menu re-seed, no manual step.*

**Release N+1**

3. **PR 3 — Cleanup** (Phase 3): delete the legacy lang files, blades and Jetstream surface; add the `terms.show`/`policy.show` redirects.

**Later**

4. **PR 4 — Acceptance version recording** (Phase 4). Optional 5th micro-PR for the forced re-acceptance gate.

## [BUILT] What implementation changed about this plan

PR 1 and PR 2 are implemented and verified against the real database and a browser.
Five things the plan got wrong or did not know:

1. **`passedValidation()` was the wrong hook — it left stored XSS live.** `validated()`
   returns the data the validator captured, so sanitizing afterwards left the controller
   holding the raw submission; a `<script>` payload reached the database. Must be
   `prepareForValidation()`. Caught only by the end-to-end test.
2. **tiptap-php is not a sanitizer by itself.** Its Link mark passes `javascript:` and
   `data:` hrefs through untouched, and it *decodes* `&#106;avascript:` into a working
   `javascript:` URL. The scheme allow-list is doing the real work. It also **keeps
   `<iframe>`** — confirmed visually: TinyMCE rendered an evil.test iframe inside the
   editor, and only the server pass removed it.
3. **tiptap throws on input with no `<body>`** (a bare `<meta>`/`<base>`), which would be a
   500 on save. The sanitizer now fails closed to a conservative `strip_tags` allow-list.
4. **`users.id` is `char(36)` (UUID).** `foreignId()` is rejected by MySQL — use
   `foreignUuid()`. This also applies to the acceptance table in Phase 4.
5. **Anchoring the menu item by `name = 'menu.admin.settings'` finds nothing on a real
   install.** The live admin menu holds literal Portuguese names ("Sistema", "Painel de
   Controlo"), not translation keys, and shares no settings routes with `config/menu.php`.
   The migration now anchors on a neighbouring *route* and falls back to root.

Verified in the browser: the sidebar entry appears with the right translated label under
"Sistema"; TinyMCE loads and round-trips content; publish creates v2; and a payload posted
**directly to the endpoint, bypassing the editor** (script, img/onerror, iframe, mixed-case
and entity-encoded `javascript:`, `onmouseover`, `svg onload`) is stripped server-side —
canary never fires even when click/mouseover/load/error are dispatched on every element,
while legitimate text, headings and safe links survive with `rel="noopener noreferrer"`.

Test suite: 34 new tests, full suite **2470 passed**, no regressions.

## [U] Pre-existing issues surfaced by the audit — disposition

Each was assessed on its merits rather than filed under "pre-existing, not mine".

**Fixed here** (cheap, additive, zero behaviour change for existing installs):

- **`MenuSeeder` missing from `FreshInstallSeeder`** — one line, added in Phase 1 above. See the note there for why the real-world severity is low.
- **`/terms-of-service` double-claimed** by `routes/web.php:107` and Jetstream's conditional route when `Features::termsAndPrivacyPolicy()` is enabled (last registration wins). Only affects forks that turned it on, and Phase 3's `terms.show`/`policy.show` redirects plus the release note resolve it.

**Deliberately NOT fixed here — and the reason is not "out of scope":**

- **The `can` key in `config/menu.php` is inert.** `MenuSeeder` writes `permissions => []` for every item, and `MenuBuilderService:282-316` treats empty `permissions` + empty `selected_roles` as "visible to everyone". So a number of existing sidebar items are ungated in the database and rely on the route's own middleware to bounce unauthorized users *after* they click. Honouring `can` globally is probably the correct behaviour — but note **the fix cannot be delivered to existing installs without the exact destructive re-seed this plan just spent a release avoiding.** Changing `MenuSeeder` only affects the database when `MenuSeeder` runs, and running it wipes and rebuilds every menu item, discarding admin customizations. So a real fix needs either a non-destructive backfill migration that sets `permissions` on existing rows by matching them against config, or an accepted re-seed window — plus an audit of which items would change visibility for which roles, since misconfigured permissions would hide navigation users currently depend on. That is a deliberate piece of work with its own blast radius, not a rider on a legal-pages PR.

  This plan's opt-in `permissions` config key is the narrow slice that is safe today: no existing entry declares it, so nothing existing changes, and the new entry is correctly gated.

## Follow-ups explicitly out of scope

- `shared/insurance/document-pdf.blade.php:629-630` — hardcoded bilingual PT/EN policy disclaimer in generated PDFs; needs lang-key extraction in a separate sweep.
- `x-public-layout` has no language switcher (it lives in the authenticated header, `components/layout/header.blade.php:155`), so an anonymous reader on a legal page cannot change language in place. Worth a small follow-up.
