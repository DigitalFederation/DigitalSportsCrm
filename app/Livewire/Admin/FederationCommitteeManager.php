<?php

namespace App\Livewire\Admin;

use App\Models\Committee;
use Domain\Federations\Models\Federation;
use Livewire\Component;

class FederationCommitteeManager extends Component
{
    /**
     * The federation being managed
     */
    public Federation $federation;

    /**
     * Array of selected committee IDs
     */
    public array $selectedCommittees = [];

    /**
     * Success message
     */
    public string $successMessage = '';

    /**
     * Mount the component with the federation
     */
    public function mount(Federation $federation): void
    {
        $this->federation = $federation;
        $this->selectedCommittees = $federation->committees()
            ->pluck('committee.id')
            ->map(fn ($id) => (string) $id)
            ->toArray();
    }

    /**
     * Get all available committees
     */
    public function getCommitteesProperty()
    {
        return Committee::whereNotNull('code')
            ->whereRaw('LENGTH(code) <= 20') // Filter out test/random committees
            ->orderByRaw("FIELD(code, 'SPORT', 'DIVINGSERVICES', 'DIVING', 'SCIENTIFIC')")
            ->get();
    }

    /**
     * Update the federation's committees
     */
    public function updateCommittees(): void
    {
        // Convert string IDs back to integers for database
        $committeeIds = collect($this->selectedCommittees)
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->toArray();

        // Sync committees with the federation
        $this->federation->committees()->sync($committeeIds);

        // Show success message
        $this->successMessage = __('federation.committees_updated');

        // Dispatch event for other components
        $this->dispatch('committees-updated');

        // Clear message after 3 seconds
        $this->dispatch('clear-message');
    }

    /**
     * Clear success message
     */
    public function clearMessage(): void
    {
        $this->successMessage = '';
    }

    /**
     * Toggle all committees
     */
    public function toggleAll(): void
    {
        $allCommitteeIds = $this->committees->pluck('id')->map(fn ($id) => (string) $id)->toArray();

        if (count($this->selectedCommittees) === count($allCommitteeIds)) {
            $this->selectedCommittees = [];
        } else {
            $this->selectedCommittees = $allCommitteeIds;
        }
    }

    public function render()
    {
        return view('livewire.admin.federation-committee-manager');
    }
}
