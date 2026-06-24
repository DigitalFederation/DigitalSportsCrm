<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class EntityProfessionalRoleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'entity_id' => null,
            'individual_id' => null,
            'professional_role_id' => null,
            'entity_name' => null,
            'individual_name' => null,
            'role_name' => null,
            'status_class' => null,
        ];
    }
}
