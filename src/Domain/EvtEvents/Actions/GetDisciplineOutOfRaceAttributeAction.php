<?php

namespace Domain\EvtEvents\Actions;

use App\Enums\EvtAttributeTypesEnum;
use Domain\EvtEvents\Models\Attribute;
use Domain\EvtEvents\Models\Discipline;

class GetDisciplineOutOfRaceAttributeAction
{
    public function execute(Discipline $discipline): ?Attribute
    {
        return $discipline->attributes()
            ->where('attribute_type', EvtAttributeTypesEnum::OUTOFRACE)
            ->first();
    }

    public function hasOutOfRaceAttribute(Discipline $discipline): bool
    {
        return $discipline->attributes()
            ->where('attribute_type', EvtAttributeTypesEnum::OUTOFRACE)
            ->exists();
    }

    public function isOutOfRace(array $attributeValues, ?Attribute $outOfRaceAttribute): bool
    {
        if (! $outOfRaceAttribute) {
            return false;
        }

        // A value of 'yes' explicitly means out-of-race
        // Anything else (including NULL or 'no') is considered in-race
        return ($attributeValues[$outOfRaceAttribute->id] ?? null) === 'yes';
    }
}
