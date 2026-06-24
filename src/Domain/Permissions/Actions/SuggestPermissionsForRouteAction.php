<?php

namespace Domain\Permissions\Actions;

class SuggestPermissionsForRouteAction
{
    public static function execute(string $routeName): array
    {
        if (empty($routeName)) {
            return [];
        }

        $suggestions = [];
        $parts = explode('.', $routeName);

        // Extract the resource and action from route name
        $prefix = $parts[0] ?? '';
        $resource = $parts[1] ?? '';
        $action = end($parts);

        // Map common route actions to permissions
        $actionPermissionMap = [
            'index' => 'view',
            'show' => 'view',
            'create' => 'create',
            'store' => 'create',
            'edit' => 'edit',
            'update' => 'edit',
            'destroy' => 'delete',
            'export' => 'export',
            'import' => 'import',
            'download' => 'download',
        ];

        // Generate suggestions based on route patterns
        if ($resource) {
            // Resource-based permission (e.g., "manage users")
            $suggestions[] = "manage {$resource}";

            // Action-based permission (e.g., "view users")
            if (isset($actionPermissionMap[$action])) {
                $suggestions[] = "{$actionPermissionMap[$action]} {$resource}";
            } else {
                $suggestions[] = "{$action} {$resource}";
            }

            // Full route permission (e.g., "access admin.users.index")
            $suggestions[] = "access {$routeName}";
        }

        // Add prefix-based suggestions
        if ($prefix === 'admin') {
            $suggestions[] = 'access admin';
        } elseif ($prefix === 'federation') {
            $suggestions[] = 'access federation';
            $suggestions[] = 'access federation admin';
        }

        // Remove duplicates and return
        return array_unique($suggestions);
    }
}
