<?php

namespace Domain\Imports\Actions;

use App\Jobs\GenerateModelQrCode;
use App\Models\Group;
use App\Models\User;
use Domain\Entities\Actions\AssociateUserToEntityAction;
use Domain\Entities\Models\Entity;
use Domain\Entities\States\ActiveEntityFederationState;
use Domain\Federations\Models\Federation;
use Domain\Users\Actions\CreateUserAction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Support\UtilityMethods;

class BulkInsertEntitiesAction
{
    protected int $successCount = 0;

    protected int $errorCount = 0;

    protected array $errors = [];

    protected array $createdEntities = [];

    /**
     * Execute bulk insert of entities.
     *
     * @param  array  $entities  Array of entity data to insert
     * @param  array  $options  Import options (federation_ids, etc.)
     */
    public function execute(array $entities, array $options = []): array
    {
        $this->successCount = 0;
        $this->errorCount = 0;
        $this->errors = [];
        $this->createdEntities = [];

        foreach ($entities as $index => $entityData) {
            try {
                $entity = $this->createEntity($entityData, $options);
                if ($entity) {
                    $this->createdEntities[] = $entity;
                    $this->successCount++;
                }
            } catch (\Exception $e) {
                Log::error('Entity creation failed during bulk insert', [
                    'index' => $index,
                    'data' => $entityData,
                    'error' => $e->getMessage(),
                ]);
                $this->errors[$index] = $e->getMessage();
                $this->errorCount++;
            }
        }

        return [
            'success_count' => $this->successCount,
            'error_count' => $this->errorCount,
            'errors' => $this->errors,
            'created_entities' => $this->createdEntities,
        ];
    }

    /**
     * Create a single entity with federation sync.
     */
    protected function createEntity(array $data, array $options = []): ?Entity
    {
        return DB::transaction(function () use ($data, $options) {
            // Generate unique international code
            $data['member_code'] = UtilityMethods::generateUniqueEntityCode();

            // Set legal_name if not provided
            if (empty($data['legal_name']) && ! empty($data['name'])) {
                $data['legal_name'] = $data['name'];
            }

            // Auto-set country_id: from data, then Main Federation, then app default (Portugal)
            $countryId = $data['country_id']
                ?? $this->getMainFederationCountryId()
                ?? config('app.default_country_id');

            // Create entity
            $entity = Entity::create([
                'name' => $data['name'] ?? null,
                'legal_name' => $data['legal_name'] ?? $data['name'] ?? null,
                'member_number' => $data['member_number'] ?? null,
                'vat_number' => $data['vat_number'] ?? null,
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'website' => $data['website'] ?? null,
                'address' => $data['address'] ?? null,
                'location' => $data['location'] ?? null,
                'postal_code' => $data['postal_code'] ?? null,
                'country_id' => $countryId,
                'district_id' => $data['district_id'] ?? null,
                'member_code' => $data['member_code'],
                'legal_responsible_person' => $data['legal_responsible_person'] ?? null,
                'public_description' => $data['public_description'] ?? null,
                'facebook_url' => $data['facebook_url'] ?? null,
                'x_url' => $data['x_url'] ?? null,
                'instagram_url' => $data['instagram_url'] ?? null,
                'linkedin_url' => $data['linkedin_url'] ?? null,
            ]);

            // Sync federations
            $this->syncFederations($entity, $data, $options);

            // Sync zones if provided
            $this->syncZones($entity, $data, $options);

            // Dispatch QR code generation job
            dispatch(new GenerateModelQrCode($entity));

            // Create user for entity if email is provided
            $this->createEntityUser($entity, $data);

            // Log activity
            activity('Entity')
                ->performedOn($entity)
                ->event('created')
                ->withProperties([
                    'name' => $entity->name,
                    'source' => 'import',
                    'import_date' => now()->toDateTimeString(),
                ])
                ->log('Entity imported: ' . $entity->name);

            return $entity;
        });
    }

    /**
     * Sync entity with federations.
     */
    protected function syncFederations(Entity $entity, array $data, array $options): void
    {
        // Determine federation IDs
        $federationIds = [];

        // Use federation from data if provided
        if (! empty($data['federation_id'])) {
            $federationIds = is_array($data['federation_id'])
                ? $data['federation_id']
                : [$data['federation_id']];
        }

        // Add federations from import options
        if (! empty($options['federation_ids'])) {
            $federationIds = array_merge(
                $federationIds,
                is_array($options['federation_ids'])
                    ? $options['federation_ids']
                    : [$options['federation_ids']]
            );
        }

        // If no federations specified, use default
        if (empty($federationIds)) {
            $defaultFederation = Federation::where('is_default_federation', 1)->first();
            if ($defaultFederation) {
                $federationIds = [$defaultFederation->id];
            }
        }

        // Get all ancestor federations for hierarchy (recursively)
        $allFederationIds = $federationIds;
        $currentIds = $federationIds;

        while (! empty($currentIds)) {
            $parentIds = Federation::whereIn('id', $currentIds)
                ->whereNotNull('parent_id')
                ->pluck('parent_id')
                ->toArray();

            // Only add parents we haven't already processed (avoid infinite loops)
            $newParentIds = array_diff($parentIds, $allFederationIds);
            if (empty($newParentIds)) {
                break;
            }

            $allFederationIds = array_merge($allFederationIds, $newParentIds);
            $currentIds = $newParentIds;
        }

        $federationIds = array_unique($allFederationIds);

        // Determine the state based on user role
        $stateClass = $this->determineEntityFederationState();

        foreach ($federationIds as $federationId) {
            $entity->federations()->attach($federationId, [
                'active' => $stateClass === ActiveEntityFederationState::class ? 1 : 0,
                'status_class' => $stateClass,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Sync entity with zones.
     */
    protected function syncZones(Entity $entity, array $data, array $options): void
    {
        $zoneIds = [];

        // Use zones from data if provided
        if (! empty($data['zone_ids'])) {
            $zoneIds = is_array($data['zone_ids'])
                ? $data['zone_ids']
                : [$data['zone_ids']];
        }

        // Add zones from import options
        if (! empty($options['zone_ids'])) {
            $zoneIds = array_merge(
                $zoneIds,
                is_array($options['zone_ids'])
                    ? $options['zone_ids']
                    : [$options['zone_ids']]
            );
        }

        if (! empty($zoneIds)) {
            $entity->zones()->sync($zoneIds);
        }
    }

    /**
     * Create a user for the entity and associate them.
     */
    protected function createEntityUser(Entity $entity, array $data): void
    {
        $email = $data['email'] ?? null;

        if (empty($email)) {
            Log::warning('Entity imported without user - no email provided', [
                'entity_id' => $entity->id,
                'entity_name' => $entity->name,
            ]);

            return;
        }

        // Check if user already exists with this email
        $existingUser = User::where('email', $email)->first();
        if ($existingUser) {
            // Associate existing user to entity if not already associated
            if (! $existingUser->entities()->where('entity.id', $entity->id)->exists()) {
                $associateUserToEntity = new AssociateUserToEntityAction;
                $associateUserToEntity($existingUser, $entity, 'entity-admin');
            }

            return;
        }

        // Create new user
        $createUser = new CreateUserAction;
        $createUserResult = $createUser([
            'name' => $data['name'] ?? $email,
            'email' => $email,
            'group_id' => Group::where('code', 'ENTITY')->value('id'),
            'bypass_verification' => true,
        ], true);

        $user = $createUserResult['user'];

        // Associate user to entity with entity-admin role
        $associateUserToEntity = new AssociateUserToEntityAction;
        $associateUserToEntity($user, $entity, 'entity-admin');
    }

    /**
     * Determine the appropriate entity federation state based on the current user's role.
     */
    protected function determineEntityFederationState(): string
    {
        $user = Auth::user();

        if (! $user) {
            // For imports, default to Active state since admin is performing the import
            return ActiveEntityFederationState::class;
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

        return \Domain\Entities\States\PendingEntityFederationState::class;
    }

    /**
     * Get the country ID from the Main Federation.
     * All entities must belong to the same country as the Main Federation.
     */
    protected function getMainFederationCountryId(): ?int
    {
        $mainFederation = Federation::where('is_default_federation', 1)->first();

        return $mainFederation?->country_id;
    }

    /**
     * Update existing entities.
     */
    public function updateExisting(array $entities): array
    {
        $updated = 0;
        $errors = [];

        foreach ($entities as $index => $entityData) {
            try {
                if (empty($entityData['id'])) {
                    continue;
                }

                $entity = Entity::find($entityData['id']);
                if (! $entity) {
                    $errors[$index] = 'Entity not found';

                    continue;
                }

                unset($entityData['id']);
                $entity->update($entityData);
                $updated++;

            } catch (\Exception $e) {
                $errors[$index] = $e->getMessage();
            }
        }

        return [
            'updated' => $updated,
            'errors' => $errors,
        ];
    }

    /**
     * Create entities with email suffix for duplicates.
     */
    public function createWithSuffix(array $entities, string $suffix): array
    {
        // For entities, we might want to suffix the name instead of email
        // since email is optional for entities
        foreach ($entities as &$entityData) {
            if (! empty($entityData['name'])) {
                $entityData['name'] = $entityData['name'] . $suffix;
            }
            if (! empty($entityData['legal_name'])) {
                $entityData['legal_name'] = $entityData['legal_name'] . $suffix;
            }
        }

        return $this->execute($entities);
    }
}
