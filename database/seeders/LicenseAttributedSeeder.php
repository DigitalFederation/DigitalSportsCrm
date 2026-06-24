<?php

namespace Database\Seeders;

use App\Models\User;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\PendingLicenseAttributedState;
use Illuminate\Database\Seeder;

class LicenseAttributedSeeder extends Seeder
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
            LicenseAttributed::create([
                'status_class' => PendingLicenseAttributedState::class,
                'license_id' => fake()->randomElement(License::select('id')->pluck('id')),
                'federation_id' => fake()->randomElement(Federation::select('id')->pluck('id')),
                'model_type' => 'individual',
                'model_id' => $individual->id,
                'created_by' => User::first()->id,
                'updated_by' => User::first()->id,
            ]);
        }

        $entities = Entity::all();
        foreach ($entities as $entity) {
            LicenseAttributed::create([
                'status_class' => PendingLicenseAttributedState::class,
                'license_id' => fake()->randomElement(License::select('id')->pluck('id')),
                'federation_id' => fake()->randomElement(Federation::select('id')->pluck('id')),
                'model_type' => 'entity',
                'model_id' => $entity->id,
                'created_by' => User::first()->id,
                'updated_by' => User::first()->id,
            ]);
        }
    }
}
