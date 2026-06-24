<?php

namespace Domain\Menus\Actions;

use App\Models\MenuGroup;
use Illuminate\Support\Str;

class UpdateMenuGroupAction
{
    /**
     * Execute the action to update a menu group
     */
    public function execute(MenuGroup $menuGroup, array $data): MenuGroup
    {
        // If updating machine name, ensure uniqueness
        if (isset($data['machine_name']) && $data['machine_name'] !== $menuGroup->machine_name) {
            $baseMachineName = Str::slug($data['machine_name']);
            $counter = 1;
            $newMachineName = $baseMachineName;

            while (MenuGroup::where('menu_id', $menuGroup->menu_id)
                ->where('machine_name', $newMachineName)
                ->where('id', '!=', $menuGroup->id)
                ->exists()) {
                $newMachineName = $baseMachineName . '-' . $counter;
                $counter++;
            }

            $data['machine_name'] = $newMachineName;
        }

        // Update the menu group
        $menuGroup->update([
            'name' => $data['name'] ?? $menuGroup->name,
            'machine_name' => $data['machine_name'] ?? $menuGroup->machine_name,
            'description' => $data['description'] ?? $menuGroup->description,
            'icon' => $data['icon'] ?? $menuGroup->icon,
            'order' => $data['order'] ?? $menuGroup->order,
            'is_default' => $data['is_default'] ?? $menuGroup->is_default,
            'active' => $data['active'] ?? $menuGroup->active,
            'visibility_type' => $data['visibility_type'] ?? $menuGroup->visibility_type,
            'required_roles' => $data['required_roles'] ?? $menuGroup->required_roles,
        ]);

        // If setting as default, the model will handle unsetting other defaults

        return $menuGroup->fresh();
    }
}
