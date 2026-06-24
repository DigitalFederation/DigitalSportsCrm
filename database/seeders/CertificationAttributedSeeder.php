<?php

namespace Database\Seeders;

use App\Models\User;
use Domain\Certifications\Models\Certification;
use Domain\Certifications\Models\CertificationAttributed;
use Domain\Certifications\Models\CertificationAttributedInstructor;
use Domain\Certifications\Models\CertificationSlotType;
use Domain\Certifications\States\PendingCertificationAttributedState;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Illuminate\Database\Seeder;

class CertificationAttributedSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $individuals = Individual::all();
        foreach ($individuals as $individual) {
            CertificationAttributed::create([
                'certification_id' => fake()->randomElement(Certification::select('id')->pluck('id')),
                'federation_id' => fake()->randomElement(Federation::select('id')->pluck('id')),
                'entity_id' => fake()->randomElement(Entity::select('id')->pluck('id')),
                'status_class' => PendingCertificationAttributedState::class,
                'individual_id' => $individual->id,
                'instructor_id' => Individual::first()->id,
                'created_by' => User::first()->id,
                'updated_by' => User::first()->id,
                'slot_type_id' => CertificationSlotType::first()->id,
            ]);
        }

        $certifications = CertificationAttributed::all();

        foreach ($certifications as $certification) {
            CertificationAttributedInstructor::create([
                'attributed_id' => $certification->id,
                'individual_id' => fake()->randomElement(Individual::select('id')->pluck('id')),
                'is_main' => fake()->boolean,
            ]);
        }
    }
}
