<?php

use Domain\Licenses\Actions\PurchaseLicenseAction;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\Scopes\ExcludeInternationalScope;
use Domain\Memberships\Services\ValidationPlanPrivilegeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Helpers\DivingTestHelpers;

uses(RefreshDatabase::class);

test('entity licenses use correct morph type alias', function () {
    DivingTestHelpers::seedDivingModule();
    $setup = DivingTestHelpers::createEntityWithDivingLicense();
    $entity = $setup['entity'];
    $license = $setup['license'];

    $this->mock(ValidationPlanPrivilegeService::class, function ($mock) {
        $mock->shouldReceive('canRequestLicense')->andReturn(true);
        $mock->shouldReceive('getValidationPlanReason')->andReturn(null);
    });

    $licenseAttributed = app(PurchaseLicenseAction::class)($license, $entity);

    expect($licenseAttributed->model_type)->toBe('entity')
        ->not->toBe('Domain\\Entities\\Models\\Entity')
        ->and($licenseAttributed->requester_model_type)->toBe('entity')
        ->not->toBe('Domain\\Entities\\Models\\Entity');

    $entityLicenses = $entity->licenses()->withoutGlobalScope(ExcludeInternationalScope::class);
    expect($entityLicenses->count())->toBe(1)
        ->and($entityLicenses->first()->id)->toBe($licenseAttributed->id);
});

test('entity licenses relationship works with morph alias', function () {
    DivingTestHelpers::seedDivingModule();
    $setup = DivingTestHelpers::createEntityWithDivingLicense();
    $entity = $setup['entity'];
    $license = $setup['license'];

    $licenseAttributed = LicenseAttributed::factory()->create([
        'license_id' => $license->id,
        'model_type' => 'entity',
        'model_id' => $entity->id,
        'license_name' => $license->name,
    ]);

    $licenses = $entity->licenses()->withoutGlobalScope(ExcludeInternationalScope::class)->get();
    expect($licenses)->toHaveCount(1)
        ->and($licenses->first()->id)->toBe($licenseAttributed->id);

    $divingLicenses = $entity->licenses()
        ->withoutGlobalScope(ExcludeInternationalScope::class)
        ->whereHas('license', fn ($query) => $query->whereHas('committee', fn ($q) => $q->where('code', 'DIVING')))
        ->count();

    expect($divingLicenses)->toBe(1);
});

test('migration fixes existing full class names to morph aliases', function () {
    DivingTestHelpers::seedDivingModule();
    $setup = DivingTestHelpers::createEntityWithDivingLicense();
    $entity = $setup['entity'];
    $license = $setup['license'];
    $federation = $setup['federation'];
    $user = $setup['user'];

    DB::table('license_attributed')->insert([
        'id' => Str::uuid(),
        'status_class' => 'Domain\\Licenses\\States\\PendingLicenseAttributedState',
        'license_id' => $license->id,
        'federation_id' => $federation->id,
        'model_type' => 'Domain\\Entities\\Models\\Entity',
        'model_id' => $entity->id,
        'requester_model_type' => 'Domain\\Entities\\Models\\Entity',
        'requested_by_id' => $entity->id,
        'license_name' => 'Test License',
        'holder_name' => $entity->name,
        'federation_name' => 'Test Federation',
        'total_value' => 100,
        'created_by' => $user->id,
        'updated_by' => $user->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect(DB::table('license_attributed')->where('model_type', 'Domain\\Entities\\Models\\Entity')->count())
        ->toBeGreaterThan(0);

    DB::table('license_attributed')
        ->where('model_type', 'Domain\\Entities\\Models\\Entity')
        ->update(['model_type' => 'entity']);

    DB::table('license_attributed')
        ->where('requester_model_type', 'Domain\\Entities\\Models\\Entity')
        ->update(['requester_model_type' => 'entity']);

    expect(DB::table('license_attributed')->where('model_type', 'Domain\\Entities\\Models\\Entity')->count())->toBe(0)
        ->and(DB::table('license_attributed')->where('model_type', 'entity')->where('model_id', $entity->id)->count())->toBeGreaterThan(0)
        ->and($entity->fresh()->licenses()->withoutGlobalScope(ExcludeInternationalScope::class)->count())->toBeGreaterThan(0);
});
