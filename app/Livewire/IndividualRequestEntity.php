<?php

namespace App\Livewire;

use Domain\Entities\Models\Entity;
use Domain\Entities\States\ActiveEntityFederationState;
use Domain\Geographic\Models\District;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class IndividualRequestEntity extends Component
{
    public ?int $districtId = null;

    public ?int $entitySelected = null;

    public function updatedDistrictId(): void
    {
        $this->entitySelected = null;
    }

    #[Computed]
    public function districts(): Collection
    {
        return District::query()
            ->active()
            ->whereHas('entities', function (Builder $query) {
                $query->filterAffiliationStatus('active');
            })
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function entities(): Collection
    {
        if (! $this->districtId) {
            return collect();
        }

        $userIndividualId = auth()->user()->individuals()->first()?->id;

        if (! $userIndividualId) {
            return collect();
        }

        return Entity::query()
            ->filterAffiliationStatus('active')
            ->filterByDistrict($this->districtId)
            ->whereHas('federations', function (Builder $query) {
                $query->where('status_class', ActiveEntityFederationState::class);
            })
            ->whereDoesntHave('individuals', function (Builder $query) use ($userIndividualId) {
                $query->where('individual_id', $userIndividualId)
                    ->where('status_class', \Domain\Individuals\States\ActiveIndividualEntityState::class);
            })
            ->with([
                'individualEntities' => function ($q) use ($userIndividualId) {
                    $q->where('individual_id', $userIndividualId);
                },
            ])
            ->orderBy('name')
            ->get();
    }

    public function render(): View
    {
        return view('livewire.individual-request-entity');
    }
}
