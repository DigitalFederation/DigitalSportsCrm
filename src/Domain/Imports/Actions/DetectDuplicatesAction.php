<?php

namespace Domain\Imports\Actions;

use Domain\Individuals\Models\Individual;

class DetectDuplicatesAction
{
    /**
     * Find duplicates for a batch of individuals.
     */
    public function execute(array $individuals): array
    {
        if (empty($individuals)) {
            return [];
        }

        // Build keys for all individuals
        $searchKeys = [];
        foreach ($individuals as $index => $data) {
            $searchKeys[$index] = [
                'email' => $data['email'],
                'composite' => $this->buildCompositeKey($data),
            ];
        }

        // Find duplicates by email
        $emails = array_column($individuals, 'email');
        $duplicatesByEmail = Individual::whereIn('email', $emails)
            ->with('user')
            ->get()
            ->keyBy('email');

        // Find duplicates by composite key (name + surname + birthdate + country)
        $duplicatesByComposite = $this->findByCompositeKeys($individuals);

        // Compile results
        $duplicates = [];
        foreach ($searchKeys as $index => $keys) {
            $duplicate = null;

            // Check email first (highest priority)
            if ($duplicatesByEmail->has($keys['email'])) {
                $duplicate = $duplicatesByEmail->get($keys['email']);
            }
            // Then check composite key
            elseif (isset($duplicatesByComposite[$keys['composite']])) {
                $duplicate = $duplicatesByComposite[$keys['composite']];
            }

            if ($duplicate) {
                $duplicates[$index] = [
                    'individual' => $duplicate,
                    'match_type' => $duplicatesByEmail->has($keys['email']) ? 'email' : 'composite',
                ];
            }
        }

        return $duplicates;
    }

    /**
     * Find individuals by composite keys using efficient query.
     */
    protected function findByCompositeKeys(array $individuals): array
    {
        // Use raw query for better performance with large datasets
        $query = Individual::query();
        $compositeKeys = [];

        foreach ($individuals as $data) {
            if (isset($data['name'], $data['surname'], $data['birthdate'], $data['country_id'])) {
                $query->orWhere(function ($q) use ($data) {
                    $q->where('name', $data['name'])
                        ->where('surname', $data['surname'])
                        ->where('birthdate', $data['birthdate'])
                        ->where('country_id', $data['country_id']);
                });

                $compositeKeys[] = $this->buildCompositeKey($data);
            }
        }

        if (empty($compositeKeys)) {
            return [];
        }

        // Execute query and key by composite
        return $query->with('user')
            ->get()
            ->keyBy(function ($item) {
                return $this->buildCompositeKey([
                    'name' => $item->name,
                    'surname' => $item->surname,
                    'birthdate' => $item->birthdate,
                    'country_id' => $item->country_id,
                ]);
            })
            ->all();
    }

    /**
     * Build a composite key for duplicate detection.
     */
    protected function buildCompositeKey(array $data): string
    {
        // Normalize birthdate to Y-m-d format (handles both Carbon objects and strings)
        $birthdate = $data['birthdate'] ?? '';
        if ($birthdate instanceof \Carbon\Carbon) {
            $birthdate = $birthdate->format('Y-m-d');
        } elseif (is_string($birthdate) && strlen($birthdate) > 10) {
            // Handle datetime strings by extracting just the date part
            $birthdate = substr($birthdate, 0, 10);
        }

        return implode('|', [
            $data['name'] ?? '',
            $data['surname'] ?? '',
            $birthdate,
            $data['country_id'] ?? '',
        ]);
    }

    /**
     * Check if an individual exists by email.
     */
    public function existsByEmail(string $email): bool
    {
        return Individual::where('email', $email)->exists();
    }

    /**
     * Get duplicate statistics for a batch.
     */
    public function getStatistics(array $individuals): array
    {
        $duplicates = $this->execute($individuals);

        $emailDuplicates = 0;
        $compositeDuplicates = 0;

        foreach ($duplicates as $duplicate) {
            if ($duplicate['match_type'] === 'email') {
                $emailDuplicates++;
            } else {
                $compositeDuplicates++;
            }
        }

        return [
            'total_duplicates' => count($duplicates),
            'email_duplicates' => $emailDuplicates,
            'composite_duplicates' => $compositeDuplicates,
            'unique_records' => count($individuals) - count($duplicates),
        ];
    }
}
