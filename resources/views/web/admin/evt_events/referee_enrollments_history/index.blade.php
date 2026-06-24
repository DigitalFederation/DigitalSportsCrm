<x-layout>
    <div class="previous-layout-classes">

        <div class="space-y-6" x-data="{ loaded: false }" x-init="setTimeout(() => loaded = true, 100)">

            {{-- Header --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden"
                 x-show="loaded"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform -translate-y-2"
                 x-transition:enter-end="opacity-100 transform translate-y-0">

                <div class="px-6 py-6 sm:px-8">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        {{-- Left: Info --}}
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-purple-100">
                                    <x-heroicon-s-clock class="w-6 h-6 text-purple-600" />
                                </div>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-purple-50 text-purple-700 border border-purple-200">
                                    {{ __('events.technical_official') }}
                                </span>
                            </div>
                            <h1 class="text-xl sm:text-2xl font-semibold text-purple-600">{{ __('events.admin_referee_enrollments_history_title') }}</h1>
                            <p class="mt-1 text-gray-500 text-sm">{{ __('events.admin_referee_enrollments_history_description') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Main Content Card --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
                 x-show="loaded"
                 x-transition:enter="transition ease-out duration-500 delay-200"
                 x-transition:enter-start="opacity-0 transform translate-y-4"
                 x-transition:enter-end="opacity-100 transform translate-y-0">

                <div class="p-4 sm:p-6 overflow-x-auto">
                    @livewire('admin.evt-events.referee-enrollments-history-table')
                </div>
            </div>

        </div>

    </div>
</x-layout>
