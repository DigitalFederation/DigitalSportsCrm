<?php

namespace Domain\EventApplications\Actions;

use Domain\EventApplications\Models\EventApplication;

class CheckDuplicateApplicationAction
{
    public function execute(int $entityId, ?int $templateId): bool
    {
        if ($templateId === null) {
            return false;
        }

        return EventApplication::where('entity_id', $entityId)
            ->where('template_id', $templateId)
            ->whereNull('deleted_at')
            ->exists();
    }

    public function getExistingApplication(int $entityId, int $templateId): ?EventApplication
    {
        return EventApplication::where('entity_id', $entityId)
            ->where('template_id', $templateId)
            ->whereNull('deleted_at')
            ->first();
    }
}
