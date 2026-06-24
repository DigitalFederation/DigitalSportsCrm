---
title: Federation Membership Rules
description: Critical business rules for local vs sport/discipline federation membership
---

# Federation Membership Rules

> **IMPORTANT**: This document defines critical business rules for federation membership. These rules MUST be followed in all code changes. Any deviation requires explicit PM approval.

## Table of Contents

1. [Terminology](#terminology)
2. [Federation Hierarchy](#federation-hierarchy)
3. [Rule 1: Entity Join - Local Federation Only](#rule-1-entity-join---local-federation-only)
4. [Rule 2: License Activation - Discipline Federation](#rule-2-license-activation---discipline-federation)
5. [Rule 3: Event Local Federation Requirement](#rule-3-event-local-federation-requirement)
6. [Rule 4: Main Federation Cannot Be Removed](#rule-4-main-federation-cannot-be-removed)
7. [Rule 5: Affiliation Activation - Direct Federation Membership](#rule-5-affiliation-activation---direct-federation-membership)
8. [Database Structure](#database-structure)
8. [Key Files](#key-files)
9. [Examples](#examples)

---

## Terminology

> The platform is a generic federation-management system. "Discipline federation" below
> refers to a sport/discipline-specific association; the database fields are the actual
> code identifiers.

| Term | Database Field | Description |
|------|----------------|-------------|
| **Main Federation** | `is_default_federation = true` | The main national federation. All individuals are members. |
| **Local Federation** | `is_local = true` | Regional/territorial associations (based on geography). |
| **Discipline Federation** | `is_local = false` (and not main) | Sport/discipline-specific associations (based on sport/activity). |

### Visual Hierarchy

```
Primary Federation (Main Federation - is_default_federation = true)
├── Local Federations (is_local = true)
│   ├── Example Local Region North
│   ├── Example Local Region Center
│   ├── Example Local Region South
│   ├── Example Autonomous Region A
│   └── Example Autonomous Region B
│
└── Discipline Federations (is_local = false)
    ├── Example Diving Federation
    ├── Example Sport Federation
    └── Example Underwater Activities Federation
```

---

## Federation Hierarchy

### Database Identification

```php
// Main Federation
Federation::where('is_default_federation', true)->first();

// Local Federations (Territorial Associations)
Federation::where('is_local', true)->get();

// Discipline Federations (Sport Associations)
Federation::where('is_local', false)
    ->where('is_default_federation', false)
    ->get();
```

---

## Rule 1: Entity Join - Local Federation Only

### Business Rule

> **When an individual becomes a member of an entity (club), they are ONLY synced to the LOCAL federations (territorial associations) that the entity belongs to. They are NEVER automatically synced to discipline federations.**

### Rationale

- An individual joining an example club should become a member of "Example Local Region" (territorial), NOT the Example Diving Federation (discipline)
- Discipline membership is earned through licenses, not entity membership
- An individual can be an athlete in one sport at Club A and another sport at Club B

### Implementation

**File:** `src/Domain/Individuals/Actions/SyncIndividualLocalFederationsAction.php`

```php
// CRITICAL: Only sync to LOCAL federations
$entityLocalFederations = $entity->entityFederations()
    ->where('status_class', ActiveEntityFederationState::class)
    ->whereHas('federation', fn ($q) => $q->where('is_local', true))  // <-- MUST filter by is_local
    ->with('federation')
    ->get();
```

### What NOT To Do

```php
// WRONG - This would sync to ALL federations including discipline
$entityFederations = $entity->entityFederations()
    ->where('status_class', ActiveEntityFederationState::class)
    ->get();
```

---

## Rule 2: License Activation - Discipline Federation

### Business Rule

> **When a sport license is activated for an individual, THAT is the trigger for the individual to become a member of ALL discipline federations linked to that license.**

### Rationale

- Sport licenses can be tied to MULTIPLE federations via the `federation_licenses` pivot table
- An individual earns discipline membership by having an active license in that sport
- Only federations where `is_local = false` (discipline federations) trigger membership sync
- Local federations linked to a license are NOT synced (those come from entity membership)

### Database Structure

**Pivot Table:** `federation_licenses`

| Column | Type | Description |
|--------|------|-------------|
| `federation_id` | bigint | Federation FK |
| `license_id` | bigint | License FK |

**License Model Relationship:**
```php
public function federations(): BelongsToMany
{
    return $this->belongsToMany(Federation::class, 'federation_licenses')
        ->withTimestamps();
}
```

### Implementation

**File:** `src/Domain/Licenses/Actions/ActivateLicenseAttributedAction.php`

```php
// When activating an individual license, sync to ALL discipline federations
if ($individual && $license->license) {
    $disciplineFederations = $license->license->federations()
        ->where('is_local', false)  // Only discipline federations
        ->get();

    foreach ($disciplineFederations as $federation) {
        $this->syncIndividualToLicenseFederation($individual, $federation->id);
    }
}
```

### Trigger Flow

```
Entity assigns Sport License to Individual
         ↓
License goes through approval/payment
         ↓
License is ACTIVATED (ActivateLicenseAttributedAction)
         ↓
Get ALL federations linked to license (via federation_licenses pivot)
         ↓
Filter only discipline federations (is_local = false)
         ↓
Sync individual to EACH discipline federation
         ↓
Individual is now a member of those Discipline Federations
```

---

## Rule 3: Event Local Federation Requirement

### Business Rule

> **Events can require that athletes have active membership in the SAME local federation as the entity registering them.**

### Rationale

- Ensures athletes are properly affiliated with their regional association
- Prevents entities from registering athletes who are members of different territorial zones
- Optional requirement - can be enabled per competition

### Implementation

**Database Field:** `evt_competitions.requires_local_federation_affiliation`

**File:** `src/Domain/EvtEvents/Actions/ApplyAthleteEligibilityFiltersAction.php`

```php
protected function applyLocalFederationRequirement(Builder $query, Entity $entity): void
{
    // Get entity's active local federations (territorial associations)
    $entityLocalFederationIds = $entity->entityFederations()
        ->where('status_class', ActiveEntityFederationState::class)
        ->whereHas('federation', fn ($q) => $q->where('is_local', true))
        ->pluck('federation_id')
        ->toArray();

    // Individual must have active membership in at least one of entity's local federations
    $query->whereHas('individualFederations', function ($q) use ($entityLocalFederationIds) {
        $q->whereIn('federation_id', $entityLocalFederationIds)
            ->where('status_class', ActiveIndividualFederationState::class);
    });
}
```

### UI Location

Competition form → Registration Filter Requirements → "Require Local Federation Affiliation"

---

## Rule 4: Main Federation Cannot Be Removed

### Business Rule

> **Individuals CANNOT remove their association with the Main Federation. This membership is mandatory and permanent for all individuals in the system.**

### Rationale

- The Main Federation is the national federation that governs all activities
- All individuals must be members of the Main Federation as a prerequisite for any other federation membership
- Removing Main Federation membership would break the entire membership hierarchy

### Implementation

**Backend Protection**

**File:** `app/Http/Controllers/Individual/FederationController.php`

```php
public function destroy(string $federationId): RedirectResponse
{
    $federation = Federation::findOrFail($federationId);

    // Prevent removal from main federation
    if ($federation->is_default_federation) {
        return redirect()
            ->route('individual.federation.index')
            ->with('error', __('individuals.cannot_disassociate_main_federation'));
    }

    // ... proceed with detach for other federations
}
```

**Frontend Protection**

**File:** `resources/views/web/individual/federation/index.blade.php`

```blade
@unless($federation->is_default_federation)
    <x-dynamic-table-buttons
        type="disassociate"
        method="DELETE"
        :route="route('individual.federation.delete', $federation->id)"
        :confirmText="__('individuals.confirm_disassociate_federation')"
    ></x-dynamic-table-buttons>
@endunless
```

### Database Check

```php
// Check if federation is the main federation
$isMainFederation = $federation->is_default_federation === true;
```

### Tests

**File:** `tests/Feature/Individual/FederationControllerTest.php`

- `individual can disassociate from a regular federation` - verifies normal disassociation works
- `individual cannot disassociate from the main federation` - verifies protection is enforced
- `disassociate button is not shown for main federation in view` - verifies UI protection

---

## Rule 5: Affiliation Activation - Direct Federation Membership

### Business Rule

> **When an affiliation is activated for an individual (via subscription), the individual is synced to that affiliation's federation.**

### Rationale

- Individuals can directly subscribe to federation membership via affiliation plans
- This is independent of entity membership or license activation
- The individual is explicitly paying for this federation membership
- Unlike entity-join sync (LOCAL only) or license sync (DISCIPLINE only), this syncs to ANY federation specified in the affiliation

### Trigger Flow

```
Individual subscribes to Membership Package
         ↓
Membership Package has Affiliation Plan(s) with federation_id
         ↓
MemberSubscription and Affiliation records are created
         ↓
Payment is confirmed
         ↓
ActivateMemberSubscriptionAction runs
         ↓
Affiliation is activated
         ↓
Individual is synced to affiliation's federation (creates IndividualFederation record)
         ↓
Individual is now a member of that Federation
```

### Implementation

**File:** `src/Domain/Memberships/Actions/ActivateMemberSubscriptionAction.php`

```php
// When activating an affiliation for an individual, sync to the federation
if ($affiliation->federation_id &&
    ($memberSubscription->member_type === 'individual' ||
     $memberSubscription->member_type === Individual::class)) {
    $this->syncIndividualToAffiliationFederation(
        $memberSubscription->member,
        $affiliation->federation_id
    );
}
```

### Tests

**File:** `tests/Feature/Domain/Memberships/Actions/ActivateMemberSubscriptionActionTest.php`

- `activating subscription syncs individual to affiliation federation`
- `activating subscription activates existing pending federation membership`
- `does not duplicate federation membership if already active`
- `does not sync entity subscription to individual federation table`

---

## Database Structure

### Federation Table

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `name` | varchar | Federation name |
| `is_local` | boolean | `true` = Territorial Association, `false` = Discipline or Main |
| `is_default_federation` | boolean | `true` = Main Federation |
| `parent_id` | bigint | Parent federation ID |

### Individual Federation Table

| Column | Type | Description |
|--------|------|-------------|
| `individual_id` | bigint | Individual FK |
| `federation_id` | bigint | Federation FK |
| `status_class` | varchar | State class (Active, Pending, Rejected) |
| `active` | boolean | Quick active flag |

### Entity Federation Table

| Column | Type | Description |
|--------|------|-------------|
| `entity_id` | bigint | Entity FK |
| `federation_id` | bigint | Federation FK |
| `status_class` | varchar | State class |

### Competition Table (Event Requirement)

| Column | Type | Description |
|--------|------|-------------|
| `requires_local_federation_affiliation` | boolean | Require local federation match |

---

## Key Files

### Actions

| File | Purpose |
|------|---------|
| `src/Domain/Individuals/Actions/SyncIndividualLocalFederationsAction.php` | Syncs individual to entity's LOCAL federations only |
| `src/Domain/Licenses/Actions/ActivateLicenseAttributedAction.php` | Activates license AND syncs to discipline federation |
| `src/Domain/Memberships/Actions/ActivateMemberSubscriptionAction.php` | Activates subscription AND syncs individual to affiliation's federation |
| `src/Domain/EvtEvents/Actions/ApplyAthleteEligibilityFiltersAction.php` | Filters athletes for event eligibility |
| `src/Domain/EvtEvents/Actions/GetEligibleEntityAthletesAction.php` | Gets eligible athletes for entity registration |
| `app/Http/Controllers/Individual/FederationController.php` | Individual federation management (includes main federation protection) |

### Models

| File | Purpose |
|------|---------|
| `src/Domain/Federations/Models/Federation.php` | Federation model with `is_local`, `is_default_federation` |
| `src/Domain/Individuals/Models/IndividualFederation.php` | Pivot model for individual-federation |
| `src/Domain/EvtEvents/Models/Competition.php` | Competition with `requires_local_federation_affiliation` |

### Tests

| File | Purpose |
|------|---------|
| `tests/Feature/ActivateLicenseAttributedActionTest.php` | Tests license activation federation sync |
| `tests/Feature/Domain/Memberships/Actions/ActivateMemberSubscriptionActionTest.php` | Tests subscription activation and affiliation federation sync |
| `tests/Feature/EvtEvents/GetEligibleAthletesTest.php` | Tests event eligibility with local federation filter |
| `tests/Feature/Individual/FederationControllerTest.php` | Tests main federation disassociation prevention |

---

## Examples

### Example 1: Individual Joins Entity

**Scenario:** Example Member A joins "Example Diving Club" (entity)

**Entity's Federations:**
- Primary Federation (Main) - Active
- Example Local Region (Local) - Active
- Example Diving Federation (Discipline) - Active

**Result for Example Member A:**
- Synced to: Example Local Region (Local) - Pending approval
- NOT synced to: Example Diving Federation (Discipline)
- Already has: Primary Federation (Main) - from individual creation

### Example 2: License Activation

**Scenario:** Example Diving Club assigns "Example Sport License" to Example Member A

**License Federations (via `federation_licenses` pivot):**
- Primary Federation (Main) - skipped (not discipline)
- Example Local Region (Local, `is_local = true`) - skipped (not discipline)
- Example Sport Federation (Discipline, `is_local = false`) - **SYNCED**

**Before Activation:**
- Example Member A is member of: Primary Federation, Example Local Region

**After License Activation:**
- Example Member A is member of: Primary Federation, Example Local Region, **Example Sport Federation** (newly added as Active)

### Example 3: Event Registration

**Scenario:** Competition requires local federation affiliation

**Entity:** Example Diving Club (member of Example Local Region)

**Athletes:**
- Example Member A: Member of Example Local Region (eligible)
- Example Member B: Member of Another Local Region (NOT eligible)
- Example Member C: No local federation membership (NOT eligible)

**Result:** Only Example Member A can be registered for this competition

---

## Migrations Reference

> The migration history has been squashed into `database/schema/mysql-schema.sql`; the entries
> below are historical and no longer exist as individual migration files.

| Migration (historical) | Purpose |
|-------------------------|---------|
| `fix_is_local_for_discipline_federations` | Fixed `is_local = 0` for international diving and other discipline federations |
| `add_requires_local_federation_affiliation_to_evt_competitions` | Added event local federation requirement |
