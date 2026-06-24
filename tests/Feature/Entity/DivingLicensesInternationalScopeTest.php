<?php

use App\Models\User;
use Domain\Entities\Models\Entity;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('controller correctly bypasses international scope for entity own licenses', function () {
    // Create entity with user
    \Tests\Helpers\DivingTestHelpers::seedDivingModule();

    $setup = \Tests\Helpers\DivingTestHelpers::createEntityWithDivingLicense();
    $entity = $setup['entity'];
    $user = $setup['user'];
    $license = $setup['license'];

    // The license internationality is determined by the committee (DIVING is international)
    // DivingTestHelpers creates licenses with DIVING committee which has is_international = true

    // Create a license attributed record for the international license
    $licenseAttributed = LicenseAttributed::factory()->create([
        'status_class' => 'Domain\\Licenses\\States\\PendingLicenseAttributedState',
        'license_id' => $license->id,
        'model_type' => 'entity', // Use morph alias
        'model_id' => $entity->id,
        'license_name' => $license->name,
        'holder_name' => $entity->name,
        'total_value' => '50.00',
        'deleted_at' => null,
    ]);

    // Authenticate as entity user (non-international)
    $this->actingAs($user);

    // Test the exact query the controller uses (with scope bypass)
    $controllerQuery = $entity->licenses()
        ->withoutGlobalScope(\Domain\Licenses\Scopes\ExcludeInternationalScope::class)
        ->whereHas('license', function ($query) {
            $query->whereHas('committee', function ($q) {
                $q->where('code', 'DIVING');
            });
        })
        ->count();

    expect($controllerQuery)->toBe(1, 'Controller query with scope bypass should show international diving license');
});

test('entity diving licenses controller bypasses international scope', function () {
    // Create entity with user
    \Tests\Helpers\DivingTestHelpers::seedDivingModule();

    $setup = \Tests\Helpers\DivingTestHelpers::createEntityWithDivingLicense();
    $entity = $setup['entity'];
    $user = $setup['user'];
    $license = $setup['license'];

    // The license internationality is determined by the committee (DIVING is international)
    // DivingTestHelpers creates licenses with DIVING committee which has is_international = true

    // Create a license attributed record for the international license
    $licenseAttributed = LicenseAttributed::factory()->create([
        'status_class' => 'Domain\\Licenses\\States\\PendingLicenseAttributedState',
        'license_id' => $license->id,
        'model_type' => 'entity', // Use morph alias
        'model_id' => $entity->id,
        'license_name' => $license->name,
        'holder_name' => $entity->name,
        'deleted_at' => null,
    ]);

    // Authenticate as entity user (non-international)
    $this->actingAs($user);

    // Test that without the scope bypass, the license would be hidden
    $normalQuery = $entity->licenses()
        ->whereHas('license', function ($query) {
            $query->whereHas('committee', function ($q) {
                $q->where('code', 'DIVING');
            });
        })
        ->count();

    // With scope bypass, the license should be visible
    $bypassQuery = $entity->licenses()
        ->withoutGlobalScope(\Domain\Licenses\Scopes\ExcludeInternationalScope::class)
        ->whereHas('license', function ($query) {
            $query->whereHas('committee', function ($q) {
                $q->where('code', 'DIVING');
            });
        })
        ->count();

    expect($normalQuery)->toBe(0, 'Normal query should filter out international license for non-international user');
    expect($bypassQuery)->toBe(1, 'Query with scope bypass should show international license');
});
