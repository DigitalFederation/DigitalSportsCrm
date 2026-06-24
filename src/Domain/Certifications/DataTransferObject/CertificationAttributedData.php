<?php

namespace Domain\Certifications\DataTransferObject;

use App\Http\Requests\CertificationAttributedRequest;
use Carbon\Carbon;
use Domain\Certifications\Models\Certification;
use Domain\Certifications\Models\CertificationAttributed;
use Domain\Certifications\States\PendingCertificationAttributedState;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Illuminate\Support\Str;

class CertificationAttributedData
{
    public function __construct(
        public string $id,
        public string $certification_id,
        public ?int $federation_id,
        public ?int $entity_id,
        public array $individual_ids,
        public ?string $director_instructor_id,
        public ?string $national_code,
        public array $assistant_instructor_ids,
        public ?string $code,
        public ?string $number,
        public ?int $activator_id,
        public ?string $activator_type,
        public ?string $activated_at,
        public ?string $current_term_starts_at,
        public ?string $current_term_ends_at,
        public ?string $notes,
        public string $status_class,
        public ?string $certification_name,
        public ?string $federation_name,
        public ?string $entity_name,
        public ?string $holder_name,
        public ?string $international_code,
        public ?bool $approved_by_federation,
        public ?bool $approve_without_slots,
        public ?string $individual_id = null,
        public ?string $instructor_id = null,
        public string $price_option = 'digital',
        public ?float $price_paid = null,
        public ?string $batch_id = null
    ) {
        $this->id = Str::uuid()->toString();

        // Certifications always belong to the main federation
        $mainFederation = Federation::where('is_default_federation', true)->first();
        if ($mainFederation) {
            $this->federation_id = $mainFederation->id;
            $this->federation_name = $mainFederation->legal_name ?? $mainFederation->name;
        }
        if ($this->entity_id) {
            $this->entity_name = Entity::select('id', 'name')->where('id', $entity_id)->pluck('name')->first();
        }
        if ($this->certification_id) {
            $this->certification_name = Certification::select('id', 'name')->where('id', $certification_id)->pluck('name')->first();
        }
    }

    public static function fromArray(CertificationAttributedRequest|array $data): self
    {
        // Certifications always belong to the main federation
        $mainFederation = Federation::where('is_default_federation', true)->with('country')->first();
        $federation_id = $mainFederation?->id;
        $federation = $mainFederation;

        // Handle both individual_ids array and individual_id
        $individualIds = [];
        if (isset($data['individual_ids'])) {
            $individualIds = $data['individual_ids'];
        } elseif (isset($data['individual_id'])) {
            $individualIds = [$data['individual_id']];
        }

        return new self(
            $data['id'] ?? Str::uuid()->toString(),
            $data['certification_id'],
            $federation_id ?? null,
            $data['entity_id'] ?? null,
            $individualIds,
            $data['director_instructor_id'] ?? null,
            $data['national_code'] ?? null,
            $data['assistant_instructor_ids'] ?? [],
            $data['code'] ?? null,
            $data['number'] ?? null,
            isset($data['activator_id']) ? $data['activator_id'] : (isset($data['activated_at']) ? $federation_id : null),
            isset($data['activator_type']) ? $data['activator_type'] : (isset($data['activated_at']) ? Federation::class : null),
            $data['activated_at'] ?? null,
            $data['current_term_starts_at'] ?? Carbon::today()->toDateTimeString(),
            $data['current_term_ends_at'] ?? null, // Almost all certifications are not time limited
            $data['notes'] ?? null,
            $data['status_class'] ?? PendingCertificationAttributedState::class,
            $data['certification_name'] ?? ($data['certification_id'] ? Certification::select('id', 'name')->where('id', $data['certification_id'])->pluck('name')->first() : null),
            $data['federation_name'] ?? $federation?->name,
            $data['entity_name'] ?? (! empty($data['entity_id']) ? Entity::select('id', 'name')->where('id', $data['entity_id'])->pluck('name')->first() : null),
            $data['holder_name'] ?? null,
            $data['international_code'] ?? null,
            $data['approved_by_federation'] ?? null,
            $data['approve_without_slots'] ?? null,
            isset($data['individual_id']) ? $data['individual_id'] : (! empty($individualIds) ? $individualIds[0] : null),
            isset($data['instructor_id']) ? $data['instructor_id'] : null,
            $data['price_option'] ?? 'digital',
            isset($data['price_paid']) && $data['price_paid'] !== '' ? (float) $data['price_paid'] : null,
            $data['batch_id'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'certification_id' => $this->certification_id,
            'federation_id' => $this->federation_id,
            'entity_id' => $this->entity_id,
            'individual_ids' => $this->individual_ids,
            'individual_id' => $this->individual_id,
            'director_instructor_id' => $this->director_instructor_id,
            'national_code' => $this->national_code,
            'assistant_instructor_ids' => $this->assistant_instructor_ids,
            'code' => $this->code,
            'number' => $this->number,
            'activator_id' => $this->activator_id,
            'activator_type' => $this->activator_type,
            'activated_at' => $this->activated_at,
            'current_term_starts_at' => $this->current_term_starts_at,
            'current_term_ends_at' => $this->current_term_ends_at,
            'notes' => $this->notes,
            'status_class' => $this->status_class,
            'certification_name' => $this->certification_name,
            'federation_name' => $this->federation_name,
            'entity_name' => $this->entity_name,
            'holder_name' => $this->holder_name,
            'international_code' => $this->international_code,
            'approved_by_federation' => $this->approved_by_federation,
            'approve_without_slots' => $this->approve_without_slots,
            'instructor_id' => $this->instructor_id,
            'price_option' => $this->price_option,
            'price_paid' => $this->price_paid,
            'batch_id' => $this->batch_id,
        ];
    }

    public function toModel(): CertificationAttributed
    {
        $model = new CertificationAttributed;
        $model->id = $this->id;
        $model->certification_id = $this->certification_id;
        $model->federation_id = $this->federation_id;
        $model->entity_id = $this->entity_id;
        $model->individual_ids = $this->individual_ids;
        $model->director_instructor_id = $this->director_instructor_id;
        $model->national_code = $this->national_code;
        $model->assistant_instructor_ids = $this->assistant_instructor_ids;
        $model->code = $this->code;
        $model->number = $this->number;
        $model->activator_id = $this->activator_id;
        $model->activator_type = $this->activator_type;
        $model->activated_at = $this->activated_at;
        $model->current_term_starts_at = $this->current_term_starts_at;
        $model->current_term_ends_at = $this->current_term_ends_at;
        $model->notes = $this->notes;
        $model->status_class = $this->status_class;
        $model->certification_name = $this->certification_name;
        $model->federation_name = $this->federation_name;
        $model->entity_name = $this->entity_name;
        $model->international_code = $this->international_code;
        $model->approved_by_federation = $this->approved_by_federation;
        $model->approve_without_slots = $this->approve_without_slots;
        $model->price_option = $this->price_option;
        $model->price_paid = $this->price_paid;

        return $model;
    }
}
