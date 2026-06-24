<?php

use App\Models\Committee;
use App\Models\Sport;
use App\Models\User;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Licenses\Actions\PurchaseLicenseAction;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseType;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create common test data
    $this->user = User::factory()->create();
    $this->federation = Federation::factory()->create();
    $this->committee = Committee::factory()->create();
    $this->sport = Sport::factory()->create();
    $this->professionalRole = ProfessionalRole::factory()->create();
    $this->licenseType = LicenseType::factory()->create();
});

it('respects license active status', function () {
    // Create individual
    $individual = Individual::factory()->create();
    $individual->federations()->attach($this->federation);

    // Create inactive license
    $inactiveLicense = License::factory()->create([
        'committee_id' => $this->committee->id,
        'name' => 'Inactive License',
        'requester_model' => ['Individual', 'Entity', 'Federation'],
        'unit_value_individual' => 100,
        'active' => false, // Inactive
    ]);

    // Associate license with federation through pivot table
    $inactiveLicense->federations()->attach($this->federation);

    // Try to purchase inactive license
    $purchaseAction = app(PurchaseLicenseAction::class);

    // Should throw exception for inactive license
    expect(fn () => $purchaseAction($inactiveLicense, $individual))
        ->toThrow(Exception::class);
});
