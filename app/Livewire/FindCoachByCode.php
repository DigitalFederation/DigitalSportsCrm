<?php

namespace App\Livewire;

use Domain\Entities\DataTransferObject\EntityProfessionalRoleData;
use Domain\Entities\States\RejectedEntityProfessionalRoleState;
use Domain\Individuals\Actions\AssociateProfessionalRoleToEntityAction;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Licenses\Models\License;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class FindCoachByCode extends Component
{
    public ?Individual $coach = null;

    public string $coachCode = '';

    public Collection $licenses;

    public int $licenseSelected = 0;

    public string $errorMessage = '';

    public function findCoach()
    {
        if (! empty($this->coachCode)) {
            $this->coach = Individual::where('member_code', $this->coachCode)
                ->IndividualsFromEntity()
                ->whereDoesntHave('professionalRoleEntities', function (Builder $query) {
                    $query->where('entity_id', Auth()->user()->entities()->first()->id)
                        ->whereNot('status_class', RejectedEntityProfessionalRoleState::class)
                        ->whereHas('professionalRole', function (Builder $query) {
                            $query->whereHas('licenses', function (Builder $query) {
                                $query->where('id', $this->licenseSelected);
                            });
                        });
                })->first();
            if (empty($this->coach)) {
                $this->errorMessage = 'Coach not found or already invited';
            } else {
                $this->errorMessage = '';
            }
        }
    }

    public function inviteCoach()
    {
        if (! empty($this->coach)) {
            $action = new AssociateProfessionalRoleToEntityAction;
            try {
                $coach = $action(EntityProfessionalRoleData::fromArray([
                    'entity_id' => Auth()->user()->entities()->first()->id,
                    'individual_id' => $this->coach->id,
                    'professional_role_id' => License::where('id', $this->licenseSelected)->first()->professional_role_id,
                    'entity_name' => Auth()->user()->entities()->first()->name,
                    'individual_name' => $this->coach->name,
                    'role_name' => ProfessionalRole::whereHas('licenses', function (Builder $query) {
                        $query->where('id', $this->licenseSelected);
                    })->first()->name,
                ]), 'COACH');
            } catch (Exception $e) {
                Log::error($e->getMessage());
                $this->errorMessage = $e->getMessage();
            }

            if (! empty($coach)) {
                return redirect(request()->header('Referer'));
            } else {
                if (empty($this->errorMessage)) {
                    $this->errorMessage = 'Error inviting coach';
                }
            }
        }
    }

    public function render(): View
    {
        return view('livewire.find-coach-by-code');
    }
}
