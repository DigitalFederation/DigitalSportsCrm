<?php

namespace Domain\Permissions\Actions;

use App\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BulkCreatePermissionsAction
{
    public static function execute(array $permissions): array
    {
        $results = [
            'created' => [],
            'skipped' => [],
            'errors' => [],
        ];

        DB::transaction(function () use ($permissions, &$results) {
            foreach ($permissions as $index => $permissionData) {
                try {
                    // Validate permission data
                    $validator = Validator::make($permissionData, [
                        'name' => 'required|string|max:255',
                        'description' => 'nullable|string',
                        'category' => 'nullable|string|max:100',
                    ]);

                    if ($validator->fails()) {
                        $results['errors'][] = [
                            'index' => $index,
                            'data' => $permissionData,
                            'error' => $validator->errors()->first(),
                        ];

                        continue;
                    }

                    // Check if permission already exists
                    if (Permission::where('name', $permissionData['name'])->exists()) {
                        $results['skipped'][] = $permissionData['name'];

                        continue;
                    }

                    // Create permission
                    $permission = CreatePermissionAction::execute($permissionData);
                    $results['created'][] = $permission->name;

                } catch (\Exception $e) {
                    $results['errors'][] = [
                        'index' => $index,
                        'data' => $permissionData,
                        'error' => $e->getMessage(),
                    ];
                }
            }
        });

        return $results;
    }
}
