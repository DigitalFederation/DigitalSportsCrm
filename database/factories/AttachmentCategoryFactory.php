<?php

namespace Database\Factories;

use Domain\Attachments\Models\AttachmentCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttachmentCategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AttachmentCategory::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->unique()->word,
        ];
    }
}
