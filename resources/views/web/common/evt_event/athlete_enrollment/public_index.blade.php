<x-layout-full>
    <div class="previous-layout-classes relative">
        <!-- Page header -->
        <div class="h-80 items-center flex flex-col justify-center bg-cover bg-center relative bg-zoom-in zoom-in"
            style="background-image: url('{{ $event->heroImage }}');">
            <!-- Overlay: Color Blue with Opacity -->
            <div class="absolute top-0 left-0 right-0 bottom-0 bg-blue-500/50 h-80"></div>
            <!-- Left: Title -->
            <div class="relative mb-4 sm:mb-0 flex flex-col justify-center text-center mx-auto">
                <h1 class="text-4xl md:text-6xl text-white font-bold subpixel-antialiased drop-shadow-lg">
                    @if (!$event->name)
                        {{ __('Event Details') }}
                    @else
                        {{ $event->name }}
                    @endif
                </h1>
            </div>
        </div>

        <div class="page-wrapper -mt-20 md:-mt-24">
            <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">

                <!-- Main Content Area -->
                <div class="py-8">
                    {{-- Render the Livewire component for the list and filters --}}
                    <livewire:common.evt-event.public-athlete-enrollment-list :event="$event" />
                </div>

            </div>
        </div>
    </div>

    <style>
        /* CSS animation for zooming effect */
        @keyframes zoomIn {
            0% {
                transform: scale(1.1);
                /* Final scale */
            }

            100% {
                transform: scale(1);
                /* Initial scale */
            }
        }

        /* Apply animation to the background image */
        .bg-cover.zoom-in {
            transform-origin: center;
            animation: zoomIn 1s ease forwards;
        }
    </style>
</x-layout-full>
