<?php

namespace App\Livewire\Geographic;

use Domain\Geographic\Enums\TerritorySelection;
use Domain\Geographic\Models\District;
use Domain\Geographic\Models\Zone;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class AdministrativeAreaSelector extends Component
{
    public int $countryId;

    public ?int $selectedZoneId = null;

    public int|string|null $selectedDistrictId = null;

    /** @var Collection<int, Zone> */
    public Collection $availableZones;

    /** @var Collection<int, District> */
    public Collection $availableDistricts;

    public bool $required = false;

    public bool $allowNotListed = true;

    public function mount(
        int $countryId,
        int|string|null $selectedDistrictId = null,
        ?int $selectedZoneId = null,
        bool $required = false,
        bool $allowNotListed = true
    ): void {
        $this->countryId = $countryId;
        $this->required = $required;
        $this->allowNotListed = $allowNotListed;
        $this->selectedZoneId = $selectedZoneId;
        $this->selectedDistrictId = $selectedDistrictId;

        $this->loadZones();

        if (is_numeric($selectedDistrictId)) {
            $district = District::query()
                ->where('country_id', $this->countryId)
                ->find((int) $selectedDistrictId);

            if ($district !== null) {
                $this->selectedZoneId = $district->administrative_zone_id;
            } else {
                $this->selectedDistrictId = null;
            }
        }

        $this->loadDistricts();
    }

    public function updatedSelectedZoneId(): void
    {
        $this->selectedDistrictId = null;
        $this->loadDistricts();
    }

    private function loadZones(): void
    {
        $this->availableZones = Zone::query()
            ->administrativeLevelOne()
            ->where('country_id', $this->countryId)
            ->active()
            ->orderBy('name')
            ->get();
    }

    private function loadDistricts(): void
    {
        $this->availableDistricts = District::query()
            ->where('country_id', $this->countryId)
            ->active()
            ->when(
                $this->availableZones->isNotEmpty(),
                fn ($query) => $query->where('administrative_zone_id', $this->selectedZoneId)
            )
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'administrative_zone_id']);
    }

    public function render()
    {
        return view('livewire.geographic.administrative-area-selector', [
            'notListedValue' => TerritorySelection::NOT_LISTED->value,
        ]);
    }
}
