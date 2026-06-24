<?php

namespace Database\Factories\Domain\EventApplications;

use App\Enums\EventApplicationTypeEnum;
use Domain\Entities\Models\Entity;
use Domain\EventApplications\Models\ApplicationTemplate;
use Domain\EventApplications\Models\EventApplication;
use Domain\EventApplications\States\ApprovedApplicationState;
use Domain\EventApplications\States\DraftApplicationState;
use Domain\EventApplications\States\InValidationApplicationState;
use Domain\EventApplications\States\PublishedApplicationState;
use Domain\EventApplications\States\RejectedApplicationState;
use Domain\EventApplications\States\ReturnedForCorrectionApplicationState;
use Domain\EventApplications\States\SubmittedApplicationState;
use Domain\EvtEvents\Models\Sport;
use Domain\Geographic\Models\District;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventApplicationFactory extends Factory
{
    protected $model = EventApplication::class;

    public function definition(): array
    {
        return [
            'application_type' => $this->faker->randomElement(EventApplicationTypeEnum::values()),
            'template_id' => null,
            'entity_id' => Entity::factory(),
            'entity_type' => Entity::class,
            'status_class' => DraftApplicationState::class,
            'event_name' => $this->faker->sentence(3),
            'event_type' => $this->faker->randomElement(['organization', 'competition']),
            'sport_id' => Sport::factory(),
            'event_category' => $this->faker->randomElement(['regional', 'national', 'international']),
            'start_date' => $this->faker->dateTimeBetween('+1 week', '+2 months'),
            'end_date' => $this->faker->dateTimeBetween('+2 months', '+3 months'),
            'district_id' => District::factory(),
            'municipality' => $this->faker->city,
            'responsible_name' => $this->faker->name,
            'responsible_phone' => $this->faker->phoneNumber,
            'target_audience' => $this->faker->randomElement(['athletes', 'coaches', 'referees', 'all']),
            'expected_participants' => $this->faker->numberBetween(10, 200),
            'form_data' => null,
            'admin_notes' => null,
            'submitted_at' => null,
            'validated_at' => null,
            'decided_at' => null,
            'published_at' => null,
        ];
    }

    public function fromTemplate(): static
    {
        return $this->state(fn (array $attributes) => [
            'application_type' => EventApplicationTypeEnum::FederationInitiated->value,
            'template_id' => ApplicationTemplate::factory(),
        ]);
    }

    public function directSubmission(): static
    {
        return $this->state(fn (array $attributes) => [
            'application_type' => EventApplicationTypeEnum::DirectSubmission->value,
            'template_id' => null,
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_class' => DraftApplicationState::class,
            'submitted_at' => null,
            'validated_at' => null,
            'decided_at' => null,
            'published_at' => null,
        ]);
    }

    public function submitted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_class' => SubmittedApplicationState::class,
            'submitted_at' => now(),
        ]);
    }

    public function inValidation(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_class' => InValidationApplicationState::class,
            'submitted_at' => now()->subDays(2),
            'validated_at' => now(),
        ]);
    }

    public function returnedForCorrection(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_class' => ReturnedForCorrectionApplicationState::class,
            'submitted_at' => now()->subDays(3),
            'validated_at' => now()->subDays(1),
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_class' => ApprovedApplicationState::class,
            'submitted_at' => now()->subDays(5),
            'validated_at' => now()->subDays(3),
            'decided_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_class' => RejectedApplicationState::class,
            'submitted_at' => now()->subDays(5),
            'validated_at' => now()->subDays(3),
            'decided_at' => now(),
        ]);
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_class' => PublishedApplicationState::class,
            'submitted_at' => now()->subDays(7),
            'validated_at' => now()->subDays(5),
            'decided_at' => now()->subDays(2),
            'published_at' => now(),
        ]);
    }
}
