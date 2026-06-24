<?php

namespace App\Livewire;

use Domain\Entities\DataTransferObject\EntityAthleteData;
use Domain\Entities\States\ActiveEntityProfessionalRoleState;
use Domain\Entities\States\PendingEntityProfessionalRoleState;
use Domain\Entities\States\RejectedEntityProfessionalRoleState;
use Domain\Individuals\Actions\AssociateAthleteToEntityAction;
use Domain\Individuals\Models\Individual;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Livewire\Component;

class FindAthleteByCode extends Component
{
    public ?Individual $athlete = null;

    public string $athleteCode = '';

    public Collection $sports;

    public int $sportSelected = 0;

    public string $errorMessage = '';

    public ?int $entityId = null;

    public function mount()
    {
        $this->entityId = Auth::user()->entities()->first()?->id;
        if (! $this->entityId) {
            $this->errorMessage = 'Unable to determine your associated entity.';
            Log::warning('FindAthleteByCode component loaded without a valid entity ID for user: ' . Auth::id());
        }
    }

    public function findAthlete()
    {
        if (! empty($this->athleteCode) && $this->sportSelected) {
            $this->athlete = Individual::where('member_code', $this->athleteCode)
                ->IndividualsFromEntity()
                // Check athlete is not already at THIS entity (any status except rejected)
                ->whereDoesntHave('EntityAthletes', function (Builder $query) {
                    $query->where('entity_id', Auth::user()->entities()->first()->id)
                        ->whereNot('status_class', RejectedEntityProfessionalRoleState::class);
                })
                // Check athlete is not active/pending at ANY entity for this sport (global rule)
                ->whereDoesntHave('EntityAthletes', function (Builder $query) {
                    $query->where('sport_id', $this->sportSelected)
                        ->whereIn('status_class', [
                            ActiveEntityProfessionalRoleState::class,
                            PendingEntityProfessionalRoleState::class,
                        ]);
                })
                ->first();
            if (empty($this->athlete)) {
                $this->errorMessage = __('athletes.not_found_or_unavailable');
            } else {
                $this->errorMessage = '';
            }
        }
    }

    public function inviteAthlete()
    {
        if (! empty($this->athlete)) {
            $action = new AssociateAthleteToEntityAction;
            try {
                $athlete = $action(EntityAthleteData::fromArray([
                    'entity_id' => Auth::user()->entities()->first()->id,
                    'individual_id' => $this->athlete->id,
                    'sport_id' => $this->sportSelected,
                    'entity_name' => Auth::user()->entities()->first()->name,
                    'individual_name' => $this->athlete->name,
                    'sport_name' => $this->sports->where('id', $this->sportSelected)->value('name'),
                ]));
            } catch (InvalidArgumentException $e) {
                $this->errorMessage = $e->getMessage();
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
    }

    public function render()
    {
        return view('livewire.find-athlete-by-code');
    }
}
