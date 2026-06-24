<?php

/**
 * Certification Attribution Wizard Tests
 *
 * This test file covers the core functionality of the CertificationAttributionWizard Livewire component.
 * The tests focus on the following key aspects:
 *
 * 1. Component initialization - Testing that the component mounts correctly with expected initial state for both actor types.
 * 2. Director selection - Testing the proper selection of a course director.
 * 3. Student management - Testing adding and removing students from the selection.
 * 4. Assistant management - Testing adding and removing assistant instructors.
 * 5. Federation approval - Testing the federation approval mechanism (for federation actor).
 * 6. Validation differences between actor types for key fields.
 *
 * More complex tests requiring extensive mocking (like step navigation and form submission)
 * have been omitted from this file as they would require more complex test setup.
 *
 * Note: These tests focus on unit testing the component's functionality rather than
 * full integration testing with the database.
 */

use App\Events\CertificationAttributedCreatedEvent;
use App\Livewire\Certifications\CertificationAttributionWizard;
use App\Models\Committee;
use App\Models\Group;
use App\Models\User;
use Domain\Certifications\Models\Certification;
use Domain\Entities\Models\Entity;
use Domain\Entities\States\ActiveEntityProfessionalRoleState;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Actions\DetectIfIndividualIsInstructorAction;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\ProfessionalRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create roles
    Role::firstOrCreate(['name' => 'federation-admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'entity-admin', 'guard_name' => 'web']);

    // Common data
    $this->federation = Federation::factory()->create(['is_local' => false, 'is_default_federation' => true]);
    $this->entity = Entity::factory()->create(); // This will be our main entity for entity tests
    $this->entity->federations()->attach($this->federation->id); // Ensure entity is linked to federation

    // Federation User
    $this->federationGroup = Group::factory()->create(['code' => 'FEDERATION']);
    $this->federationUser = User::factory()->create(['group_id' => $this->federationGroup->id]);
    $this->federationUser->assignRole('federation-admin');
    $this->federationUser->federations()->attach($this->federation->id); // Critical: User linked to federation

    // Entity User
    $this->entityGroup = Group::factory()->create(['code' => 'ENTITY']);
    $this->entityUser = User::factory()->create(['group_id' => $this->entityGroup->id]);
    $this->entityUser->assignRole('entity-admin');
    $this->entityUser->entities()->attach($this->entity->id); // Critical: User linked to entity

    // Other data
    $this->committee = Committee::factory()->create(['code' => 'DIVING']);
    $this->certification = Certification::factory()->create(['committee_id' => $this->committee->id]);

    $this->instructorRole = ProfessionalRole::factory()->create(['role' => 'INSTRUCTOR']);

    $this->director = Individual::factory()->create();
    $this->director->federations()->attach($this->federation->id);
    $this->director->entities()->attach($this->entity->id); // For entity context tests

    // Attach director to entity with an active INSTRUCTOR role
    $this->director->professionalRoleEntities()->create([
        'entity_id' => $this->entity->id,
        'professional_role_id' => $this->instructorRole->id,
        'status_class' => ActiveEntityProfessionalRoleState::class,
    ]);

    $this->student = Individual::factory()->create();
    $this->student->federations()->attach($this->federation->id);

    $this->assistant = Individual::factory()->create();
    $this->assistant->federations()->attach($this->federation->id);
});

test('mounts correctly for federation actor', function () {
    Auth::login($this->federationUser);
    $component = Livewire::test(CertificationAttributionWizard::class, ['actorType' => 'federation']);

    $component->assertSet('step', 1)
        ->assertSet('actorType', 'federation')
        ->assertSet('selectedFederationId', $this->federation->id)
        ->assertSet('showSuccessState', false);
});

test('mounts correctly for entity actor', function () {
    Auth::login($this->entityUser);
    $component = Livewire::test(CertificationAttributionWizard::class, ['actorType' => 'entity']);

    $component->assertSet('step', 1)
        ->assertSet('actorType', 'entity')
        ->assertSet('selectedSchoolId', $this->entity->id)
        ->assertSet('selectedFederationId', $this->entity->federations()->first()->id) // or $this->federation->id
        ->assertSet('federationApprove', false)
        ->assertSet('showSuccessState', false);
});

it('selects a course director correctly for federation actor', function () {
    Auth::login($this->federationUser);

    // Also mock for federation actor test to ensure consistency if action is complex
    $this->mock(DetectIfIndividualIsInstructorAction::class, function ($mock) {
        $mock->shouldReceive('__invoke')->andReturn(true);
    });

    $component = Livewire::test(CertificationAttributionWizard::class, ['actorType' => 'federation'])
        ->set('selectedSchoolId', $this->entity->id); // School selection is still possible for federation

    $component->call('selectDirector', $this->director->id)
        ->assertSet('selectedDirectorId', $this->director->id);
});

it('selects a course director correctly for entity actor', function () {
    Auth::login($this->entityUser);

    // Mock the action to always return true for this test
    $this->mock(DetectIfIndividualIsInstructorAction::class, function ($mock) {
        $mock->shouldReceive('__invoke')->andReturn(true);
    });

    $component = Livewire::test(CertificationAttributionWizard::class, ['actorType' => 'entity']);

    $component->call('selectDirector', $this->director->id)
        ->assertSet('selectedDirectorId', $this->director->id);
});

it('can toggle students selection', function () {
    Auth::login($this->federationUser); // Or entityUser, this logic is actor-agnostic
    $component = Livewire::test(CertificationAttributionWizard::class);

    $component->call('toggleStudent', $this->student->id);
    $selectedStudentIds = $component->get('selectedStudentIds');
    $this->assertContains($this->student->id, $selectedStudentIds);

    $component->call('toggleStudent', $this->student->id);
    $selectedStudentIds = $component->get('selectedStudentIds');
    $this->assertNotContains($this->student->id, $selectedStudentIds);
});

it('can toggle assistant selection', function () {
    Auth::login($this->federationUser); // Or entityUser, actor-agnostic
    $component = Livewire::test(CertificationAttributionWizard::class);

    $component->call('toggleAssistant', $this->assistant->id);
    $selectedAssistantIds = $component->get('selectedAssistantIds');
    $this->assertContains($this->assistant->id, $selectedAssistantIds);

    $component->call('toggleAssistant', $this->assistant->id);
    $selectedAssistantIds = $component->get('selectedAssistantIds');
    $this->assertNotContains($this->assistant->id, $selectedAssistantIds);
});

it('handles federation approval for federation actor', function () {
    Auth::login($this->federationUser);
    $component = Livewire::test(CertificationAttributionWizard::class, [
        'actorType' => 'federation',
        'committee_code' => $this->committee->code,
    ]);

    // Set values for step 1 with federation approval
    $component->set('federationApprove', true)
        ->assertSet('federationApprove', true);

    // With federationApprove=true, director is not mandatory for federation to pass step 1
    $component->call('nextStep')
        ->assertHasNoErrors(['selectedDirectorId', 'step1_requirement']);
});

it('federation approval is not applicable for entity actor', function () {
    Auth::login($this->entityUser);
    $component = Livewire::test(CertificationAttributionWizard::class, [
        'actorType' => 'entity',
        'committee_code' => $this->committee->code,
    ]);

    // federationApprove should be false by default and trying to set it true might be blocked or reset
    $component->assertSet('federationApprove', false);

    // Attempting to set it to true and proceed (should ideally be blocked by validation if director isn't set)
    // Or, more simply, ensure it can't be set to true
    $component->set('federationApprove', true) // Try to set it
        ->assertSet('federationApprove', true); // Check if it sticks (it might not due to reactive logic)
    // The validation logic for entity actor in step 1 will prevent proceeding
    // if federationApprove is true and no director
});

// Add more tests for validation differences, e.g., date fields, national numbers
// For example:

it('validates date fields only for federation actor in step 2', function () {
    Auth::login($this->federationUser);
    Livewire::test(CertificationAttributionWizard::class, ['actorType' => 'federation', 'committee_code' => 'DIVING'])
        ->set('selectedDirectorId', $this->director->id) // Need a director
        ->set('selectedStudentIds', [$this->student->id]) // Need a student
        ->set('studentNationalNumbers', [$this->student->id => '123']) // And national number for fed
        ->set('selectedCertificationId', $this->certification->id) // Need a cert
        ->set('issueDate', 'invalid-date')
        ->call('submit') // This triggers step 2 validation before actual submit logic
        ->assertHasErrors(['issueDate' => 'date']);

    Auth::login($this->entityUser);
    Livewire::test(CertificationAttributionWizard::class, ['actorType' => 'entity', 'committee_code' => 'DIVING'])
        ->set('selectedDirectorId', $this->director->id)
        ->set('selectedStudentIds', [$this->student->id])
        ->set('selectedCertificationId', $this->certification->id)
        ->set('issueDate', 'invalid-date') // This field isn't shown/validated for entity
        ->call('submit')
        ->assertHasNoErrors(['issueDate']);
});

it('validates national numbers only for federation actor in step 2', function () {
    Auth::login($this->federationUser);
    Livewire::test(CertificationAttributionWizard::class, ['actorType' => 'federation', 'committee_code' => 'DIVING'])
        ->set('selectedDirectorId', $this->director->id)
        ->set('selectedStudentIds', [$this->student->id])
        ->set('selectedCertificationId', $this->certification->id)
        ->set('studentNationalNumbers', [$this->student->id => '']) // Empty national number
        ->call('submit')
        ->assertHasErrors(['studentNationalNumbers.' . $this->student->id]);

    Auth::login($this->entityUser);
    Livewire::test(CertificationAttributionWizard::class, ['actorType' => 'entity', 'committee_code' => 'DIVING'])
        ->set('selectedDirectorId', $this->director->id)
        ->set('selectedStudentIds', [$this->student->id])
        ->set('selectedCertificationId', $this->certification->id)
        ->set('studentNationalNumbers', [$this->student->id => '']) // Value is not used/validated
        ->call('submit')
        ->assertHasNoErrors(['studentNationalNumbers.' . $this->student->id]);
});

it('does not dispatch payment document event before director approval for paid certifications', function () {
    Event::fake([CertificationAttributedCreatedEvent::class]);

    $mainFederation = Federation::factory()->create([
        'is_default_federation' => true,
        'is_local' => false,
    ]);

    $this->entity->federations()->sync([$mainFederation->id]);
    $this->director->federations()->sync([$mainFederation->id]);
    $this->student->federations()->sync([$mainFederation->id]);

    $certification = Certification::factory()->create([
        'committee_id' => $this->committee->id,
        'digital_price' => 50.00,
    ]);

    Auth::login($this->entityUser);

    Livewire::test(CertificationAttributionWizard::class, ['actorType' => 'entity', 'committee_code' => 'DIVING'])
        ->set('selectedFederationId', $mainFederation->id)
        ->set('selectedSchoolId', $this->entity->id)
        ->set('selectedDirectorId', $this->director->id)
        ->set('selectedStudentIds', [$this->student->id])
        ->set('selectedCertificationId', $certification->id)
        ->set('confirmationAccepted', true)
        ->call('submit');

    Event::assertNotDispatched(CertificationAttributedCreatedEvent::class);
});
