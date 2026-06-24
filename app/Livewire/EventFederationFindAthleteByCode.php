<?php

namespace App\Livewire;

use Domain\EvtEvents\Models\Event;
use Domain\Individuals\Models\Individual;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class EventFederationFindAthleteByCode extends Component
{
    public ?Individual $athlete = null;

    public string $athleteCode = '';

    public Event $event;

    public int $disciplineSelected;

    public string $errorMessage = '';

    public function findAthlete(): void
    {
        if (! empty($this->athleteCode)) {
            $this->athlete = Individual::where('member_code', $this->athleteCode)
                ->whereHas('professionalRoles', function (Builder $query) {
                    return $query->where('role', 'ATHLETE');
                })
                ->first();
            if (empty($this->athlete)) {
                $this->errorMessage = 'Athlete not found or already invited';
            } else {
                $this->errorMessage = '';
            }
        }
    }

    public function inviteAthlete()
    {
        try {
            if (! empty($this->athlete)) {
                // TODO Criar primeiro o enroll no evento e campo para escolher a disciplina
                /* $this->event->athleteEnrollments()->create([
                     'enrollment_id' => $this->enroll->id,
                     'event_id' => $this->event->id,
                     'discipline_id' => '$this->disciplineSelected',
                     'federation_id' => auth()->user()->federations()->first()->id,
                 ]);*/
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
            $this->errorMessage = $e->getMessage();
        }

        if (! empty($athlete)) {
            return redirect(request()->header('Referer'));
        } else {
            if (empty($this->errorMessage)) {
                $this->errorMessage = 'Error inviting athlete';
            }
        }
    }

    public function render(): View
    {
        return view('livewire.event-federation-find-athlete-by-code');
    }
}
