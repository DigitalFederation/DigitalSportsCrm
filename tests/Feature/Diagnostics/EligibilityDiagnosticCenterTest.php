<?php

use App\Livewire\Admin\Diagnostics\EligibilityDiagnosticCenter;
use App\Models\Group;
use App\Models\Sport;
use App\Models\User;
use Domain\Certifications\Models\Certification;
use Domain\Certifications\Models\CertificationAttributed;
use Domain\Certifications\States\ActiveCertificationAttributedState;
use Domain\Entities\Models\Entity;
use Domain\Entities\Models\EntityAthlete;
use Domain\EvtEvents\Models\Competition;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\Organizer;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Individuals\States\ActiveIndividualEntityState;
use Domain\Individuals\States\ActiveIndividualFederationState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create permissions and roles
    Permission::firstOrCreate(['name' => 'access settings', 'guard_name' => 'web']);
    $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $adminRole->givePermissionTo('access settings');

    // Create admin user
    $this->adminGroup = Group::factory()->create(['code' => 'ADMIN']);
    $this->adminUser = User::factory()->create(['group_id' => $this->adminGroup->id]);
    $this->adminUser->assignRole('admin');

    // Create federation user
    $this->federationGroup = Group::factory()->create(['code' => 'FEDERATION']);
    $this->federationUser = User::factory()->create(['group_id' => $this->federationGroup->id]);
    $this->federation = Federation::factory()->create();
    $this->federationUser->federations()->attach($this->federation->id);

    // Test data
    $this->sport = Sport::factory()->create();
    $this->entity = Entity::factory()->create();
    $this->individual = Individual::factory()->create([
        'name' => 'John',
        'surname' => 'Doe',
        'email' => 'john.doe@example.test',
        'member_code' => 'PT-12345',
    ]);

    $this->event = Event::factory()->create([
        'name' => 'Test Championship',
    ]);

    // Create organizer linking event to federation
    Organizer::create([
        'event_id' => $this->event->id,
        'organizable_type' => Federation::class,
        'organizable_id' => $this->federation->id,
    ]);
});

test('admin can access diagnostic center', function () {
    $this->actingAs($this->adminUser)
        ->get(route('admin.diagnostics.index'))
        ->assertStatus(200)
        ->assertSeeLivewire(EligibilityDiagnosticCenter::class);
});

test('federation admin can access diagnostic center', function () {
    $this->actingAs($this->federationUser)
        ->get(route('federation.diagnostics.index'))
        ->assertStatus(200)
        ->assertSeeLivewire(EligibilityDiagnosticCenter::class);
});

test('component initializes with individual tab active', function () {
    Livewire::actingAs($this->adminUser)
        ->test(EligibilityDiagnosticCenter::class)
        ->assertSet('activeTab', 'individual');
});

test('can switch between tabs', function () {
    Livewire::actingAs($this->adminUser)
        ->test(EligibilityDiagnosticCenter::class)
        ->call('setTab', 'event')
        ->assertSet('activeTab', 'event')
        ->call('setTab', 'individual')
        ->assertSet('activeTab', 'individual');
});

test('search returns individuals matching query', function () {
    Livewire::actingAs($this->adminUser)
        ->test(EligibilityDiagnosticCenter::class)
        ->set('individualSearch', 'John')
        ->assertCount('searchResults', 1);
});

test('search returns individuals by member_code', function () {
    // Search using partial member_code
    Livewire::actingAs($this->adminUser)
        ->test(EligibilityDiagnosticCenter::class)
        ->set('individualSearch', 'PT-')
        ->assertCount('searchResults', 1);
});

test('search returns individuals by email', function () {
    Livewire::actingAs($this->adminUser)
        ->test(EligibilityDiagnosticCenter::class)
        ->set('individualSearch', 'john.doe@example')
        ->assertCount('searchResults', 1);
});

test('search requires minimum 2 characters', function () {
    Livewire::actingAs($this->adminUser)
        ->test(EligibilityDiagnosticCenter::class)
        ->set('individualSearch', 'J')
        ->assertCount('searchResults', 0);
});

test('selecting individual runs profile diagnostic', function () {
    $component = Livewire::actingAs($this->adminUser)
        ->test(EligibilityDiagnosticCenter::class)
        ->call('selectIndividual', $this->individual->id)
        ->assertSet('selectedIndividualId', $this->individual->id);

    expect($component->get('profileDiagnostic'))->not->toBeNull();
});

test('clear individual selection resets state', function () {
    Livewire::actingAs($this->adminUser)
        ->test(EligibilityDiagnosticCenter::class)
        ->call('selectIndividual', $this->individual->id)
        ->call('clearIndividualSelection')
        ->assertSet('selectedIndividualId', null)
        ->assertSet('profileDiagnostic', null)
        ->assertSet('individualSearch', '');
});

test('available events are loaded', function () {
    Livewire::actingAs($this->adminUser)
        ->test(EligibilityDiagnosticCenter::class)
        ->assertCount('availableEvents', 1);
});

test('competitions load when event is selected', function () {
    $competition = Competition::factory()->create(['event_id' => $this->event->id]);

    Livewire::actingAs($this->adminUser)
        ->test(EligibilityDiagnosticCenter::class)
        ->set('selectedEventId', $this->event->id)
        ->assertCount('availableCompetitions', 1);
});

test('changing event resets competition selection', function () {
    $event2 = Event::factory()->create();

    Livewire::actingAs($this->adminUser)
        ->test(EligibilityDiagnosticCenter::class)
        ->set('selectedEventId', $this->event->id)
        ->set('selectedCompetitionId', 1)
        ->set('selectedEventId', $event2->id)
        ->assertSet('selectedCompetitionId', null);
});

test('can select event individual for diagnostic', function () {
    Livewire::actingAs($this->adminUser)
        ->test(EligibilityDiagnosticCenter::class)
        ->set('activeTab', 'event')
        ->set('eventIndividualSearch', 'John')
        ->call('selectEventIndividual', $this->individual->id)
        ->assertSet('eventSelectedIndividualId', $this->individual->id)
        ->assertSet('eventIndividualSearch', '');
});

test('run event diagnostic requires event and individual', function () {
    Livewire::actingAs($this->adminUser)
        ->test(EligibilityDiagnosticCenter::class)
        ->set('activeTab', 'event')
        ->call('runEventDiagnostic')
        ->assertSet('eventDiagnosticResult', null);
});

test('run event diagnostic creates result', function () {
    $this->individual->individualFederations()->create([
        'federation_id' => $this->federation->id,
        'status_class' => ActiveIndividualFederationState::class,
    ]);

    $component = Livewire::actingAs($this->adminUser)
        ->test(EligibilityDiagnosticCenter::class)
        ->set('activeTab', 'event')
        ->set('selectedEventId', $this->event->id)
        ->set('selectedRole', 'official')
        ->call('selectEventIndividual', $this->individual->id)
        ->call('runEventDiagnostic');

    expect($component->get('eventDiagnosticResult'))->not->toBeNull();
});

test('federation admin only sees their federation members', function () {
    // Create individual in federation
    $federationIndividual = Individual::factory()->create(['name' => 'FedMember']);
    $federationIndividual->individualFederations()->create([
        'federation_id' => $this->federation->id,
        'status_class' => ActiveIndividualFederationState::class,
    ]);

    // Create individual NOT in federation
    $otherIndividual = Individual::factory()->create(['name' => 'OtherMember']);

    Livewire::actingAs($this->federationUser)
        ->test(EligibilityDiagnosticCenter::class)
        ->set('individualSearch', 'Member')
        ->assertCount('searchResults', 1);
});

test('federation admin only sees their federation events', function () {
    $otherFederation = Federation::factory()->create();
    $otherEvent = Event::factory()->create([
        'name' => 'Other Event',
    ]);
    Organizer::create([
        'event_id' => $otherEvent->id,
        'organizable_type' => Federation::class,
        'organizable_id' => $otherFederation->id,
    ]);

    Livewire::actingAs($this->federationUser)
        ->test(EligibilityDiagnosticCenter::class)
        ->assertCount('availableEvents', 1);
});

test('admin sees all individuals regardless of federation', function () {
    // Create another individual with a common pattern
    $individual2 = Individual::factory()->create(['name' => 'Johan', 'surname' => 'Smith']);

    // Search for "Joh" which matches both "John Doe" and "Johan Smith"
    Livewire::actingAs($this->adminUser)
        ->test(EligibilityDiagnosticCenter::class)
        ->set('individualSearch', 'Joh')
        ->assertCount('searchResults', 2);
});

test('displays profile diagnostic data correctly', function () {
    $this->individual->individualFederations()->create([
        'federation_id' => $this->federation->id,
        'status_class' => ActiveIndividualFederationState::class,
    ]);
    $this->individual->individualEntities()->create([
        'entity_id' => $this->entity->id,
        'status_class' => ActiveIndividualEntityState::class,
    ]);
    EntityAthlete::create([
        'entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
        'sport_id' => $this->sport->id,
    ]);

    $component = Livewire::actingAs($this->adminUser)
        ->test(EligibilityDiagnosticCenter::class)
        ->call('selectIndividual', $this->individual->id);

    $diagnostic = $component->get('profileDiagnostic');

    expect($diagnostic)->not->toBeNull();
    expect($diagnostic['federationMemberships'])->toHaveCount(1);
    expect($diagnostic['entityMemberships'])->toHaveCount(1);
    expect($diagnostic['quickStatus']['athlete']['eligible'])->toBeTrue();
});

test('displays referee diagnostic with certification flow', function () {
    $refereeRole = ProfessionalRole::factory()->create(['role' => 'TECHNICAL_OFFICIAL']);
    $certification = Certification::factory()->create(['professional_role_id' => $refereeRole->id]);

    $this->individual->individualFederations()->create([
        'federation_id' => $this->federation->id,
        'status_class' => ActiveIndividualFederationState::class,
    ]);
    $this->individual->professionalRoles()->attach($refereeRole->id);

    CertificationAttributed::factory()->create([
        'individual_id' => $this->individual->id,
        'certification_id' => $certification->id,
        'status_class' => ActiveCertificationAttributedState::class,
    ]);

    $component = Livewire::actingAs($this->adminUser)
        ->test(EligibilityDiagnosticCenter::class)
        ->set('activeTab', 'event')
        ->set('selectedEventId', $this->event->id)
        ->set('selectedRole', 'referee')
        ->call('selectEventIndividual', $this->individual->id)
        ->call('runEventDiagnostic');

    $result = $component->get('eventDiagnosticResult');

    expect($result)->not->toBeNull();
    expect($result['isEligible'])->toBeTrue();
});

test('role selection changes diagnostic type', function () {
    $this->individual->individualFederations()->create([
        'federation_id' => $this->federation->id,
        'status_class' => ActiveIndividualFederationState::class,
    ]);

    // Test each role
    foreach (['athlete', 'coach', 'referee', 'official'] as $role) {
        $component = Livewire::actingAs($this->adminUser)
            ->test(EligibilityDiagnosticCenter::class)
            ->set('activeTab', 'event')
            ->set('selectedEventId', $this->event->id)
            ->set('selectedRole', $role)
            ->call('selectEventIndividual', $this->individual->id)
            ->call('runEventDiagnostic');

        expect($component->get('eventDiagnosticResult'))->not->toBeNull();
    }
});
