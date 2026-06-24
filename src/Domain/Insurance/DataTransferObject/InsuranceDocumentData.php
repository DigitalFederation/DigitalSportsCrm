<?php

namespace Domain\Insurance\DataTransferObject;

class InsuranceDocumentData
{
    public function __construct(
        public readonly int $insurancePlanId,
        public readonly int $documentableId,
        public readonly string $documentableType,
        public readonly string $issueDate,
        public readonly string $expiryDate,
        public readonly string $status
    ) {}

    public static function fromRequest(array $request): self
    {
        return new self(
            insurancePlanId: $request['insurance_plan_id'],
            documentableId: $request['documentable_id'],
            documentableType: $request['documentable_type'],
            issueDate: $request['issue_date'],
            expiryDate: $request['expiry_date'],
            status: $request['status'],
        );
    }
}
