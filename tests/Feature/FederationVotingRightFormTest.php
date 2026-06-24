<?php

namespace Tests\Feature; // Adjusted namespace

use App\Livewire\Admin\FederationVotingRightForm;
use App\Models\User;
use Domain\Federations\Models\Federation;
use Domain\Federations\Models\FederationVotingRight;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

// Setup
beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $this->user = User::factory()->create();
    $this->user->assignRole('admin');
    actingAs($this->user);

    $this->federation = Federation::factory()->create(['is_local' => false]);
    $this->year = Carbon::now()->year;
});

it('mounts with existing data or defaults', function () {
    // Case 1: Existing data
    $existingData = FederationVotingRight::factory()->create([
        'federation_id' => $this->federation->id,
        'year' => $this->year,
        'general_assembly_status' => FederationVotingRight::STATUS_VOTING_RIGHT,
    ]);

    Livewire::test(FederationVotingRightForm::class, ['federationId' => $this->federation->id, 'year' => $this->year])
        ->assertSet('general_assembly_status', FederationVotingRight::STATUS_VOTING_RIGHT)
        ->assertSet('technical_committee_status', $existingData->technical_committee_status); // Check another field

    // Case 2: No existing data (should use defaults)
    $federationNew = Federation::factory()->create(['is_local' => false]);
    Livewire::test(FederationVotingRightForm::class, ['federationId' => $federationNew->id, 'year' => $this->year])
        ->assertSet('general_assembly_status', FederationVotingRight::STATUS_NO_VOTING_RIGHT)
        ->assertSet('sport_committee_status', FederationVotingRight::STATUS_NO_VOTING_RIGHT);
});

it('validates required fields and status options', function () {
    Livewire::test(FederationVotingRightForm::class, ['federationId' => $this->federation->id, 'year' => $this->year])
        ->set('general_assembly_status', '') // Empty value
        ->set('technical_committee_status', 'Invalid Status') // Invalid enum
        ->call('save')
        ->assertHasErrors(['general_assembly_status' => 'required'])
        ->assertHasErrors(['technical_committee_status' => 'in']);
});

it('saves new voting rights correctly', function () {
    Livewire::test(FederationVotingRightForm::class, ['federationId' => $this->federation->id, 'year' => $this->year])
        ->set('general_assembly_status', FederationVotingRight::STATUS_VOTING_RIGHT)
        ->set('technical_committee_status', FederationVotingRight::STATUS_SUSPENDED)
        ->set('sport_committee_status', FederationVotingRight::STATUS_PROBATION)
        // Set remaining to default to ensure all fields are covered
        ->set('scientific_committee_status', FederationVotingRight::STATUS_NO_VOTING_RIGHT)
        ->set('finswimming_commission_status', FederationVotingRight::STATUS_NO_VOTING_RIGHT)
        ->set('freediving_commission_status', FederationVotingRight::STATUS_NO_VOTING_RIGHT)
        ->set('aquathlon_commission_status', FederationVotingRight::STATUS_NO_VOTING_RIGHT)
        ->set('underwater_hockey_commission_status', FederationVotingRight::STATUS_NO_VOTING_RIGHT)
        ->set('underwater_rugby_commission_status', FederationVotingRight::STATUS_NO_VOTING_RIGHT)
        ->set('target_shooting_commission_status', FederationVotingRight::STATUS_NO_VOTING_RIGHT)
        ->set('sport_diving_commission_status', FederationVotingRight::STATUS_NO_VOTING_RIGHT)
        ->set('spearfishing_commission_status', FederationVotingRight::STATUS_NO_VOTING_RIGHT)
        ->set('orienteering_commission_status', FederationVotingRight::STATUS_NO_VOTING_RIGHT)
        ->set('visual_commission_status', FederationVotingRight::STATUS_NO_VOTING_RIGHT)
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('votingRightsSaved');

    assertDatabaseHas('federation_voting_rights', [
        'federation_id' => $this->federation->id,
        'year' => $this->year,
        'general_assembly_status' => FederationVotingRight::STATUS_VOTING_RIGHT,
        'technical_committee_status' => FederationVotingRight::STATUS_SUSPENDED,
        'sport_committee_status' => FederationVotingRight::STATUS_PROBATION,
        'scientific_committee_status' => FederationVotingRight::STATUS_NO_VOTING_RIGHT, // Check one default
    ]);
});

it('updates existing voting rights correctly', function () {
    FederationVotingRight::factory()->create([
        'federation_id' => $this->federation->id,
        'year' => $this->year,
        'general_assembly_status' => FederationVotingRight::STATUS_VOTING_RIGHT,
        'technical_committee_status' => FederationVotingRight::STATUS_VOTING_RIGHT,
    ]);

    Livewire::test(FederationVotingRightForm::class, ['federationId' => $this->federation->id, 'year' => $this->year])
        ->assertSet('general_assembly_status', FederationVotingRight::STATUS_VOTING_RIGHT) // Check initial state
        ->set('general_assembly_status', FederationVotingRight::STATUS_SUSPENDED)
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('votingRightsSaved');

    assertDatabaseHas('federation_voting_rights', [
        'federation_id' => $this->federation->id,
        'year' => $this->year,
        'general_assembly_status' => FederationVotingRight::STATUS_SUSPENDED,
        'technical_committee_status' => FederationVotingRight::STATUS_VOTING_RIGHT, // Ensure others weren't changed
    ]);
});

it('emits cancel event when cancel method is called', function () {
    Livewire::test(FederationVotingRightForm::class, ['federationId' => $this->federation->id, 'year' => $this->year])
        ->call('cancel')
        ->assertDispatched('cancelVotingRightsEdit');
});
