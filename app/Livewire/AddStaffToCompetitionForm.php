<?php

namespace App\Livewire;

use Domain\EvtEvents\Models\Competition;
use Domain\EvtEvents\Models\CompetitionStaff;
use Domain\EvtEvents\Models\Event;
use Domain\Individuals\Models\Individual;
use Livewire\Component;

class AddStaffToCompetitionForm extends Component
{
    public $searchCode = '';

    public $competitionId;

    public $staff = [];

    public $sport_id;

    public $message = '';

    public $confirmingStaffRemoval = false;

    public $staffIdToRemove;

    public function mount($competitionId)
    {
        $this->competitionId = $competitionId;
        $this->loadStaff();
    }

    public function loadStaff()
    {
        $this->staff = Competition::with('competitionStaff.individual')->find($this->competitionId)->competitionStaff;
    }

    public function addStaff($individualId)
    {
        // Check if this individual is already added as a referee
        $existingStaff = CompetitionStaff::where([
            'competition_id' => $this->competitionId,
            'individual_id' => $individualId,
        ])->first();

        if (! $existingStaff) {
            // Logic to add referee
            CompetitionStaff::create([
                'competition_id' => $this->competitionId,
                'individual_id' => $individualId,
            ]);
        } else {
            $this->message = 'Staff already added';
        }

        $this->loadStaff();
    }

    public function confirmStaffRemoval($staffId)
    {
        $this->confirmingStaffRemoval = true;
        $this->staffIdToRemove = $staffId;
    }

    public function removeStaff($staffId)
    {
        CompetitionStaff::where('id', $staffId)->delete();
        $this->loadStaff();
        $this->message = 'Referee removed successfully';
        $this->confirmingStaffRemoval = false;
    }

    public function resetMessage()
    {
        $this->message = '';
    }

    public function render()
    {
        $individuals = collect();

        // If user is not of group ADMIN, then get the federation of the user
        if (! auth()->user()->group->code == 'ADMIN') {
            $federationId = auth()->user()->federations()->first();
        } else {
            // Get Organizer or Winner Federation
            $event = Event::select('id')->whereHas('competitions', function ($query) {
                $query->where('id', $this->competitionId);
            })->with('organizer')->first();

            $federationId = $event->organizer->organizable_id ?? null;
        }

        if (strlen($this->searchCode) >= 3) { // Adjust the number as per your requirements
            $individuals = Individual::where('member_code', 'like', '%'.$this->searchCode.'%')
                ->fromFederation($federationId)
                ->get();
        }

        return view('livewire.add-staff-to-competition-form', [
            'individuals' => $individuals,
        ]);
    }
}
