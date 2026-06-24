<?php

namespace Domain\Federations\Enums;

enum FederationCategoryEnum: string
{
    case SPORT_OR_CLASS_ASSOCIATION = 'Domain\Federations\Enums\SportOrClassAssociationCategory';
    case TERRITORIAL_ASSOCIATION = 'Domain\Federations\Enums\TerritorialAssociationCategory';

    public function label(): string
    {
        return match ($this) {
            self::SPORT_OR_CLASS_ASSOCIATION => __('federation.sport_or_class_association'),
            self::TERRITORIAL_ASSOCIATION => __('federation.territorial_association'),
        };
    }

    public function value(): string
    {
        return $this->value;
    }
}
