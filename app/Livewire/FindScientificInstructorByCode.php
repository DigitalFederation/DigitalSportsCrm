<?php

namespace App\Livewire;

use Domain\Entities\DataTransferObject\EntityProfessionalRoleData;
use Domain\Entities\States\RejectedEntityProfessionalRoleState;
use Domain\Individuals\Actions\AssociateProfessionalRoleToEntityAction;
use Domain\Individuals\Models\Individual;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class FindScientificInstructorByCode extends Component
{
    public ?Individual $instructor = null;

    public string $instructorCode = '';

    public Collection $professionalRoles;

    public int $professionalRoleSelected = 0;

    public string $errorMessage = '';

    public function findInstructor()
    {
        if (! empty($this->instructorCode)) {
            $this->instructor = Individual::where('member_code', $this->instructorCode)
                ->IndividualsFromEntity()
                ->whereDoesntHave('professionalRoleEntities', function (Builder $query) {
                    $query->where('entity_id', Auth()->user()->entities->first()->id)
                        ->whereNot('status_class', RejectedEntityProfessionalRoleState::class)
                        ->whereHas('professionalRole', function (Builder $query) {
                            $query->where('id', $this->professionalRoleSelected);
                        });
                })->first();
            if (empty($this->instructor)) {
                $this->errorMessage = 'Instructor not found or already invited';
            } else {
                $this->errorMessage = '';
            }
        }
    }

    public function inviteInstructor()
    {
        if (! empty($this->instructor)) {
            $action = new AssociateProfessionalRoleToEntityAction;
            try {
                $instructor = $action(EntityProfessionalRoleData::fromArray([
                    'entity_id' => Auth()->user()->entities->first()->id,
                    'individual_id' => $this->instructor->id,
                    'professional_role_id' => $this->professionalRoleSelected,
                    'entity_name' => Auth()->user()->entities->first()->name,
                    'individual_name' => $this->instructor->name,
                    'role_name' => $this->professionalRoles->where('id', $this->professionalRoleSelected)->value('name'),
                ]), 'INSTRUCTOR');
            } catch (Exception $e) {
                $this->errorMessage = $e->getMessage();
                Log::error($e->getMessage());
            }

            if (! empty($instructor)) {
                return redirect(request()->header('Referer'));
            } else {
                if (empty($this->errorMessage)) {
                    $this->errorMessage = 'Error inviting instructor';
                }
            }
        }
    }

    public function render(): View
    {
        return view('livewire.find-scientific-instructor-by-code');
    }
}
