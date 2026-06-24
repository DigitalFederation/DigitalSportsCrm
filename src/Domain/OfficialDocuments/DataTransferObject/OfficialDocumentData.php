<?php

namespace Domain\OfficialDocuments\DataTransferObject;

use App\Http\Requests\OfficialDocumentRequest;
use Domain\OfficialDocuments\Models\OfficialDocument;
use Illuminate\Support\Facades\Auth;

class OfficialDocumentData
{
    public function __construct(
        public ?string $name,
        public ?string $individual_id,
        public ?string $owner_type,
        public ?string $owner_id,
        public int $country_id,
        public ?string $type,
        public ?int $federation_id,
        public ?string $status_class,
        public ?string $expiry_date,
        public ?string $issue_date,
        public ?string $role,
        public ?string $created_by,
        public ?string $updated_by
    ) {}

    public static function fromArray(OfficialDocumentRequest|array $data): self
    {
        return new self(
            $data['name'] ?? null,
            $data['individual_id'] ?? null,
            $data['owner_type'] ?? null,
            $data['owner_id'] ?? null,
            $data['country_id'],
            $data['type'] ?? null,
            $data['federation_id'] ?? null,
            $data['status_class'] ?? null,
            $data['expiry_date'] ?? null,
            $data['issue_date'] ?? null,
            $data['role'] ?? null,
            $data['created_by'] ?? Auth::user()->id,
            $data['updated_by'] ?? Auth::user()->id,
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'individual_id' => $this->individual_id,
            'owner_type' => $this->owner_type,
            'owner_id' => $this->owner_id,
            'country_id' => $this->country_id,
            'type' => $this->type, // Convert the enum to its string value
            'federation_id' => $this->federation_id,
            'status_class' => $this->status_class,
            'expiry_date' => $this->expiry_date,
            'issue_date' => $this->issue_date,
            'role' => $this->role,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
        ];
    }

    public function toModel(): OfficialDocument
    {
        $model = new OfficialDocument;
        $model->name = $this->name;
        $model->individual_id = $this->individual_id;
        $model->owner_type = $this->owner_type;
        $model->owner_id = $this->owner_id;
        $model->country_id = $this->country_id;
        $model->type = $this->type; // Convert the enum to its string value
        $model->federation_id = $this->federation_id;
        $model->status_class = $this->status_class;
        $model->expiry_date = $this->expiry_date;
        $model->issue_date = $this->issue_date;
        $model->role = $this->role;
        $model->created_by = $this->created_by;
        $model->updated_by = $this->updated_by;

        return $model;
    }
}
