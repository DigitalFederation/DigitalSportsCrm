<?php

namespace App\Enums;

use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;

enum LicenseRequesterModelsEnum: string
{
    case Individual = 'Individual';
    case Entity = 'Entity';
    case Federation = 'Federation';

    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function toArray(): array
    {
        return array_combine(self::names(), self::values());
    }

    public static function getKeyForValue($value): string
    {
        foreach (self::cases() as $case) {
            if ($case->value === $value) {
                return $case->name;
            }
        }

        return '';
    }

    /**
     * Get all possible combinations of requester types
     */
    public static function getAllCombinations(): array
    {
        $types = self::values();
        $combinations = [];

        // Single types
        foreach ($types as $type) {
            $combinations[$type] = [$type];
        }

        // Two types combinations
        $combinations['Individual + Entity'] = ['Individual', 'Entity'];
        $combinations['Individual + Federation'] = ['Individual', 'Federation'];
        $combinations['Entity + Federation'] = ['Entity', 'Federation'];

        // All three types
        $combinations['All'] = ['Individual', 'Entity', 'Federation'];

        return $combinations;
    }

    /**
     * Get the display name for a combination of requester types
     */
    public static function getDisplayName(array $types): string
    {
        if (empty($types)) {
            return 'All';
        }

        // Sort to ensure consistent display
        sort($types);

        // Check if all types are included
        if (count($types) === 3 &&
            in_array('Individual', $types) &&
            in_array('Entity', $types) &&
            in_array('Federation', $types)) {
            return 'All';
        }

        return implode(' + ', $types);
    }

    /**
     * Convert old format to new format
     */
    public static function convertToSimpleType(string $classOrType): string
    {
        $map = [
            Individual::class => 'Individual',
            Entity::class => 'Entity',
            Federation::class => 'Federation',
            'All' => 'All',
        ];

        return $map[$classOrType] ?? $classOrType;
    }
}
