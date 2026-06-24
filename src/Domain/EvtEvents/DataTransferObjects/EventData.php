<?php

namespace Domain\EvtEvents\DataTransferObjects;

use Carbon\Carbon;
use Domain\EvtEvents\Models\Event;

/**
 * @mixin \Domain\EvtEvents\DataTransferObjects\EventData
 */
class EventData
{
    public function __construct(
        public ?int $id,
        public string $name,
        public ?string $notes,
        public ?string $location,
        public ?int $geo_zone_id,
        public ?string $start_date,
        public ?string $end_date,
        public ?string $category_selected,
        public ?string $event_category,
        public ?string $event_type,
        public ?string $event_geographical_coverage,
        public ?string $organization_type,
        public ?string $status_class_selected,
        public string $enrollment_type,
        public ?array $attachments = [],
        public ?float $event_fee = null,
        public ?string $event_fee_type = null,
        public ?int $sport_id = null,
        public ?string $competition_type = null,
        public ?array $selected_countries = [],
        public ?array $selected_sub_regions = [],
        public ?array $selected_geo_zones = [],
        public ?array $selected_referee_certifications = [],
        public ?array $selected_coach_certifications = [],
        public ?string $external_url = null,
        public ?string $regulations_url = null,
        public ?bool $allow_coach_enrollment = null,
        public ?bool $allow_referee_enrollment = null,
        public ?bool $allow_individual_enrollment = null,
        public ?bool $public_athlete_list = null,
        public ?bool $public_coach_list = null,
        public ?bool $public_referee_list = null,
        public ?bool $broadcast = null,
        public ?string $broadcast_information = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'] ?? null,
            $data['name'],
            $data['notes'] ?? null,
            $data['location'] ?? null,
            $data['geo_zone_id'] ?? null,
            isset($data['start_date']) ? new Carbon($data['start_date']) : null,
            isset($data['end_date']) ? new Carbon($data['end_date']) : null,
            $data['category_selected'] ?? null,
            $data['event_category'] ?? null,
            $data['event_type'] ?? null,
            $data['event_geographical_coverage'] ?? null,
            $data['organization_type'] ?? null,
            $data['status_class_selected'] ?? null,
            $data['enrollment_type'],
            $data['attachments'] ?? [],
            $data['event_fee'] ?? null,
            $data['event_fee_type'] ?? null,
            $data['sport_id'] ?? null,
            $data['competition_type'] ?? null,
            $data['selected_countries'] ?? [],
            $data['selected_sub_regions'] ?? [],
            $data['selected_geo_zones'] ?? [],
            $data['selected_referee_certifications'] ?? [],
            $data['selected_coach_certifications'] ?? [],
            $data['external_url'] ?? null,
            $data['regulations_url'] ?? null,
            $data['allow_coach_enrollment'] ?? null,
            $data['allow_referee_enrollment'] ?? null,
            $data['allow_individual_enrollment'] ?? null,
            $data['public_athlete_list'] ?? null,
            $data['public_coach_list'] ?? null,
            $data['public_referee_list'] ?? null,
            $data['broadcast'] ?? null,
            $data['broadcast_information'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'notes' => $this->notes,
            'location' => $this->location,
            'geo_zone_id' => $this->geo_zone_id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'category_selected' => $this->category_selected,
            'event_category' => $this->event_category,
            'event_type' => $this->event_type,
            'event_geographical_coverage' => $this->event_geographical_coverage,
            'organization_type' => $this->organization_type,
            'status_class_selected' => $this->status_class_selected,
            'enrollment_type' => $this->enrollment_type,
            'attachments' => $this->attachments,
            'event_fee' => $this->event_fee,
            'event_fee_type' => $this->event_fee_type,
            'sport_id' => $this->sport_id,
            'competition_type' => $this->competition_type,
            'selected_countries' => $this->selected_countries,
            'selected_sub_regions' => $this->selected_sub_regions,
            'selected_geo_zones' => $this->selected_geo_zones,
            'selected_referee_certifications' => $this->selected_referee_certifications,
            'selected_coach_certifications' => $this->selected_coach_certifications,
            'external_url' => $this->external_url,
            'regulations_url' => $this->regulations_url,
            'allow_coach_enrollment' => $this->allow_coach_enrollment,
            'allow_referee_enrollment' => $this->allow_referee_enrollment,
            'allow_individual_enrollment' => $this->allow_individual_enrollment,
            'public_athlete_list' => $this->public_athlete_list,
            'public_coach_list' => $this->public_coach_list,
            'public_referee_list' => $this->public_referee_list,
            'broadcast' => $this->broadcast,
            'broadcast_information' => $this->broadcast_information,
        ];
    }

    public static function toModel(EventData $dto): Event
    {
        $model = new Event;
        $model->fill([
            'id' => $dto->id,
            'name' => $dto->name,
            'notes' => $dto->notes,
            'location' => $dto->location,
            'geo_zone_id' => $dto->geo_zone_id,
            'start_date' => $dto->start_date,
            'end_date' => $dto->end_date,
            'category_selected' => $dto->category_selected,
            'event_category' => $dto->event_category,
            'event_type' => $dto->event_type,
            'event_geographical_coverage' => $dto->event_geographical_coverage,
            'organization_type' => $dto->organization_type,
            'status_class_selected' => $dto->status_class_selected,
            'enrollment_type' => $dto->enrollment_type,
            'attachments' => $dto->attachments,
            'event_fee' => $dto->event_fee,
            'event_fee_type' => $dto->event_fee_type,
            'sport_id' => $dto->sport_id,
            'competition_type' => $dto->competition_type,
            'selected_countries' => $dto->selected_countries,
            'selected_sub_regions' => $dto->selected_sub_regions,
            'selected_geo_zones' => $dto->selected_geo_zones,
            'selected_referee_certifications' => $dto->selected_referee_certifications,
            'selected_coach_certifications' => $dto->selected_coach_certifications,
            'external_url' => $dto->external_url,
            'regulations_url' => $dto->regulations_url,
            'allow_coach_enrollment' => $dto->allow_coach_enrollment,
            'allow_referee_enrollment' => $dto->allow_referee_enrollment,
            'allow_individual_enrollment' => $dto->allow_individual_enrollment,
            'public_athlete_list' => $dto->public_athlete_list,
            'public_coach_list' => $dto->public_coach_list,
            'public_referee_list' => $dto->public_referee_list,
            'broadcast' => $dto->broadcast,
            'broadcast_information' => $dto->broadcast_information,
        ]);

        return $model;
    }

    public static function fromModel(Event $model): self
    {
        return new self(
            $model->id,
            $model->name,
            $model->notes,
            $model->location,
            $model->geo_zone_id,
            $model->start_date,
            $model->end_date,
            $model->category_selected,
            $model->event_category,
            $model->event_type,
            $model->event_geographical_coverage,
            $model->organization_type,
            $model->status_class_selected,
            $model->enrollment_type,
            $model->attachments,
            $model->event_fee,
            $model->event_fee_type,
            $model->sport_id,
            $model->competition_type,
            $model->selected_countries,
            $model->selected_sub_regions,
            $model->selected_geo_zones,
            $model->selected_referee_certifications,
            $model->selected_coach_certifications,
            $model->external_url,
            $model->regulations_url,
            $model->allow_coach_enrollment,
            $model->allow_referee_enrollment,
            $model->allow_individual_enrollment,
            $model->public_athlete_list,
            $model->public_coach_list,
            $model->public_referee_list,
            $model->broadcast,
            $model->broadcast_information
        );
    }
}
