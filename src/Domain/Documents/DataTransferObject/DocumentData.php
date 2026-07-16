<?php

namespace Domain\Documents\DataTransferObject;

use Domain\Documents\Models\Document;
use Domain\Documents\Models\DocumentType;
use Domain\Documents\States\DraftDocumentState;

/**
 * @mixin \Domain\Documents\DataTransferObject\DocumentData
 */
class DocumentData
{
    const DEFAULT_TYPE_CODE = 'ORD';

    public function __construct(
        public ?int $type_id = null,
        public ?int $number = null,
        /**
         * Number pad, used to complete the number with zeros
         */
        public ?int $number_pad = null,
        /**
         * Year of the document
         */
        public ?int $number_year = null,
        /**
         * Composite number, sum of $number and $number_pad and Year
         */
        public ?string $number_extended = null,
        public ?string $status_class = DraftDocumentState::class,
        public ?string $customer_name = null,
        public ?string $tax_number = null,
        public ?float $tax_percentage = null,
        public ?int $method_id = null,
        public ?float $net_value = null,
        public ?float $tax_value = null,
        public ?float $total_value = null,
        public ?string $due_date = null,
        public ?string $notes = null,
        public ?string $owner_type = null,
        public ?string $owner_id = null,
        public ?string $customer_city = null,
        public ?string $customer_address = null,
        public ?string $customer_country = null,
        public ?string $customer_postal_code = null,
        public ?string $currency = null
    ) {
        if (! $this->type_id) {
            $this->type_id = DocumentType::where('code', self::DEFAULT_TYPE_CODE)->first()->id;
        }
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['type_id'] ?? DocumentType::where('code', self::DEFAULT_TYPE_CODE)->first()->id,
            $data['number'] ?? null,
            $data['number_pad'] ?? null,
            $data['number_year'] ?? null,
            $data['number_extended'] ?? null,
            $data['status_class'] ?? DraftDocumentState::class,
            $data['customer_name'] ?? null,
            $data['tax_number'] ?? null,
            $data['tax_percentage'] ?? null,
            $data['method_id'] ?? null,
            $data['net_value'] ?? null,
            $data['tax_value'] ?? null,
            $data['total_value'] ?? null,
            $data['due_date'] ?? null,
            $data['notes'] ?? null,
            $data['owner_type'] ?? null,
            $data['owner_id'] ?? null,
            $data['customer_city'] ?? null,
            $data['customer_address'] ?? null,
            $data['customer_country'] ?? null,
            $data['customer_postal_code'] ?? null,
            $data['currency'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'type_id' => $this->type_id,
            'number' => $this->number,
            'number_pad' => $this->number_pad,
            'number_year' => $this->number_year,
            'number_extended' => $this->number_extended,
            'status_class' => $this->status_class,
            'customer_name' => $this->customer_name,
            'tax_number' => $this->tax_number,
            'tax_percentage' => $this->tax_percentage,
            'method_id' => $this->method_id,
            'net_value' => $this->net_value,
            'tax_value' => $this->tax_value,
            'total_value' => $this->total_value,
            'due_date' => $this->due_date,
            'notes' => $this->notes,
            'owner_type' => $this->owner_type,
            'owner_id' => $this->owner_id,
            'customer_city' => $this->customer_city,
            'customer_address' => $this->customer_address,
            'customer_country' => $this->customer_country,
            'customer_postal_code' => $this->customer_postal_code,
            'currency' => $this->currency,
        ];
    }

    public static function toModel(DocumentData $dto): Document
    {
        $model = new Document;
        $model->type_id = $dto->type_id;
        $model->number = $dto->number;
        $model->number_pad = $dto->number_pad;
        $model->number_year = $dto->number_year;
        $model->number_extended = $dto->number_extended;
        $model->status_class = $dto->status_class;
        $model->customer_name = $dto->customer_name;
        $model->tax_number = $dto->tax_number;
        $model->tax_percentage = $dto->tax_percentage;
        $model->method_id = $dto->method_id;
        $model->net_value = $dto->net_value;
        $model->tax_value = $dto->tax_value;
        $model->total_value = $dto->total_value;
        $model->due_date = $dto->due_date;
        $model->notes = $dto->notes;
        $model->owner_type = $dto->owner_type;
        $model->owner_id = $dto->owner_id;
        $model->customer_city = $dto->customer_city;
        $model->customer_address = $dto->customer_address;
        $model->customer_country = $dto->customer_country;
        $model->customer_postal_code = $dto->customer_postal_code;
        $model->currency = $dto->currency ?? config('app.currency', 'EUR');

        return $model;
    }

    public static function fromModel(Document $model): self
    {
        return new self(
            $model->type_id,
            $model->number,
            $model->number_pad,
            $model->number_year,
            $model->number_extended,
            $model->status_class,
            $model->customer_name,
            $model->tax_number,
            $model->tax_percentage,
            $model->method_id,
            $model->net_value,
            $model->tax_value,
            $model->total_value,
            $model->due_date,
            $model->notes,
            $model->owner_type,
            $model->owner_id,
            $model->customer_city,
            $model->customer_address,
            $model->customer_country,
            $model->customer_postal_code,
            $model->currency
        );
    }
}
