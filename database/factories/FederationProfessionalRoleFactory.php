<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class FederationProfessionalRoleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'federation_id' => null,
            'individual_id' => null,
            'professional_role_id' => null,
            'federation_name' => null,
            'individual_name' => null,
            'role_name' => null,
            'status_class' => null,
        ];
    }
}
