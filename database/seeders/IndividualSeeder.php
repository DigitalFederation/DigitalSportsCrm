<?php

namespace Database\Seeders;

use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Illuminate\Database\Seeder;

class IndividualSeeder extends Seeder
{
    /**
     * Individual - associação com um user, uma entidade e uma federação
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(UserGroupSeeder::class);

        $individual = Individual::factory()->create();

        $individual->entities()->attach(Entity::factory()->create());
        $individual->federations()->attach(Federation::factory()->create());

    }
}
