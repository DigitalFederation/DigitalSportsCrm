<?php

namespace Domain\Individuals\Actions;

use Domain\Federations\Models\Federation;
use Domain\Individuals\DataTransferObject\IndividualData;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\States\PendingIndividualEntityState;
use Domain\Individuals\States\PendingIndividualFederationState;
use Exception;

class EditIndividualAction
{
    /**
     * @throws Exception
     */
    public function __invoke(
        IndividualData $individualData,
        string $id)
    {
        $individualDataArray = (array) $individualData;
        unset($individualDataArray['email']);

        $individual = Individual::findOrFail($id);
        $updated = $individual->update($individualDataArray);

        if (! empty($individualDataArray['logo'])) {
            $individual->clearMediaCollection('profile');
            $individual->addMedia($individualDataArray['logo'])->toMediaCollection('profile');
        }

        // Update professional roles
        if (isset($individualData->professional_role_ids)) {
            $individual->professionalRoles()->sync($individualData->professional_role_ids);
        }

        // Handle federation association if federation_id is present
        if ($individualData->federation_id !== null) {
            $this->handleFederationAssociation($individualData, $individual);
        }

        // Handle zones and districts
        $this->handleZonesAndDistricts($individualData, $individual);

        return $updated;
    }

    private function SyncFederations(int $federationId, Individual $individual): void
    {
        $federationList = [$federationId];
        $federationParent = Federation::select('id', 'parent_id')->where('id', $federationId)->value('parent_id');

        if (! empty($federationParent)) {
            $federationList[] = $federationParent;
        }
        foreach ($federationList as $federation) {
            $individual->federations()->attach($federation, ['active' => 0, 'created_at' => now(), 'updated_at' => now()]);
        }
    }

    private function SyncEntities(int $entityId, Individual $individual): void
    {
        $individual->entities()->attach($entityId, ['status_class' => PendingIndividualEntityState::class, 'created_at' => now(), 'updated_at' => now()]);
    }

    private function handleFederationAssociation(IndividualData $data, Individual $individual): void
    {
        // Get current federations
        $currentFederations = $individual->federations->pluck('id')->toArray();

        // Get new federations from request (ensure it's an array)
        $newFederations = is_array($data->federation_id) ? $data->federation_id : [$data->federation_id];
        $newFederations = array_filter($newFederations); // Remove null/empty values

        // Get existing federation relationships to preserve states
        $existingFederationData = $individual->individualFederations()
            ->whereIn('federation_id', $newFederations)
            ->get()
            ->keyBy('federation_id');

        // Remove old associations that are not in the new list
        $individual->federations()->detach(
            array_diff($currentFederations, $newFederations)
        );

        // Add new associations or update existing ones
        foreach ($newFederations as $federationId) {
            $existingFederation = $existingFederationData->get($federationId);

            // Prepare federation data
            $federationData = [
                'status_class' => $existingFederation?->status_class ?? PendingIndividualFederationState::class,
                'active' => $existingFederation?->active ?? 0,
                'created_at' => $existingFederation?->created_at ?? now(),
                'updated_at' => now(),
            ];

            // Sync without detaching to preserve existing relationships
            $individual->federations()->syncWithoutDetaching([
                $federationId => $federationData,
            ]);
        }
    }

    private function handleZonesAndDistricts(IndividualData $individualData, Individual $individual): void
    {
        // Handle zones - sync with new zone IDs
        if ($individualData->zone_ids !== null) {
            $individual->zones()->sync($individualData->zone_ids);
        }

        // District is updated directly as part of the individual update
        // The district_id is already included in the individualDataArray
    }

}
