<?php

namespace Domain\Licenses\Actions;

use Domain\Licenses\Enums\RequesterTypeEnum;

/**
 * Normalizes requester type values to ensure consistency across the application.
 *
 * This action converts various formats of requester types (class names, display names,
 * morph map keys) into a standardized array of morph map keys.
 */
class NormalizeRequesterTypeAction
{
    /**
     * Execute the normalization action
     *
     * @param  mixed  $requesterModel  The requester model value to normalize
     * @return array|null Array of normalized morph map keys or null for "all types"
     */
    public function __invoke($requesterModel): ?array
    {
        // Handle null or empty - means all types allowed
        if (is_null($requesterModel) || (is_array($requesterModel) && empty($requesterModel))) {
            return null;
        }

        // Handle special "All" value
        if ($requesterModel === 'All') {
            return null;
        }

        // Convert to array if string
        if (is_string($requesterModel)) {
            $requesterModel = [$requesterModel];
        }

        // If not an array at this point, return null (allow all)
        if (! is_array($requesterModel)) {
            return null;
        }

        $normalized = [];

        foreach ($requesterModel as $type) {
            if (! is_string($type)) {
                continue;
            }

            // Try to normalize the type
            $normalizedType = $this->normalizeType($type);
            if ($normalizedType !== null) {
                $normalized[] = $normalizedType->value;
            }
        }

        // If we have all three types, return null (allow all)
        if (count($normalized) === 3 &&
            in_array(RequesterTypeEnum::INDIVIDUAL->value, $normalized) &&
            in_array(RequesterTypeEnum::ENTITY->value, $normalized) &&
            in_array(RequesterTypeEnum::FEDERATION->value, $normalized)) {
            return null;
        }

        // Return unique values
        return array_values(array_unique($normalized));
    }

    /**
     * Normalize a single type value
     *
     * @param  string  $type  The type to normalize
     */
    public function normalizeType(string $type): ?RequesterTypeEnum
    {
        // Try direct enum value match first (fastest)
        $enumValue = RequesterTypeEnum::tryFrom($type);
        if ($enumValue !== null) {
            return $enumValue;
        }

        // Try from display name (Individual, Entity, Federation)
        $enumValue = RequesterTypeEnum::fromDisplay($type);
        if ($enumValue !== null) {
            return $enumValue;
        }

        // Try from class name
        $enumValue = RequesterTypeEnum::fromClass($type);
        if ($enumValue !== null) {
            return $enumValue;
        }

        // Handle legacy formats
        return match ($type) {
            'All' => null, // This will be handled at the array level
            default => null,
        };
    }

    /**
     * Check if a requester type is allowed
     *
     * @param  mixed  $requesterModel  The license's requester_model value
     * @param  string  $requesterType  The type to check (class name or morph key)
     */
    public function isTypeAllowed($requesterModel, string $requesterType): bool
    {
        $normalized = $this->__invoke($requesterModel);

        // Null means all types allowed
        if ($normalized === null) {
            return true;
        }

        // Normalize the requester type we're checking
        $typeToCheck = $this->normalizeType($requesterType);
        if ($typeToCheck === null) {
            return false;
        }

        return in_array($typeToCheck->value, $normalized);
    }
}
