<?php

namespace Domain\Individuals\DataTransferObject;

class IndividualData
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $surname,
        public readonly ?string $native_name,
        public readonly int $country_id,
        public readonly ?string $birthdate,
        public readonly ?string $gender,
        public readonly ?string $address,
        public readonly ?string $location,
        public readonly ?string $postal_code,
        public readonly ?string $vat_number,
        public readonly ?string $phone,
        public readonly ?string $doc_ref_type,
        public readonly ?string $doc_ref,
        public readonly ?string $doc_ref_validation_date,
        public readonly ?string $email,
        public readonly ?string $member_code,
        public string $user_id,
        public int|array|null $federation_id,
        public ?int $entity_id,
        public $logo,
        public ?array $professional_role_ids,
        public ?string $national_federation_number = null,
        public ?string $member_number = null,
        public readonly ?string $facebook_url = null,
        public readonly ?string $x_url = null,
        public readonly ?string $instagram_url = null,
        public readonly ?string $linkedin_url = null,
        public readonly ?int $district_id = null,
        public readonly ?array $zone_ids = null,
    ) {
        $this->professional_role_ids = $professional_role_ids;
    }

    public static function fromArray(array $data, string $userId): IndividualData
    {
        return new self(
            $data['name'],
            $data['surname'] ?? null,
            $data['native_name'] ?? null,
            $data['country_id'],
            $data['birthdate'] ?? null,
            $data['gender'] ?? null,
            $data['address'] ?? null,
            $data['location'] ?? null,
            $data['postal_code'] ?? null,
            $data['vat_number'] ?? null,
            $data['phone'] ?? null,
            $data['doc_ref_type'] ?? null,
            $data['doc_ref'] ?? null,
            $data['doc_ref_validation_date'] ?? null,
            $data['email'] ?? null,
            $data['member_code'] ?? null,
            $userId,
            $data['federation_id'] ?? null,
            $data['entity_id'] ?? null,
            $data['logo'] ?? null,
            $data['professional_role_ids'] ?? null,
            $data['national_federation_number'] ?? null,
            $data['member_number'] ?? null,
            $data['facebook_url'] ?? null,
            $data['x_url'] ?? null,
            $data['instagram_url'] ?? null,
            $data['linkedin_url'] ?? null,
            $data['district_id'] ?? null,
            $data['zone_ids'] ?? null,
        );
    }
}
