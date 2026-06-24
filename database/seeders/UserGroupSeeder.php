<?php

namespace Database\Seeders;

use App\Models\Group;
use Illuminate\Database\Seeder;

class UserGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $groups = [
            [
                'name' => 'Individual',
                'code' => 'INDIVIDUAL',
            ],
            [
                'name' => 'Entity',
                'code' => 'ENTITY',
            ],
            [
                'name' => 'Federation',
                'code' => 'FEDERATION',
            ],
            [
                'name' => 'Admin',
                'code' => 'ADMIN',
            ],
        ];

        Group::insert($groups);
    }
}
