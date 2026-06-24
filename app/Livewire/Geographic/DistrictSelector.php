<?php

namespace App\Livewire\Geographic;

use App\Models\Country;
use Domain\Geographic\Models\District;
use Livewire\Component;

class DistrictSelector extends Component
{
    public $selectedDistrictId;
    public $selectedCountryId;
    public $availableDistricts = [];
    public $availableCountries = [];
    public string $searchTerm = '';
    public $model;
    public string $label = 'Select District';
    public bool $required = false;
    public bool $showCountrySelector = true;
    public $zoneId = null;
    public bool $listenToZoneChanges = false;

    protected $listeners = ['resetDistrictSelector' => 'resetSelection', 'zone-selection-updated' => 'onZoneChanged'];

    public function mount($model = null, $selectedDistrictId = null, string $label = 'Select District', bool $required = false, bool $showCountrySelector = true, $zoneId = null, bool $listenToZoneChanges = false): void
    {
        $this->model = $model;
        $this->label = $label;
        $this->required = $required;
        $this->showCountrySelector = $showCountrySelector;
        $this->zoneId = $zoneId;
        $this->listenToZoneChanges = $listenToZoneChanges || $zoneId !== null;

        // Load available countries
        $this->loadAvailableCountries();

        // Set initial district if provided
        if ($model && $model->exists && $model->district_id) {
            $this->selectedDistrictId = $model->district_id;
            $district = District::find($this->selectedDistrictId);
            if ($district) {
                $this->selectedCountryId = $district->country_id;
                $this->loadAvailableDistricts();
            }
        } elseif ($selectedDistrictId) {
            $this->selectedDistrictId = $selectedDistrictId;
            $district = District::find($this->selectedDistrictId);
            if ($district) {
                $this->selectedCountryId = $district->country_id;
                $this->loadAvailableDistricts();
            }
        }
    }

    public function onZoneChanged($zoneIds)
    {
        // Only react to zone changes if this component is configured to filter by zone
        if (! $this->listenToZoneChanges) {
            return;
        }

        // When zone changes, update the filter and reset district selection
        $this->zoneId = is_array($zoneIds) ? ($zoneIds[0] ?? null) : $zoneIds;
        $this->selectedDistrictId = null;
        $this->loadAvailableDistricts();
    }

    public function updatedSelectedCountryId()
    {
        $this->selectedDistrictId = null;
        $this->loadAvailableDistricts();
        $this->dispatch('district-country-changed', $this->selectedCountryId);
    }

    public function updatedSelectedDistrictId()
    {
        $this->dispatch('district-selection-updated', $this->selectedDistrictId);
    }

    public function updatedSearchTerm()
    {
        $this->loadAvailableDistricts();
    }

    public function resetSelection()
    {
        $this->selectedDistrictId = null;
        $this->selectedCountryId = null;
        $this->searchTerm = '';
        $this->availableDistricts = [];
    }

    public function getSelectedDistrict()
    {
        return $this->selectedDistrictId ? District::find($this->selectedDistrictId) : null;
    }

    private function loadAvailableCountries()
    {
        $this->availableCountries = Country::whereHas('districts', function ($query) {
            $query->where('is_active', true);
        })->orderBy('name')->get();
    }

    private function loadAvailableDistricts()
    {
        if (! $this->selectedCountryId) {
            $this->availableDistricts = [];

            return;
        }

        $query = District::query()
            ->where('country_id', $this->selectedCountryId)
            ->where('is_active', true)
            ->withCount(['entities', 'federations', 'individuals']);

        // Filter by zone if zoneId is set
        if ($this->zoneId) {
            $query->whereHas('zones', function ($q) {
                $q->where('zones.id', $this->zoneId);
            });
        }

        if (! empty($this->searchTerm)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->searchTerm . '%')
                    ->orWhere('code', 'like', '%' . $this->searchTerm . '%');
            });
        }

        $this->availableDistricts = $query->orderBy('name')->get();
    }

    public function render()
    {
        return view('livewire.geographic.district-selector');
    }
}
