<?php

namespace App\Livewire;

use Domain\EvtEvents\Models\EventPin;
use Livewire\Component;

class AntiDopingPinManager extends Component
{
    public $pin;

    public $pins;

    public function mount()
    {
        $this->pins = EventPin::all(); // Fetch existing PINs when the component is mounted
    }

    protected function rules()
    {
        return [
            'pin' => 'required|string|unique:evt_event_pins,pin',
        ];
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function addPin()
    {
        $validatedData = $this->validate();
        EventPin::create($validatedData);
        $this->reset('pin');

        $this->dispatch('pinAdded'); // Emit an event to notify the modal to close or reset
        session()->flash('success', __('PIN created successfully.'));
    }

    public function removePin($pinId)
    {
        $eventPin = EventPin::findOrFail($pinId);
        $eventPin->delete();
        $this->pins = EventPin::all(); // Update the list of pins

        session()->flash('success', __('PIN removed successfully.'));
    }

    public function render()
    {
        return view('livewire.anti-doping-pin-manager');
    }
}
