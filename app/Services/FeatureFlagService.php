<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class FeatureFlagService
{
    protected const CACHE_PREFIX = 'feature_flag.';
    protected const CACHE_TTL = 300; // 5 minutes

    /**
     * Check if a feature is enabled
     */
    public static function isEnabled(string $feature): bool
    {
        $cacheKey = self::CACHE_PREFIX . $feature;

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($feature) {
            return config("features.{$feature}", false);
        });
    }

    /**
     * Check if dynamic menu is enabled globally
     */
    public static function isDynamicMenuEnabled(): bool
    {
        return self::isEnabled('dynamic_menu.enabled');
    }

    /**
     * Check if dynamic menu is enabled for a specific menu type
     */
    public static function isDynamicMenuEnabledFor(string $menuType): bool
    {
        // Check global flag first
        if (! self::isDynamicMenuEnabled()) {
            return false;
        }

        // Check specific menu type flag
        return self::isEnabled("dynamic_menu.menus.{$menuType}");
    }

    /**
     * Check if dynamic menu admin interface is enabled
     */
    public static function isDynamicMenuAdminEnabled(): bool
    {
        return self::isDynamicMenuEnabled() && self::isEnabled('dynamic_menu.admin_interface');
    }

    /**
     * Check if dynamic menu debug mode is enabled
     */
    public static function isDynamicMenuDebugEnabled(): bool
    {
        return self::isDynamicMenuEnabled() && self::isEnabled('dynamic_menu.debug_mode');
    }

    /**
     * Check if dynamic menu caching is enabled
     */
    public static function isDynamicMenuCacheEnabled(): bool
    {
        return self::isEnabled('dynamic_menu.cache_enabled');
    }

    /**
     * Get all enabled menu types
     */
    public static function getEnabledMenuTypes(): array
    {
        if (! self::isDynamicMenuEnabled()) {
            return [];
        }

        $menuTypes = ['cmas', 'federation', 'entity', 'individual'];
        $enabled = [];

        foreach ($menuTypes as $menuType) {
            if (self::isDynamicMenuEnabledFor($menuType)) {
                $enabled[] = $menuType;
            }
        }

        return $enabled;
    }

    /**
     * Clear feature flag cache
     */
    public static function clearCache(): void
    {
        $keys = [
            'dynamic_menu.enabled',
            'dynamic_menu.menus.cmas',
            'dynamic_menu.menus.federation',
            'dynamic_menu.menus.entity',
            'dynamic_menu.menus.individual',
            'dynamic_menu.admin_interface',
            'dynamic_menu.debug_mode',
            'dynamic_menu.cache_enabled',
        ];

        foreach ($keys as $key) {
            Cache::forget(self::CACHE_PREFIX . $key);
        }
    }

    /**
     * Get feature flag status for debugging
     */
    public static function getDebugInfo(): array
    {
        return [
            'dynamic_menu' => [
                'enabled' => self::isDynamicMenuEnabled(),
                'admin_interface' => self::isDynamicMenuAdminEnabled(),
                'debug_mode' => self::isDynamicMenuDebugEnabled(),
                'cache_enabled' => self::isDynamicMenuCacheEnabled(),
                'enabled_menus' => self::getEnabledMenuTypes(),
            ],
        ];
    }
}
