<?php

namespace App\Enums;

enum OfficialDocumentTypeEnum: string
{
    case DivingProfessionalCodeOfConduct = 'DivingProfessionalCodeOfConduct';
    case MedicalStatement = 'MedicalStatement';
    case MedicalFirstAidCprOxygenProvider = 'MedicalFirstAidCprOxygenProvider';
    case ProfessionalLiabilityInsurance = 'ProfessionalLiabilityInsurance';
    case InternationalAthleteCodeOfConduct = 'InternationalAthleteCodeOfConduct';
    case ADELCertificate = 'ADELCertificate';
    case InsuranceAthlete = 'InsuranceAthlete';
    case InternationalCoachCodeOfConduct = 'InternationalCoachCodeOfConduct';
    case TptdGrauI = 'TptdGrauI';
    case TptdGrauII = 'TptdGrauII';
    case TptdGrauIII = 'TptdGrauIII';
    case TptdGrauIV = 'TptdGrauIV';
    case InternationalRefereeJudgeCodeOfConduct = 'InternationalRefereeJudgeCodeOfConduct';
    case TeamOfficialCodeOfConduct = 'TeamOfficialCodeOfConduct';

    // Federation-specific types
    case Statutes = 'Statutes';
    case GovernmentNOCRecognition = 'GovernmentNOCRecognition';
    case FederationRepresentatives = 'FederationRepresentatives';
    case OtherDocument = 'OtherDocument';

    // Entity-specific document types (simplified per requirement)
    case EntityStatutes = 'EntityStatutes';
    case EntityAccidentInsurance = 'EntityAccidentInsurance';
    case EntityLiabilityInsurance = 'EntityLiabilityInsurance';
    case EntityLegalPersonality = 'EntityLegalPersonality';
    case EntityInaugurationMinutes = 'EntityInaugurationMinutes';
    case EntityOther = 'EntityOther';

    // Diving-specific document types
    case DivingInstructorCertificate = 'DivingInstructorCertificate';
    case CompressorCertificate = 'CompressorCertificate';
    case DivingInsurance = 'DivingInsurance';
    case TechnicalDirectorAcceptance = 'TechnicalDirectorAcceptance';
    case DivingProfessionalMedicalStatement = 'DivingProfessionalMedicalStatement';
    case DivingProfessionalInsurance = 'DivingProfessionalInsurance';
    case BusinessLicense = 'BusinessLicense';
    case TaxRegistration = 'TaxRegistration';
    case LegalRepresentativeDocument = 'LegalRepresentativeDocument';
    case EntityInsurance = 'EntityInsurance';
    case BankAccountDocument = 'BankAccountDocument';
    case FacilityLicense = 'FacilityLicense';
    case SafetyCompliance = 'SafetyCompliance';
    case EmergencyActionPlan = 'EmergencyActionPlan';
    case EquipmentMaintenanceRecords = 'EquipmentMaintenanceRecords';
    case SafetyEquipmentInventory = 'SafetyEquipmentInventory';
    case OperatingProcedures = 'OperatingProcedures';
    case EquipmentInspectionCertificates = 'EquipmentInspectionCertificates';
    case RentalAgreementTemplate = 'RentalAgreementTemplate';
    case GasAnalysisEquipmentCertificates = 'GasAnalysisEquipmentCertificates';
    case OperatorQualifications = 'OperatorQualifications';
    case QualityControlProcedures = 'QualityControlProcedures';

    /**
     * Get document types relevant to individuals (excluding entity/federation categories).
     *
     * @return array<self>
     */
    public static function individualTypes(): array
    {
        $categories = self::groupByCategory();
        $individualCategories = ['instructor-leader', 'diver', 'athlete', 'coach', 'referee-judge', 'team-official', 'diving-professional'];

        $types = [];
        foreach ($individualCategories as $category) {
            foreach ($categories[$category] ?? [] as $type) {
                $types[$type->value] = $type;
            }
        }

        return array_values($types);
    }

    public static function groupByCategory(): array
    {
        return [
            'instructor-leader' => [
                self::DivingProfessionalCodeOfConduct,
                self::MedicalStatement,
                self::MedicalFirstAidCprOxygenProvider,
                self::ProfessionalLiabilityInsurance,
            ],
            'diver' => [
                self::MedicalStatement,
            ],
            'athlete' => [
                self::InternationalAthleteCodeOfConduct,
                self::MedicalStatement,
                self::ADELCertificate,
                self::InsuranceAthlete,
            ],
            'coach' => [
                self::InternationalCoachCodeOfConduct,
                self::MedicalStatement,
                self::ADELCertificate,
                self::ProfessionalLiabilityInsurance,
                self::TptdGrauI,
                self::TptdGrauII,
                self::TptdGrauIII,
                self::TptdGrauIV,
            ],
            'referee-judge' => [
                self::InternationalRefereeJudgeCodeOfConduct,
                self::MedicalStatement,
                self::ADELCertificate,
                self::ProfessionalLiabilityInsurance,
            ],
            'team-official' => [
                self::TeamOfficialCodeOfConduct,
                self::MedicalStatement,
                self::ADELCertificate,
            ],
            'entity' => [
                self::EntityStatutes,
                self::EntityAccidentInsurance,
                self::EntityLiabilityInsurance,
                self::EntityLegalPersonality,
                self::EntityInaugurationMinutes,
                self::EntityOther,
            ],
            'diving-entity' => [
                self::BusinessLicense,
                self::TaxRegistration,
                self::LegalRepresentativeDocument,
                self::EntityInsurance,
                self::DivingInsurance,
                self::TechnicalDirectorAcceptance,
                self::CompressorCertificate,
                self::BankAccountDocument,
                self::FacilityLicense,
                self::SafetyCompliance,
                self::EmergencyActionPlan,
                self::EquipmentMaintenanceRecords,
                self::SafetyEquipmentInventory,
                self::OperatingProcedures,
                self::EquipmentInspectionCertificates,
                self::RentalAgreementTemplate,
                self::GasAnalysisEquipmentCertificates,
                self::OperatorQualifications,
                self::QualityControlProcedures,
            ],
            'diving-professional' => [
                self::DivingProfessionalMedicalStatement,
                self::DivingProfessionalInsurance,
                self::OtherDocument,
            ],
            'federation' => [
                self::Statutes,
                self::GovernmentNOCRecognition,
                self::FederationRepresentatives,
                self::OtherDocument,
            ],
        ];
    }

    // Method to return the enum keys for a specific committee
    public static function getKeysByCommittee(string $committeeCode): array
    {
        // Define the mapping of committee codes to enum keys
        $committeeMap = [
            'DIVING' => [
                self::DivingProfessionalCodeOfConduct,
                self::MedicalStatement,
                self::MedicalFirstAidCprOxygenProvider,
                self::ProfessionalLiabilityInsurance,
            ],
            'SCIENTIFIC' => [
                self::DivingProfessionalCodeOfConduct,
                self::MedicalStatement,
                self::MedicalFirstAidCprOxygenProvider,
                self::ProfessionalLiabilityInsurance,
            ],
            'SPORT' => [
                self::InternationalAthleteCodeOfConduct,
                self::MedicalStatement,
                self::ADELCertificate,
                self::InsuranceAthlete,
                self::InternationalCoachCodeOfConduct,
                self::ProfessionalLiabilityInsurance,
                self::InternationalRefereeJudgeCodeOfConduct,
            ],
        ];

        // Retrieve the enum values for the specified committee
        $enumValuesForCommittee = $committeeMap[$committeeCode] ?? [];

        // Return the enum keys
        return array_map(fn ($enumValue) => $enumValue->name, $enumValuesForCommittee);
    }

    public static function toString($type): string
    {
        $value = is_string($type) ? $type : $type->value;

        $translated = __('official_documents.types.' . $value);

        // If translation key not found, format the value as readable text
        if ($translated === 'official_documents.types.' . $value) {
            $formatted = preg_replace('/([a-z])([A-Z])/', '$1 $2', $value);
            $formatted = str_replace('_', ' ', $formatted);

            return ucwords($formatted);
        }

        return $translated;
    }

    /**
     * Sort an array of enum cases alphabetically by their translated name.
     *
     * @param  array<self>  $types
     * @return array<self>
     */
    public static function sortedByTranslation(array $types): array
    {
        usort($types, fn (self $a, self $b) => strcmp(
            self::toString($a),
            self::toString($b)
        ));

        return $types;
    }

    /**
     * Build a sorted key=>label array from enum cases, sorted alphabetically by translated name.
     *
     * @param  array<self>  $types
     * @return array<string, string>
     */
    public static function toSortedArray(array $types): array
    {
        $result = array_combine(
            array_map(fn (self $enum) => $enum->value, $types),
            array_map(fn (self $enum) => self::toString($enum), $types)
        );

        asort($result);

        return $result;
    }

    /**
     * Get document types for a specific enrollment type.
     * Maps enrollment roles to their corresponding document category.
     */
    public static function getDocumentsForEnrollmentType(string $enrollmentType): array
    {
        $categoryMap = [
            'athlete' => 'athlete',
            'coach' => 'coach',
            'referee' => 'referee-judge',
            'official' => 'team-official',
        ];

        $category = $categoryMap[$enrollmentType] ?? null;

        if ($category === null) {
            return [];
        }

        return self::groupByCategory()[$category] ?? [];
    }
}
