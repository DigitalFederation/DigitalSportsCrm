<?php

namespace Database\Factories;

use Domain\Federations\Models\Federation;
use Domain\Federations\Models\FederationVotingRight;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Federations\Models\FederationVotingRight>
 */
class FederationVotingRightFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<TModel>
     */
    protected $model = FederationVotingRight::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'federation_id' => Federation::factory(), // Associate with a federation
            'year' => Carbon::now()->year,
            'general_assembly_status' => FederationVotingRight::STATUS_NO_VOTING_RIGHT,
            'technical_committee_status' => FederationVotingRight::STATUS_NO_VOTING_RIGHT,
            'scientific_committee_status' => FederationVotingRight::STATUS_NO_VOTING_RIGHT,
            'sport_committee_status' => FederationVotingRight::STATUS_NO_VOTING_RIGHT,
            'finswimming_commission_status' => FederationVotingRight::STATUS_NO_VOTING_RIGHT,
            'freediving_commission_status' => FederationVotingRight::STATUS_NO_VOTING_RIGHT,
            'aquathlon_commission_status' => FederationVotingRight::STATUS_NO_VOTING_RIGHT,
            'underwater_hockey_commission_status' => FederationVotingRight::STATUS_NO_VOTING_RIGHT,
            'underwater_rugby_commission_status' => FederationVotingRight::STATUS_NO_VOTING_RIGHT,
            'target_shooting_commission_status' => FederationVotingRight::STATUS_NO_VOTING_RIGHT,
            'sport_diving_commission_status' => FederationVotingRight::STATUS_NO_VOTING_RIGHT,
            'spearfishing_commission_status' => FederationVotingRight::STATUS_NO_VOTING_RIGHT,
            'orienteering_commission_status' => FederationVotingRight::STATUS_NO_VOTING_RIGHT,
            'visual_commission_status' => FederationVotingRight::STATUS_NO_VOTING_RIGHT,
        ];
    }
}
