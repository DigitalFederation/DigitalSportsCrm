<?php

namespace Domain\EventApplications\Actions;

use Domain\EventApplications\Models\ApplicationTemplate;

class CreateApplicationTemplateAction
{
    public function execute(array $data): ApplicationTemplate
    {
        $template = ApplicationTemplate::create([
            'name' => $data['name'],
            'event_type' => $data['event_type'],
            'sport_id' => $data['sport_id'] ?? null,
            'event_category' => $data['event_category'] ?? null,
            'submission_start_date' => $data['submission_start_date'],
            'submission_end_date' => $data['submission_end_date'],
            'event_start_date' => $data['event_start_date'] ?? null,
            'event_end_date' => $data['event_end_date'] ?? null,
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'state' => $data['state'] ?? 'draft',
            'max_applications' => $data['max_applications'] ?? null,
            'created_by' => $data['created_by'],
        ]);

        return $template;
    }
}
