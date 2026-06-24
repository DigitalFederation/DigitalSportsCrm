<?php

namespace Domain\Menus\Actions;

use App\Models\Menu;
use App\Models\MenuGroup;
use Illuminate\Support\Str;

class CreateMenuGroupAction
{
    /**
     * Execute the action to create a new menu group
     */
    public function execute(array $data): MenuGroup
    {
        // Validate menu exists
        $menu = Menu::findOrFail($data['menu_id']);

        // Generate machine name if not provided
        if (empty($data['machine_name'])) {
            $data['machine_name'] = Str::slug($data['name']);
        }

        // Ensure machine name is unique within the menu
        $baseMachineName = $data['machine_name'];
        $counter = 1;
        while (MenuGroup::where('menu_id', $data['menu_id'])
            ->where('machine_name', $data['machine_name'])
            ->exists()) {
            $data['machine_name'] = $baseMachineName . '-' . $counter;
            $counter++;
        }

        // If this is the first group or set as default, ensure it's the default
        if ($data['is_default'] ?? false || ! $menu->groups()->exists()) {
            $data['is_default'] = true;
        }

        // Create the menu group
        $menuGroup = MenuGroup::create([
            'menu_id' => $data['menu_id'],
            'name' => $data['name'],
            'machine_name' => $data['machine_name'],
            'description' => $data['description'] ?? null,
            'icon' => $data['icon'] ?? null,
            'order' => $data['order'] ?? 0,
            'is_default' => $data['is_default'] ?? false,
            'active' => $data['active'] ?? true,
            'visibility_type' => $data['visibility_type'] ?? 'all',
            'required_roles' => $data['required_roles'] ?? null,
        ]);

        // Clear menu cache
        $menu->clearCache();

        return $menuGroup;
    }
}
