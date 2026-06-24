<?php

namespace App\Livewire;

use Domain\EvtEvents\Models\Competition;
use Domain\EvtEvents\Models\CompetitionReferee;
use Domain\Individuals\Models\Individual;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class AddRefereeToCompetitionForm extends Component
{
    public $searchCode = '';

    public $competitionId;

    public $referees = [];

    public $sport_id;

    public $message = '';

    public $confirmingRefereeRemoval = false;

    public $refereeIdToRemove;

    public function mount($competitionId)
    {
        $this->competitionId = $competitionId;
        $this->loadReferees();
    }

    public function loadReferees()
    {
        $this->referees = Competition::with('referees.individual')->find($this->competitionId)->referees;
    }

    public function addReferee($individualId)
    {
        $alreadyExists = CompetitionReferee::where([
            'competition_id' => $this->competitionId,
            'individual_id' => $individualId,
        ])->exists();

        if ($alreadyExists) {
            $this->message = 'Referee already added';
        } else {
            CompetitionReferee::create([
                'competition_id' => $this->competitionId,
                'individual_id' => $individualId,
            ]);
        }

        $this->loadReferees();
    }

    public function confirmRefereeRemoval($refereeId)
    {
        $this->confirmingRefereeRemoval = true;
        $this->refereeIdToRemove = $refereeId;
    }

    public function removeReferee($refereeId)
    {
        CompetitionReferee::where('id', $refereeId)->delete();
        $this->loadReferees();
        $this->message = 'Referee removed successfully';
        $this->confirmingRefereeRemoval = false;
    }

    public function resetMessage()
    {
        $this->message = '';
    }

    public function render()
    {
        $individuals = collect();

        if (strlen($this->searchCode) >= 3) { // Adjust the number as per your requirements
            $individuals = Individual::where('member_code', 'like', '%'.$this->searchCode.'%')
                ->whereHas('licenses', function (Builder $query) {
                    return $query->whereHas('license', function (Builder $query) {
                        return $query->where('sport_id', $this->sport_id)
                            ->whereHas('professionalRole', function (Builder $query) {
                                return $query->where('role', 'TECHNICAL_OFFICIAL');
                            });
                    });
                })->get();
        }

        return view('livewire.add-referee-to-competition-form', [
            'individuals' => $individuals,
        ]);
    }
}
