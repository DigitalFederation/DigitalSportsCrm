<?php

use App\Enums\UserGroupEnum;
use App\Models\Group;
use App\Models\User;
use Domain\Diving\Actions\ApproveTechnicalDirectorLicenseAction;
use Domain\Diving\Models\DivingEntityTechnicalDirector;
use Domain\Diving\States\AssignedDivingTechnicalDirectorState;
use Domain\Diving\States\RemovedDivingTechnicalDirectorState;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Domain\Licenses\States\PendingTechnicalDirectorApprovalLicenseAttributedState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\DivingTestHelpers;

uses(RefreshDatabase::class);

beforeEach(function () {
    DivingTestHelpers::seedDivingModule();

    $setup = DivingTestHelpers::createEntityWithDivingLicense();
    $this->entity = $setup['entity'];
    $this->license = $setup['license'];

    $instructorSetup = DivingTestHelpers::createCertifiedDivingInstructor(['CMAS', 'SSI']);
    $this->individual = $instructorSetup['individual'];
    $this->user = $instructorSetup['user'];

    $this->licenseAttributed = LicenseAttributed::factory()->create([
        'model_type' => 'entity',
        'model_id' => $this->entity->id,
        'license_id' => $this->license->id,
        'status_class' => PendingTechnicalDirectorApprovalLicenseAttributedState::class,
    ]);

    $this->technicalDirector = DivingEntityTechnicalDirector::factory()->create([
        'entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
        'license_attributed_id' => $this->licenseAttributed->id,
        'license_id' => $this->license->id,
        'certification_systems' => ['CMAS', 'SSI'],
        'status_class' => AssignedDivingTechnicalDirectorState::class,
        'assigned_at' => now(),
    ]);
});

test('technical director can access positions index', function () {
    $this->actingAs($this->user);

    $response = $this->get(route('individual.technical_director_positions.index'));

    $response->assertOk();
    $response->assertViewIs('web.individual.technical_director_positions.index');
});

test('approve endpoint returns success for valid approval', function () {
    $this->actingAs($this->user)
        ->postJson(route('individual.technical_director_positions.approve', $this->technicalDirector), [
            'approval_notes' => 'All requirements satisfied',
        ])
        ->assertOk()
        ->assertJson(['success' => true, 'message' => __('diving.license_approved_successfully')]);

    $this->technicalDirector->refresh();
    expect($this->technicalDirector->hasApproved())->toBeTrue()
        ->and($this->technicalDirector->approval_notes)->toBe('All requirements satisfied');
});

test('approve endpoint works without approval notes', function () {
    $this->actingAs($this->user)
        ->postJson(route('individual.technical_director_positions.approve', $this->technicalDirector))
        ->assertOk()
        ->assertJson(['success' => true, 'message' => __('diving.license_approved_successfully')]);

    $this->technicalDirector->refresh();
    expect($this->technicalDirector->hasApproved())->toBeTrue()
        ->and($this->technicalDirector->approval_notes)->toBeNull();
});

test('approve endpoint returns license status in response', function () {
    $response = $this->actingAs($this->user)
        ->postJson(route('individual.technical_director_positions.approve', $this->technicalDirector))
        ->assertOk()
        ->assertJsonStructure(['success', 'message', 'license_status']);

    expect($response->json('license_status'))->toBeString();
});

test('reject endpoint requires rejection reason', function () {
    $this->actingAs($this->user)
        ->postJson(route('individual.technical_director_positions.reject', $this->technicalDirector), ['rejection_reason' => ''])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['rejection_reason']);
});

test('reject endpoint returns success for valid rejection', function () {
    $this->actingAs($this->user)
        ->postJson(route('individual.technical_director_positions.reject', $this->technicalDirector), [
            'rejection_reason' => 'Safety standards not met',
        ])
        ->assertOk()
        ->assertJson(['success' => true, 'message' => __('diving.license_rejected_successfully')]);

    $this->technicalDirector->refresh();
    expect($this->technicalDirector->hasRejected())->toBeTrue()
        ->and($this->technicalDirector->rejection_reason)->toBe('Safety standards not met');
});

test('reject endpoint validates reason length', function () {
    $this->actingAs($this->user)
        ->postJson(route('individual.technical_director_positions.reject', $this->technicalDirector), [
            'rejection_reason' => str_repeat('a', 1001),
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['rejection_reason']);
});

test('unauthorized user cannot approve license', function () {
    $otherSetup = DivingTestHelpers::createCertifiedDivingInstructor(['PADI']);

    $this->actingAs($otherSetup['user'])
        ->postJson(route('individual.technical_director_positions.approve', $this->technicalDirector))
        ->assertForbidden()
        ->assertJson(['success' => false, 'message' => __('diving.unauthorized_technical_director_action')]);
});

test('unauthorized user cannot reject license', function () {
    $otherSetup = DivingTestHelpers::createCertifiedDivingInstructor(['PADI']);

    $this->actingAs($otherSetup['user'])
        ->postJson(route('individual.technical_director_positions.reject', $this->technicalDirector), [
            'rejection_reason' => 'Trying to reject',
        ])
        ->assertForbidden()
        ->assertJson(['success' => false, 'message' => __('diving.unauthorized_technical_director_action')]);
});

test('user without individual profile cannot approve', function () {
    $individualGroup = Group::firstOrCreate(
        ['id' => UserGroupEnum::INDIVIDUAL->value],
        ['code' => 'INDIVIDUAL', 'name' => 'Individual']
    );
    $userWithoutIndividual = User::factory()->create(['group_id' => $individualGroup->id]);

    $this->actingAs($userWithoutIndividual)
        ->postJson(route('individual.technical_director_positions.approve', $this->technicalDirector))
        ->assertForbidden()
        ->assertJson(['message' => "This user doesn't have any relation with any item."]);
});

test('cannot approve already approved license', function () {
    $this->actingAs($this->user)
        ->postJson(route('individual.technical_director_positions.approve', $this->technicalDirector));

    $this->postJson(route('individual.technical_director_positions.approve', $this->technicalDirector))
        ->assertBadRequest()
        ->assertJson(['success' => false, 'message' => __('diving.technical_director_already_approved')]);
});

test('cannot reject already rejected license', function () {
    $this->actingAs($this->user)
        ->postJson(route('individual.technical_director_positions.reject', $this->technicalDirector), [
            'rejection_reason' => 'First rejection',
        ]);

    $this->postJson(route('individual.technical_director_positions.reject', $this->technicalDirector), [
        'rejection_reason' => 'Second rejection',
    ])
        ->assertBadRequest()
        ->assertJson(['success' => false, 'message' => __('diving.technical_director_already_rejected')]);
});

test('cannot approve rejected license', function () {
    $this->actingAs($this->user)
        ->postJson(route('individual.technical_director_positions.reject', $this->technicalDirector), [
            'rejection_reason' => 'Initial rejection',
        ]);

    $this->postJson(route('individual.technical_director_positions.approve', $this->technicalDirector))
        ->assertBadRequest()
        ->assertJson(['success' => false, 'message' => __('diving.technical_director_already_rejected')]);
});

test('cannot reject approved license', function () {
    $this->actingAs($this->user)
        ->postJson(route('individual.technical_director_positions.approve', $this->technicalDirector));

    $this->postJson(route('individual.technical_director_positions.reject', $this->technicalDirector), [
        'rejection_reason' => 'Trying to reject approved',
    ])
        ->assertBadRequest()
        ->assertJson(['success' => false, 'message' => __('diving.technical_director_already_approved')]);
});

test('endpoints handle non-existent technical director', function () {
    $this->actingAs($this->user);
    $nonExistentId = fake()->uuid();

    $this->postJson("/individual/technical-director-positions/{$nonExistentId}/approve")
        ->assertNotFound();

    $this->postJson("/individual/technical-director-positions/{$nonExistentId}/reject", ['rejection_reason' => 'Test reason'])
        ->assertNotFound();
});

test('endpoints return error for invalid license state', function () {
    $this->licenseAttributed->update(['status_class' => ActiveLicenseAttributedState::class]);

    $this->actingAs($this->user)
        ->postJson(route('individual.technical_director_positions.approve', $this->technicalDirector))
        ->assertBadRequest()
        ->assertJson(['success' => false, 'message' => __('diving.license_not_pending_technical_director_approval')]);
});

test('endpoints handle technical director not assigned', function () {
    $this->technicalDirector->update(['status_class' => RemovedDivingTechnicalDirectorState::class]);

    $this->actingAs($this->user)
        ->postJson(route('individual.technical_director_positions.approve', $this->technicalDirector))
        ->assertBadRequest()
        ->assertJson(['success' => false, 'message' => __('diving.technical_director_not_assigned')]);
});

test('endpoints use correct HTTP methods', function () {
    $this->actingAs($this->user);

    $this->get(route('individual.technical_director_positions.approve', $this->technicalDirector))
        ->assertMethodNotAllowed();

    $this->get(route('individual.technical_director_positions.reject', $this->technicalDirector))
        ->assertMethodNotAllowed();

    $this->put(route('individual.technical_director_positions.approve', $this->technicalDirector))
        ->assertMethodNotAllowed();

    $this->postJson(route('individual.technical_director_positions.approve', $this->technicalDirector))
        ->assertOk();
});

test('endpoints require authentication', function () {
    $this->postJson(route('individual.technical_director_positions.approve', $this->technicalDirector))
        ->assertUnauthorized();

    $this->postJson(route('individual.technical_director_positions.reject', $this->technicalDirector), ['rejection_reason' => 'Test'])
        ->assertUnauthorized();
});

test('endpoints handle server errors gracefully', function () {
    $this->app->bind(ApproveTechnicalDirectorLicenseAction::class, function () {
        $mock = Mockery::mock(ApproveTechnicalDirectorLicenseAction::class);
        $mock->shouldReceive('execute')->andThrow(new Exception('Database connection failed'));

        return $mock;
    });

    $this->actingAs($this->user)
        ->postJson(route('individual.technical_director_positions.approve', $this->technicalDirector))
        ->assertBadRequest()
        ->assertJson(['success' => false, 'message' => 'Database connection failed']);
});

test('approve endpoint accepts both form and JSON content types', function () {
    $this->actingAs($this->user)
        ->post(route('individual.technical_director_positions.approve', $this->technicalDirector))
        ->assertOk()
        ->assertHeader('content-type', 'application/json')
        ->assertJson(['success' => true]);
});
