<?php

declare(strict_types=1);

namespace App\Livewire\Public;

use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\States\ArchiveEventState;
use Domain\EvtEvents\States\CanceledEventState;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class EventShow extends Component
{
    public Event $event;

    public function mount(Event $event): void
    {
        if (! $event->is_visible) {
            abort(404);
        }

        if (in_array($event->status_class, [ArchiveEventState::class, CanceledEventState::class], true)) {
            abort(404);
        }

        $event->load([
            'competition.sport',
            'competitions.sport',
            'competitions.venueCountry',
            'competitions.disciplineTemplate.disciplines',
            'venueCountry',
            'venueDistrict',
            'pricing',
            'organizer.organizable.district',
            'organizer.organizable.media',
            'organizerDetails',
            'technicalDelegate.individual',
            'chiefJudge.individual',
            'competitionDirector.individual',
            'media',
        ]);

        $this->event = $event;
    }

    public function render(): View
    {
        return view('livewire.public.event-show')
            ->layout('layouts.public', [
                'title' => $this->event->name,
                'currentPage' => 'events',
            ]);
    }
}
