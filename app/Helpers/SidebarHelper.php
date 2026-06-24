<?php

namespace App\Helpers;

use App\Models\User;
use App\Services\FeatureFlagService;
use App\Services\MenuBuilderService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Request;

class SidebarHelper
{
    public static function getUserSidebar(User $user): ?string
    {
        return match ($user->group->code) {
            'ADMIN' => 'sidebar_admin',
            'FEDERATION' => 'sidebar_federation',
            'ENTITY' => 'sidebar_entity',
            'INDIVIDUAL' => 'sidebar_individual',
            default => null,
        };
    }

    /**
     * Get the menu machine name for a user's group
     */
    public static function getUserMenuType(User $user): string
    {
        $userGroup = $user->group->code;

        return match ($userGroup) {
            'ADMIN' => 'admin',
            'FEDERATION' => 'federation',
            'ENTITY' => 'entity',
            'INDIVIDUAL' => 'individual',
            default => 'individual'
        };
    }

    /**
     * Get menu structure for user (uses dynamic menu if enabled)
     */
    public static function getMenuForUser(User $user): Collection
    {
        $menuType = self::getUserMenuType($user);

        // Check if dynamic menu is enabled for this menu type
        if (FeatureFlagService::isDynamicMenuEnabledFor($menuType)) {
            return self::getDynamicMenuForUser($user, $menuType);
        }

        // Fall back to config-based menu
        return self::getConfigMenuForUser($menuType);
    }

    /**
     * Get dynamic menu structure for user
     */
    private static function getDynamicMenuForUser(User $user, string $menuType): Collection
    {
        $menuBuilder = new MenuBuilderService;

        $options = [];

        // Note: We intentionally do NOT filter the sidebar by committee.
        // All committee menus (Sport, Diving, Scientific) should always be visible
        // regardless of which committee page the user is viewing.

        $accessibleGroups = self::getMenuGroups($menuType, $user);

        // Get active group filter if applicable; ensure it is one of the user's
        // accessible groups (defends against stale session entries after roles change).
        $activeGroup = self::getActiveGroup($menuType);
        if ($activeGroup && ! $accessibleGroups->contains('id', $activeGroup)) {
            self::setActiveGroup($menuType, null);
            $activeGroup = null;
        }

        // No explicit selection: fall back to the user's default accessible group
        // (or the first one). This ensures items are grouped/filtered even when only
        // one group is accessible and the selector is not rendered.
        if (! $activeGroup && $accessibleGroups->isNotEmpty()) {
            $defaultGroup = $accessibleGroups->firstWhere('is_default', true) ?? $accessibleGroups->first();
            $activeGroup = $defaultGroup->id;
        }

        if ($activeGroup) {
            $options['group_id'] = $activeGroup;
        }

        return $menuBuilder->buildForUser($menuType, $user, $options);
    }

    /**
     * Get config-based menu structure (legacy)
     */
    private static function getConfigMenuForUser(string $menuType): Collection
    {
        $menu = config("menu.{$menuType}", []);

        return collect($menu);
    }

    public static function isMenuActive($item)
    {
        // Handle both config-based items (arrays) and dynamic menu items (MenuItem objects)
        if (is_object($item) && method_exists($item, 'isActive')) {
            return $item->isActive();
        }

        // Legacy handling for config-based menu items
        $isSegmentActive = false;
        $isIdentifierActive = false;

        // Check if it matches the segment
        if (isset($item['active']) && is_array($item['active'])) {
            if (in_array(Request::segment(2), $item['active'])) {
                $isSegmentActive = true;
            }
        }

        // Check identifier based on request's query parameters
        if (isset($item['identifier'])) {
            $identifierParts = explode(';', $item['identifier']);

            if ($identifierParts[0] === Request::route()->getName()) {
                $isIdentifierActive = true;

                // Skip the first part since it's the route name, and start from 1
                for ($i = 1; $i < count($identifierParts); $i++) {
                    [$queryKey, $queryValue] = explode('.', $identifierParts[$i]);

                    if (Request::input($queryKey) !== $queryValue) {
                        $isIdentifierActive = false;
                        break;
                    }
                }
            }
        }

        if ($isSegmentActive && ! $isIdentifierActive && isset($item['identifier'])) {
            return false;
        }

        return $isSegmentActive || $isIdentifierActive;
    }

    /**
     * Get the active menu group from session/request
     */
    public static function getActiveGroup(string $menuType): ?int
    {
        // First check request
        $groupId = request()->input('menu_group');

        if ($groupId) {
            // Store in session for persistence
            session()->put("menu.{$menuType}.active_group", $groupId);

            return $groupId;
        }

        // Then check session
        return session()->get("menu.{$menuType}.active_group");
    }

    /**
     * Set the active menu group
     */
    public static function setActiveGroup(string $menuType, ?int $groupId): void
    {
        if ($groupId) {
            session()->put("menu.{$menuType}.active_group", $groupId);
        } else {
            session()->forget("menu.{$menuType}.active_group");
        }
    }

    /**
     * Get available menu groups for a menu
     */
    public static function getMenuGroups(string $menuType, $user = null): Collection
    {
        if (! FeatureFlagService::isDynamicMenuEnabledFor($menuType)) {
            return collect();
        }

        $user = $user ?? auth()->user();
        $menuBuilder = new MenuBuilderService;

        return $menuBuilder->getMenuGroups($menuType, $user);
    }

    /**
     * Check if menu has multiple groups
     */
    public static function hasMultipleGroups(string $menuType, $user = null): bool
    {
        return self::getMenuGroups($menuType, $user)->count() > 1;
    }
}
