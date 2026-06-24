<?php

namespace Database\Seeders;

use App\Models\Committee;
use Domain\Certifications\Models\Certification;
use Domain\Certifications\Models\CertificationAttributed;
use Domain\Certifications\States\ActiveCertificationAttributedState;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Illuminate\Database\Seeder;

class InstructorSeeder extends Seeder
{
    /**
     * Individual com licença de Instructor e respetiva certificação e professional role
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(UserGroupSeeder::class);
        $individual = Individual::factory()->create();
        $this->call(CommitteeSeeder::class);
        $role = ProfessionalRole::factory()->create([
            'name' => 'Diving Instructor',
            'code' => 'DIVINGINSTRUCTOR',
            'role' => 'INSTRUCTOR',
            'committee_id' => Committee::where('code', 'DIVING')->first()->id,
        ]);

        $license = License::factory()->create([
            'committee_id' => Committee::where('code', 'DIVING')->first()->id,
            'professional_role_id' => $role->id,
        ]);

        LicenseAttributed::factory()->create([
            'license_id' => $license->id,
            'model_id' => $individual->id,
            'status_class' => ActiveLicenseAttributedState::class,
        ])->attributable()->associate($individual);

        $certification = Certification::factory()->create([
            'committee_id' => Committee::where('code', 'DIVING')->first()->id,
        ]);

        CertificationAttributed::factory()->create([
            'certification_id' => $certification->id,
            'individual_id' => $individual->id,
            'status_class' => ActiveCertificationAttributedState::class,
        ]);
    }
}
