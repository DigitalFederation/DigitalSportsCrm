<?php

namespace Database\Factories;

use App\Models\User;
use Domain\Attachments\Models\Attachment;
use Domain\Federations\Models\Federation;
use Illuminate\Database\Eloquent\Factories\Factory;

// Import User model
// Import Federation model

class AttachmentFactory extends Factory
{
    protected $model = Attachment::class;

    public function definition()
    {

        return [
            'name' => $this->faker->word,
            'owner_type' => $this->faker->randomElement([User::class, Federation::class]),
            'owner_id' => $this->faker->uuid,
            'recipient_name' => $this->faker->randomElement(['individual', 'all', 'federation']),
            'recipient_type' => null,
            'recipient_id' => null,
            'category_id' => AttachmentCategoryFactory::new()->create()->id,
            'committee_id' => $this->faker->numberBetween(1, 3),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Attachment $attachment) {
            // Attachments to licenses, certifications, and professional roles can be created here if needed
            // Assuming these methods exist and are correct.
            // Uncomment and adjust the following lines as necessary:
            // $attachment->licenses()->attach(License::factory()->create()->id);
            // $attachment->certifications()->attach(Certification::factory()->create()->id);
            // $attachment->professionalRoles()->attach(ProfessionalRole::factory()->create()->id);
            // $attachment->federations()->attach(Federation::factory()->create()->id);
        });
    }

    public function withOwner($ownerType, $ownerId)
    {
        return $this->state(function (array $attributes) use ($ownerType, $ownerId) {
            return [
                'owner_type' => $ownerType,
                'owner_id' => $ownerId,
            ];
        });
    }

    // State function to set the recipient to an individual.
    public function forIndividual($individualId)
    {
        return $this->state(function (array $attributes) use ($individualId) {
            return [
                'recipient_name' => 'individual',
                'recipient_id' => $individualId,
            ];
        });
    }

    // State function to set the owner to a federation (assuming this is a valid scenario).
    public function forFederation($federationId)
    {
        return $this->state(function (array $attributes) use ($federationId) {
            return [
                'owner_type' => Federation::class,
                'owner_id' => $federationId,
            ];
        });
    }
}
