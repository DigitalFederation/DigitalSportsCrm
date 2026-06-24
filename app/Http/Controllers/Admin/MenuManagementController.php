<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Services\FeatureFlagService;

class MenuManagementController extends Controller
{
    public function index()
    {
        // Check if dynamic menu system is enabled
        if (! FeatureFlagService::isDynamicMenuAdminEnabled()) {
            // Provide detailed error information for debugging
            $debugInfo = [
                'dynamic_menu_enabled' => env('DYNAMIC_MENU_ENABLED', 'not set'),
                'dynamic_menu_admin' => env('DYNAMIC_MENU_ADMIN', 'not set'),
                'config_enabled' => config('features.dynamic_menu.enabled', 'not set'),
                'config_admin' => config('features.dynamic_menu.admin_interface', 'not set'),
            ];

            if (app()->environment('local', 'staging')) {
                abort(404, 'Dynamic menu admin feature is disabled. Debug info: ' . json_encode($debugInfo));
            } else {
                abort(404, __('menu.dynamic.admin.feature_not_enabled'));
            }
        }

        // Check permissions
        if (! auth()->user()->can('manage_menus')) {
            abort(403, __('menu.dynamic.admin.access_denied'));
        }

        $enabledMenuTypes = [];
        foreach (['cmas', 'federation', 'entity', 'individual'] as $type) {
            if (FeatureFlagService::isDynamicMenuEnabledFor($type)) {
                $enabledMenuTypes[] = $type;
            }
        }

        $stats = [
            'total_menus' => Menu::active()->count(),
            'total_items' => \App\Models\MenuItem::count(),
            'enabled_menus' => Menu::active()->whereIn('machine_name', $enabledMenuTypes)->count(),
        ];

        return view('admin.menu-management.index', compact('stats'));
    }
}
