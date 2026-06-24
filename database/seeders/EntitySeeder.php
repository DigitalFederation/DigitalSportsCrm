<?php

namespace Database\Seeders;

use Domain\Entities\Models\Entity;
use Illuminate\Database\Seeder;

class EntitySeeder extends Seeder
{
    /**
     *  Entidade - associação com um user e uma federação
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(UserGroupSeeder::class);

        $entity = Entity::factory()->create();
        $entity->federations()->attach(\Domain\Federations\Models\Federation::factory()->create());
        $entity->individuals()->attach(\Domain\Individuals\Models\Individual::factory()->create());
    }
}
