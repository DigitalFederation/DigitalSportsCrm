<?php

namespace Domain\EventApplications\Actions;

use App\Enums\EventApplicationTypeEnum;
use Domain\EventApplications\Models\EventApplication;
use Illuminate\Database\Eloquent\Collection;

class CheckForConflictingDirectSubmissionAction
{
    public function execute(int $entityId, array $data, ?int $excludeApplicationId = null): Collection
    {
        $query = EventApplication::where('entity_id', $entityId)
            ->where('application_type', EventApplicationTypeEnum::DirectSubmission->value)
            ->whereNotIn('status_class', [
                'Domain\EventApplications\States\RejectedApplicationState',
                'Domain\EventApplications\States\PublishedApplicationState',
            ]);

        if ($excludeApplicationId) {
            $query->where('id', '!=', $excludeApplicationId);
        }

        if (isset($data['event_name'])) {
            $query->where('event_name', 'LIKE', '%' . $data['event_name'] . '%');
        }

        if (isset($data['event_type'])) {
            $query->where('event_type', $data['event_type']);
        }

        if (isset($data['start_date'])) {
            $query->where('start_date', $data['start_date']);
        }

        return $query->get();
    }
}
