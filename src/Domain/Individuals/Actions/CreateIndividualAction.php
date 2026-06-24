<?php

namespace Domain\Individuals\Actions;

use App\Jobs\GenerateModelQrCode;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\DataTransferObject\IndividualData;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\States\ActiveIndividualEntityState;
use Domain\Individuals\States\ActiveIndividualFederationState;
use Domain\Individuals\States\PendingIndividualFederationState;
use Domain\Users\Actions\SyncUserRolesAction;
use Illuminate\Support\Facades\Log;
use Support\UtilityMethods;

class CreateIndividualAction
{
    public function __invoke(IndividualData $individualData, bool $addedByFederation = false, bool $addedByEntity = false)
    {
        $syncUserRolesAction = new SyncUserRolesAction;

        try {

            $data = [
                ...(array) $individualData,
                'member_code' => UtilityMethods::generateUniqueIndividualCode(),
            ];

            $individual = Individual::create($data);

            $federationId = $individualData->federation_id;
            // If no federation is provided, get the Special Federation's ID
            if (empty($federationId)) {
                $federationId = $this->getSpecialFederationId();
            }

            // If the individual is added by a federation or entity, the individual is automatically approved
            $this->syncFederations(
                $federationId,
                $individual,
                $addedByFederation,
                $addedByEntity);

            if (! empty($individualData->entity_id)) {
                $this->syncEntities($individualData->entity_id, $individual);
            }

            // Sync zones and districts
            $this->syncZonesAndDistricts($individualData, $individual);

            $syncUserRolesAction->execute($individual->user()->first());

            $this->processAdditionalActions($individual, $individualData);

            // Only dispatch QR code generation if not in testing environment
            if (! app()->environment('testing')) {
                dispatch(new GenerateModelQrCode($individual));
            }

            return $individual;
        } catch (\Exception $e) {
            Log::error('Error creating individual: ' . $e->getMessage());
            throw $e;
        }
    }

    private function syncFederations(
        int|array $federationId,
        Individual $individual,
        bool $addedByFederation,
        bool $addedByEntity = false): void
    {
        if (is_array($federationId)) {
            $federationList = $federationId;
        } else {
            $federationList = [$federationId];
            // Only get parent federation when a single federation ID is passed
            $federationParent = Federation::select('id', 'parent_id')->where('id', $federationId)->value('parent_id');
            if (! empty($federationParent)) {
                $federationList[] = $federationParent;
            }
        }

        // When entity passes an array of federations, it already includes all relevant federations
        // including main and local federations, so we don't need to add parents

        // Determine the appropriate status based on who is adding the individual
        // Active if added by federation OR entity, pending otherwise
        $statusClass = ($addedByFederation || $addedByEntity) ? ActiveIndividualFederationState::class : PendingIndividualFederationState::class;

        foreach ($federationList as $federation) {
            $pivotData = [
                'active' => ($addedByFederation || $addedByEntity) ? 1 : 0,
                'status_class' => $statusClass,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $individual->federations()->attach($federation, $pivotData);
        }
    }

    private function syncEntities(int $entityId, Individual $individual): void
    {
        $individual->entities()->attach($entityId, [
            'status_class' => ActiveIndividualEntityState::class,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Sync individual to entity's local federations (per federation membership rules)
        $entity = Entity::find($entityId);
        if ($entity) {
            (new SyncIndividualLocalFederationsAction)->execute($individual, $entity);
        }
    }

    private function getSpecialFederationId(): int
    {
        return Federation::where('is_default_federation', true)->value('id');
    }

    private function processAdditionalActions(Individual $individual, IndividualData $individualData): void
    {
        if (! empty($individualData->logo)) {
            $individual->addMedia($individualData->logo)->toMediaCollection('profile');
        }
    }

    private function syncZonesAndDistricts(IndividualData $individualData, Individual $individual): void
    {
        // Sync zones if provided (for individuals, only single zone based on requirements)
        if (! empty($individualData->zone_ids) && is_array($individualData->zone_ids)) {
            $individual->zones()->sync($individualData->zone_ids);
        }

        // District is saved directly as it's a foreign key
        // The district_id is already included in the individualData array and saved with the individual
    }
}
