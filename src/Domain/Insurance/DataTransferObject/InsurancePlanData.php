<?php

namespace Domain\Insurance\DataTransferObject;

class InsurancePlanData
{
    public function __construct(
        public readonly string $name,
        public readonly string $targetAudience,
        public readonly string $type,
        public readonly ?float $individualFee,
        public readonly ?float $entityFee,
        public readonly ?string $policyNumber,
        public readonly ?string $policyNumberPrefix,
        public readonly ?int $policyNumberSequence,
        public readonly ?string $policyNumberFormat,
        public readonly ?int $period,
        public readonly ?string $periodUnit,
        public readonly ?string $description,
        public readonly ?string $insuredActivity,
        public readonly ?string $territorialScope,
        public readonly ?string $cmasLicenseCode,
        public readonly int $vatRate = 23,
        public readonly bool $requiresOfficialDocument = false,
        public readonly ?string $requiredDocumentType = null,
        public readonly bool $requiresActiveAffiliation = false,
        public readonly ?string $startDate = null,
        public readonly ?string $endDate = null,
        public readonly ?string $insurerAddress = null,
        public readonly ?string $insurerEmail = null,
        public readonly ?string $insurerPhone = null,
        public readonly ?string $applicableDeductibles = null,
        public readonly ?string $coverageDetails = null,
        public readonly ?string $insuranceCompanyName = null,
        public readonly ?string $moloniReference = null,
    ) {}

    public static function fromRequest(array $request): self
    {
        return new self(
            name: $request['name'],
            targetAudience: $request['target_audience'],
            type: $request['type'],
            individualFee: isset($request['individual_fee']) ? (float) $request['individual_fee'] : null,
            entityFee: isset($request['entity_fee']) ? (float) $request['entity_fee'] : null,
            policyNumber: $request['policy_number'] ?? null,
            policyNumberPrefix: $request['policy_number_prefix'] ?? null,
            policyNumberSequence: isset($request['policy_number_sequence']) ? (int) $request['policy_number_sequence'] : null,
            policyNumberFormat: $request['policy_number_format'] ?? null,
            period: isset($request['period']) ? (int) $request['period'] : null,
            periodUnit: $request['period_unit'] ?? null,
            description: $request['description'] ?? null,
            insuredActivity: $request['insured_activity'] ?? null,
            territorialScope: $request['territorial_scope'] ?? null,
            cmasLicenseCode: $request['cmas_license_code'] ?? null,
            vatRate: $request['vat_rate'] ?? 23,
            requiresOfficialDocument: (bool) ($request['requires_official_document'] ?? false),
            requiredDocumentType: $request['required_document_type'] ?? null,
            requiresActiveAffiliation: (bool) ($request['requires_active_affiliation'] ?? false),
            startDate: $request['start_date'] ?? null,
            endDate: $request['end_date'] ?? null,
            insurerAddress: $request['insurer_address'] ?? null,
            insurerEmail: $request['insurer_email'] ?? null,
            insurerPhone: $request['insurer_phone'] ?? null,
            applicableDeductibles: $request['applicable_deductibles'] ?? null,
            coverageDetails: $request['coverage_details'] ?? null,
            insuranceCompanyName: $request['insurance_company_name'] ?? null,
            moloniReference: $request['moloni_reference'] ?? null,
        );
    }
}
