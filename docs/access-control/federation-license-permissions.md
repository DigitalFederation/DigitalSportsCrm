# Federation License Permissions System

> **Status: implemented and shipped.** This document describes the federation license
> permission system as it exists in the codebase. The `federation_licenses` table,
> `License::federations()` / `Federation::licenses()` relationships,
> `GetAllowedEntityLicensesAction`, and the `FederationLicenseManager` admin component are
> all present. Code blocks below illustrate the design; the exact production source is the
> source of truth.

## Overview

This permission system allows federations to control which specific licenses their member entities can request. It addresses the prior limitation where entities could request any license matching their committee type, regardless of their federation's actual offerings.

## Problem Statement

Previously, entities could request any license that matched their committee type (e.g., 'sport', 'diving'). This created issues where:
- Entities from one federation could potentially request licenses they were never meant to offer
- There was no way to limit which specific licenses a federation can offer
- The system relied only on committee-level permissions, which is too broad

## Solution Architecture

### Database Schema

#### New Table: `federation_licenses`
```sql
CREATE TABLE federation_licenses (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    federation_id BIGINT UNSIGNED NOT NULL,
    license_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (federation_id) REFERENCES federation(id) ON DELETE CASCADE,
    FOREIGN KEY (license_id) REFERENCES license(id) ON DELETE CASCADE,
    UNIQUE KEY unique_federation_license (federation_id, license_id),
    INDEX idx_federation_id (federation_id),
    INDEX idx_license_id (license_id)
);
```

### Model Updates

#### Federation Model (`src/Domain/Federations/Models/Federation.php`)
```php
public function licenses(): BelongsToMany
{
    return $this->belongsToMany(License::class, 'federation_licenses')
        ->withTimestamps();
}

public function hasLicense(License $license): bool
{
    return $this->licenses()->where('license_id', $license->id)->exists();
}

public function availableLicensesForEntities(): Collection
{
    return $this->licenses()
        ->hasLicenseType('entity')
        ->get();
}
```

#### License Model (`src/Domain/Licenses/Models/License.php`)
```php
public function federations(): BelongsToMany
{
    return $this->belongsToMany(Federation::class, 'federation_licenses')
        ->withTimestamps();
}

public function scopeForFederationEntities(Builder $query, Collection $federationIds): Builder
{
    return $query->whereHas('federations', function ($q) use ($federationIds) {
        $q->whereIn('federation_id', $federationIds);
    });
}
```

### Action Updates

#### GetAllowedEntityLicensesAction
```php
<?php

namespace Domain\Licenses\Actions;

use Domain\Entities\Models\Entity;
use Domain\Licenses\Models\License;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class GetAllowedEntityLicensesAction
{
    public function __invoke(string $type, Entity $entity)
    {
        $licensesCacheKey = "licenses_for_type_{$type}_entity_{$entity->id}";

        // TTL is 5 (an integer), which Laravel's Cache::remember() interprets as 5 SECONDS.
        $licenses = Cache::remember($licensesCacheKey, 5, function () use ($type, $entity) {
            // Get all federations the entity belongs to
            $federationIds = $entity->federations()
                ->where('entity_federation.status_class', 'Domain\Entities\States\ActiveEntityFederationState')
                ->pluck('federation_id');

            return License::query()
                ->hasCommitteeCode($type)
                ->hasLicenseType('entity')
                ->forFederationEntities($federationIds) // New scope
                ->whereDoesntHave('licensesAttributed', function (Builder $query) use ($entity) {
                    $query->where(['model_type' => 'entity', 'model_id' => $entity->id]);
                })
                ->orderBy('name')
                ->get();
        });

        return $licenses;
    }
}
```

### Component Updates

#### EntityLicenseRequestSelector (`app/Livewire/Entity/EntityLicenseRequestSelector.php`)
No changes needed - it already uses `GetAllowedEntityLicensesAction`

#### LicensePurchaseForm (`app/Livewire/Entity/LicensePurchaseForm.php`)
Its `loadLicenses()` method applies the federation filtering:

```php
public function loadLicenses($forceFetch = false)
{
    // ... existing code ...

    $query = License::query()
        ->whereHas('committee', function (Builder $query) {
            $query->where('code', $this->type);
        })
        ->hasLicenseType('entity');

    // Federation filtering
    $federationIds = $this->entity->federations()
        ->where('entity_federation.status_class', 'Domain\Entities\States\ActiveEntityFederationState')
        ->pluck('federation_id');
    
    $query->forFederationEntities($federationIds);

    // ... rest of existing filters ...
}
```

### Controller Updates

#### LicensePurchaseController (`app/Http/Controllers/Entity/LicensePurchaseController.php`)
The store method's license validation applies the same federation check:

```php
// In the store() method
$federationIds = $entity->federations()
    ->where('entity_federation.status_class', 'Domain\Entities\States\ActiveEntityFederationState')
    ->pluck('federation_id');

$license = License::query()
    ->where('active', true)
    ->hasCommitteeCode($committee)
    ->hasLicenseType('entity')
    ->forFederationEntities($federationIds) // Add federation check
    ->find($licenseId);

if (!$license) {
    return redirect()->back()
        ->with('error', __('entity.license_purchase.license_not_available'));
}
```

## Admin Interface

### Federation License Management

Federation→license assignments are managed by the shipped Livewire component
`app/Livewire/Admin/FederationLicenseManager.php` (view `livewire.admin.federation-license-manager`). Key behaviour:

- `mount(Federation $federation)` seeds `selectedLicenses` from the `federation_licenses` pivot via `pluck('license_id')` (cast to strings to match the checkbox state).
- `loadAvailableLicenses()` lists licenses (eager-loading `committee`, `type`, `professionalRole`, `sport`), supports a `searchTerm` (matches `name` / `license_code`) and a `selectedCommittee` filter, and groups results by committee name.
- `updateLicenses()` casts the selected IDs back to integers, calls `$federation->licenses()->sync($licenseIds)`, then clears the per-entity license caches (`licenses_for_type_{committee}_entity_{id}` for the `sport`, `diving`, `scientific`, `technical` committees) and dispatches `licenses-updated`.
- Committee-group helpers: `toggleCommitteeGroup()`, `isGroupSelected()`, `getGroupSelectedCount()`.

## Migration & Setup

> These steps describe how the feature was rolled out. The schema migration ships with the
> application; the data/seeder steps below are reference examples for populating initial
> federation-license relationships.

### 1. Database Migration
```php
// federation_licenses table — part of the baseline schema (database/schema/mysql-schema.sql).
// Shown here as a reference for the table structure.
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('federation_licenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('federation_id')->constrained('federation')->cascadeOnDelete();
            $table->foreignId('license_id')->constrained('license')->cascadeOnDelete();
            $table->timestamps();
            
            $table->unique(['federation_id', 'license_id']);
            $table->index('federation_id');
            $table->index('license_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('federation_licenses');
    }
};
```

### 2. Data Migration

For existing federations, create a seeder or command to populate initial federation-license relationships:

```php
// database/seeders/FederationLicenseSeeder.php
namespace Database\Seeders;

use Domain\Federations\Models\Federation;
use Domain\Licenses\Models\License;
use Illuminate\Database\Seeder;

class FederationLicenseSeeder extends Seeder
{
    public function run()
    {
        // Example: Assign all existing licenses to the main federation
        $mainFederation = Federation::where('is_default_federation', true)->first();
        
        if ($mainFederation) {
            $allLicenses = License::all()->pluck('id');
            $mainFederation->licenses()->sync($allLicenses);
        }
        
        // Add specific logic based on committee types
        $divingFederations = Federation::whereHas('memberships.plans', function ($q) {
            $q->whereHas('committee', function ($q2) {
                $q2->where('code', 'diving');
            });
        })->get();
        
        $divingLicenses = License::hasCommitteeCode('diving')->pluck('id');
        
        foreach ($divingFederations as $federation) {
            $federation->licenses()->syncWithoutDetaching($divingLicenses);
        }
    }
}
```

## Testing

### Feature Tests

```php
// tests/Feature/FederationLicensePermissionsTest.php
namespace Tests\Feature;

use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Licenses\Models\License;
use Tests\TestCase;

class FederationLicensePermissionsTest extends TestCase
{
    public function test_entity_can_only_see_licenses_from_their_federations()
    {
        $federation1 = Federation::factory()->create();
        $federation2 = Federation::factory()->create();
        
        $license1 = License::factory()->create();
        $license2 = License::factory()->create();
        
        $federation1->licenses()->attach($license1);
        $federation2->licenses()->attach($license2);
        
        $entity = Entity::factory()->create();
        $entity->federations()->attach($federation1, ['active' => true]);
        
        $action = new GetAllowedEntityLicensesAction();
        $availableLicenses = $action('sport', $entity);
        
        $this->assertTrue($availableLicenses->contains($license1));
        $this->assertFalse($availableLicenses->contains($license2));
    }
    
    public function test_entity_with_multiple_federations_sees_combined_licenses()
    {
        // Test implementation
    }
}
```

## Rollback Plan

If issues arise, the system can be rolled back without data loss:

1. Remove the federation_licenses constraint from queries
2. The original committee-based filtering will still work
3. Drop the federation_licenses table if needed

## Performance Considerations

1. **Caching**: License queries are cached via `Cache::remember(..., 5, ...)` in `GetAllowedEntityLicensesAction`. The TTL value `5` is an integer, so Laravel treats it as **5 seconds** (not minutes)
2. **Indexes**: Proper indexes on federation_licenses table
3. **Eager Loading**: Use `with('federations')` when loading licenses
4. **Query Optimization**: The additional join is minimal overhead

## Security Considerations

1. **Admin Only**: Federation license management restricted to admin users
2. **Validation**: Ensure licenses exist before assignment
3. **Audit Trail**: Log changes to federation-license relationships
4. **Cache Invalidation**: Clear caches when relationships change

## Future Enhancements

1. **Bulk Operations**: Allow copying license permissions between federations
2. **Templates**: Create license permission templates for common federation types
3. **Time-based Permissions**: Allow temporary license permissions
4. **Hierarchical Permissions**: Child federations inherit parent's licenses
5. **API Endpoints**: REST API for managing federation licenses