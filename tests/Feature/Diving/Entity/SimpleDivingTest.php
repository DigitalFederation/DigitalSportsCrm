<?php

use App\Models\Committee;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseType;

test('can create diving license', function () {
    // Create committees
    $divingCommittee = Committee::factory()->create(['code' => 'DIVING', 'name' => 'Diving Committee']);

    // Create license types
    $entityLicenseType = LicenseType::create(['name' => 'entity']);

    // Create diving school license
    $license = License::create([
        'name' => 'Test Diving School License',
        'committee_id' => $divingCommittee->id,
        'type_id' => $entityLicenseType->id,
        'unit_value' => 250.00,
        'unit_value_entity' => 250.00,
        'active' => true,
        'interval' => 1,
        'interval_unit' => 'years',
        'requires_official_documents' => true,
        'required_document_types' => json_encode(['BusinessLicense', 'TaxRegistration']),
        'requester_model' => 'entity',
        'requires_admin_validation' => true,
    ]);

    expect($license)->not->toBeNull();
    expect($license->name)->toBe('Test Diving School License');
    expect($license->required_document_types)->toBe('["BusinessLicense","TaxRegistration"]');
});
