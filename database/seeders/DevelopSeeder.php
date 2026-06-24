<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\User;
use Domain\Documents\Models\Document;
use Domain\Documents\Models\DocumentDetail;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\States\ActiveIndividualEntityState;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Illuminate\Database\Seeder;

class DevelopSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(UserSeeder::class);

        $user_federation_1 = User::factory([
            'name' => 'federation',
            'email' => 'federation@example.test',
            'group_id' => Group::select('id')->where('code', 'FEDERATION')->pluck('id')->first(),
        ]);

        $user_individual_1 = User::factory()
            ->create([
                'name' => 'individual 1',
                'email' => 'individual1@example.test',
                'group_id' => Group::select('id')->where('code', 'INDIVIDUAL')->pluck('id')->first(),
            ])
            ->assignRole('individual');

        $user_individual_2 = User::factory()
            ->create([
                'name' => 'individual 2',
                'email' => 'individual2@example.test',
                'group_id' => Group::select('id')->where('code', 'INDIVIDUAL')->pluck('id')->first(),
            ])
            ->assignRole('individual');

        $user_individual_3 = User::factory()
            ->create([
                'name' => 'individual 3',
                'email' => 'individual3@example.test',
                'group_id' => Group::select('id')->where('code', 'INDIVIDUAL')->pluck('id')->first(),
            ])
            ->assignRole('individual');

        $user_entity_1 = User::factory([
            'name' => 'entity',
            'email' => 'entity@example.test',
            'group_id' => Group::select('id')->where('code', 'ENTITY')->pluck('id')->first(),
        ]);

        $individual_1 = Individual::factory()
            ->for($user_individual_1)
            ->create();

        $individual_2 = Individual::factory()
            ->for($user_individual_2)
            ->create();

        $individual_3 = Individual::factory()
            ->for($user_individual_3)
            ->create();

        $individuals = Individual::all();

        $entities = Entity::factory()
            ->has($user_entity_1)
            ->create();

        $federation_1 = Federation::factory()
            ->has($user_federation_1)
            ->hasEntities($entities)
            ->hasMemberships(1)
            ->create();

        // Get Federation
        foreach ($individuals as $individual) {
            $individual->federations()->attach($federation_1);
            $individual->entities()->attach($entities, ['status_class' => ActiveIndividualEntityState::class]);
        }

        // User especifico para Federação e Login
        $fed1 = User::where('email', 'federation@example.test')->first();
        $fed1->assignRole('federation');
        $ent1 = User::where('email', 'entity@example.test')->first();
        $ent1->assignRole('entity');

        $this->call(CertificationAttributedSeeder::class);
        $this->call(CertificationSlotPriceSeeder::class);
        $this->call(CertificationSlotSeeder::class);

        $license = License::where('name', 'Diving Instructor')->first();

        LicenseAttributed::factory()
            ->for($license)
            ->create([
                'license_name' => $license->name,
                'holder_name' => $individual_3->name,
                'federation_id' => $federation_1->id,
                'model_id' => $individual_3->id,
            ]);

        Document::factory()
            ->create();

        DocumentDetail::factory()
            ->create();

    }
}
