<section class="w-full">
    @if($logs->count() > 0)
        <div class="w-full bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-100 dark:border-gray-700">
            <div class="flex items-center justify-between p-4 border-b border-gray-100 dark:border-gray-700">
                <div class="flex items-center space-x-3">
                    <div class="h-8 w-8 rounded-full bg-primary-50 dark:bg-primary-900/50 flex items-center justify-center">
                        <svg class="h-4 w-4 text-primary-600 dark:text-primary-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('dashboard.recent_actions') }}</h2>
                </div>

                <span class="inline-flex items-center rounded-md bg-primary-50 dark:bg-primary-900/50 px-2 py-1 text-xs font-medium text-primary-700 dark:text-primary-400 ring-1 ring-inset ring-primary-600/10">
                    {{ $logs->total() }} {{ __('activity.entries') }}
                </span>
            </div>

            <div class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach($logs as $log)
                    <div wire:key="log-{{ $log->id }}" class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $this->getFormattedDescription($log) }}
                                </p>
                                <div class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs">
                                    <span class="text-gray-500 dark:text-gray-400">
                                        {{ $log->created_at->translatedFormat('d/m/Y H:i') }}
                                    </span>
                                </div>
                            </div>

                            @if($this->isAdmin())
                            <button
                                wire:click="showDetails('{{ $log->id }}')"
                                class="flex-shrink-0 inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input hover:bg-accent hover:text-accent-foreground h-8 px-3 text-xs bg-white dark:bg-gray-800"
                            >
                                <svg class="h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M10 12.5a2.5 2.5 0 100-5 2.5 2.5 0 000 5z" />
                                    <path fill-rule="evenodd" d="M.664 10.59a1.651 1.651 0 010-1.186A10.004 10.004 0 0110 3c4.257 0 7.893 2.66 9.336 6.41.147.381.146.804 0 1.186A10.004 10.004 0 0110 17c-4.257 0-7.893-2.66-9.336-6.41zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                </svg>
                                {{ __('activity.details') }}
                            </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($logs->hasPages())
            <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700">
                {{ $logs->links() }}
            </div>
            @endif

        </div>
    @else
        <div class="text-center rounded-lg border border-dashed border-gray-200 dark:border-gray-700 p-8">
            <div class="mx-auto h-12 w-12 rounded-full bg-gray-50 dark:bg-gray-800 flex items-center justify-center">
                <svg class="h-6 w-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM12.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM18.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                </svg>
            </div>
            <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('activity.no_recent_actions') }}</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('activity.no_activity_recorded') }}</p>
        </div>
    @endif

    @if($this->isAdmin())
    <div x-data="{ open: false }" @open-modal.window="open = true" @close-modal.window="open = false" x-show="open"
         class="fixed inset-0 z-50 overflow-y-auto"
         x-cloak>
        <!-- Backdrop -->
        <div x-show="open"
             class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/90 transition-opacity"
             @click="open = false"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
        </div>

        <!-- Modal Panel -->
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div x-show="open"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">

                    @if($selectedLog)
                        <!-- Header -->
                        <div class="mb-6">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">
                                    {{ __('Activity Details') }}
                                </h3>
                                <button @click="open = false" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                                    <span class="sr-only">{{ __('Close') }}</span>
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="space-y-4">
                            <!-- Description -->
                            <div class="rounded-lg bg-gray-50 dark:bg-gray-900/50 p-4">
                                <div class="flex items-center space-x-3">
                                    <div class="h-10 w-10 rounded-full bg-primary-50 dark:bg-primary-900/50 flex items-center justify-center">
                                        <svg class="h-5 w-5 text-primary-600 dark:text-primary-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="text-sm font-medium text-gray-900 dark:text-white">{{ __('What Happened?') }}</h4>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $selectedLog->description }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Details Grid -->
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <!-- Action Type -->
                                <div class="rounded-lg bg-gray-50 dark:bg-gray-900/50 p-4">
                                    <h4 class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Action Type') }}</h4>
                                    <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">
                                        @switch($selectedLog->event)
                                            @case('created')
                                                {{ __('New Record Created') }}
                                                @break
                                            @case('updated')
                                                {{ __('Record Updated') }}
                                                @break
                                            @case('deleted')
                                                {{ __('Record Deleted') }}
                                                @break
                                            @default
                                                {{ __(ucfirst($selectedLog->event)) }}
                                        @endswitch
                                    </p>
                                </div>

                                <!-- Date -->
                                <div class="rounded-lg bg-gray-50 dark:bg-gray-900/50 p-4">
                                    <h4 class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('When?') }}</h4>
                                    <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $selectedLog->created_at->translatedFormat('d M Y, H:i') }}
                                    </p>
                                </div>

                                <!-- User -->
                                <div class="rounded-lg bg-gray-50 dark:bg-gray-900/50 p-4">
                                    <h4 class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Who?') }}</h4>
                                    <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $selectedLog->causer ? $selectedLog->causer->name : __('Automatic System Action') }}
                                    </p>
                                </div>

                                <!-- Properties - Only show relevant information -->
                                @if($selectedLog->properties && $selectedLog->properties->count() > 0)
                                    <div class="rounded-lg bg-gray-50 dark:bg-gray-900/50 p-4 sm:col-span-2">
                                        <h4 class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Details') }}</h4>
                                        <div class="mt-2 space-y-2">
                                            @foreach($selectedLog->properties as $key => $value)
                                                @if(in_array($key, ['total_value', 'status']) || (!str_contains($key, '_id') && !str_contains($key, '_type')))
                                                    <div class="flex items-start space-x-2 text-sm">
                                                        <span class="font-medium text-gray-900 dark:text-white">
                                                            @switch($key)
                                                                @case('total_value')
                                                                    {{ __('Amount') }}:
                                                                    @break
                                                                @case('status')
                                                                    {{ __('Status') }}:
                                                                    @break
                                                                @case('is_self_request')
                                                                    {{ __('Self Requested') }}:
                                                                    @break
                                                                @default
                                                                    {{ __(ucfirst(str_replace('_', ' ', $key))) }}:
                                                            @endswitch
                                                        </span>
                                                        <span class="text-gray-500 dark:text-gray-400">
                                                            @if($key === 'status' || $key === 'status_class')
                                                                <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium {{ $this->getStatusBadgeColor($this->formatStateClass($value)) === 'success' ? 'bg-green-50 text-green-700 ring-green-600/20' : '' }}
                                                                    {{ $this->getStatusBadgeColor($this->formatStateClass($value)) === 'warning' ? 'bg-yellow-50 text-yellow-700 ring-yellow-600/20' : '' }}
                                                                    {{ $this->getStatusBadgeColor($this->formatStateClass($value)) === 'danger' ? 'bg-red-50 text-red-700 ring-red-600/20' : '' }}
                                                                    {{ $this->getStatusBadgeColor($this->formatStateClass($value)) === 'info' ? 'bg-blue-50 text-blue-700 ring-blue-600/20' : '' }}
                                                                    ring-1 ring-inset">
                                                                    {{ $this->formatStateClass($value) }}
                                                                </span>
                                                            @elseif($key === 'total_value')
                                                                {{ money($value, $selectedLog->properties['currency'] ?? null) }}
                                                            @elseif($key === 'is_self_request')
                                                                {{ $value ? __('Yes') : __('No') }}
                                                            @else
                                                                @if(is_array($value))
                                                                    <ul class="list-disc pl-4">
                                                                        @foreach($value as $itemKey => $itemValue)
                                                                            <li>
                                                                                @if(is_array($itemValue))
                                                                                    {{ $itemKey }}: {{ json_encode($itemValue) }}
                                                                                @else
                                                                                    {{ $itemKey }}: {{ $itemValue }}
                                                                                @endif
                                                                            </li>
                                                                        @endforeach
                                                                    </ul>
                                                                @else
                                                                    {{ $value }}
                                                                @endif
                                                            @endif
                                                        </span>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="mt-6 flex justify-end space-x-3">
                            <button @click="open = false"
                                    class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 px-4">
                                {{ __('Close') }}
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
</section>
