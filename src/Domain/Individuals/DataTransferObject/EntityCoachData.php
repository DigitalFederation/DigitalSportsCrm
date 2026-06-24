<?php

namespace Domain\Individuals\DataTransferObject;

use Domain\Individuals\States\Coach\PendingEntityCoachState;

class EntityCoachData
{
    public function __construct(
        public readonly int $coach_id,
        public readonly int $entity_id,
        public readonly ?string $coach_name,
        public readonly ?string $entity_name,
        public readonly string $status_class,
    ) {}

    public static function fromArray(array $data): EntityCoachData
    {
        return new self(
            $data['coach_id'],
            $data['entity_id'] ?? (auth()->user()->entities()->first()->id ?? null),
            $data['coach_name'] ?? null,
            $data['entity_name'] ?? null,
            PendingEntityCoachState::class
        );
    }

    public function toArray(): array
    {
        return [
            'coach_id' => $this->coach_id,
            'entity_id' => $this->entity_id,
            'coach_name' => $this->coach_name,
            'entity_name' => $this->entity_name,
            'status_class' => $this->status_class,
        ];
    }
}
