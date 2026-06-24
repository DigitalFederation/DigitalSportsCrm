<?php

namespace Database\Factories;

use Domain\EvtEvents\Models\Attribute;
use Domain\EvtEvents\Models\Discipline;
use Domain\EvtEvents\Models\DisciplineAttributeAssociation;
use Illuminate\Database\Eloquent\Factories\Factory;

class DisciplineAttributeAssociationFactory extends Factory
{
    protected $model = DisciplineAttributeAssociation::class;

    public function definition()
    {
        return [
            'discipline_id' => Discipline::factory(),
            'attribute_id' => Attribute::factory(),
            'custom_value' => $this->faker->word,
        ];
    }
}
