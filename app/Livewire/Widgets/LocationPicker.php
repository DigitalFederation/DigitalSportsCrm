<?php

namespace App\Livewire\Widgets;

use Illuminate\Support\Facades\Validator;
use Livewire\Component;

class LocationPicker extends Component
{
    /**
     * The latitude value
     */
    public ?float $latitude = null;

    /**
     * The longitude value
     */
    public ?float $longitude = null;

    /**
     * Whether the map modal is open
     */
    public bool $isModalOpen = false;

    /**
     * Input field names for form binding
     */
    public string $latFieldName = 'lat';
    public string $lngFieldName = 'lng';

    /**
     * Mount the component with optional initial values
     */
    public function mount(?float $initialLat = null, ?float $initialLng = null, ?string $latField = null, ?string $lngField = null)
    {
        $this->latitude = $initialLat;
        $this->longitude = $initialLng;

        if ($latField) {
            $this->latFieldName = $latField;
        }
        if ($lngField) {
            $this->lngFieldName = $lngField;
        }
    }

    /**
     * Handle location selection from the map
     */
    public function handleLocationSelected($lat, $lng)
    {
        // Validate the coordinates
        $validator = Validator::make(
            ['latitude' => $lat, 'longitude' => $lng],
            [
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
            ]
        );

        if ($validator->fails()) {
            $this->addError('location', 'Invalid coordinates selected');

            return;
        }

        $this->latitude = (float) $lat;
        $this->longitude = (float) $lng;

        // Emit event for parent components
        $this->dispatch('coordinates-updated', [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ]);
    }

    /**
     * Open the map modal
     */
    public function openModal()
    {
        $this->isModalOpen = true;
        $this->dispatch('openMapModal');
    }

    /**
     * Close the map modal
     */
    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->dispatch('modalClosed');
    }

    /**
     * Clear the selected location
     */
    public function clearLocation()
    {
        $this->latitude = null;
        $this->longitude = null;

        $this->dispatch('coordinates-updated', [
            'latitude' => null,
            'longitude' => null,
        ]);
    }

    /**
     * Render the component
     */
    public function render()
    {
        return view('livewire.widgets.location-picker');
    }
}
