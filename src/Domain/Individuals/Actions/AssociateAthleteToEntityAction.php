<?php

namespace Domain\Individuals\Actions;

use App\Notifications\InviteIndividualToBeProfessionalRoleNotification;
use Domain\Entities\DataTransferObject\EntityAthleteData;
use Domain\Entities\Models\Entity;
use Domain\Entities\Models\EntityAthlete;
use Domain\Entities\States\ActiveEntityProfessionalRoleState;
use Domain\Entities\States\PendingEntityProfessionalRoleState;
use Domain\Individuals\Models\Individual;
use Exception;
use Illuminate\Support\Facades\DB;

class AssociateAthleteToEntityAction
{
    /**
     * @throws Exception
     */
    public function __invoke(EntityAthleteData $data): EntityAthlete
    {
        try {
            DB::beginTransaction();

            $individualCanBeAthlete = new DetectIfIndividualCanBeAthleteAction;

            if ($individualCanBeAthlete($data->individual_id, $data->sport_id)) {
                // Safety check: ensure athlete is not already at another entity for this sport
                if ($this->hasActiveSportAssociation($data->individual_id, $data->sport_id, $data->entity_id)) {
                    throw new Exception(__('athletes.already_associated_sport'));
                }

                $entity_athlete = EntityAthlete::firstOrCreate($data->toArray());

                activity('Athlete To Entity')
                    ->performedOn($entity_athlete)
                    ->event('associate')
                    ->withProperties($entity_athlete->toArray())
                    ->log('Athlete associated to entity '.$entity_athlete->entity_name);

                $this->notifyProfessionalRole($entity_athlete);
            } else {
                DB::rollBack();
                throw new Exception('The individual '.$data->individual_name.' is missing the required license to be invited');
            }
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }

        DB::commit();

        return $entity_athlete;
    }

    private function notifyProfessionalRole(EntityAthlete $entity_athlete): void
    {
        $individual = Individual::where('id', $entity_athlete->individual_id)->with('user')->first();
        $entity = Entity::where('id', $entity_athlete->entity_id)->first();

        $individual->user->notify(new InviteIndividualToBeProfessionalRoleNotification($entity_athlete, $entity, 'athlete', 'sport'));
    }

    /**
     * Check if an individual already has an active or pending athlete association
     * for the given sport at a DIFFERENT entity.
     */
    private function hasActiveSportAssociation(string $individualId, int $sportId, int $excludeEntityId): bool
    {
        return EntityAthlete::where('individual_id', $individualId)
            ->where('sport_id', $sportId)
            ->where('entity_id', '!=', $excludeEntityId)
            ->whereIn('status_class', [
                ActiveEntityProfessionalRoleState::class,
                PendingEntityProfessionalRoleState::class,
            ])
            ->exists();
    }
}
