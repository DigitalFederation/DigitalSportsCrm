<?php

namespace Domain\Federations\DataTransferObject;

class FederationData
{
    public function __construct(
        public int $country_id,
        public ?int $parent_id,
        public string $name,
        public ?bool $is_local,
        public ?string $category,
        public ?bool $is_manual,
        public ?string $legal_name,
        public ?string $address,
        public ?string $location,
        public ?float $lat,
        public ?float $lng,
        public ?string $website,
        public ?string $email,
        public ?string $phone,
        public ?string $zip_code,
        public ?string $vat_number,
        public string $member_code,
        public $logo,
        public $attachments,
        public bool $is_default_federation,
        public bool $can_issue_certifications,
    ) {}

    public static function fromArray(array $data): FederationData
    {
        return new self(
            $data['country_id'],
            $data['parent_id'] ?? null,
            $data['name'],
            $data['is_local'] ?? 0,
            $data['category'] ?? null,
            $data['is_manual'] ?? false,
            $data['legal_name'] ?? null,
            $data['address'] ?? null,
            $data['location'] ?? null,
            $data['latitude'] ?? null,
            $data['longitude'] ?? null,
            $data['website'] ?? null,
            $data['email'] ?? null,
            $data['phone'] ?? null,
            $data['zip_code'] ?? null,
            $data['vat_number'] ?? null,
            $data['member_code'],
            $data['logo'] ?? null,
            $data['attachments'] ?? null,
            $data['is_default_federation'] ?? false,
            $data['can_issue_certifications'] ?? true,
        );
    }

    public function toArray(): array
    {
        return [
            'country_id' => $this->country_id,
            'parent_id' => $this->parent_id,
            'name' => $this->name,
            'is_local' => $this->is_local,
            'category' => $this->category,
            'is_manual' => $this->is_manual,
            'legal_name' => $this->legal_name,
            'address' => $this->address,
            'location' => $this->location,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'website' => $this->website,
            'email' => $this->email,
            'phone' => $this->phone,
            'zip_code' => $this->zip_code,
            'vat_number' => $this->vat_number,
            'member_code' => $this->member_code,
            'logo' => $this->logo,
            'attachments' => $this->attachments,
            'is_default_federation' => $this->is_default_federation,
            'can_issue_certifications' => $this->can_issue_certifications,
        ];
    }
}
