<?php

namespace App\Livewire\Admin;

use Domain\Federations\Models\Federation;
use Livewire\Component;

class IndividualFederationSelector extends Component
{
    public $mainFederationId;
    public $localFederationIds = [];
    public $individual;
    public $availableMainFederations;
    public $availableLocalFederations = [];
    public $selectedFederationIds = [];
    public $isFederationFlow = false;

    public function mount($individual = null)
    {
        $this->individual = $individual;
        $this->isFederationFlow = auth()->user()->group->code === 'FEDERATION';

        if ($this->isFederationFlow) {
            $this->mainFederationId = auth()->user()->federations()->first()->id;
            $this->availableLocalFederations = Federation::where('parent_id', $this->mainFederationId)->get();
        } else {
            $this->availableMainFederations = Federation::whereNull('parent_id')->get();
        }

        if ($individual) {
            if ($this->isFederationFlow) {
                // Set selected local federations for federation flow
                $this->localFederationIds = $individual->federations()
                    ->whereNotNull('federation.parent_id')
                    ->pluck('federation.id')
                    ->toArray();
            } else {
                // Set initial values for international flow
                $mainFed = $individual->federations()->whereNull('federation.parent_id')->first();
                if ($mainFed) {
                    $this->mainFederationId = $mainFed->id;
                    $this->updateLocalFederations();
                    $this->localFederationIds = $individual->federations()
                        ->whereNotNull('federation.parent_id')
                        ->pluck('federation.id')
                        ->toArray();
                }
            }
            $this->updateSelectedFederationIds();
        }
    }

    public function updatedMainFederationId()
    {
        $this->localFederationIds = []; // Reset local selections when main federation changes
        $this->updateLocalFederations();
        $this->updateSelectedFederationIds(); // Update the combined IDs
    }

    public function updatedLocalFederationIds()
    {
        $this->updateSelectedFederationIds(); // Update when local federations change
    }

    private function updateSelectedFederationIds()
    {
        $this->selectedFederationIds = $this->getAllSelectedFederationIds();
    }

    public function updateLocalFederations()
    {
        if ($this->mainFederationId) {
            $this->availableLocalFederations = Federation::where('parent_id', $this->mainFederationId)->get();
        } else {
            $this->availableLocalFederations = [];
        }
    }

    // Add this method to combine federation IDs
    public function getAllSelectedFederationIds(): array
    {
        $ids = [];
        if ($this->mainFederationId) {
            $ids[] = $this->mainFederationId;
        }
        if (! empty($this->localFederationIds)) {
            $ids = array_merge($ids, $this->localFederationIds);
        }

        return array_unique($ids);
    }

    public function render()
    {
        return view('livewire.admin.individual-federation-selector', [
            'selectedFederationIds' => $this->selectedFederationIds,
            'isFederationFlow' => $this->isFederationFlow,
        ]);
    }
}
