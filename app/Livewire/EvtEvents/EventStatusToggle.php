<?php

namespace App\Livewire\EvtEvents;

use Domain\EvtEvents\Models\Event;
use Filament\Notifications\Notification;
use Livewire\Component;

class EventStatusToggle extends Component
{
    public Event $event;
    public string $status_class;
    public $availableStates;

    public function mount(Event $event)
    {
        $this->event = $event;
        $this->status_class = $event->status_class;
        $this->availableStates = Event::availableStates();
    }

    public function updatedStatusClass()
    {
        $this->event->update([
            'status_class' => $this->status_class,
        ]);

        Notification::make()
            ->title('Status Updated')
            ->body('The event status has been updated successfully.')
            ->success()
            ->send();
    }

    public function render()
    {
        return view('livewire.evt-events.event-status-toggle', [
            'availableStates' => Event::availableStates(),
        ]);
    }

}
