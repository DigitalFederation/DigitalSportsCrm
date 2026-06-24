<?php

namespace Database\Seeders;

use App\Models\Committee;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\ProfessionalRole;
use Illuminate\Database\Seeder;

class AthleteSeeder extends Seeder
{
    /**
     * Individual com licença de desporto e respetivo professional role
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(UserGroupSeeder::class);

        $individual = Individual::factory()->create();

        $this->call(CommitteeSeeder::class);

        $role = ProfessionalRole::factory()->create([
            'name' => 'Athlete',
            'code' => 'ATHLETE',
            'role' => null,
            'committee_id' => Committee::where('code', 'SPORT')->first()->id,
        ]);

        $license = \Domain\Licenses\Models\License::factory()->create([
            'committee_id' => Committee::where('code', 'SPORT')->first()->id,
            'professional_role_id' => $role->id,
        ]);

        \Domain\Licenses\Models\LicenseAttributed::factory()->create([
            'license_id' => $license->id,
        ])->attributable()->associate($individual);

    }
}
