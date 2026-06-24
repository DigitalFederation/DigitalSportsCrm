@props(['menuItems' => collect(), 'menuType' => '', 'isDynamic' => false])

<div class="sidebar-menu">
    @if($isDynamic && \App\Services\FeatureFlagService::isDynamicMenuDebugEnabled())
        <div class="p-2 mb-2 text-xs bg-yellow-100 text-yellow-800 rounded">
            🔧 Dynamic Menu: {{ $menuType }} ({{ $menuItems->count() }} items)
        </div>
    @endif

    @if($menuItems->isNotEmpty())
        <ul class="space-y-1">
            @foreach($menuItems as $item)
                <x-menu.dynamic-menu-item :item="$item" />
            @endforeach
        </ul>
    @else
        <div class="p-4 text-center text-gray-500">
            {{ __('menu.no_items_available') }}
        </div>
    @endif
</div>