@php
    $user = Auth::user();
    $menuItems = \App\Helpers\SidebarHelper::getMenuForUser($user);
@endphp

{{-- Admin users don't need group selector --}}
@foreach ($menuItems as $item)
    <x-menu.dynamic-menu-item :item="$item" />
@endforeach
