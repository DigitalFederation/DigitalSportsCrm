<?php

namespace Domain\Entities\Actions;

use App\Jobs\GenerateModelQrCode;
use Domain\Entities\DataTransferObject\EntityData;
use Domain\Entities\Models\Entity;
use Domain\Entities\States\ActiveEntityFederationState;
use Domain\Entities\States\PendingEntityFederationState;
use Domain\Federations\Models\Federation;
use Illuminate\Support\Facades\Auth;
use Support\UtilityMethods;

class CreateEntityAction
{
    public function __invoke(EntityData $data): Entity
    {
        $data = [
            ...(array) $data,
            'member_code' => UtilityMethods::generateUniqueEntityCode(),
        ];

        $entity = Entity::create($data);

        $this->SyncFederations($data['federation_id'], $entity);

        // Sync zones and districts
        $this->SyncZonesAndDistricts($data, $entity);

        if (! empty($data['logo'])) {
            $entity->addMedia($data['logo'])->toMediaCollection('profile', 'public');
        }

        // Dispatch QR code generation job
        dispatch(new GenerateModelQrCode($entity));

        activity('Entity')
            ->performedOn($entity)
            ->event('created')
            ->withProperties([
                ...$data,
                'terms_accepted' => true,
                'data_sharing_accepted' => true,
                'terms_accepted_at' => now(),
            ])
            ->log('New entity created.'.$entity->name);

        return $entity;
    }
    private function SyncFederations(int|array|null $federationId, Entity $entity): void
    {

        // If no federation ID is provided, use the default federation
        if (empty($federationId)) {
            $defaultFederation = Federation::where('is_default_federation', 1)->first();

            if ($defaultFederation) {
                $federationId = $defaultFederation->id;
            } else {
                // Handle the case where no default federation is found
                throw new \Exception('Default federation not found.');
            }
        }

        $federationList = is_array($federationId) ? $federationId : [$federationId];

        // Get parent federations for all provided federation IDs
        $parentFederations = Federation::select('id', 'parent_id')
            ->whereIn('id', $federationList)
            ->whereNotNull('parent_id')
            ->pluck('parent_id')
            ->toArray();

        if (! empty($parentFederations)) {
            $federationList = array_merge($federationList, $parentFederations);
        }
        foreach (array_unique($federationList) as $federation) {
            $entity->federations()->attach($federation, [
                'active' => 0,
                'status_class' => $this->determineEntityFederationState(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function SyncZonesAndDistricts(array $data, Entity $entity): void
    {
        // Sync zones if provided
        if (! empty($data['zone_ids']) && is_array($data['zone_ids'])) {
            $entity->zones()->sync($data['zone_ids']);
        }

        // District is saved directly as it's a foreign key
        // The district_id is already included in the $data array and saved with the entity
    }

    /**
     * Determine the appropriate entity federation state based on the current user's role
     */
    private function determineEntityFederationState(): string
    {
        $user = Auth::user();

        if (! $user) {
            return PendingEntityFederationState::class;
        }

        // Check if user has international or Federation roles
        $cmasAndFederationRoles = [
            'admin',
            'association-sport-admin',
            'association-scientific-admin',
            'association-admin',
            'association-territorial-admin',
            'admin-notifications',
            'federation-admin',
        ];

        if ($user->hasAnyRole($cmasAndFederationRoles)) {
            return ActiveEntityFederationState::class;
        }

        return PendingEntityFederationState::class;
    }
}
