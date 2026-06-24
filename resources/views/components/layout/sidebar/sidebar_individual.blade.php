@php
    $user = Auth::user();
    $menuType = \App\Helpers\SidebarHelper::getUserMenuType($user);
    $menuGroups = \App\Helpers\SidebarHelper::getMenuGroups($menuType, $user);
    $hasMultipleGroups = $menuGroups->count() > 1;
    $menuItems = \App\Helpers\SidebarHelper::getMenuForUser($user);
@endphp

@if($hasMultipleGroups)
    <x-menu.group-selector :menu-type="$menuType" :groups="$menuGroups" />
@endif

@foreach ($menuItems as $item)
    <x-menu.dynamic-menu-item :item="$item" />
@endforeach
