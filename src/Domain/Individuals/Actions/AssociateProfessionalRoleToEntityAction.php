<?php

namespace Domain\Individuals\Actions;

use App\Notifications\InviteIndividualToBeProfessionalRoleNotification;
use Domain\Entities\DataTransferObject\EntityProfessionalRoleData;
use Domain\Entities\Models\Entity;
use Domain\Entities\Models\EntityProfessionalRole;
use Domain\Individuals\Models\Individual;
use Exception;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AssociateProfessionalRoleToEntityAction
{
    /**
     * @throws Exception
     */
    public function __invoke(EntityProfessionalRoleData $data, string $role): EntityProfessionalRole
    {
        try {
            DB::beginTransaction();
            // TODO validar não só individuos
            $canBeInstructor = new DetectIfIndividualIsInstructorAction;

            if ($canBeInstructor(Individual::where('id', $data->individual_id), $data->professional_role_id, $role)) {
                $entity_professional_role = EntityProfessionalRole::firstOrCreate($data->toArray());

                activity(ucfirst(strtolower($data->role_name)).' To Entity')
                    ->performedOn($entity_professional_role)
                    ->event('associate')
                    ->withProperties($entity_professional_role->toArray())
                    ->log(ucfirst(strtolower($data->role_name)).' associated to entity '.$entity_professional_role->entity_name);

                $entity_professional_role->load('professionalRole.committee');

                $this->notifyProfessionalRole($entity_professional_role, $role, $entity_professional_role->professionalRole->committee->code);
            } else {
                DB::rollBack();
                throw new InvalidArgumentException('The individual '.$data->individual_name.' don\'t have the required licenses to be an '.strtolower($data->role_name));
            }
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }

        DB::commit();

        return $entity_professional_role;
    }

    private function notifyProfessionalRole(EntityProfessionalRole $entity_professional_role, string $role, string $committee_code): void
    {
        $individual = Individual::where('id', $entity_professional_role->individual_id)->with('user')->first();
        $entity = Entity::where('id', $entity_professional_role->entity_id)->first();

        $individual->user->notify(new InviteIndividualToBeProfessionalRoleNotification($entity_professional_role, $entity, $role, $committee_code));
    }
}
