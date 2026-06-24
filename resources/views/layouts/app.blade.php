<!doctype html>
<html lang="{{ config('app.locale') }}">

<head>
    @include('web.layout.head')
</head>

<body
    class="font-inter antialiased text-slate-600 bg-cover bg-waves-full-bg-one animate-in"
    :class="{ 'sidebar-expanded': sidebarExpanded }"
    x-data="{ sidebarOpen: false, sidebarExpanded: localStorage.getItem('sidebar-expanded') == 'true' }"
    x-init="$watch('sidebarExpanded', value => localStorage.setItem('sidebar-expanded', value))"
>


<!-- Page wrapper -->
<div class="flex h-screen overflow-hidden">

    @include('web.layout.main')
</div>


@livewire('notifications')
@filamentScripts
@livewireScripts(['defer' => true])
@stack('footer-scripts')
</body>
</html>
