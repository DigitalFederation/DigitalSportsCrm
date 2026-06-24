<?php

use App\Models\Group;
use App\Models\User;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\IndividualFederation;
use Domain\Individuals\States\ActiveIndividualFederationState;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\artisan;

uses(RefreshDatabase::class);

beforeEach(function () {
    artisan('db:seed --class=RoleAndPermissionSeeder');
    $group = Group::factory()->create(['code' => 'INDIVIDUAL']);

    // Create user and individual
    $this->individualUser = User::factory()->create(['group_id' => $group->id]);
    $this->individual = Individual::factory()->create(['user_id' => $this->individualUser->id]);

    $this->individualUser->assignRole('individual-approved');
    $this->actingAs($this->individualUser);
});

test('individual can disassociate from a regular federation', function () {
    // Create a regular federation (not the main one)
    $regularFederation = Federation::factory()->create([
        'is_default_federation' => false,
    ]);

    // Associate individual with the federation
    IndividualFederation::create([
        'individual_id' => $this->individual->id,
        'federation_id' => $regularFederation->id,
        'status_class' => ActiveIndividualFederationState::class,
        'active' => true,
    ]);

    // Attempt to disassociate
    $response = $this->delete(route('individual.federation.delete', $regularFederation->id));

    // Assert redirect with success message
    $response->assertRedirect(route('individual.federation.index'));
    $response->assertSessionHas('success');

    // Assert federation was detached
    expect(IndividualFederation::where('individual_id', $this->individual->id)
        ->where('federation_id', $regularFederation->id)
        ->exists())->toBeFalse();
});

test('individual cannot disassociate from the main federation', function () {
    // Create the main federation
    $mainFederation = Federation::factory()->create([
        'is_default_federation' => true,
    ]);

    // Associate individual with the main federation
    IndividualFederation::create([
        'individual_id' => $this->individual->id,
        'federation_id' => $mainFederation->id,
        'status_class' => ActiveIndividualFederationState::class,
        'active' => true,
    ]);

    // Attempt to disassociate
    $response = $this->delete(route('individual.federation.delete', $mainFederation->id));

    // Assert redirect with error message
    $response->assertRedirect(route('individual.federation.index'));
    $response->assertSessionHas('error', __('individuals.cannot_disassociate_main_federation'));

    // Assert federation was NOT detached
    expect(IndividualFederation::where('individual_id', $this->individual->id)
        ->where('federation_id', $mainFederation->id)
        ->exists())->toBeTrue();
});

test('disassociate button is not shown for main federation in view', function () {
    // Create main federation
    $mainFederation = Federation::factory()->create([
        'is_default_federation' => true,
    ]);

    // Create regular federation
    $regularFederation = Federation::factory()->create([
        'is_default_federation' => false,
    ]);

    // Associate individual with both federations
    IndividualFederation::create([
        'individual_id' => $this->individual->id,
        'federation_id' => $mainFederation->id,
        'status_class' => ActiveIndividualFederationState::class,
        'active' => true,
    ]);
    IndividualFederation::create([
        'individual_id' => $this->individual->id,
        'federation_id' => $regularFederation->id,
        'status_class' => ActiveIndividualFederationState::class,
        'active' => true,
    ]);

    // Visit the federation index page
    $response = $this->get(route('individual.federation.index'));
    $response->assertStatus(200);

    // Get the HTML content
    $content = $response->getContent();

    // The disassociate form for main federation should NOT be in the HTML
    // Pattern: form with action pointing to federation delete and containing DELETE method
    $mainFedDeleteFormPattern = 'action="' . preg_quote(route('individual.federation.delete', $mainFederation->id), '/') . '"';
    expect(preg_match('/' . $mainFedDeleteFormPattern . '/', $content))->toBe(0);

    // The disassociate form for regular federation SHOULD be in the HTML
    $regularFedDeleteFormPattern = 'action="' . preg_quote(route('individual.federation.delete', $regularFederation->id), '/') . '"';
    expect(preg_match('/' . $regularFedDeleteFormPattern . '/', $content))->toBe(1);
});
