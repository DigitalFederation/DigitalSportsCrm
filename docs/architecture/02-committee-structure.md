---
title: Committee Structure
description: Committee types, internationality flags, and federation access control
---

# Committee Structure and Federation Access Control

> **Last Updated:** June 2026 — added actor terminology and committee-access configuration guide (model introduced January 2026, commit 1730743)
> **Status:** Canonical reference for committee-based access control

## Overview

Certifications and licenses in the federation portal are organized by **committees**. The `is_international` flag lives **exclusively on the `committee` table** - not on individual certifications or licenses.

This architecture was implemented to:
1. Centralize internationality logic in one place
2. Enable proper federation-based access control via `federation_committee` pivot
3. Support both International and national diving services

## Actor & Federation Terminology

This portal is federation-agnostic. The access tables below are illustrated with a
deployment modeled on an international diving-federation deployment,
but access rules depend on the **user group** and **federation flags** — never on names.
Map the generic roles to the underlying model as follows:

| Generic role | What it is in the model | What it sees |
|--------------|-------------------------|--------------|
| **Admin** | User in the `ADMIN` group | Everything — bypasses the committee/federation access filters |
| **Main Federation** | `Federation` with `is_default_federation = true` (one per deployment) | All records; linked to every committee. Owns all certifications |
| **Association** | `Federation` with `is_local = true` and `parent_id` → Main Federation | The Main Federation's records, **filtered to the committees the Association is linked to** |
| **Entity** | Organization in the `ENTITY` group (e.g. a club or operator) | Its own members/records |
| **Individual** | End member in the `INDIVIDUAL` group | Its own records |

> **Main Federation and Association are both in the `FEDERATION` user group.** They are
> distinguished only by the `is_default_federation` / `is_local` / `parent_id` flags — not by
> a separate role.

> **National vs. international Associations are not separate types.** An Association is
> "national-content" when it is linked to `SPORT` / `DIVINGSERVICES`, and
> "international-content" when it is linked to `DIVING` / `SCIENTIFIC`. This is purely a matter
> of which committees it is linked to. A deployment's "international" association (e.g. the body
> that manages international content) is just an Association linked to the international committees — it
> is **not** the Admin.

## Committees Reference Table

| Code | Name | `is_international` | Description |
|------|------|-------------------|-------------|
| `SPORT` | Sport Committee | `false` | National underwater sports (swimming, finswimming, etc.) |
| `DIVINGSERVICES` | Diving Services Committee | `false` | National diving services (third-party diving operations) |
| `DIVING` | International Diving Committee | `true` | International diving certifications/licenses |
| `SCIENTIFIC` | International Scientific Committee | `true` | International scientific diving |

## Federation Access by Type

### Main Federation (`is_default_federation = true`)

The main federation has access to ALL committees:

| Area | Committee |
|------|-----------|
| Sport | `SPORT` |
| Diving Services | `DIVINGSERVICES` |
| International Diving | `DIVING` |
| International Scientific | `SCIENTIFIC` |

### International Diving Federation (an Association linked only to international committees)

The international diving federation only manages international content:

| Area | Committee |
|------|-----------|
| International Diving | `DIVING` |
| International Scientific | `SCIENTIFIC` |

### Territorial Associations (`is_local = true`)

Local/territorial federations only manage national content:

| Area | Committee |
|------|-----------|
| Sport | `SPORT` |
| Diving Services | `DIVINGSERVICES` |

> **The committee split is exhaustive and mutually exclusive.** The national set
> (`SPORT` + `DIVINGSERVICES`) and the international set (`DIVING` + `SCIENTIFIC`) together
> cover all four committees — i.e. every certification. No single Association sees all of them;
> only **Admin** and the **Main Federation** do.

## Configuring an Association's Committee Access

An Association sees a certification or license **only when both** conditions hold:

1. **Hierarchy** — the record belongs to the Association's own, child, or parent (Main)
   federation. Because all certifications are owned by the Main Federation, this requires the
   Association's `parent_id` to point at the Main Federation.
2. **Committee link** — the Association is linked, in the `federation_committee` pivot, to the
   committee that owns the record.

> **Hard rule:** an Association with **no** committee links sees **nothing**. The query
> short-circuits (`whereRaw('1 = 0')`) when the allowed-committee list is empty.

### How to assign committees

```php
// Admin UI: app/Livewire/Admin/FederationCommitteeManager.php
// — assign/unassign committees per federation from the admin screen.

// Programmatically (seeder or tinker):
$association->committees()->syncWithoutDetaching([$divingId, $scientificId]);
```

### How to verify

```php
$association->parent_id;                    // should equal the Main Federation id
$association->committees()->pluck('code');  // committees it can see, e.g. ['DIVING','SCIENTIFIC']
```

### Symptom → cause

If a committee tab (e.g. `?filter[committee]=diving`) is **empty** for an Association that
should see that content, it is almost always **missing the `federation_committee` link** for
those committees — or its `parent_id` is not the Main Federation. This is a
data/configuration issue, not a code bug. Add the committee link (and confirm the parent) and
the records appear.

> Committee membership is stored in the `federation_committee` pivot. A fresh install seeds
> no federations, so each deployment links its own — typically the international-content
> federation (matched by `config('branding.international.name')` / env
> `INTERNATIONAL_FEDERATION_NAME`) to the international committees. Establish the links through
> the admin UI (or `syncWithoutDetaching`); an Association with no links for a committee will
> show empty tabs for it.

## How Visibility Is Resolved

The federation-facing certification and license lists combine **two filters**. Using the
certification list (`app/Http/Controllers/Federation/CertificationAttributedController.php`) as
the reference — the license controller mirrors it:

1. **Federation set.** For a non-main federation (an Association), results are restricted to
   `federation_id IN { own, child federations, parent (Main) }`. Because every certification is
   owned by the Main Federation, the **parent** entry is what lets an Association see them. The
   **Main Federation** (`is_default_federation = true`) skips this restriction and sees all.
2. **Committee set.** Results are further restricted to records whose committee is among the
   Association's linked committees (`federation_committee`). If that set is empty, the query
   short-circuits to `whereRaw('1 = 0')` — the Association sees nothing.

Effective query for an Association (simplified):

```php
CertificationAttributed::query()
    ->whereIn('federation_id', [$ownId, ...$childIds, $parentMainId])
    ->whereHas('certification', fn ($q) =>
        $q->whereIn('committee_id', $association->committees()->pluck('committee.id'))
    );
// The UI committee tab (e.g. ?filter[committee]=diving) narrows further to DIVING + SCIENTIFIC.
```

> **Scope coverage — important.** `ExcludeInternationalScope` is a **global scope on `License`
> and `LicenseAttributed` only** — *not* on `Certification` / `CertificationAttributed`.
> Certification visibility is therefore enforced **explicitly in the controllers** (the two
> filters above), not by a global scope, so certifications are **not** auto-filtered by
> internationality — they are gated purely by committee links. `ExcludeInternationalScope` also
> skips `ADMIN` users entirely.

## Code Examples

### Checking if a License/Certification is International

```php
// CORRECT - Use committee's is_international flag
$isInternational = $license->committee->is_international;
$isInternational = $license->committee->isInternational();
$isInternational = $license->isInternationalLicense();

// CORRECT - For certifications
$isInternational = $certification->committee->is_international;
$isInternational = $certification->isInternationalCertification();
```

### Filtering for Diving Licenses (Both Types)

```php
// CORRECT - Include both DIVING and DIVINGSERVICES
$isDivingLicense = $license->committee
    && in_array($license->committee->code, ['DIVING', 'DIVINGSERVICES']);

// In queries
->whereHas('license.committee', function ($q) {
    $q->whereIn('code', ['DIVING', 'DIVINGSERVICES']);
});
```

### Filtering by Internationality

```php
// International only (international content)
->whereHas('license.committee', fn ($q) => $q->where('is_international', true));

// National only (national content)
->whereHas('license.committee', fn ($q) => $q->where('is_international', false));

// Using scopes
Committee::international()->get(); // DIVING, SCIENTIFIC
Committee::national()->get();      // SPORT, DIVINGSERVICES
```

### Federation Committee Access Control

```php
// Check if federation can manage a committee
$federation->canManageCommittee($committee);
$federation->committees()->where('code', 'DIVING')->exists();

// Get federation's allowed committees
$federation->committees; // Collection of Committee models
```

## Key Files

| File | Purpose |
|------|---------|
| `app/Models/Committee.php` | Committee model with `isInternational()` helper and scopes |
| `src/Domain/Federations/Models/Federation.php` | Federation model with `canManageCommittee()` method |
| `app/Http/Middleware/EnsureFederationCanManageCommittee.php` | Middleware for committee-based authorization |
| `src/Domain/Licenses/Scopes/ExcludeInternationalScope.php` | Global scope that filters by `committee.is_international` |
| `database/seeders/CommitteeSeeder.php` | Seeds the committees (SPORT, DIVING, DIVINGSERVICES, SCIENTIFIC) and their `is_international` flags |

## Database Schema

### `committee` Table

```sql
CREATE TABLE committee (
    id BIGINT PRIMARY KEY,
    code VARCHAR(255) UNIQUE,      -- 'SPORT', 'DIVING', 'DIVINGSERVICES', 'SCIENTIFIC'
    name VARCHAR(255),
    is_international BOOLEAN       -- true for DIVING, SCIENTIFIC; false for SPORT, DIVINGSERVICES
);
```

### `federation_committee` Pivot Table

```sql
CREATE TABLE federation_committee (
    federation_id BIGINT REFERENCES federation(id),
    committee_id BIGINT REFERENCES committee(id),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    PRIMARY KEY (federation_id, committee_id)
);
```

## Technical Director Approval Flow

For **entity diving licenses** (both `DIVING` and `DIVINGSERVICES` committees), the purchase flow includes Technical Director (TD) approval:

```
Entity purchases diving license
    ↓
State: PendingTechnicalDirectorApprovalLicenseAttributedState
    ↓
TD approves
    ↓
State: PendingValidationLicenseAttributedState (Pending Federation)
    ↓
Federation validates
    ↓
State: ActiveLicenseAttributedState
```

See `src/Domain/Licenses/Actions/PurchaseLicenseAction.php` for implementation.

## Common Mistakes to Avoid

1. **Never check `is_international` on license or certification directly** - it no longer exists there
2. **Never filter diving licenses by only `'DIVING'`** - always include `['DIVING', 'DIVINGSERVICES']`
3. **Don't assume committee access** - always verify via `federation_committee` pivot or middleware
