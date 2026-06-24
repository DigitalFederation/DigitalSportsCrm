<?php

namespace Database\Factories\Domain\EventApplications;

use Domain\EventApplications\Models\ApplicationDocument;
use Domain\EventApplications\Models\ApplicationTemplate;
use Domain\EventApplications\Models\EventApplication;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApplicationDocumentFactory extends Factory
{
    protected $model = ApplicationDocument::class;

    public function definition(): array
    {
        return [
            'application_id' => EventApplication::factory(),
            'template_id' => null,
            'document_type' => $this->faker->randomElement(['insurance', 'authorization', 'venue_agreement', 'other']),
            'file_name' => $this->faker->word . '.pdf',
            'file_path' => 'documents/' . $this->faker->uuid . '.pdf',
            'file_size' => $this->faker->numberBetween(1024, 5242880),
            'mime_type' => 'application/pdf',
            'uploaded_by_type' => null,
            'uploaded_by_id' => null,
        ];
    }

    public function forTemplate(): static
    {
        return $this->state(fn (array $attributes) => [
            'template_id' => ApplicationTemplate::factory(),
            'application_id' => null,
        ]);
    }

    public function forApplication(): static
    {
        return $this->state(fn (array $attributes) => [
            'application_id' => EventApplication::factory(),
            'template_id' => null,
        ]);
    }
}
