<?php

namespace App\View\Components\Certification;

use Illuminate\View\Component;

class TrainingEntity extends Component
{
    public $entity;
    public bool $hasEntity;
    public array $contactInfo = [];
    public array $locationInfo = [];
    public $entityMedia;
    public $hasLogo = false;
    public $hasQrCode = false;

    public function __construct($certification)
    {
        $this->entity = $certification->entity;
        $this->hasEntity = ! is_null($this->entity);

        if ($this->hasEntity) {
            $this->entityMedia = $this->entity->getMedia('logo')->first();
            $this->hasLogo = ! is_null($this->entityMedia);
            $this->hasQrCode = ! empty($this->entity->qrcode_path);
            // Contact and location info
            $this->setContactInfo();
            $this->setLocationInfo();
        }

    }

    protected function setContactInfo(): void
    {
        $this->contactInfo = array_filter([
            'email' => $this->entity->email ?? null,
            'phone' => $this->entity->phone ?? null,
            'website' => $this->entity->website ?? null,
        ]);
    }

    protected function setLocationInfo(): void
    {
        $this->locationInfo = array_filter([
            'address' => $this->entity->address ?? null,
            'location' => $this->entity->location ?? null,
            'postal_code' => $this->entity->postal_code ?? null,
            'country' => $this->entity->country?->name ?? null,
            'country_iso' => $this->entity->country?->iso ?? null,
        ]);
    }

    protected function hasContactInfo(): bool
    {
        return ! empty($this->contactInfo);
    }

    protected function hasLocationInfo(): bool
    {
        return ! empty($this->locationInfo);
    }

    public function render()
    {
        return view('components.certification.training-entity', [
            'hasContactInfo' => $this->hasContactInfo(),
            'hasLocationInfo' => $this->hasLocationInfo(),
        ]);
    }
}
