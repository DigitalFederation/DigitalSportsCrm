<?php

namespace Domain\EvtEvents\DataTransferObjects;

class PricingData
{
    public $id;
    public $eventId;
    public $disciplineId;
    public $priceType;
    public $targetGroup;
    public $startDate;
    public $endDate;
    public $price;
    public $isActive;
    public $pricingOption;
    public $description;
    public $enrollmentRole;
    public function __construct(array $data)
    {
        $this->id = $data['id'] ?? null;
        $this->eventId = $data['event_id'] ?? null;
        $this->disciplineId = $data['discipline_id'] ?? null;
        $this->priceType = $data['price_type'] ?? null;
        $this->targetGroup = $data['target_group'] ?? null;
        $this->startDate = $data['start_date'] ?? null;
        $this->endDate = $data['end_date'] ?? null;
        $this->price = $data['price'] ?? null;
        $this->isActive = $data['is_active'] ?? true;
        $this->pricingOption = $data['pricing_option'] ?? null;
        $this->description = $data['description'] ?? null;
        $this->enrollmentRole = $data['enrollment_role'] ?? null;

    }

    /**
     * Validates the DTO data.
     *
     * @return bool|array Returns true if validation passes, or an array of validation errors.
     */
    public function validate()
    {
        $errors = [];

        if (empty($this->priceType)) {
            $errors['price_type'] = 'Price type is required.';
        }

        if (empty($this->targetGroup)) {
            $errors['target_group'] = 'Target group is required.';
        }

        if ($this->price === null) {
            $errors['price'] = 'Price is required.';
        }

        return empty($errors) ? true : $errors;
    }

    /**
     * Create a new instance of the DTO from an array.
     *
     * @return static
     */
    public static function fromArray(array $data)
    {
        return new static([
            'id' => $data['id'] ?? null,
            'event_id' => $data['event_id'] ?? null,
            'discipline_id' => $data['discipline_id'] ?? null,
            'price_type' => $data['price_type'] ?? null,
            'target_group' => $data['target_group'] ?? null,
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
            'price' => $data['price'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'pricing_option' => $data['pricing_option'] ?? null,
            'description' => $data['description'] ?? null,
            'enrollment_role' => $data['enrollment_role'] ?? null,
        ]);
    }

    /**
     * Convert the DTO to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'event_id' => $this->eventId,
            'discipline_id' => $this->disciplineId,
            'price_type' => $this->priceType,
            'target_group' => $this->targetGroup,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'price' => $this->price,
            'is_active' => $this->isActive,
            'pricing_option' => $this->pricingOption,
            'description' => $this->description,
            'enrollment_role' => $this->enrollmentRole,
        ];
    }
}
