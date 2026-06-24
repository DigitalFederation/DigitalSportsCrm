<?php

namespace Domain\Documents\DataTransferObject;

use Domain\Documents\Actions\CalculateSubtractTaxAction;
use Domain\Documents\Models\DocumentDetail;

class DocumentDetailData
{
    public function __construct(
        public int|string|null $document_id,
        public int|string $owner_id,
        public string $owner_type,
        public int $quantity = 1,
        public ?string $description = null,
        public ?string $reference = null,
        public ?float $unit_value = null,
        public ?float $net_value = null,
        public ?float $tax_value = null,
        public ?float $tax_percentage = null,
        public ?float $total_value = null,
        public ?bool $is_debit = false,
        public ?string $customer_name = null,
    ) {
        if (! empty($unit_value)) {

            $subtractTax = new CalculateSubtractTaxAction;
            $taxes = $subtractTax($unit_value, $quantity, $tax_percentage);

            $this->net_value = $taxes['net_value'];
            $this->tax_value = $taxes['tax_value'];
            $this->total_value = $taxes['total_value'];
            $this->tax_percentage = $taxes['tax_number'];
        }
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['document_id'] ?? null,
            $data['owner_id'],
            $data['owner_type'],
            $data['quantity'] ?? 1,
            $data['description'] ?? null,
            $data['reference'] ?? null,
            $data['unit_value'] ?? null,
            $data['net_value'] ?? null,
            $data['tax_value'] ?? null,
            $data['tax_percentage'] ?? null,
            $data['total_value'] ?? null,
            $data['is_debit'] ?? false,
            $data['customer_name'] ?? false
        );
    }

    public function toArray(): array
    {
        return [
            'document_id' => $this->document_id,
            'owner_id' => $this->owner_id,
            'owner_type' => $this->owner_type,
            'quantity' => $this->quantity,
            'description' => $this->description,
            'reference' => $this->reference,
            'unit_value' => $this->unit_value,
            'net_value' => $this->net_value,
            'tax_value' => $this->tax_value,
            'tax_percentage' => $this->tax_percentage,
            'total_value' => $this->total_value,
            'is_debit' => $this->is_debit,
            'customer_name' => $this->customer_name,
        ];
    }

    public static function toModel(DocumentDetailData $dto): DocumentDetail
    {
        $model = new DocumentDetail;
        $model->document_id = $dto->document_id;
        $model->description = $dto->description;
        $model->owner_id = $dto->owner_id;
        $model->owner_type = $dto->owner_type;
        $model->reference = $dto->reference;
        $model->quantity = $dto->quantity;
        $model->unit_value = $dto->unit_value;
        $model->net_value = $dto->net_value;
        $model->tax_value = $dto->tax_value;
        $model->tax_percentage = $dto->tax_percentage;
        $model->total_value = $dto->total_value;
        $model->is_debit = $dto->is_debit;
        $model->customer_name = $dto->customer_name;

        return $model;
    }

    public static function fromModel(DocumentDetail $model): self
    {
        return new self(
            $model->document_id,
            $model->owner_id,
            $model->owner_type,
            $model->quantity,
            $model->description,
            $model->reference,
            $model->unit_value,
            $model->net_value,
            $model->tax_value,
            $model->tax_percentage,
            $model->total_value,
            $model->is_debit,
            $model->customer_name
        );
    }
}
