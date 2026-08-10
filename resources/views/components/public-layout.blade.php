@props(['languageSwitcher' => true])
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('web.layout.head')
</head>

<body class="font-inter antialiased text-slate-600">
    @if ($languageSwitcher)
        <div class="fixed top-4 right-4 z-50 bg-white/90 backdrop-blur rounded-lg shadow-md">
            <x-language-switcher />
        </div>
    @endif
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
