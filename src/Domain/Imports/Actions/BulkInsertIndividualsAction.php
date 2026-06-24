<?php

namespace Domain\Imports\Actions;

use App\Models\Group;
use App\Models\User;
use Domain\Federations\Models\Federation;
use Domain\Geographic\Models\District;
use Domain\Geographic\Models\Zone;
use Domain\Individuals\Actions\CreateIndividualAction;
use Domain\Individuals\DataTransferObject\IndividualData;
use Domain\Individuals\Models\Individual;
use Domain\Memberships\Services\MemberNumberService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class BulkInsertIndividualsAction
{
    protected CreateIndividualAction $createIndividualAction;

    public function __construct()
    {
        $this->createIndividualAction = new CreateIndividualAction;
    }

    /**
     * Bulk insert individuals with their user accounts.
     */
    public function execute(array $individuals, array $options = []): array
    {
        \Log::info('BulkInsertIndividualsAction::execute called', [
            'count' => count($individuals),
            'options' => $options,
        ]);

        if (empty($individuals)) {
            return ['users' => 0, 'individuals' => 0];
        }

        return DB::transaction(function () use ($individuals, $options) {
            $createdUsers = 0;
            $createdIndividuals = 0;
            $skippedExisting = 0;
            $failures = [];

            // Determine if we're adding as federation or entity
            $addedByFederation = ! empty($options['federation_ids']);
            $addedByEntity = ! empty($options['entity_id']);

            // Get existing users by email to avoid duplicates
            $emails = array_column($individuals, 'email');
            $existingUsers = User::whereIn('email', $emails)
                ->pluck('email', 'id')
                ->toArray();
            $existingEmails = array_values($existingUsers);

            // Process individuals in chunks for better performance
            $chunks = array_chunk($individuals, 50); // Process 50 at a time

            foreach ($chunks as $chunk) {
                foreach ($chunk as $data) {
                    try {
                        // Skip if user already exists (based on duplicate strategy)
                        // Note: We only skip if duplicate_strategy is 'skip' AND the individual already exists
                        // This allows creating individuals for existing users who don't have individual records yet

                        // Create user if doesn't exist
                        $user = User::firstOrCreate(
                            ['email' => $data['email']],
                            [
                                'name' => $data['name'] . ' ' . $data['surname'],
                                'password' => Hash::make(Str::random(16)),
                                'group_id' => Group::where('code', 'INDIVIDUAL')->value('id'),
                            ]
                        );

                        if ($user->wasRecentlyCreated) {
                            $createdUsers++;

                            // Assign the individual-approved role to newly created users
                            $approvedRole = Role::firstOrCreate(
                                ['name' => 'individual-approved', 'guard_name' => 'web']
                            );
                            $user->assignRole($approvedRole);
                        }

                        // Check if individual already exists for this user
                        $existingIndividual = Individual::where('user_id', $user->id)->first();
                        if ($existingIndividual) {
                            if (($options['duplicate_strategy'] ?? 'skip') === 'skip') {
                                $skippedExisting++;

                                continue;
                            }
                        }

                        // Determine entity_id: per-row entity_id takes precedence over options
                        $entityId = ! empty($data['entity_id']) ? (int) $data['entity_id'] : ($options['entity_id'] ?? null);

                        // Auto-set country_id from Main Federation if not provided
                        $countryId = ! empty($data['country_id']) ? (int) $data['country_id'] : $this->getMainFederationCountryId();

                        // Prepare IndividualData DTO using correct parameter order
                        $individualData = new IndividualData(
                            name: $data['name'],
                            surname: $data['surname'] ?? null,
                            native_name: $data['native_name'] ?? null,
                            country_id: $countryId,
                            birthdate: $data['birthdate'] ?? null,
                            gender: $this->normalizeGender($data['gender'] ?? null),
                            address: $data['address'] ?? null,
                            location: $data['location'] ?? null,
                            postal_code: $data['postal_code'] ?? null,
                            vat_number: $data['vat_number'] ?? null,
                            phone: $data['phone'] ?? null,
                            doc_ref_type: $data['doc_ref_type'] ?? null,
                            doc_ref: $data['doc_ref'] ?? null,
                            doc_ref_validation_date: $data['doc_ref_validation_date'] ?? null,
                            email: $data['email'],
                            member_code: $data['member_code'] ?? null,
                            user_id: $user->id,
                            federation_id: $options['federation_ids'] ?? null,
                            entity_id: $entityId,
                            logo: null, // No logo for bulk imports
                            professional_role_ids: $data['professional_role_ids'] ?? null,
                            national_federation_number: null, // Deprecated - not used in imports
                            member_number: $this->resolveNullableInteger($data['member_number'] ?? null),
                            facebook_url: $data['facebook_url'] ?? null,
                            x_url: $data['x_url'] ?? null,
                            instagram_url: $data['instagram_url'] ?? null,
                            linkedin_url: $data['linkedin_url'] ?? null,
                            district_id: $this->resolveDistrictId($data['district_id'] ?? null),
                            zone_ids: $this->resolveZoneIds($data['zone_ids'] ?? null)
                        );

                        // Determine if this individual is being added by an entity
                        // Per-row entity_id or options entity_id both count as added by entity
                        $isAddedByEntity = $addedByEntity || ! empty($entityId);

                        // Use CreateIndividualAction for proper business logic
                        $individual = ($this->createIndividualAction)(
                            $individualData,
                            $addedByFederation,
                            $isAddedByEntity
                        );

                        if ($individual) {
                            // Auto-assign member_number if not provided in import
                            if ($individual->member_number === null) {
                                $memberNumberService = new MemberNumberService;
                                $memberNumberService->assignIndividualMemberNumber($individual);
                            }
                            $createdIndividuals++;
                        }

                    } catch (\Exception $e) {
                        \Log::error('Failed to create individual during bulk import', [
                            'email' => $data['email'] ?? 'unknown',
                            'error' => $e->getMessage(),
                            'data' => array_intersect_key($data, array_flip(['name', 'surname', 'email', 'birthdate'])),
                        ]);

                        // Track failures for reporting
                        $failures[] = [
                            'email' => $data['email'] ?? 'unknown',
                            'error' => $e->getMessage(),
                        ];

                        continue;
                    }
                }

                // Small delay between chunks to avoid overwhelming the system
                usleep(100000); // 100ms
            }

            $result = [
                'users' => $createdUsers,
                'individuals' => $createdIndividuals,
                'total' => count($individuals),
                'skipped' => $skippedExisting,
                'failures' => $failures,
            ];

            // Sync member_number counter to avoid duplicates
            // Set counter to MAX(member_number) + 1 if imported numbers are higher
            $this->syncMemberNumberCounter();

            \Log::info('BulkInsertIndividualsAction completed', $result);

            return $result;
        });
    }

    /**
     * Sync the member_number counter to be at least MAX(member_number) + 1.
     * This prevents duplicates when importing individuals with existing member numbers.
     */
    protected function syncMemberNumberCounter(): void
    {
        $maxMemberNumber = Individual::max('member_number');

        if ($maxMemberNumber === null) {
            return;
        }

        $memberNumberService = new MemberNumberService;
        $currentCounter = $memberNumberService->getCurrentIndividualCounter();

        // Only update if max is >= current counter
        if ($maxMemberNumber >= $currentCounter) {
            $newCounter = $maxMemberNumber + 1;
            $memberNumberService->updateIndividualCounter($newCounter);
            \Log::info('Member number counter synced', [
                'previous_counter' => $currentCounter,
                'new_counter' => $newCounter,
                'max_member_number' => $maxMemberNumber,
            ]);
        }
    }

    /**
     * Update existing individuals using update strategy.
     * This method updates existing individuals when duplicate_strategy is 'update'
     */
    public function updateExisting(array $individuals): int
    {
        if (empty($individuals)) {
            return 0;
        }

        $updated = 0;

        DB::transaction(function () use ($individuals, &$updated) {
            foreach ($individuals as $data) {
                try {
                    // Find by ID if provided, otherwise by email
                    $query = Individual::query();
                    if (isset($data['id'])) {
                        $query->where('id', $data['id']);
                    } else {
                        $query->where('email', $data['email']);
                    }

                    $individual = $query->first();

                    if ($individual) {
                        $individual->update([
                            'name' => $data['name'],
                            'surname' => $data['surname'],
                            'birthdate' => $data['birthdate'],
                            'country_id' => $data['country_id'] ?? $individual->country_id ?? $this->getMainFederationCountryId(),
                            'gender' => $data['gender'] ?? $individual->gender,
                            'native_name' => $data['native_name'] ?? $individual->native_name,
                            'address' => $data['address'] ?? $individual->address,
                            'location' => $data['location'] ?? $individual->location,
                            'postal_code' => $data['postal_code'] ?? $individual->postal_code,
                            'vat_number' => $data['vat_number'] ?? $individual->vat_number,
                            'phone' => $data['phone'] ?? $individual->phone,
                            'doc_ref_type' => $data['doc_ref_type'] ?? $individual->doc_ref_type,
                            'doc_ref' => $data['doc_ref'] ?? $individual->doc_ref,
                            'doc_ref_validation_date' => $data['doc_ref_validation_date'] ?? $individual->doc_ref_validation_date,
                        ]);

                        $updated++;
                    }
                } catch (\Exception $e) {
                    \Log::error('Failed to update individual', [
                        'email' => $data['email'] ?? 'unknown',
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        });

        return $updated;
    }

    /**
     * Create individuals with suffix for duplicates.
     */
    public function createWithSuffix(array $individuals, string $suffix = '_import'): array
    {
        \Log::info('BulkInsertIndividualsAction::createWithSuffix called', [
            'suffix' => $suffix,
            'individuals_count' => count($individuals),
            'original_emails' => array_column($individuals, 'email'),
        ]);

        foreach ($individuals as &$individual) {
            // Add suffix to email to make it unique
            $individual['email'] = str_replace('@', $suffix.'@', $individual['email']);
        }

        \Log::info('Emails after suffix applied', [
            'modified_emails' => array_column($individuals, 'email'),
        ]);

        return $this->execute($individuals);
    }

    /**
     * Get the country ID from the Main Federation.
     * All individuals must belong to the same country as the Main Federation.
     */
    protected function getMainFederationCountryId(): ?int
    {
        $mainFederation = Federation::where('is_default_federation', 1)->first();

        return $mainFederation?->country_id;
    }

    /**
     * Resolve a nullable integer value.
     * Returns null if the value is empty, non-numeric, zero, or negative.
     */
    protected function resolveNullableInteger(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = trim((string) $value);

        if ($value === '' || ! ctype_digit($value)) {
            return null;
        }

        $intValue = (int) $value;

        // Zero is not a valid member_number (would cause unique constraint issues)
        return $intValue > 0 ? $intValue : null;
    }

    /**
     * Resolve a nullable string value.
     * Returns null if the value is empty or only whitespace.
     */
    protected function resolveNullableString(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    /**
     * Resolve and validate a district ID.
     * Returns null if the value is empty, non-numeric, or the district doesn't exist.
     */
    protected function resolveDistrictId(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Convert to string and trim
        $value = trim((string) $value);

        // Check if it's empty after trim
        if ($value === '') {
            return null;
        }

        // Must be a positive integer
        if (! ctype_digit($value)) {
            return null;
        }

        $districtId = (int) $value;

        // Zero is not a valid district ID
        if ($districtId <= 0) {
            return null;
        }

        // Validate the district exists in the database
        if (! District::where('id', $districtId)->exists()) {
            return null;
        }

        return $districtId;
    }

    /**
     * Resolve and validate zone IDs.
     * Returns null if the value is empty, or an array of valid zone IDs.
     * Filters out invalid/non-existent zone IDs.
     */
    protected function resolveZoneIds(mixed $value): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }

        // If already an array, process each element
        if (is_array($value)) {
            $zoneIds = $value;
        } else {
            // Convert to string and split by comma
            $value = trim((string) $value);
            if ($value === '') {
                return null;
            }
            $zoneIds = explode(',', $value);
        }

        $validZoneIds = [];

        foreach ($zoneIds as $zoneId) {
            $zoneId = trim((string) $zoneId);

            // Skip empty values
            if ($zoneId === '') {
                continue;
            }

            // Must be a positive integer
            if (! ctype_digit($zoneId)) {
                continue;
            }

            $zoneIdInt = (int) $zoneId;

            // Zero is not a valid zone ID
            if ($zoneIdInt <= 0) {
                continue;
            }

            // Validate the zone exists in the database
            if (Zone::where('id', $zoneIdInt)->exists()) {
                $validZoneIds[] = $zoneIdInt;
            }
        }

        return ! empty($validZoneIds) ? $validZoneIds : null;
    }

    /**
     * Normalize gender value to system format.
     * Supports multiple languages: English, Portuguese, French.
     */
    protected function normalizeGender(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = strtolower(trim((string) $value));

        return match ($value) {
            'm', 'male', 'masculino', 'homme' => 'male',
            'f', 'female', 'feminino', 'femme' => 'female',
            'o', 'other', 'outro', 'autre' => 'other',
            default => null
        };
    }
}
