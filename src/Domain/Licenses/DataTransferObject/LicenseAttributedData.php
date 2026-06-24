<?php

namespace Domain\Licenses\DataTransferObject;

use Domain\Federations\Models\Federation;
use Domain\Licenses\Models\License;
use Domain\Licenses\States\PendingLicenseAttributedState;
use Illuminate\Support\Str;

class LicenseAttributedData
{
    public function __construct(
        public string $id,
        public int $license_id,
        public int $federation_id,
        public string $model_type,
        public string $model_id,
        public ?string $license_name = null,
        public ?string $holder_name = null,
        public ?string $federation_name = null,
        public ?string $federation_code = null,
        public ?string $license_code = null,
        public ?float $total_value = null,
        public ?string $activated_at = null,
        public ?string $current_term_starts_at = null,
        public ?string $current_term_ends_at = null,
        public ?string $last_billing_at = null,
        public ?string $cancelled_at = null,
        public ?string $notes = null,
        public ?string $license_number = null,
        public ?string $status_class = PendingLicenseAttributedState::class,
        public ?string $owner_member_code = null,
        // Property that represents the Model that requests this license
        public ?string $requester_model_type = null,
        public ?bool $requires_cmas_approval = null,
        public ?string $requested_by_id = null,
        public ?string $request_type = 'direct',
        public ?int $payment_id = null,
        public ?string $purchased_at = null
    ) {
        $this->id = Str::uuid()->toString();

        if ($this->federation_id) {
            $fed = Federation::find($this->federation_id);
            if ($fed) {
                $this->federation_name = $fed->legal_name;
                $this->federation_code = $fed->member_code;
            }
        }

        if ($this->license_id && ! $this->license_code) {
            $license = License::withoutGlobalScopes()->find($this->license_id);
            if ($license) {
                $this->license_code = $license->license_code;
            }
        }
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'status_class' => $this->status_class,
            'license_id' => $this->license_id,
            'federation_id' => $this->federation_id,
            'model_type' => $this->model_type,
            'model_id' => $this->model_id,
            'license_name' => $this->license_name,
            'holder_name' => $this->holder_name,
            'federation_name' => $this->federation_name,
            'federation_code' => $this->federation_code,
            'license_code' => $this->license_code,
            'total_value' => $this->total_value,
            'activated_at' => $this->activated_at,
            'current_term_starts_at' => $this->current_term_starts_at,
            'current_term_ends_at' => $this->current_term_ends_at,
            'last_billing_at' => $this->last_billing_at,
            'cancelled_at' => $this->cancelled_at,
            'notes' => $this->notes,
            'license_number' => $this->license_number,
            'owner_member_code' => $this->owner_member_code,
            'requester_model_type' => $this->requester_model_type,
            'requires_cmas_approval' => $this->requires_cmas_approval,
            'requested_by_id' => $this->requested_by_id,
            'request_type' => $this->request_type,
            'payment_id' => $this->payment_id,
            'purchased_at' => $this->purchased_at,
        ];
    }

    public static function fromArray(array $attributes): self
    {
        return new self(
            id: Str::uuid()->toString(),
            license_id: $attributes['license_id'],
            federation_id: $attributes['federation_id'],
            model_type: $attributes['model_type'],
            model_id: $attributes['model_id'],
            license_name: $attributes['license_name'] ?? null,
            holder_name: $attributes['holder_name'] ?? null,
            federation_name: $attributes['federation_name'] ?? null,
            federation_code: $attributes['federation_code'] ?? null,
            license_code: $attributes['license_code'] ?? null,
            total_value: $attributes['total_value'] ?? null,
            activated_at: $attributes['activated_at'] ?? null,
            current_term_starts_at: $attributes['current_term_starts_at'] ?? null,
            current_term_ends_at: $attributes['current_term_ends_at'] ?? null,
            last_billing_at: $attributes['last_billing_at'] ?? null,
            cancelled_at: $attributes['cancelled_at'] ?? null,
            notes: $attributes['notes'] ?? null,
            license_number: $attributes['license_number'] ?? null,
            status_class: $attributes['status_class'],
            owner_member_code: $attributes['owner_member_code'] ?? null,
            requester_model_type: $attributes['requester_model_type'] ?? null,
            requires_cmas_approval: $attributes['requires_cmas_approval'] ?? null,
            requested_by_id: $attributes['requested_by_id'] ?? null,
            request_type: $attributes['request_type'] ?? 'direct',
            payment_id: $attributes['payment_id'] ?? null,
            purchased_at: $attributes['purchased_at'] ?? null,
        );
    }
}
