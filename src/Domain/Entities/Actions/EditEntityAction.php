<?php

namespace Domain\Entities\Actions;

use Domain\Entities\DataTransferObject\EntityData;
use Domain\Entities\Models\Entity;
use Domain\Entities\States\PendingEntityFederationState;
use Exception;
use Illuminate\Support\Facades\DB;

class EditEntityAction
{
    /**
     * @throws Exception
     */
    public function __invoke(EntityData $data, int $id): bool
    {

        DB::beginTransaction();

        try {
            $entity = Entity::findOrFail($id);

            // Convert DTO to array
            $updateData = (array) $data;

            // Explicitly remove qrcode_path and member_code from the update data
            // to prevent them from being inadvertently changed.
            unset($updateData['qrcode_path']);
            unset($updateData['member_code']); // member_code should also be immutable after creation

            $updated = $entity->update($updateData);

            if (! empty($data->logo)) {
                $entity->clearMediaCollection('profile');
                $entity->addMedia($data->logo)->toMediaCollection('profile', 'public');
            }

            // Handle federation association if federation_id is present
            if ($data->federation_id !== null) {
                $this->handleFederationAssociation($data, $entity);
            }

            // Handle zones and districts
            $this->handleZonesAndDistricts($data, $entity);

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }

        return $updated;
    }

    private function handleFederationAssociation(EntityData $data, Entity $entity): void
    {
        // Get current federations
        $currentFederations = $entity->federations->pluck('id')->toArray();

        // Get new federations from request
        $newFederations = $data->federation_id; // Already normalized to array in DTO

        // Remove old associations
        $entity->federations()->detach(
            array_diff($currentFederations, $newFederations)
        );

        // Add new associations
        foreach ($newFederations as $federationId) {
            // Get existing federation relationship if any
            $existingFederation = $entity->entityFederations()
                ->where('federation_id', $federationId)
                ->first();

            // Prepare federation data
            $federationData = [
                $federationId => [
                    'status_class' => $existingFederation?->status_class ?? PendingEntityFederationState::class,
                    'national_federation_number' => $data->national_federation_number,
                    'active' => $existingFederation?->active ?? false,
                    'created_at' => $existingFederation?->created_at ?? now(),
                    'updated_at' => now(),
                ],
            ];

            // Sync without detaching to preserve existing relationships
            $entity->federations()->syncWithoutDetaching($federationData);
        }
    }

    private function handleZonesAndDistricts(EntityData $data, Entity $entity): void
    {
        // Handle zones - sync with new zone IDs
        if ($data->zone_ids !== null) {
            $entity->zones()->sync($data->zone_ids);
        }

        // District is updated directly as part of the entity update
        // The district_id is already included in the updateData array
    }
}
