<?php

namespace Database\Factories\Domain\EventApplications;

use App\Models\User;
use Domain\EventApplications\Models\ApplicationTemplate;
use Domain\EvtEvents\Models\Sport;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApplicationTemplateFactory extends Factory
{
    protected $model = ApplicationTemplate::class;

    public function definition(): array
    {
        $submissionStart = now()->addDays($this->faker->numberBetween(1, 30))->startOfDay();
        $submissionEnd = $submissionStart->copy()->addDays($this->faker->numberBetween(1, 30))->startOfDay();
        $eventStart = $submissionEnd->copy()->addDays($this->faker->numberBetween(1, 30))->startOfDay();
        $eventEnd = $eventStart->copy()->addDays($this->faker->numberBetween(1, 30))->startOfDay();

        return [
            'name' => $this->faker->sentence(4),
            'event_type' => $this->faker->randomElement(['organization', 'competition']),
            'sport_id' => Sport::factory(),
            'event_category' => $this->faker->randomElement(['regional', 'national', 'international']),
            'state' => 'draft',
            'submission_start_date' => $submissionStart,
            'submission_end_date' => $submissionEnd,
            'event_start_date' => $eventStart,
            'event_end_date' => $eventEnd,
            'description' => $this->faker->paragraph,
            'target_audience' => 'both',
            'max_applications' => $this->faker->numberBetween(10, 100),
            'created_by' => User::factory(),
        ];
    }

    public function openForSubmissions(): static
    {
        return $this->state(fn (array $attributes) => [
            'submission_start_date' => now()->subDays(5),
            'submission_end_date' => now()->addDays(30),
            'state' => 'open',
        ]);
    }

    public function closedForSubmissions(): static
    {
        return $this->state(fn (array $attributes) => [
            'submission_start_date' => now()->subDays(60),
            'submission_end_date' => now()->subDays(1),
            'state' => 'closed',
        ]);
    }

    public function open(): static
    {
        return $this->state(fn (array $attributes) => [
            'state' => 'open',
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'state' => 'closed',
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'state' => 'archived',
        ]);
    }

    public function forEntities(): static
    {
        return $this->state(fn (array $attributes) => [
            'target_audience' => 'entities',
        ]);
    }

    public function forFederations(): static
    {
        return $this->state(fn (array $attributes) => [
            'target_audience' => 'federations',
        ]);
    }

    public function forBoth(): static
    {
        return $this->state(fn (array $attributes) => [
            'target_audience' => 'both',
        ]);
    }
}
