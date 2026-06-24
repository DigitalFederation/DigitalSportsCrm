@props(['menuType', 'groups'])

@php
    $activeGroupId = \App\Helpers\SidebarHelper::getActiveGroup($menuType);
    $defaultGroup = $groups->firstWhere('is_default', true);
    
    // If no active group, use default
    if (!$activeGroupId && $defaultGroup) {
        $activeGroupId = $defaultGroup->id;
        \App\Helpers\SidebarHelper::setActiveGroup($menuType, $activeGroupId);
    }
    
    $activeGroup = $groups->firstWhere('id', $activeGroupId) ?? $groups->first();
@endphp

<div class="px-3 mb-4" x-data="{ open: false }">
    <div class="relative">
        <button
            @click="open = !open"
            @click.away="open = false"
            class="w-full flex items-center justify-between bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg transition-colors duration-200"
        >
            <div class="flex items-center space-x-2">
                @if($activeGroup && $activeGroup->icon)
                    @svg('heroicon-o-' . $activeGroup->icon, 'w-4 h-4')
                @else
                    @svg('heroicon-o-folder', 'w-4 h-4')
                @endif
                <span class="text-sm font-medium">{{ $activeGroup ? $activeGroup->name : __('menu.dynamic.admin.all_items') }}</span>
            </div>
            <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>

        <!-- Dropdown -->
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="opacity-0 transform scale-95"
            x-transition:enter-end="opacity-100 transform scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 transform scale-100"
            x-transition:leave-end="opacity-0 transform scale-95"
            class="absolute z-50 w-full mt-2 bg-white rounded-lg shadow-lg overflow-hidden"
        >
            <div class="max-h-64 overflow-y-auto">
                @foreach($groups as $group)
                    <a
                        href="?menu_group={{ $group->id }}"
                        @class([
                            'block px-4 py-2.5 text-sm hover:bg-gray-50 transition-colors duration-150',
                            'bg-blue-50 text-blue-600 font-medium' => $activeGroupId == $group->id,
                            'text-gray-700' => $activeGroupId != $group->id
                        ])
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                @if($group->icon)
                                    @svg('heroicon-o-' . $group->icon, 'w-4 h-4')
                                @else
                                    @svg('heroicon-o-folder', 'w-4 h-4')
                                @endif
                                <span>{{ $group->name }}</span>
                            </div>
                            @if($group->is_default)
                                <span class="text-xs text-gray-500">{{ __('menu.dynamic.admin.default') }}</span>
                            @endif
                        </div>
                        @if($group->description)
                            <p class="text-xs text-gray-500 mt-1 ml-6">{{ $group->description }}</p>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</div>