<?php

namespace App\Livewire\Geographic;

use Domain\Geographic\Models\Zone;
use Livewire\Component;

class ZoneSelector extends Component
{
    public $selectedZoneIds = [];
    public $selectedZoneId; // For single selection
    public $availableZones = [];
    public $searchTerm = '';
    public $model;
    public string $label = 'Select Zones';
    public bool $allowMultiple = true;
    public bool $required = false;

    protected $listeners = ['resetZoneSelector' => 'resetSelection'];

    public function mount($model = null, array $selectedZoneIds = [], string $label = 'Select Zones', bool $allowMultiple = true, bool $required = false): void
    {
        $this->model = $model;
        $this->label = $label;
        $this->allowMultiple = $allowMultiple;
        $this->required = $required;

        // Load zones with their district counts
        $this->loadAvailableZones();

        // Set initial selected zones
        if ($model && $model->exists) {
            $zoneIds = $model->zones()->pluck('zones.id')->toArray();
            if ($this->allowMultiple) {
                $this->selectedZoneIds = $zoneIds;
            } else {
                $this->selectedZoneId = ! empty($zoneIds) ? $zoneIds[0] : null;
                $this->selectedZoneIds = $zoneIds;
            }
        } elseif (! empty($selectedZoneIds)) {
            if ($this->allowMultiple) {
                $this->selectedZoneIds = is_array($selectedZoneIds) ? $selectedZoneIds : [$selectedZoneIds];
            } else {
                $this->selectedZoneId = is_array($selectedZoneIds) ? reset($selectedZoneIds) : $selectedZoneIds;
                $this->selectedZoneIds = is_array($selectedZoneIds) ? $selectedZoneIds : [$selectedZoneIds];
            }
        }
    }

    public function updatedSearchTerm()
    {
        $this->loadAvailableZones();
    }

    public function updatedSelectedZoneId()
    {
        if (! $this->allowMultiple) {
            $this->selectedZoneIds = $this->selectedZoneId ? [$this->selectedZoneId] : [];
            $this->dispatch('zone-selection-updated', $this->selectedZoneIds);
        }
    }

    public function updatedSelectedZoneIds()
    {
        if ($this->allowMultiple) {
            // Ensure selectedZoneIds is always an array for multiple selection
            if (! is_array($this->selectedZoneIds)) {
                $this->selectedZoneIds = $this->selectedZoneIds ? [$this->selectedZoneIds] : [];
            }
            $this->dispatch('zone-selection-updated', $this->selectedZoneIds);
        }
    }

    public function resetSelection()
    {
        $this->selectedZoneIds = [];
        $this->searchTerm = '';
        $this->loadAvailableZones();
    }

    public function getSelectedZones()
    {
        return Zone::whereIn('id', $this->selectedZoneIds)->get();
    }

    private function loadAvailableZones()
    {
        $query = Zone::query()
            ->where('is_active', true)
            ->withCount(['districts', 'entities', 'federations', 'individuals']);

        if (! empty($this->searchTerm)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->searchTerm . '%')
                    ->orWhere('code', 'like', '%' . $this->searchTerm . '%');
            });
        }

        $this->availableZones = $query->orderBy('name')->get();
    }

    public function render()
    {
        return view('livewire.geographic.zone-selector');
    }
}
