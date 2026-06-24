<?php

namespace Domain\Payments\DataTransferObject;

use Domain\Payments\Models\PaymentTransaction;

class PaymentTransactionData
{
    public function __construct(
        public ?string $document_id = null,
        public ?float $amount = null,
        public ?string $status = 'pending',
        public ?array $payment_data = null,
        public ?string $comment = null,
        public ?int $payment_method_id = null

    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            $data['document_id'] ?? null,
            $data['amount'] ?? null,
            $data['status'] ?? 'pending',
            $data['payment_data'] ?? null,
            $data['comment'] ?? null,
            $data['payment_method_id'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'document_id' => $this->document_id,
            'amount' => $this->amount,
            'status' => $this->status,
            'payment_data' => $this->payment_data,
            'comment' => $this->comment,
            'payment_method_id' => $this->payment_method_id,
        ];
    }

    public static function toModel(PaymentTransactionData $dto): PaymentTransaction
    {
        $model = new PaymentTransaction;
        $model->document_id = $dto->document_id;
        $model->amount = $dto->amount;
        $model->status = $dto->status;
        $model->payment_data = json_encode($dto->payment_data);
        $model->comment = $dto->comment;
        $model->payment_method_id = $dto->payment_method_id;

        return $model;
    }

    public static function fromModel(PaymentTransaction $model): self
    {
        return new self(
            $model->document_id,
            $model->amount,
            $model->status,
            json_decode($model->payment_data, true),
            $model->comment,
            $model->payment_method_id
        );
    }
}
