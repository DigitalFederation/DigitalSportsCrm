<?php

namespace Domain\EventApplications\Actions;

use Domain\EventApplications\Models\EventApplication;
use Illuminate\Database\Eloquent\Collection;

class CheckConflictingApplicationsAction
{
    public function execute(array $data, ?int $excludeApplicationId = null): Collection
    {
        $query = EventApplication::query()
            ->where('event_type', $data['event_type'])
            ->whereNotIn('status_class', [
                'Domain\EventApplications\States\RejectedApplicationState',
                'Domain\EventApplications\States\PublishedApplicationState',
            ]);

        if ($excludeApplicationId) {
            $query->where('id', '!=', $excludeApplicationId);
        }

        if (isset($data['sport_id']) && $data['sport_id']) {
            $query->where('sport_id', $data['sport_id']);
        }

        if (isset($data['event_category_id']) && $data['event_category_id']) {
            $query->where('event_category_id', $data['event_category_id']);
        }

        if (isset($data['start_date']) && isset($data['end_date'])) {
            $query->where(function ($q) use ($data) {
                $q->whereBetween('start_date', [$data['start_date'], $data['end_date']])
                    ->orWhereBetween('end_date', [$data['start_date'], $data['end_date']])
                    ->orWhere(function ($q2) use ($data) {
                        $q2->where('start_date', '<=', $data['start_date'])
                            ->where('end_date', '>=', $data['end_date']);
                    });
            });
        }

        if (isset($data['district_id']) && $data['district_id']) {
            $query->where('district_id', $data['district_id']);
        }

        return $query->get();
    }
}
