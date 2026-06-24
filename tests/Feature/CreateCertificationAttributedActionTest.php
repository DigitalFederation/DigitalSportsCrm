<?php

use App\Models\Group;
use Domain\Certifications\Actions\ActivateCertificationAttributedAction;
use Domain\Certifications\Actions\ActivateCertificationAttributedByFederationAction;
use Domain\Certifications\Actions\CreateCertificationAttributedAction;
use Domain\Certifications\DataTransferObject\CertificationAttributedData;
use Domain\Certifications\Models\Certification;
use Domain\Certifications\Models\CertificationAttributed;
use Domain\Certifications\States\ActiveCertificationAttributedState;
use Domain\Certifications\States\PendingCertificationAttributedState;
use Domain\Certifications\States\ProvisionalCertificationAttributedState;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

use function Pest\Laravel\artisan;

uses(RefreshDatabase::class);

beforeEach(function () {
    artisan('db:seed --class=UserGroupSeeder');
    artisan('db:seed --class=RoleAndPermissionSeeder');

    Federation::factory()->create(['is_default_federation' => true, 'is_local' => false]);

    $this->activateCertificationAction = new ActivateCertificationAttributedAction;
    $this->activateCertificationByFederationAction = new ActivateCertificationAttributedByFederationAction(
        $this->activateCertificationAction
    );
    $this->createCertificationAttributedAction = new CreateCertificationAttributedAction(
        $this->activateCertificationByFederationAction
    );
});

it('sets certification to active state when approved by federation', function () {

    // Arrange
    $group = Group::factory()->create(['code' => 'FEDERATION']);
    $localFederation = Federation::factory()->create(['is_local' => true]);
    $certification = Certification::factory()->create();
    $user = \App\Models\User::factory()->create(['group_id' => $group->id]);
    $user->federations()->attach($localFederation->id);
    $individual = Individual::factory()->create();

    // Certifications always belong to the main federation (which is not local),
    // so approved_by_federation triggers activation via the else branch.
    $certificationAttributedData = new CertificationAttributedData(
        id: Str::uuid()->toString(),
        certification_id: $certification->id,
        federation_id: $localFederation->id,
        entity_id: null,
        individual_ids: [$individual->id],
        director_instructor_id: null,
        national_code: null,
        assistant_instructor_ids: [],
        code: null,
        number: null,
        activator_id: null,
        activator_type: null,
        activated_at: null,
        current_term_starts_at: now()->toDateString(),
        current_term_ends_at: null,
        notes: 'Test notes',
        status_class: ProvisionalCertificationAttributedState::class,
        certification_name: $certification->name,
        federation_name: $localFederation->name,
        entity_name: null,
        holder_name: 'Test Holder',
        international_code: null,
        approved_by_federation: true,
        approve_without_slots: false
    );

    // Act
    $this->createCertificationAttributedAction->__invoke($certificationAttributedData);

    // Assert
    $certificationAttributed = CertificationAttributed::latest()->first();
    expect($certificationAttributed->status_class)->toBe(ActiveCertificationAttributedState::class);
});

it('saves the provided national_code', function () {
    // Arrange
    $group = Group::factory()->create(['code' => 'FEDERATION', 'id' => 3]);
    $mainFederation = Federation::factory()->create(['is_local' => false]);
    $certification = Certification::factory()->create();
    $user = \App\Models\User::factory()->create(['group_id' => $group->id]);
    $user->federations()->attach($mainFederation->id);
    $individual = Individual::factory()->create();
    $mainFederation->individuals()->attach($individual);

    // Login as the created user
    $this->actingAs($user);

    $testNationalCode = 'TEST-NAT-12345';

    $certificationAttributedData = new CertificationAttributedData(
        id: Str::uuid()->toString(),
        certification_id: $certification->id,
        federation_id: $mainFederation->id,
        entity_id: null,
        individual_ids: [$individual->id],
        director_instructor_id: null,
        national_code: $testNationalCode, // Provide a specific national code
        assistant_instructor_ids: [],
        code: null,
        number: null,
        activator_id: null,
        activator_type: null,
        activated_at: null,
        current_term_starts_at: now()->toDateString(),
        current_term_ends_at: null,
        notes: 'Test national code',
        status_class: PendingCertificationAttributedState::class,
        certification_name: $certification->name,
        federation_name: $mainFederation->name,
        entity_name: null,
        holder_name: 'Test Holder',
        international_code: null,
        approved_by_federation: false, // Let the action logic handle activation/state
        approve_without_slots: false
    );

    // Act
    $action = new CreateCertificationAttributedAction($this->activateCertificationByFederationAction);
    $action($certificationAttributedData);

    // Assert
    $certificationAttributed = CertificationAttributed::where('individual_id', $individual->id)
        ->where('certification_id', $certification->id)
        ->latest()
        ->first();

    expect($certificationAttributed)->not->toBeNull();
    expect($certificationAttributed->national_code)->toBe($testNationalCode);
});
