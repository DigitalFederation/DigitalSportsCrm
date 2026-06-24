<?php

namespace Database\Seeders;

use App\Models\Committee;
use Domain\Individuals\Models\ProfessionalRole;
use Illuminate\Database\Seeder;

class DivingProfessionalRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $divingCommitteeId = Committee::where('code', 'DIVING')->value('id');

        $divingProfessionalRoles = [
            [
                'name' => 'Diving Professional',
                'code' => 'DIVINGPROFESSIONAL',
                'role' => 'DIVINGPROFESSIONAL',
                'committee_id' => $divingCommitteeId,
            ],
        ];

        foreach ($divingProfessionalRoles as $role) {
            ProfessionalRole::updateOrCreate(
                ['code' => $role['code']],
                $role
            );
        }
    }
}
