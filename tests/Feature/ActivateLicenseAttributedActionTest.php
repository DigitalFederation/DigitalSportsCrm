<?php

use App\Models\Committee;
use App\Models\Group;
use App\Models\User;
use Carbon\Carbon;
use Domain\Documents\Actions\ManuallyMarkDocumentAsPaidAction;
use Domain\Documents\Models\Document;
use Domain\Documents\Models\DocumentDetail;
use Domain\Documents\Models\DocumentType;
use Domain\Documents\States\PendingDocumentState;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\IndividualFederation;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Individuals\States\ActiveIndividualFederationState;
use Domain\Individuals\States\PendingIndividualFederationState;
use Domain\Licenses\Actions\ActivateLicenseAttributedAction;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Domain\Licenses\States\PendingLicenseAttributedState;
use Domain\Payments\Models\PaymentMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\artisan;

uses(RefreshDatabase::class);

beforeEach(function () {
    artisan('db:seed --class=RoleAndPermissionSeeder');

    DocumentType::factory()->create(['code' => 'ORD']);
    $this->committee = Committee::factory()->create();
    $this->professionalRole = ProfessionalRole::factory()->create(['role' => 'ATHLETE']);
    $group = Group::factory()->create(['code' => 'FEDERATION']);
    $user = User::factory()->create(['group_id' => $group->id]);
    $this->federation = Federation::factory()->create();
    $user->federations()->attach($this->federation->id);
    $this->individual = Individual::factory()->create();
    $this->federation->individuals()->attach($this->individual->id);
    $this->actingAs($user);
    $this->license = License::factory()->create([
        'professional_role_id' => $this->professionalRole->id,
        'interval' => 1,
        'interval_unit' => 'years',
    ]);
    $this->documentType = DocumentType::factory()->create([
        'name' => 'Order',
        'code' => 'ORD',
    ]);
    PaymentMethod::factory()->create([
        'handler' => \Domain\Payments\Handlers\OfflinePaymentHandler::class,
    ]);
    // Set up initial state with factories for Entity and Individual
    Entity::factory()->create();
});

it('activates a license to active state correctly', function () {
    $license = LicenseAttributed::factory()->create(
        [
            'status_class' => PendingLicenseAttributedState::class,
            'model_type' => 'individual',
            'model_id' => $this->individual->id,
            'license_id' => $this->license->id,
            'federation_id' => $this->federation->id,
            'current_term_starts_at' => null,
            'current_term_ends_at' => null,
            'total_value' => 0, // Free license - can be activated without payment
        ]
    );
    $calculateAction = new \Domain\Licenses\Actions\CalculateLicenseValidityDatesAction;
    $action = new ActivateLicenseAttributedAction($calculateAction);

    // Note: The second parameter is not used by the action anymore
    $action($license, '2024-01-01');

    $updatedLicense = LicenseAttributed::find($license->id);
    expect($updatedLicense->status_class)->toBe(ActiveLicenseAttributedState::class);
    expect($updatedLicense->activated_at)->not()->toBeNull();
    // Since the license has interval=1 and interval_unit=years, it should add 1 year from activation date
    expect($updatedLicense->current_term_ends_at)->not()->toBeNull();

    // The action uses now() for activation, so the end date should be 1 year from now
    $activatedAt = Carbon::parse($updatedLicense->activated_at);
    $endsAt = Carbon::parse($updatedLicense->current_term_ends_at);
    expect($endsAt->year)->toBe($activatedAt->year + 1);
});

it('activates license and runs user sync roles after document payment', function () {
    // Setting up the environment for the test
    $document = Document::factory()->create([
        'type_id' => $this->documentType->id,
        'status_class' => PendingDocumentState::class,
    ]);

    $licenseAttributed = LicenseAttributed::factory()->create([
        'status_class' => PendingLicenseAttributedState::class,
        'model_type' => 'individual',
        'model_id' => $this->individual->id,
        'license_id' => $this->license->id,
        'federation_id' => $this->federation->id,
    ]);

    DocumentDetail::factory()->create([
        'document_id' => $document->id,
        'owner_id' => $licenseAttributed->id,
        'owner_type' => LicenseAttributed::class,
    ]);

    // Simulating document payment
    $action = new ManuallyMarkDocumentAsPaidAction;
    $action->execute($document->id);

    // Refreshing the licenseAttributed instance to reflect updated state
    $licenseAttributed->refresh();

    // Assertions to ensure the licenseAttributed has been activated
    expect($licenseAttributed->status_class)->toBe(ActiveLicenseAttributedState::class);
});

it('activates a license with default end date when no date is provided', function () {
    $license = LicenseAttributed::factory()->create([
        'status_class' => PendingLicenseAttributedState::class,
        'model_type' => 'individual',
        'model_id' => $this->individual->id,
        'license_id' => $this->license->id,
        'federation_id' => $this->federation->id,
        'current_term_starts_at' => null,
        'current_term_ends_at' => null,
        'total_value' => 0, // Free license - can be activated without payment
    ]);

    $calculateAction = new \Domain\Licenses\Actions\CalculateLicenseValidityDatesAction;
    $action = new ActivateLicenseAttributedAction($calculateAction);
    $action($license, null);

    $updatedLicense = LicenseAttributed::find($license->id);
    expect($updatedLicense->status_class)->toBe(ActiveLicenseAttributedState::class);
    expect($updatedLicense->activated_at)->not()->toBeNull();
    // Since the license has interval=1 and interval_unit=years, it should add 1 year from activation date
    expect($updatedLicense->current_term_ends_at)->not()->toBeNull();

    // The action uses now() for activation, so the end date should be 1 year from now
    $activatedAt = Carbon::parse($updatedLicense->activated_at);
    $endsAt = Carbon::parse($updatedLicense->current_term_ends_at);
    expect($endsAt->year)->toBe($activatedAt->year + 1);
});

it('syncs individual to license federation when license is activated', function () {
    // Create a modalidade federation (sport-specific association)
    $modalidadeFederation = Federation::factory()->create([
        'name' => 'International Federation Test',
        'is_local' => false,
    ]);

    // Attach the modalidade federation to the license via pivot table
    $this->license->federations()->attach($modalidadeFederation->id);

    // Ensure individual is NOT a member of the modalidade federation initially
    expect(IndividualFederation::where('individual_id', $this->individual->id)
        ->where('federation_id', $modalidadeFederation->id)
        ->exists())->toBeFalse();

    // Create license attributed (the federation link is now on the License, not LicenseAttributed)
    $license = LicenseAttributed::factory()->create([
        'status_class' => PendingLicenseAttributedState::class,
        'model_type' => 'individual',
        'model_id' => $this->individual->id,
        'license_id' => $this->license->id,
        'current_term_starts_at' => null,
        'current_term_ends_at' => null,
        'total_value' => 0, // Free license - can be activated without payment
    ]);

    $calculateAction = new \Domain\Licenses\Actions\CalculateLicenseValidityDatesAction;
    $action = new ActivateLicenseAttributedAction($calculateAction);
    $action($license, null);

    // Verify individual is now a member of the modalidade federation
    $individualFederation = IndividualFederation::where('individual_id', $this->individual->id)
        ->where('federation_id', $modalidadeFederation->id)
        ->first();

    expect($individualFederation)->not()->toBeNull();
    expect($individualFederation->status_class)->toBe(ActiveIndividualFederationState::class);
    expect($individualFederation->active)->toBe(1);
});

it('activates existing pending federation membership when license is activated', function () {
    // Create a modalidade federation
    $modalidadeFederation = Federation::factory()->create([
        'name' => 'International Federation Test 2',
        'is_local' => false,
    ]);

    // Attach the modalidade federation to the license via pivot table
    $this->license->federations()->attach($modalidadeFederation->id);

    // Create a pending federation membership for the individual
    $existingMembership = IndividualFederation::create([
        'individual_id' => $this->individual->id,
        'federation_id' => $modalidadeFederation->id,
        'status_class' => PendingIndividualFederationState::class,
        'active' => 0,
    ]);

    // Create license attributed (the federation link is now on the License, not LicenseAttributed)
    $license = LicenseAttributed::factory()->create([
        'status_class' => PendingLicenseAttributedState::class,
        'model_type' => 'individual',
        'model_id' => $this->individual->id,
        'license_id' => $this->license->id,
        'current_term_starts_at' => null,
        'current_term_ends_at' => null,
        'total_value' => 0, // Free license - can be activated without payment
    ]);

    $calculateAction = new \Domain\Licenses\Actions\CalculateLicenseValidityDatesAction;
    $action = new ActivateLicenseAttributedAction($calculateAction);
    $action($license, null);

    // Verify the existing membership is now active
    $existingMembership->refresh();
    expect($existingMembership->status_class)->toBe(ActiveIndividualFederationState::class);
    expect($existingMembership->active)->toBe(1);

    // Verify no duplicate membership was created
    $membershipCount = IndividualFederation::where('individual_id', $this->individual->id)
        ->where('federation_id', $modalidadeFederation->id)
        ->count();
    expect($membershipCount)->toBe(1);
});

it('prevents activation of paid license without completed payment', function () {
    // Create a paid license (total_value > 0) without a paid document
    $license = LicenseAttributed::factory()->create([
        'status_class' => PendingLicenseAttributedState::class,
        'model_type' => 'individual',
        'model_id' => $this->individual->id,
        'license_id' => $this->license->id,
        'federation_id' => $this->federation->id,
        'total_value' => 50.00, // Paid license
    ]);

    $calculateAction = new \Domain\Licenses\Actions\CalculateLicenseValidityDatesAction;
    $action = new ActivateLicenseAttributedAction($calculateAction);

    // Attempt to activate should fail because there's no paid document
    expect(fn () => $action($license, null))
        ->toThrow(Exception::class, __('licenses.cannot_activate_unpaid_license'));

    // License should remain in pending state
    $updatedLicense = LicenseAttributed::find($license->id);
    expect($updatedLicense->status_class)->toBe(PendingLicenseAttributedState::class);
    expect($updatedLicense->activated_at)->toBeNull();
});

it('allows activation of paid license with bypass flag', function () {
    // Create a paid license (total_value > 0) without a paid document
    $license = LicenseAttributed::factory()->create([
        'status_class' => PendingLicenseAttributedState::class,
        'model_type' => 'individual',
        'model_id' => $this->individual->id,
        'license_id' => $this->license->id,
        'federation_id' => $this->federation->id,
        'total_value' => 50.00, // Paid license
        'current_term_starts_at' => null,
        'current_term_ends_at' => null,
    ]);

    $calculateAction = new \Domain\Licenses\Actions\CalculateLicenseValidityDatesAction;
    $action = new ActivateLicenseAttributedAction($calculateAction);

    // Activation with bypass flag should succeed (used by payment listener)
    $action($license, null, true);

    // License should be activated
    $updatedLicense = LicenseAttributed::find($license->id);
    expect($updatedLicense->status_class)->toBe(ActiveLicenseAttributedState::class);
    expect($updatedLicense->activated_at)->not()->toBeNull();
});
