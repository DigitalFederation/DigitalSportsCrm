<?php

use App\Livewire\Profile\UpdateEntityRegistryPrivacyForm;
use App\Livewire\Public\ClubRegistry;
use App\Livewire\Public\DivingServiceProviderRegistry;
use App\Models\Committee;
use App\Models\Group;
use App\Models\User;
use Domain\Entities\Models\Entity;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\Models\LicenseType;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Livewire\Livewire;

function createEntityUser(Entity $entity): User
{
    if (! Group::where('code', 'ENTITY')->exists()) {
        Group::insert([
            ['id' => 1, 'name' => 'Individual', 'code' => 'INDIVIDUAL'],
            ['id' => 2, 'name' => 'Entity', 'code' => 'ENTITY'],
        ]);
    }

    $user = User::factory()->create([
        'group_id' => 2,
    ]);

    $entity->users()->attach($user);

    return $user;
}

test('entity visibility columns default to true', function () {
    $entity = Entity::factory()->create();
    $entity->refresh();

    expect($entity->visible_in_club_registry)->toBeTrue()
        ->and($entity->visible_in_diving_service_provider_registry)->toBeTrue()
        ->and($entity->visible_in_map)->toBeTrue();
});

test('entity visibility columns can be set to false', function () {
    $entity = Entity::factory()->create([
        'visible_in_club_registry' => false,
        'visible_in_diving_service_provider_registry' => false,
        'visible_in_map' => false,
    ]);

    expect($entity->visible_in_club_registry)->toBeFalse()
        ->and($entity->visible_in_diving_service_provider_registry)->toBeFalse()
        ->and($entity->visible_in_map)->toBeFalse();
});

test('entity privacy form component renders with current values', function () {
    $entity = Entity::factory()->create([
        'visible_in_club_registry' => true,
        'visible_in_diving_service_provider_registry' => false,
        'visible_in_map' => true,
    ]);

    $user = createEntityUser($entity);

    $this->actingAs($user);

    Livewire::test(UpdateEntityRegistryPrivacyForm::class)
        ->assertSet('visible_in_club_registry', true)
        ->assertSet('visible_in_diving_service_provider_registry', false)
        ->assertSet('visible_in_map', true);
});

test('entity privacy form component updates registry visibility', function () {
    $entity = Entity::factory()->create([
        'visible_in_club_registry' => true,
        'visible_in_diving_service_provider_registry' => true,
        'visible_in_map' => true,
    ]);

    $user = createEntityUser($entity);

    $this->actingAs($user);

    Livewire::test(UpdateEntityRegistryPrivacyForm::class)
        ->set('visible_in_club_registry', false)
        ->set('visible_in_diving_service_provider_registry', false)
        ->set('visible_in_map', false)
        ->call('updateEntityRegistryPrivacy')
        ->assertDispatched('saved');

    $entity->refresh();

    expect($entity->visible_in_club_registry)->toBeFalse()
        ->and($entity->visible_in_diving_service_provider_registry)->toBeFalse()
        ->and($entity->visible_in_map)->toBeFalse();
});

test('entity privacy form auto-saves when checkbox is toggled', function () {
    $entity = Entity::factory()->create([
        'visible_in_club_registry' => true,
    ]);

    $user = createEntityUser($entity);
    $this->actingAs($user);

    Livewire::test(UpdateEntityRegistryPrivacyForm::class)
        ->set('visible_in_club_registry', false)
        ->assertDispatched('saved');

    $entity->refresh();
    expect($entity->visible_in_club_registry)->toBeFalse();
});

test('entity privacy form persists changes across page reloads', function () {
    $entity = Entity::factory()->create([
        'visible_in_club_registry' => true,
    ]);

    $user = createEntityUser($entity);
    $this->actingAs($user);

    Livewire::test(UpdateEntityRegistryPrivacyForm::class)
        ->set('visible_in_club_registry', false)
        ->assertDispatched('saved');

    Livewire::test(UpdateEntityRegistryPrivacyForm::class)
        ->assertSet('visible_in_club_registry', false);
});

test('entity privacy form appears on profile page for entity users', function () {
    $entity = Entity::factory()->create();
    $user = createEntityUser($entity);

    $this->actingAs($user)
        ->get('/user/profile')
        ->assertOk()
        ->assertSeeLivewire(UpdateEntityRegistryPrivacyForm::class);
});

test('club registry hides entities with visible_in_club_registry false', function () {
    $sportCommittee = Committee::firstOrCreate(
        ['code' => 'SPORT'],
        ['name' => 'Sport', 'is_international' => false]
    );

    $entityType = LicenseType::firstOrCreate(
        ['name' => 'entity'],
        ['is_individual' => false]
    );

    $license = License::factory()->create([
        'committee_id' => $sportCommittee->id,
        'type_id' => $entityType->id,
    ]);

    $visibleEntity = Entity::factory()->create([
        'name' => 'VisibleClubEntity',
        'visible_in_club_registry' => true,
    ]);

    $hiddenEntity = Entity::factory()->create([
        'name' => 'HiddenClubEntity',
        'visible_in_club_registry' => false,
    ]);

    LicenseAttributed::factory()->create([
        'model_type' => 'entity',
        'model_id' => $visibleEntity->id,
        'license_id' => $license->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    LicenseAttributed::factory()->create([
        'model_type' => 'entity',
        'model_id' => $hiddenEntity->id,
        'license_id' => $license->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    Livewire::test(ClubRegistry::class)
        ->assertSee('VisibleClubEntity')
        ->assertDontSee('HiddenClubEntity');
});

test('diving service provider registry hides entities with visible_in_diving_service_provider_registry false', function () {
    $divingServicesCommittee = Committee::firstOrCreate(
        ['code' => 'DIVINGSERVICES'],
        ['name' => 'Diving Services', 'is_international' => false]
    );

    $entityType = LicenseType::firstOrCreate(
        ['name' => 'entity'],
        ['is_individual' => false]
    );

    $license = License::factory()->create([
        'committee_id' => $divingServicesCommittee->id,
        'type_id' => $entityType->id,
    ]);

    $visibleEntity = Entity::factory()->create([
        'name' => 'VisibleDivingProvider',
        'visible_in_diving_service_provider_registry' => true,
    ]);

    $hiddenEntity = Entity::factory()->create([
        'name' => 'HiddenDivingProvider',
        'visible_in_diving_service_provider_registry' => false,
    ]);

    LicenseAttributed::factory()->create([
        'model_type' => 'entity',
        'model_id' => $visibleEntity->id,
        'license_id' => $license->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    LicenseAttributed::factory()->create([
        'model_type' => 'entity',
        'model_id' => $hiddenEntity->id,
        'license_id' => $license->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    Livewire::test(DivingServiceProviderRegistry::class)
        ->assertSee('VisibleDivingProvider')
        ->assertDontSee('HiddenDivingProvider');
});
