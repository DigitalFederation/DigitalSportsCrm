<?php

use App\Livewire\Admin\FederationVotingRightManager;
use App\Models\User;
use Domain\Federations\Models\Federation;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

// Setup
beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $this->user = User::factory()->create();
    $this->user->assignRole('admin');
    actingAs($this->user);
});

it('can view federation voting rights page', function () {
    Federation::factory()->create(['is_local' => false]); // Ensure there's at least one federation
    Livewire::test(FederationVotingRightManager::class)
        ->assertStatus(200)
        ->assertSee('Federation Voting Rights');
});

it('sets editing state when edit button is clicked', function () {
    $federation = Federation::factory()->create(['is_local' => false]);

    Livewire::test(FederationVotingRightManager::class)
        ->assertSet('editingFederationId', null)
        ->call('editVotingRights', $federation->id)
        ->assertSet('editingFederationId', $federation->id)
        ->assertSeeHtml('wire:submit.prevent="save"'); // Check form is now visible
});

it('clears editing state when votingRightsSaved event is received', function () {
    $federation = Federation::factory()->create(['is_local' => false]);

    Livewire::test(FederationVotingRightManager::class)
        ->call('editVotingRights', $federation->id)
        ->assertSet('editingFederationId', $federation->id)
        // Simulate event coming from child
        ->dispatch('votingRightsSaved')
        ->assertSet('editingFederationId', null)
        ->assertDontSeeHtml('wire:submit.prevent="save"'); // Check form is hidden
});

it('clears editing state when cancelVotingRightsEdit event is received', function () {
    $federation = Federation::factory()->create(['is_local' => false]);

    Livewire::test(FederationVotingRightManager::class)
        ->call('editVotingRights', $federation->id)
        ->assertSet('editingFederationId', $federation->id)
        // Simulate event coming from child
        ->dispatch('cancelVotingRightsEdit')
        ->assertSet('editingFederationId', null)
        ->assertDontSeeHtml('wire:submit.prevent="save"'); // Check form is hidden
});
