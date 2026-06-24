<?php

namespace Database\Seeders;

use App\Enums\EvtAttributeFillableTypeEnum;
use App\Enums\EvtAttributeTypesEnum;
use Domain\EvtEvents\Models\Attribute;
use Illuminate\Database\Seeder;

class TeamIdentifierAttributeSeeder extends Seeder
{
    public function run()
    {
        // We firstOrCreate so if the attribute already exists, we won't duplicate it.
        Attribute::firstOrCreate(
            ['name' => 'team_identifier'],
            [
                'attribute_type' => EvtAttributeTypesEnum::HIDDEN->value,
                'default_value' => null,
                'validation_rules' => null,
                'custom_class' => null,
                'fillable_type' => EvtAttributeFillableTypeEnum::AUTO,
                'fillable_global' => true,
                'enrollment_type' => 'ATHLETE',
                'attribute_data' => [],
            ]
        );
    }
}
