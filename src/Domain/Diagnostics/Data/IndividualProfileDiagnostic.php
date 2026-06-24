<?php

namespace Domain\Diagnostics\Data;

use Domain\Individuals\Models\Individual;

class IndividualProfileDiagnostic
{
    public function __construct(
        public Individual $individual,
        public array $federationMemberships,
        public array $entityMemberships,
        public array $professionalRoles,
        public array $activeLicenses,
        public array $certifications,
        public array $quickStatus
    ) {}

    public function toArray(): array
    {
        return [
            'individual' => [
                'id' => $this->individual->id,
                'name' => $this->individual->full_name,
                'member_code' => $this->individual->member_code,
                'email' => $this->individual->email,
                'gender' => $this->individual->gender,
                'birthday' => $this->individual->birthday?->format('Y-m-d'),
            ],
            'federationMemberships' => $this->federationMemberships,
            'entityMemberships' => $this->entityMemberships,
            'professionalRoles' => $this->professionalRoles,
            'activeLicenses' => $this->activeLicenses,
            'certifications' => $this->certifications,
            'quickStatus' => $this->quickStatus,
        ];
    }

    public function canBeAthlete(): bool
    {
        return $this->quickStatus['athlete']['eligible'] ?? false;
    }

    public function canBeCoach(): bool
    {
        return $this->quickStatus['coach']['eligible'] ?? false;
    }

    public function canBeReferee(): bool
    {
        return $this->quickStatus['referee']['eligible'] ?? false;
    }

    public function canBeOfficial(): bool
    {
        return $this->quickStatus['official']['eligible'] ?? false;
    }
}
