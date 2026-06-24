<?php

namespace App\Livewire\Admin;

use Domain\Federations\Models\Federation;
use Domain\Licenses\Models\License;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class FederationLicenseManager extends Component
{
    /**
     * The federation being managed
     */
    public Federation $federation;

    /**
     * Array of selected license IDs
     */
    public $selectedLicenses = [];

    /**
     * Available licenses grouped by committee
     */
    public $availableLicenses = [];

    /**
     * Search term for filtering licenses
     */
    public $searchTerm = '';

    /**
     * Selected committee filter
     */
    public $selectedCommittee = '';

    /**
     * Success message
     */
    public $successMessage = '';

    /**
     * Mount the component with the federation
     */
    public function mount(Federation $federation)
    {
        $this->federation = $federation;
        $this->selectedLicenses = $federation->licenses()->pluck('license_id')->map(fn ($id) => (string) $id)->toArray();
        $this->loadAvailableLicenses();
    }

    /**
     * Load all available licenses grouped by committee
     */
    public function loadAvailableLicenses()
    {
        $query = License::query()
            ->with(['committee', 'type', 'professionalRole', 'sport'])
            ->orderBy('committee_id')
            ->orderBy('name');

        // Apply search filter
        if (! empty($this->searchTerm)) {
            $search = '%' . $this->searchTerm . '%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                    ->orWhere('license_code', 'like', $search);
            });
        }

        // Apply committee filter
        if (! empty($this->selectedCommittee)) {
            $query->where('committee_id', $this->selectedCommittee);
        }

        $licenses = $query->get();

        // Group by committee name for display
        $this->availableLicenses = $licenses->groupBy(function ($license) {
            return $license->committee ? $license->committee->name : 'No Committee';
        })->toArray();
    }

    /**
     * Update the federation's licenses
     */
    public function updateLicenses()
    {
        // Convert string IDs back to integers for database
        $licenseIds = collect($this->selectedLicenses)
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->toArray();

        // Sync licenses with the federation
        $this->federation->licenses()->sync($licenseIds);

        // Clear relevant caches
        $this->clearEntityLicenseCaches();

        // Show success message
        $this->successMessage = __('licenses.Licenses updated successfully!');

        // Dispatch event for other components
        $this->dispatch('licenses-updated');

        // Clear message after 3 seconds
        $this->dispatch('clear-message');
    }

    /**
     * Clear license caches for all entities in this federation
     */
    private function clearEntityLicenseCaches()
    {
        $entityIds = $this->federation->entities()->pluck('entity.id');

        foreach ($entityIds as $entityId) {
            // Clear caches for all committee types
            $committees = ['sport', 'diving', 'scientific', 'technical'];
            foreach ($committees as $committee) {
                Cache::forget("licenses_for_type_{$committee}_entity_{$entityId}");
            }
        }
    }

    /**
     * Toggle all licenses in a committee group
     */
    public function toggleCommitteeGroup($committeeName)
    {
        if (! isset($this->availableLicenses[$committeeName])) {
            return;
        }

        $groupLicenseIds = collect($this->availableLicenses[$committeeName])
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->toArray();

        $allSelected = collect($groupLicenseIds)->every(fn ($id) => in_array($id, $this->selectedLicenses));

        if ($allSelected) {
            // Remove all from selection
            $this->selectedLicenses = array_diff($this->selectedLicenses, $groupLicenseIds);
        } else {
            // Add all to selection
            $this->selectedLicenses = array_unique(array_merge($this->selectedLicenses, $groupLicenseIds));
        }
    }

    /**
     * Check if all licenses in a committee group are selected
     */
    public function isGroupSelected($committeeName)
    {
        if (! isset($this->availableLicenses[$committeeName])) {
            return false;
        }

        $groupLicenseIds = collect($this->availableLicenses[$committeeName])
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->toArray();

        return collect($groupLicenseIds)->every(fn ($id) => in_array($id, $this->selectedLicenses));
    }

    /**
     * Get count of selected licenses in a committee group
     */
    public function getGroupSelectedCount($committeeName)
    {
        if (! isset($this->availableLicenses[$committeeName])) {
            return 0;
        }

        $groupLicenseIds = collect($this->availableLicenses[$committeeName])
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->toArray();

        return collect($groupLicenseIds)->filter(fn ($id) => in_array($id, $this->selectedLicenses))->count();
    }

    /**
     * Clear success message
     */
    public function clearMessage()
    {
        $this->successMessage = '';
    }

    /**
     * Get unique committees for filter dropdown
     */
    public function getCommitteesProperty()
    {
        return \App\Models\Committee::orderBy('name')->get();
    }

    /**
     * Watch for search and filter changes
     */
    public function updatedSearchTerm()
    {
        $this->loadAvailableLicenses();
    }

    public function updatedSelectedCommittee()
    {
        $this->loadAvailableLicenses();
    }

    public function render()
    {
        return view('livewire.admin.federation-license-manager');
    }
}
