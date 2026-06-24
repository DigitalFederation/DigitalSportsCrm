<!doctype html>
<html lang="{{ config('app.locale') }}">

<head>
    @include('web.layout.head')
</head>

<body class="font-inter antialiased text-slate-600">
    <!-- Page wrapper -->
    {{ $slot }}

    @livewireScripts
    @stack('footer-scripts')

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</body>
</html>
