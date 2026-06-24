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
                        <div class="flex items-center justify-center w-10 h-10 rounded-full bg-blue-100">
                            <x-heroicon-s-user-group class="w-6 h-6 text-blue-600" />
                        </div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                            {{ __('events.technical_team') }}
                        </span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-semibold text-gray-900">{{ __('events.technical_team_history_title') }}</h1>
                    <p class="mt-1 text-gray-500 text-sm">{{ __('events.technical_team_history_description') }}</p>
                </div>

                {{-- Right: Total Count Badge --}}
                <div class="flex-shrink-0">
                    <div class="inline-flex flex-col items-center justify-center px-5 py-3 rounded-xl bg-blue-50 border border-blue-200">
                        <span class="text-3xl sm:text-4xl font-bold text-gray-900 tabular-nums">{{ $this->historyCount }}</span>
                        <span class="text-xs font-medium text-blue-600 mt-0.5">{{ __('events.events_as_team') }}</span>
                    </div>
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

        <div class="p-4 sm:p-6">
            @if($this->historyCount === 0)
                {{-- Empty State --}}
                <div class="text-center py-12 sm:py-16 px-4">
                    <div class="relative inline-flex items-center justify-center w-20 h-20 sm:w-24 sm:h-24 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 mb-6">
                        <x-heroicon-o-user-group class="w-10 h-10 sm:w-12 sm:h-12 text-gray-400" />
                        <div class="absolute -bottom-1 -right-1 w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center">
                            <x-heroicon-m-clipboard-document-list class="w-4 h-4 text-amber-600" />
                        </div>
                    </div>
                    <h3 class="text-lg sm:text-xl font-semibold text-gray-900">{{ __('events.no_team_history') }}</h3>
                    <p class="mt-2 text-sm sm:text-base text-gray-500 max-w-md mx-auto">{{ __('events.no_team_history_desc') }}</p>
                </div>
            @else
                {{ $this->table }}
            @endif
        </div>
    </div>
</div>
