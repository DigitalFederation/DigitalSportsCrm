@props(['item', 'activeCommittee'])

@php
    use App\Helpers\MenuHelper;
    use App\Helpers\IconHelper;
    
    // Ensure $item is an array
    $item = is_array($item) ? $item : ['name' => $item, 'route' => null];
    
    $isMenuActive = \App\Helpers\SidebarHelper::isMenuActive($item);

    $isActive = $activeCommittee && isset($item['committee'])
                ? (strpos($activeCommittee, $item['committee']) !== false) || $isMenuActive
                : $isMenuActive;
    
    // Ensure that the item name is a string with a fallback
    $itemName = isset($item['name']) ? MenuHelper::safeTranslationString($item['name']) : 'Menu Item';
    $iconName = isset($item['icon']) ? IconHelper::getHeroiconName($item['icon']) : 'menu';
    
    // Ensure other required properties have fallbacks
    $item['active'] = $item['active'] ?? [];
    $item['route'] = $item['route'] ?? null;
    $item['children'] = $item['children'] ?? [];
@endphp

<li
    class="hover:bg-primary-light/50 px-3 py-1.5 rounded-lg mb-1 last:mb-0 transition duration-300 ease-in-out {{ $isActive ? 'bg-primary/70 shadow-md' : '' }}"
    x-data="{
        open: {{ $isActive ? 'true' : 'false' }},
        id: 'menu_{{ isset($item['name']) && !empty($item['name']) ? rawurlencode($item['name']) : 'item_' . uniqid() }}',
        maxHeight: 0,
        init() {
            let storedState = localStorage.getItem(this.id);
            this.open = (storedState !== null) ? (storedState === 'true') : ('{{ $isActive ? "true" : "false" }}' === 'true');
            this.maxHeight = this.open ? $refs.menu.scrollHeight : 0;
        }
    }"
    x-init="init(); $watch('open', value => { maxHeight = value ? $refs.menu.scrollHeight : 0; localStorage.setItem(this.id, value); })"
    x-on:transitionend="if (!open) { maxHeight = 0 }">

    <a class="block text-white hover:text-white/90 truncate transition duration-150 @if (isset($item['active']) && in_array(Request::segment(2), $item['active'])) text-white font-medium @endif"
       href="{{ isset($item['route']) && $item['route'] ? route(...is_array($item['route']) ? $item['route'] : [$item['route']]) : 'javascript:void(0)' }}"
       x-ref="menu"
       @if(!empty($item['children'])) @click.prevent="sidebarExpanded ? open = !open : sidebarExpanded = true" @endif>
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="bg-primary-light/50 p-1.5 rounded-md flex items-center justify-center">
                    @if (isset($item['icon']) && !empty($item['icon']))
                        @svg('heroicon-o-' . $item['icon'], 'w-5 h-5 text-white')
                    @else
                        @svg('heroicon-o-bars-3', 'w-5 h-5 text-white')
                    @endif
                </div>
                <span
                    class="text-base md:text-sm font-medium ml-1 lg:sidebar-expanded:opacity-100 2xl:opacity-100 duration-200">
                    {{ $itemName }}
                </span>
            </div>
            @if(!empty($item['children']))
                <!-- Icon -->
                <div class="flex shrink-0 ml-2 lg:sidebar-expanded:opacity-100 2xl:opacity-100 duration-200">
                    <svg class="w-4 h-4 shrink-0 ml-1 fill-current text-white/80"
                         :class="open ? 'rotate-180' : 'rotate-0'" viewBox="0 0 12 12">
                        <path d="M5.9 11.4L.5 6l1.4-1.4 4 4 4-4L11.3 6z" />
                    </svg>
                </div>
            @endif
        </div>
    </a>

    @if(!empty($item['children']))
        <div
            x-ref="menu"
            x-bind:style="`max-height: ${maxHeight}px;`"
            class="overflow-hidden transition-max-height ease-in-out duration-300">
            <ul class="pl-4 mt-1 space-y-0.5 @unless (in_array(Request::segment(2), $item['active'])) hidden @endunless"
                :class="open ? '!block' : 'hidden'">
                @foreach ($item['children'] as $child)
                    @if (!empty($child['can']))
                        @canany($child['can'])
                            <li class="mb-1 last:mb-0">
                                <a class="block text-white/80 hover:text-white py-1 px-2 rounded-md transition duration-150 truncate @if ($child['route'] && Request::fullUrl() == route(...is_array($child['route']) ? $child['route'] : [$child['route']])) {{ 'bg-primary/30 !text-white font-medium' }} @endif"
                                   href="{{ $child['route'] ? route(...is_array($child['route']) ? $child['route'] : [$child['route']]) : 'javascript:void(0)' }}">
                                    <div class="flex items-center">
                                        @if(isset($child['icon']) && !empty($child['icon']))
                                            <div class="mr-2">
                                                @svg('heroicon-o-' . $child['icon'], 'w-4 h-4 text-white')
                                            </div>
                                        @endif
                                        <span class="text-sm font-medium lg:sidebar-expanded:opacity-100 2xl:opacity-100 duration-200">{{ \App\Helpers\MenuHelper::safeTranslationString($child['name']) }}</span>
                                    </div>
                                </a>
                            </li>
                        @endcanany
                    @else
                        <li class="mb-1 last:mb-0">
                            <a class="block text-white/80 hover:text-white py-1 px-2 rounded-md transition duration-150 truncate @if ($child['route'] && Request::fullUrl() == route(...is_array($child['route']) ? $child['route'] : [$child['route']])) {{ 'bg-primary/30 !text-white font-medium' }} @endif"
                               href="{{ $child['route'] ? route(...is_array($child['route']) ? $child['route'] : [$child['route']]) : 'javascript:void(0)' }}">
                                <div class="flex items-center">
                                    @if(isset($child['icon']) && !empty($child['icon']))
                                        <div class="mr-2">
                                            @svg('heroicon-o-' . $child['icon'], 'w-4 h-4 text-white')
                                        </div>
                                    @endif
                                    <span class="text-sm font-medium lg:sidebar-expanded:opacity-100 2xl:opacity-100 duration-200">{{ \App\Helpers\MenuHelper::safeTranslationString($child['name']) }}</span>
                                </div>
                            </a>
                        </li>
                    @endif
                @endforeach
            </ul>
        </div>
    @endif
</li>
