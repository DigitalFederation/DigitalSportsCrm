<?php

namespace App\Livewire;

use Domain\Entities\Actions\FindInstructorAction;
use Domain\Entities\DataTransferObject\EntityProfessionalRoleData;
use Domain\Individuals\Actions\AssociateProfessionalRoleToEntityAction;
use Domain\Individuals\Models\Individual;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class FindDivingInstructorByCode extends Component
{
    public ?Individual $instructor = null;

    public string $instructorCode = '';

    public Collection $professionalRoles;

    public int $professionalRoleSelected = 0;

    public string $errorMessage = '';

    public ?int $entityId = null;

    public function mount()
    {
        $this->entityId = Auth::user()->entities()->first()?->id;
        if (! $this->entityId) {
            $this->errorMessage = 'Unable to determine your associated entity.';
            Log::warning('FindDivingInstructorByCode component loaded without a valid entity ID for user: ' . Auth::id());
        }
    }

    public function findInstructor(FindInstructorAction $findInstructorAction)
    {
        if (! empty($this->instructorCode)) {
            $entityFederationId = Auth::user()->entities()->first()->federations()->first()->id;
            $this->instructor = $findInstructorAction->execute(
                $this->instructorCode,
                $this->professionalRoleSelected,
                $entityFederationId);

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
                    'entity_id' => Auth::user()->entities()->first()->id,
                    'individual_id' => $this->instructor->id,
                    'professional_role_id' => $this->professionalRoleSelected,
                    'entity_name' => Auth::user()->entities()->first()->name,
                    'individual_name' => $this->instructor->name,
                    'role_name' => $this->professionalRoles->where('id', $this->professionalRoleSelected)->value('name'),
                ]), 'INSTRUCTOR');
            } catch (Exception $e) {
                $this->errorMessage = $e->getMessage();
                Log::error($e->getMessage());
            }

            if (! empty($instructor)) {
                Session::flash('success', 'Invitation sent with success');

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
        return view('livewire.find-diving-instructor-by-code');
    }
}
