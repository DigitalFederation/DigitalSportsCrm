<?php

namespace App\Livewire;

use Carbon\Carbon;
use Domain\EvtEvents\Models\Event;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class EventFederationHostEditForm extends Component
{
    public $event;

    public $status_class;

    public $location;

    public $address;

    public $featured_image;

    public $description;

    public $start_date;

    public $end_date;

    public $status_class_selected = '';

    public function mount()
    {
        $this->location = $this->event->location;
        $this->address = $this->event->address;
        $this->status_class_selected = $this->event->status_class;
        $this->start_date = Carbon::parse($this->event->start_date)->format('Y-m-d');
        $this->end_date = Carbon::parse($this->event->end_date)->format('Y-m-d');
    }

    public function submitForm()
    {
        // Validation
        $this->validate([
            'location' => 'nullable|string',
            'address' => 'nullable|string',
            'start_date' => "nullable|date|after_or_equal:{$this->event->start_date}",
            'end_date' => "nullable|date|before_or_equal:{$this->event->end_date}",
            'status_class_selected' => 'required|string',
        ]);

        $data = [
            'location' => $this->location,
            'address' => $this->address,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status_class' => $this->status_class_selected,
        ];

        try {
            DB::beginTransaction();

            Event::find($this->event->id)->update($data);

            if (isset($this->featured_image)) {
                $this->event->clearMediaCollection('featured-image');
                $this->event->addMedia($this->featured_image)->toMediaCollection('featured-image', 'public');
            }

            DB::commit();

            return redirect()->route('federation.evt-events.events.index')->with('success', 'Event successfully updated');
        } catch (Exception $e) {
            DB::rollback();
            Log::error($e->getMessage());

            return redirect()->route('federation.evt-events.events.index')->with('error', "Error: {$e->getMessage()}");
        }
    }

    public function render()
    {
        return view('livewire.event-federation-host-edit-form');
    }
}
