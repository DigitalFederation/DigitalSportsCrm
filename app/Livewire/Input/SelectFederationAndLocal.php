<?php

namespace App\Livewire\Input;

use Domain\Federations\Models\Federation;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class SelectFederationAndLocal extends Component
{
    public Collection $federations;

    public Collection $localFederations;

    public ?int $selectedMainFederation;

    public ?int $selectedLocalFederation;

    public bool $checkMainFederation = false;

    public function render(): View
    {
        if (! empty($this->selectedMainFederation)) {
            $this->localFederations = $this->findLocals();
        } else {
            $this->selectedLocalFederation = null;
            $this->localFederations = new Collection;
        }

        return view('livewire.input.select-federation-and-local');
    }

    public function findLocals(): Collection
    {
        return Federation::select('id', 'name')->where('parent_id', $this->selectedMainFederation)->get();
    }
}
