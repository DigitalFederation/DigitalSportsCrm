<?php

namespace Domain\Menus\Actions;

use App\Models\MenuGroup;
use App\Models\MenuItem;
use Illuminate\Support\Collection;

class AssignItemsToGroupAction
{
    /**
     * Execute the action to assign menu items to a group
     */
    public function execute(MenuGroup $menuGroup, array $itemIds): Collection
    {
        // Validate all items belong to the same menu as the group
        $validItemIds = MenuItem::where('menu_id', $menuGroup->menu_id)
            ->whereIn('id', $itemIds)
            ->pluck('id')
            ->toArray();

        // Update the menu items
        $updatedCount = MenuItem::whereIn('id', $validItemIds)
            ->update(['menu_group_id' => $menuGroup->id]);

        // Clear menu cache
        $menuGroup->menu->clearCache();

        // Return the updated items
        return MenuItem::whereIn('id', $validItemIds)->get();
    }

    /**
     * Remove items from any group (make them ungrouped)
     */
    public function unassignItems(int $menuId, array $itemIds): int
    {
        // Validate all items belong to the specified menu
        $validItemIds = MenuItem::where('menu_id', $menuId)
            ->whereIn('id', $itemIds)
            ->pluck('id')
            ->toArray();

        // Update the menu items
        $updatedCount = MenuItem::whereIn('id', $validItemIds)
            ->update(['menu_group_id' => null]);

        // Clear menu cache
        if ($updatedCount > 0) {
            $menuItem = MenuItem::find($validItemIds[0]);
            if ($menuItem && $menuItem->menu) {
                $menuItem->menu->clearCache();
            }
        }

        return $updatedCount;
    }
}
