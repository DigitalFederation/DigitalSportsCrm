<?php

namespace Domain\EventApplications\Actions;

use Domain\EventApplications\Models\EventApplication;
use Domain\EventApplications\States\DraftApplicationState;

class CreateApplicationAction
{
    public function execute(array $data): EventApplication
    {
        $application = EventApplication::create([
            'application_type' => $data['application_type'],
            'template_id' => $data['template_id'] ?? null,
            'entity_id' => $data['entity_id'],
            'entity_type' => $data['entity_type'],
            'status_class' => $data['status_class'] ?? DraftApplicationState::class,
            'submitted_at' => $data['submitted_at'] ?? null,
            'event_name' => $data['event_name'] ?? null,
            'event_type' => $data['event_type'] ?? null,
            'event_category' => $data['event_category'] ?? null,
            'sport_id' => $data['sport_id'] ?? null,
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
            'district_id' => $data['district_id'] ?? null,
            'municipality' => $data['municipality'] ?? null,
            'responsible_name' => $data['responsible_name'] ?? null,
            'responsible_phone' => $data['responsible_phone'] ?? null,
            'target_audience' => $data['target_audience'] ?? null,
            'expected_participants' => $data['expected_participants'] ?? null,
            'form_data' => $data['form_data'] ?? null,
        ]);

        return $application;
    }
}
