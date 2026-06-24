<?php

namespace Domain\Entities\DataTransferObject;

class EntityData
{
    public function __construct(
        public int|array|null $federation_id,
        public readonly ?string $committee_id,
        public readonly int $country_id,
        public readonly ?string $vat_number,
        public readonly string $name,
        public readonly string $legal_name,
        public readonly ?string $legal_responsible_person,
        public readonly ?string $phone,
        public readonly ?string $email,
        public readonly ?string $member_code,
        public readonly ?string $website,
        public readonly ?string $address,
        public readonly ?string $postal_code,
        public readonly ?string $location,
        public readonly ?float $lat,
        public readonly ?float $lng,
        public $logo,
        public readonly ?string $national_federation_number,
        public readonly ?string $qrcode_path = null,
        public readonly ?string $facebook_url = null,
        public readonly ?string $x_url = null,
        public readonly ?string $instagram_url = null,
        public readonly ?string $linkedin_url = null,
        public readonly ?string $public_description = null,
        public readonly ?int $district_id = null,
        public readonly ?array $zone_ids = null
    ) {
        if (is_int($this->federation_id)) {
            $this->federation_id = [$this->federation_id];
        }

        if ($this->federation_id !== null && ! is_array($this->federation_id)) {
            $this->federation_id = array_filter([$this->federation_id]);
        }
    }

    public static function fromArray(array $data): EntityData
    {
        return new self(
            federation_id: $data['federation_id'] ?? null,
            committee_id: $data['committee_id'] ?? null,
            country_id: $data['country_id'],
            vat_number: $data['vat_number'] ?? null,
            name: $data['name'],
            legal_name: $data['legal_name'],
            legal_responsible_person: $data['legal_responsible_person'] ?? null,
            phone: $data['phone'] ?? null,
            email: $data['email'] ?? null,
            member_code: $data['member_code'] ?? null,
            website: $data['website'] ?? null,
            address: $data['address'] ?? null,
            postal_code: $data['postal_code'] ?? null,
            location: $data['location'] ?? null,
            lat: $data['lat'] ?? null,
            lng: $data['lng'] ?? null,
            logo: $data['logo'] ?? null,
            national_federation_number: $data['national_federation_number'] ?? null,
            qrcode_path: $data['qrcode_path'] ?? null,
            facebook_url: $data['facebook_url'] ?? null,
            x_url: $data['x_url'] ?? null,
            instagram_url: $data['instagram_url'] ?? null,
            linkedin_url: $data['linkedin_url'] ?? null,
            public_description: $data['public_description'] ?? null,
            district_id: $data['district_id'] ?? null,
            zone_ids: $data['zone_ids'] ?? null
        );
    }
}
