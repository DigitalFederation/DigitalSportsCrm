<!doctype html>
<html lang="{{ config('app.locale') }}">

    <head>
        @include('web.layout.head')
    </head>

    <body class="font-inter antialiased  text-slate-600">

        {{ $slot }}
        @livewireScripts
        @stack('footer-scripts')
    </body>
</html>
