<?php

namespace Database\Seeders;

use App\Enums\OfficialDocumentTypeEnum;
use App\Models\Committee;
use Domain\Entities\Models\Entity;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseType;
use Illuminate\Database\Seeder;

class DivingEntityLicenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure committee exists
        if (! Committee::where('code', 'DIVING')->exists()) {
            Committee::create(['code' => 'DIVING', 'name' => 'Technical Committee']);
        }

        // Ensure license types exist
        if (! LicenseType::where('name', 'entity')->exists()) {
            LicenseType::create(['name' => 'entity']);
        }
        if (! LicenseType::where('name', 'individual')->exists()) {
            LicenseType::create(['name' => 'individual', 'is_individual' => true]);
        }

        $divingCommitteeId = Committee::where('code', 'DIVING')->value('id');
        $entityLicenseTypeId = LicenseType::where('name', 'entity')->value('id');

        $divingEntityLicenses = [
            [
                'name' => 'Licença de Escola de Mergulho',
                'committee_id' => $divingCommitteeId,
                'type_id' => $entityLicenseTypeId,
                'unit_value' => 250.00,
                'unit_value_entity' => 250.00,
                'active' => true,
                'professional_role_id' => null,
                'sport_id' => null,
                'interval' => 1,
                'interval_unit' => 'years',
                'requires_official_documents' => true,
                'required_document_types' => json_encode([
                    OfficialDocumentTypeEnum::BusinessLicense->value,
                    OfficialDocumentTypeEnum::TaxRegistration->value,
                    OfficialDocumentTypeEnum::EntityInsurance->value,
                    OfficialDocumentTypeEnum::DivingInsurance->value,
                    OfficialDocumentTypeEnum::TechnicalDirectorAcceptance->value,
                    OfficialDocumentTypeEnum::EmergencyActionPlan->value,
                ]),
                'requester_model' => Entity::class,
                'requires_admin_validation' => true,
            ],
            [
                'name' => 'Licença de Centro de Mergulho',
                'committee_id' => $divingCommitteeId,
                'type_id' => $entityLicenseTypeId,
                'unit_value' => 350.00,
                'unit_value_entity' => 350.00,
                'active' => true,
                'professional_role_id' => null,
                'sport_id' => null,
                'interval' => 1,
                'interval_unit' => 'years',
                'requires_official_documents' => true,
                'required_document_types' => json_encode([
                    OfficialDocumentTypeEnum::BusinessLicense->value,
                    OfficialDocumentTypeEnum::TaxRegistration->value,
                    OfficialDocumentTypeEnum::EntityInsurance->value,
                    OfficialDocumentTypeEnum::DivingInsurance->value,
                    OfficialDocumentTypeEnum::TechnicalDirectorAcceptance->value,
                    OfficialDocumentTypeEnum::EquipmentMaintenanceRecords->value,
                    OfficialDocumentTypeEnum::CompressorCertificate->value,
                    OfficialDocumentTypeEnum::SafetyEquipmentInventory->value,
                    OfficialDocumentTypeEnum::OperatingProcedures->value,
                ]),
                'requester_model' => Entity::class,
                'requires_admin_validation' => true,
            ],
            [
                'name' => 'Licença de Aluguer de Equipamentos',
                'committee_id' => $divingCommitteeId,
                'type_id' => $entityLicenseTypeId,
                'unit_value' => 200.00,
                'unit_value_entity' => 200.00,
                'active' => true,
                'professional_role_id' => null,
                'sport_id' => null,
                'interval' => 1,
                'interval_unit' => 'years',
                'requires_official_documents' => true,
                'required_document_types' => json_encode([
                    OfficialDocumentTypeEnum::BusinessLicense->value,
                    OfficialDocumentTypeEnum::TaxRegistration->value,
                    OfficialDocumentTypeEnum::EntityInsurance->value,
                    OfficialDocumentTypeEnum::TechnicalDirectorAcceptance->value,
                    OfficialDocumentTypeEnum::EquipmentInspectionCertificates->value,
                    OfficialDocumentTypeEnum::RentalAgreementTemplate->value,
                ]),
                'requester_model' => Entity::class,
                'requires_admin_validation' => true,
            ],
            [
                'name' => 'Licença de Estação de Enchimento',
                'committee_id' => $divingCommitteeId,
                'type_id' => $entityLicenseTypeId,
                'unit_value' => 400.00,
                'unit_value_entity' => 400.00,
                'active' => true,
                'professional_role_id' => null,
                'sport_id' => null,
                'interval' => 1,
                'interval_unit' => 'years',
                'requires_official_documents' => true,
                'required_document_types' => json_encode([
                    OfficialDocumentTypeEnum::BusinessLicense->value,
                    OfficialDocumentTypeEnum::TaxRegistration->value,
                    OfficialDocumentTypeEnum::EntityInsurance->value,
                    OfficialDocumentTypeEnum::TechnicalDirectorAcceptance->value,
                    OfficialDocumentTypeEnum::CompressorCertificate->value,
                    OfficialDocumentTypeEnum::GasAnalysisEquipmentCertificates->value,
                    OfficialDocumentTypeEnum::OperatorQualifications->value,
                    OfficialDocumentTypeEnum::QualityControlProcedures->value,
                ]),
                'requester_model' => Entity::class,
                'requires_admin_validation' => true,
            ],
        ];

        foreach ($divingEntityLicenses as $license) {
            License::updateOrCreate(
                ['name' => $license['name'], 'committee_id' => $license['committee_id']],
                $license
            );
        }
    }
}
