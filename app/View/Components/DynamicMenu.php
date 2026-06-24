<?php

namespace App\View\Components;

use App\Helpers\SidebarHelper;
use App\Services\FeatureFlagService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class DynamicMenu extends Component
{
    public Collection $menuItems;
    public string $menuType;
    public bool $isDynamic;

    /**
     * Create a new component instance.
     */
    public function __construct(
        public ?string $name = null,
        public ?object $user = null
    ) {
        $this->user = $user ?? auth()->user();

        if (! $this->user) {
            $this->menuItems = collect();
            $this->menuType = 'guest';
            $this->isDynamic = false;

            return;
        }

        // Determine menu type
        $this->menuType = $name ?? SidebarHelper::getUserMenuType($this->user);

        // Check if dynamic menu is enabled
        $this->isDynamic = FeatureFlagService::isDynamicMenuEnabledFor($this->menuType);

        // Get menu items
        $this->menuItems = SidebarHelper::getMenuForUser($this->user);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        if ($this->isDynamic) {
            return view('components.menu.dynamic-menu');
        }

        // Fall back to legacy sidebar components
        $sidebarName = SidebarHelper::getUserSidebar($this->user);

        return view("components.layout.sidebar.{$sidebarName}");
    }
}
