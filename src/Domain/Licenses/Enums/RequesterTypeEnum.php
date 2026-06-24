<?php

namespace Domain\Licenses\Enums;

enum RequesterTypeEnum: string
{
    case INDIVIDUAL = 'individual';
    case ENTITY = 'entity';
    case FEDERATION = 'federation';

    /**
     * Get all possible values as an array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Create from a class name (e.g., Domain\Individuals\Models\Individual)
     */
    public static function fromClass(string $className): ?self
    {
        return match ($className) {
            \Domain\Individuals\Models\Individual::class, 'Domain\Individuals\Models\Individual' => self::INDIVIDUAL,
            \Domain\Entities\Models\Entity::class, 'Domain\Entities\Models\Entity' => self::ENTITY,
            \Domain\Federations\Models\Federation::class, 'Domain\Federations\Models\Federation' => self::FEDERATION,
            default => null,
        };
    }

    /**
     * Create from a display name (e.g., 'Individual', 'Entity', 'Federation')
     */
    public static function fromDisplay(string $displayName): ?self
    {
        $normalized = strtolower($displayName);

        return match ($normalized) {
            'individual' => self::INDIVIDUAL,
            'entity' => self::ENTITY,
            'federation' => self::FEDERATION,
            default => null,
        };
    }

    /**
     * Get the display name (capitalized)
     */
    public function toDisplay(): string
    {
        return ucfirst($this->value);
    }

    /**
     * Get the model class name
     */
    public function toClass(): string
    {
        return match ($this) {
            self::INDIVIDUAL => \Domain\Individuals\Models\Individual::class,
            self::ENTITY => \Domain\Entities\Models\Entity::class,
            self::FEDERATION => \Domain\Federations\Models\Federation::class,
        };
    }

    /**
     * Check if this type matches a given input (flexible matching)
     */
    public function matches(string $input): bool
    {
        // Check exact match
        if ($this->value === $input) {
            return true;
        }

        // Check display name match
        if ($this->toDisplay() === $input) {
            return true;
        }

        // Check class name match
        if ($this->toClass() === $input) {
            return true;
        }

        // Check case-insensitive match
        if (strtolower($input) === $this->value) {
            return true;
        }

        return false;
    }
}
