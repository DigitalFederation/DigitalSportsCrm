<!doctype html>
<html lang="{{ config('app.locale') }}">

    <head>
        @include('web.layout.head')
    </head>

    <body class="font-inter antialiased bg-slate-100 text-slate-600">

        
        <!-- Page wrapper -->
        <div class="flex h-screen overflow-hidden">
            <div class="relative flex flex-col flex-1 overflow-y-auto overflow-x-hidden" x-ref="contentarea">
                <main>
                {{ $slot }}
                </main>
            </div>
        </div>



        @livewireScripts
    </body>
</html>