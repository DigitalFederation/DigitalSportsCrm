<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('branding.primary.short_name', 'DF') }} :: @yield('title')</title>

    <link rel="stylesheet" href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>
<body class="antialiased bg-blue-900">

<div class="relative flex items-top justify-center min-h-screen sm:items-center sm:pt-0">

    <div class="max-w-xl mx-auto px-4 lg:px-8 items-center text-center">

        <x-brand-logo class="w-32 mx-auto mb-2" text-class="text-2xl font-bold text-slate-800 block mb-2" />
        <div class="flex flex-col items-center pt-4 sm:justify-start sm:pt-0">

            <div class="px-4 mb-4 text-sm text-gray-200 tracking-wider">
                @yield('code')
            </div>

            <div class="text-gray-200">
                @yield('message')
            </div>

        </div>

    </div>
</div>
</body>
</html>
