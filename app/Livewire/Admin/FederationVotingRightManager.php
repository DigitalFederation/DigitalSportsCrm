<?php

namespace App\Livewire\Admin;

use Domain\Federations\Models\Federation;
use Domain\Federations\Models\FederationVotingRight;
use Illuminate\Support\Carbon;
use Livewire\Attributes\On;
use Livewire\Component; // Import the On attribute

class FederationVotingRightManager extends Component
{
    public int $year;
    public array $years = [];
    public $federations;
    public array $statusOptions = [];

    // State for editing
    public ?int $editingFederationId = null;

    // Listeners for events from child form component
    #[On('votingRightsSaved')]
    #[On('cancelVotingRightsEdit')]
    public function clearEditingState(): void
    {
        $this->editingFederationId = null;
        $this->loadFederations(); // Reload data to reflect changes or cancel
    }

    public function mount(): void
    {
        $this->year = Carbon::now()->year;
        $currentYear = Carbon::now()->year;
        $this->years = range($currentYear - 5, $currentYear + 5);
        $this->statusOptions = FederationVotingRight::STATUS_OPTIONS;
        $this->loadFederations();
    }

    // Reload federations when year changes
    public function updatedYear(): void
    {
        $this->editingFederationId = null; // Cancel editing if year changes
        $this->loadFederations();
    }

    public function loadFederations(): void
    {
        $this->federations = Federation::where('is_local', false)
            ->with(['votingRights' => function ($query) {
                $query->where('year', $this->year);
            }])
            ->orderBy('member_code')
            ->get()
            ->each(function ($federation) {
                // Ensure a placeholder votingRights relation exists if none found for the year
                if ($federation->votingRights->isEmpty()) {
                    $defaultVotingRight = new FederationVotingRight(['year' => $this->year]);
                    $federation->setRelation('votingRights', collect([$defaultVotingRight]));
                }
            });
    }

    public function editVotingRights(int $federationId): void
    {
        $this->editingFederationId = $federationId;
    }

    public function render()
    {
        return view('livewire.admin.federation-voting-right-manager');
    }
}
