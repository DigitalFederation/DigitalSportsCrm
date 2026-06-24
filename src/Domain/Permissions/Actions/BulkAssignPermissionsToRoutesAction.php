<?php

namespace Domain\Permissions\Actions;

use Illuminate\Support\Facades\DB;

class BulkAssignPermissionsToRoutesAction
{
    public static function execute(array $assignments): array
    {
        $results = [
            'created' => 0,
            'updated' => 0,
            'errors' => [],
        ];

        DB::transaction(function () use ($assignments, &$results) {
            foreach ($assignments as $assignment) {
                try {
                    if (empty($assignment['route_name']) || empty($assignment['permission'])) {
                        continue;
                    }

                    // Check if route permission already exists
                    $exists = \App\Models\RoutePermission::where('route_pattern', $assignment['route_name'])->exists();

                    AssignPermissionToRouteAction::execute(
                        $assignment['route_name'],
                        $assignment['permission'],
                        $assignment['is_active'] ?? true
                    );

                    // Track created vs updated
                    if ($exists) {
                        $results['updated']++;
                    } else {
                        $results['created']++;
                    }
                } catch (\Exception $e) {
                    $results['errors'][] = [
                        'route' => $assignment['route_name'] ?? 'unknown',
                        'error' => $e->getMessage(),
                    ];
                }
            }
        });

        return $results;
    }
}
