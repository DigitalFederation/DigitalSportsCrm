<?php

namespace Database\Seeders;

use App\Models\Committee;
use Domain\Entities\Models\Entity;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseType;
use Illuminate\Database\Seeder;

class InternationalLicenseSeeder extends Seeder
{
    /**
     * Run the database seeds for international International Licenses.
     */
    public function run(): void
    {
        $entityType = LicenseType::where('name', 'entity')->first();
        $individualType = LicenseType::where('name', 'individual')->first();

        $internationalLicenses = [
            // Entity International Licenses
            [
                'name' => 'CMAS International Diving School',
                'committee_id' => Committee::select('id')->where('code', 'DIVING')->pluck('id')->first(),
                'type_id' => $entityType->id,
                'unit_value' => 150.00,
                'unit_value_entity' => 150.00,
                'unit_value_individual' => 150.00,
                'active' => true,
                'requester_model' => Entity::class,
                'professional_role_id' => null,
                'sport_id' => null,
                'interval' => 1,
                'interval_unit' => 'years',
                'license_code' => 'CMAS-DS',
            ],
            [
                'name' => 'CMAS International Diving Center',
                'committee_id' => Committee::select('id')->where('code', 'DIVING')->pluck('id')->first(),
                'type_id' => $entityType->id,
                'unit_value' => 200.00,
                'unit_value_entity' => 200.00,
                'unit_value_individual' => 200.00,
                'active' => true,
                'requester_model' => Entity::class,
                'professional_role_id' => null,
                'sport_id' => null,
                'interval' => 1,
                'interval_unit' => 'years',
                'license_code' => 'CMAS-DC',
            ],
            [
                'name' => 'CMAS International Scientific Research Center',
                'committee_id' => Committee::select('id')->where('code', 'SCIENTIFIC')->pluck('id')->first(),
                'type_id' => $entityType->id,
                'unit_value' => 250.00,
                'unit_value_entity' => 250.00,
                'unit_value_individual' => 250.00,
                'active' => true,
                'requester_model' => Entity::class,
                'professional_role_id' => null,
                'sport_id' => null,
                'interval' => 1,
                'interval_unit' => 'years',
                'license_code' => 'CMAS-SRC',
            ],

            // Individual International Licenses
            [
                'name' => 'CMAS International Instructor License',
                'committee_id' => Committee::select('id')->where('code', 'DIVING')->pluck('id')->first(),
                'type_id' => $individualType->id,
                'unit_value' => 75.00,
                'unit_value_entity' => 60.00,
                'unit_value_individual' => 75.00,
                'active' => true,
                'requester_model' => null,
                'professional_role_id' => ProfessionalRole::select('id')->where('code', 'DIVINGINSTRUCTORLEADER')->pluck('id')->first(),
                'sport_id' => null,
                'interval' => 1,
                'interval_unit' => 'years',
                'license_code' => 'CMAS-INST',
            ],
            [
                'name' => 'CMAS International Scientific Diver License',
                'committee_id' => Committee::select('id')->where('code', 'SCIENTIFIC')->pluck('id')->first(),
                'type_id' => $individualType->id,
                'unit_value' => 50.00,
                'unit_value_entity' => 40.00,
                'unit_value_individual' => 50.00,
                'active' => true,
                'requester_model' => null,
                'professional_role_id' => ProfessionalRole::select('id')->where('code', 'SCIENTIFICDIVER')->pluck('id')->first(),
                'sport_id' => null,
                'interval' => 1,
                'interval_unit' => 'years',
                'license_code' => 'CMAS-SCI',
            ],
            [
                'name' => 'CMAS International Athlete License',
                'committee_id' => Committee::select('id')->where('code', 'SPORT')->pluck('id')->first(),
                'type_id' => $individualType->id,
                'unit_value' => 40.00,
                'unit_value_entity' => 30.00,
                'unit_value_individual' => 40.00,
                'active' => true,
                'requester_model' => null,
                'professional_role_id' => ProfessionalRole::select('id')->where('code', 'ATHLETE')->pluck('id')->first(),
                'sport_id' => null,
                'interval' => 1,
                'interval_unit' => 'years',
                'license_code' => 'CMAS-ATH',
            ],
            [
                'name' => 'CMAS International Coach License',
                'committee_id' => Committee::select('id')->where('code', 'SPORT')->pluck('id')->first(),
                'type_id' => $individualType->id,
                'unit_value' => 60.00,
                'unit_value_entity' => 50.00,
                'unit_value_individual' => 60.00,
                'active' => true,
                'requester_model' => null,
                'professional_role_id' => ProfessionalRole::select('id')->where('code', 'COACH')->pluck('id')->first(),
                'sport_id' => null,
                'interval' => 1,
                'interval_unit' => 'years',
                'license_code' => 'CMAS-COACH',
            ],
        ];

        foreach ($internationalLicenses as $license) {
            License::create($license);
        }
    }
}
