<?php

namespace Database\Factories;

use App\Enums\EvtCompetitionTypeEnum;
use Domain\EvtEvents\Models\Competition;
use Domain\EvtEvents\Models\CompetitionType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CompetitionType>
 */
class CompetitionTypeFactory extends Factory
{
    protected $model = CompetitionType::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $competitionTypes = EvtCompetitionTypeEnum::cases();

        return [
            'competition_id' => Competition::factory(),
            'competition_type' => $this->faker->randomElement($competitionTypes),
        ];
    }

    /**
     * Configure the factory to use a specific competition type.
     */
    public function withType(string $type): self
    {
        return $this->state(function (array $attributes) use ($type) {
            return [
                'competition_type' => $type,
            ];
        });
    }

    /**
     * Configure the factory to use a specific competition.
     */
    public function forCompetition(Competition $competition): self
    {
        return $this->state(function (array $attributes) use ($competition) {
            return [
                'competition_id' => $competition->id,
            ];
        });
    }
}
