<?php

namespace Domain\Menus\Actions;

use App\Models\MenuGroup;
use App\Models\MenuItem;

class DeleteMenuGroupAction
{
    /**
     * Execute the action to delete a menu group
     */
    public function execute(MenuGroup $menuGroup): bool
    {
        // Check if this is the last group in the menu
        $otherGroupsCount = MenuGroup::where('menu_id', $menuGroup->menu_id)
            ->where('id', '!=', $menuGroup->id)
            ->count();

        // If this is the default group and there are other groups,
        // set another group as default
        if ($menuGroup->is_default && $otherGroupsCount > 0) {
            $nextGroup = MenuGroup::where('menu_id', $menuGroup->menu_id)
                ->where('id', '!=', $menuGroup->id)
                ->orderBy('order')
                ->first();

            if ($nextGroup) {
                $nextGroup->setAsDefault();
            }
        }

        // Unassign all menu items from this group (they become ungrouped)
        MenuItem::where('menu_group_id', $menuGroup->id)
            ->update(['menu_group_id' => null]);

        // Delete the group
        $result = $menuGroup->delete();

        // Clear menu cache (handled by model boot method)

        return $result;
    }
}
