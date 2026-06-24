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
    @include('web.layout.main-full')
</div>


<script>
    if (localStorage.getItem("sidebar-expanded") == "true") {
        document.querySelector("body").classList.add("sidebar-expanded");
    } else {
        document.querySelector("body").classList.remove("sidebar-expanded");
    }
</script>

@filamentScripts
@livewireScripts(['defer' => true])
@stack('footer-scripts')
</body>
</html>
