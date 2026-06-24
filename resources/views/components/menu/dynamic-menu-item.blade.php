@props(['item'])

@php
    // Handle both dynamic menu items (objects) and legacy config items (arrays)
    $isObject = is_object($item);
    
    // Extract properties safely. Config-array items use a translation key as
    // their name, so translate it (database menu items resolve their own text).
    $name = $isObject ? $item->getDisplayText() : __($item['name'] ?? '');
    $icon = $isObject ? $item->icon : ($item['icon'] ?? null);
    $url = $isObject ? $item->getUrl() : null;
    $hasChildren = $isObject ? $item->hasVisibleChildren() : isset($item['children']);
    $children = $isObject ? ($item->children ?? collect()) : collect($item['children'] ?? []);
    $isActive = \App\Helpers\SidebarHelper::isMenuActive($item);
    
    // Generate URL for legacy items
    if (!$url && !$isObject && isset($item['route'])) {
        try {
            if (is_array($item['route'])) {
                $route = $item['route'][0] ?? '';
                $params = $item['route'][1] ?? [];
                $url = $route ? route($route, $params) : '#';
            } else {
                $url = $item['route'] ? route($item['route']) : '#';
            }
        } catch (\Exception $e) {
            $url = '#';
        }
    }
    
    // Check permissions for legacy items
    $canAccess = true;
    if (!$isObject && isset($item['can'])) {
        $permissions = is_array($item['can']) ? $item['can'] : [$item['can']];
        $canAccess = false;
        foreach ($permissions as $permission) {
            if (auth()->user()->can($permission)) {
                $canAccess = true;
                break;
            }
        }
    } elseif ($isObject) {
        // For dynamic menu items, we trust that they've already been filtered by the MenuBuilderService
        // The MenuBuilderService handles all permission checking with the correct user context
        $canAccess = true;
    }
    
    // Ensure fallback values
    $name = $name ?: 'Menu Item';
    $url = $url ?: 'javascript:void(0)';
    
    // Get badge count if available
    $badge = $isObject && isset($item->badge) ? $item->badge : null;
@endphp

@if($canAccess)
<li class="list-none"
    x-data="{
        open: {{ $isActive ? 'true' : 'false' }},
        id: 'menu_{{ rawurlencode($name) }}',
        init() {
            let storedState = localStorage.getItem(this.id);
            this.open = (storedState !== null) ? (storedState === 'true') : {{ $isActive ? 'true' : 'false' }};
        }
    }"
    x-init="init(); $watch('open', value => { localStorage.setItem(this.id, value); })">

    <a class="group flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg transition-all duration-150 {{ $isActive ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}"
       href="{{ $url }}"
       @if($hasChildren) @click.prevent="open = !open" @endif>
        <div class="flex items-center min-w-0">
            @if($icon)
                <div class="flex-shrink-0 mr-3">
                    @php
                        try {
                            // Try to render the icon
                            $iconSvg = svg('heroicon-o-' . $icon, 'w-5 h-5 ' . ($isActive ? 'text-gray-700' : 'text-gray-400 group-hover:text-gray-600'))->toHtml();
                            echo $iconSvg;
                        } catch (\BladeUI\Icons\Exceptions\SvgNotFound $e) {
                            // Fallback to a default icon if the specified icon doesn't exist
                            echo svg('heroicon-o-rectangle-stack', 'w-5 h-5 ' . ($isActive ? 'text-gray-700' : 'text-gray-400 group-hover:text-gray-600'))->toHtml();
                            
                            // Log the error for admin awareness
                            \Log::warning('Invalid menu icon specified', [
                                'menu_item_id' => $item->id ?? null,
                                'icon_name' => $icon,
                                'error' => $e->getMessage()
                            ]);
                        }
                    @endphp
                </div>
            @else
                {{-- No icon - add spacing to align with items that have icons --}}
                <div class="w-5 mr-3"></div>
            @endif
            <span class="truncate">{{ $name }}</span>
        </div>
        <div class="flex items-center flex-shrink-0 ml-2">
            @if($badge && $badge > 0)
                <span class="inline-flex items-center justify-center px-2 py-0.5 text-xs font-medium bg-gray-200 text-gray-700 rounded-full mr-2">
                    {{ $badge }}
                </span>
            @endif
            @if($hasChildren)
                <svg class="w-4 h-4 transition-transform duration-200 {{ $isActive ? 'text-gray-700' : 'text-gray-400' }}"
                     :class="open ? 'rotate-180' : 'rotate-0'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                </svg>
            @endif
        </div>
    </a>

    @if($hasChildren)
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="mt-1 ml-6 pl-3 border-l-2 border-gray-200">
            <ul class="list-none space-y-0.5">
                @foreach ($children as $child)
                    @php
                        $childName = $isObject ? $child->getDisplayText() : __($child['name'] ?? '');
                        $childUrl = $isObject ? $child->getUrl() : null;
                        $childIcon = $isObject ? $child->icon : ($child['icon'] ?? null);

                        // Generate URL for legacy child items
                        if (!$childUrl && !$isObject && isset($child['route'])) {
                            try {
                                if (is_array($child['route'])) {
                                    $route = $child['route'][0] ?? '';
                                    $params = $child['route'][1] ?? [];
                                    $childUrl = $route ? route($route, $params) : '#';
                                } else {
                                    $childUrl = $child['route'] ? route($child['route']) : '#';
                                }
                            } catch (\Exception $e) {
                                $childUrl = '#';
                            }
                        }

                        $childUrl = $childUrl ?: 'javascript:void(0)';
                        $childIsActive = false;

                        // Check permissions for legacy child items
                        $childCanAccess = true;
                        if (!$isObject && isset($child['can'])) {
                            $childPermissions = is_array($child['can']) ? $child['can'] : [$child['can']];
                            $childCanAccess = false;
                            foreach ($childPermissions as $childPermission) {
                                if (auth()->user()->can($childPermission)) {
                                    $childCanAccess = true;
                                    break;
                                }
                            }
                        }

                        // Check if child is active
                        if ($childUrl !== 'javascript:void(0)' && $childUrl !== '#') {
                            try {
                                $childIsActive = Request::fullUrl() == $childUrl;
                            } catch (\Exception $e) {
                                $childIsActive = false;
                            }
                        }
                    @endphp
                    @if($childCanAccess)
                    <li class="list-none">
                        <a class="group flex items-center py-1.5 px-2 text-[13px] rounded-md transition-colors duration-150 {{ $childIsActive ? 'bg-gray-100 text-gray-900 font-medium' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700' }}"
                           href="{{ $childUrl }}">
                            <span class="truncate">{{ $childName }}</span>
                            @if(isset($child['count']) || (is_object($child) && isset($child->count)))
                                @php $count = is_object($child) ? $child->count : $child['count']; @endphp
                                @if($count > 0)
                                    <span class="ml-auto text-xs {{ $childIsActive ? 'text-gray-700' : 'text-gray-400' }}">{{ $count }}</span>
                                @endif
                            @endif
                        </a>
                    </li>
                    @endif
                @endforeach
            </ul>
        </div>
    @endif
</li>
@endif