<?php

namespace Domain\Payments\DataTransferObject;

class PaymentResponseData
{
    public function __construct(
        public readonly string $status, // 'success', 'pending', 'failed', 'redirect'
        public readonly ?string $transactionId = null,
        public readonly ?string $redirectUrl = null,
        public readonly ?array $metadata = null,
        public readonly ?string $errorMessage = null,
        public readonly ?string $gatewayReference = null,
        public readonly ?float $amount = null,
        public readonly ?string $currency = null
    ) {}

    public static function success(
        string $transactionId,
        ?string $gatewayReference = null,
        ?float $amount = null,
        ?string $currency = 'EUR',
        ?array $metadata = null
    ): self {
        return new self(
            status: 'success',
            transactionId: $transactionId,
            gatewayReference: $gatewayReference,
            amount: $amount,
            currency: $currency,
            metadata: $metadata
        );
    }

    public static function pending(
        string $transactionId,
        ?string $gatewayReference = null,
        ?array $metadata = null
    ): self {
        return new self(
            status: 'pending',
            transactionId: $transactionId,
            gatewayReference: $gatewayReference,
            metadata: $metadata
        );
    }

    public static function redirect(
        string $redirectUrl,
        ?string $transactionId = null,
        ?string $gatewayReference = null,
        ?array $metadata = null
    ): self {
        return new self(
            status: 'redirect',
            redirectUrl: $redirectUrl,
            transactionId: $transactionId,
            gatewayReference: $gatewayReference,
            metadata: $metadata
        );
    }

    public static function failed(
        string $errorMessage,
        ?string $transactionId = null,
        ?array $metadata = null
    ): self {
        return new self(
            status: 'failed',
            transactionId: $transactionId,
            errorMessage: $errorMessage,
            metadata: $metadata
        );
    }

    public function isSuccess(): bool
    {
        return $this->status === 'success';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function requiresRedirect(): bool
    {
        return $this->status === 'redirect';
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'transaction_id' => $this->transactionId,
            'redirect_url' => $this->redirectUrl,
            'metadata' => $this->metadata,
            'error_message' => $this->errorMessage,
            'gateway_reference' => $this->gatewayReference,
            'amount' => $this->amount,
            'currency' => $this->currency,
        ];
    }
}
