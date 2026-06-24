<?php

namespace Domain\Entities\DataTransferObject;

use Domain\Entities\States\PendingEntityProfessionalRoleState;

class EntityProfessionalRoleData
{
    public function __construct(
        public readonly int $entity_id,
        public readonly string $individual_id,
        public readonly int $professional_role_id,
        public readonly ?string $entity_name,
        public readonly ?string $individual_name,
        public readonly ?string $role_name,
        public readonly string $status_class,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            $data['entity_id'] ?? (auth()->user()->entities->first()->id ?? null),
            $data['individual_id'],
            $data['professional_role_id'],
            $data['entity_name'] ?? null,
            $data['individual_name'] ?? null,
            $data['role_name'] ?? null,
            PendingEntityProfessionalRoleState::class
        );
    }

    public function toArray(): array
    {
        return [
            'entity_id' => $this->entity_id,
            'individual_id' => $this->individual_id,
            'professional_role_id' => $this->professional_role_id,
            'entity_name' => $this->entity_name,
            'individual_name' => $this->individual_name,
            'role_name' => $this->role_name,
            'status_class' => $this->status_class,
        ];
    }
}
