<div>
    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="flex flex-wrap gap-4 items-center">
            <!-- Search -->
            <div class="flex-1 min-w-[200px]">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="{{ __('operations.search_commands') }}"
                    class="form-input w-full rounded-md border-slate-300"
                />
            </div>

            <!-- Category Filter -->
            <div class="flex flex-wrap gap-2">
                <button
                    wire:click="selectCategory(null)"
                    class="px-3 py-1.5 text-sm font-medium rounded-full {{ $selectedCategory === null ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}"
                >
                    {{ __('operations.all_categories') }}
                </button>
                @foreach($categories as $key => $category)
                    <button
                        wire:click="selectCategory('{{ $key }}')"
                        class="px-3 py-1.5 text-sm font-medium rounded-full {{ $selectedCategory === $key ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}"
                    >
                        {{ __('operations.category_' . $key) }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Commands by Category -->
    @forelse($commands as $categoryKey => $category)
        <div class="bg-white rounded-lg shadow mb-6" wire:key="category-{{ $categoryKey }}">
            <div class="px-6 py-4 border-b border-slate-200">
                <h2 class="text-lg font-semibold text-slate-800">{{ __('operations.category_' . $categoryKey) }}</h2>
            </div>

            <div class="divide-y divide-slate-200">
                @foreach($category['commands'] as $signature => $config)
                    <div class="px-6 py-4 flex items-center justify-between" wire:key="command-{{ $signature }}">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <code class="text-sm font-mono bg-slate-100 px-2 py-1 rounded text-slate-800">{{ $signature }}</code>
                                @if($config['dangerous'] ?? false)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                        {{ __('operations.dangerous') }}
                                    </span>
                                @endif
                                @if($config['supports_dry_run'] ?? false)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ __('operations.supports_dry_run') }}
                                    </span>
                                @endif
                            </div>
                            <p class="text-sm text-slate-600 mt-1">{{ $config['name'] }}</p>
                            <p class="text-sm text-slate-500">{{ $config['description'] }}</p>
                            @php $lastRun = $this->getLastExecutionTime($signature); @endphp
                            @if($lastRun)
                                <p class="text-xs text-slate-400 mt-1">
                                    {{ __('operations.last_executed') }}: {{ $lastRun->diffForHumans() }}
                                </p>
                            @endif
                        </div>

                        <div class="ml-4">
                            <button
                                wire:click="executeCommand('{{ $signature }}')"
                                wire:loading.attr="disabled"
                                wire:confirm="{{ __('operations.confirm_execute_command', ['command' => $signature]) }}"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                                {{ $isRunning ? 'disabled' : '' }}
                            >
                                @if($isRunning && $runningCommand === $signature)
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ __('operations.executing') }}
                                @else
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    {{ __('operations.execute') }}
                                @endif
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="bg-white rounded-lg shadow p-8 text-center">
            <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="text-slate-500">{{ __('operations.no_commands_found') }}</p>
        </div>
    @endforelse

    <!-- Output Modal -->
    @if($showOutput)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" wire:click.self="closeOutput">
            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-[80vh] overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-slate-800">{{ __('operations.command_output') }}</h3>
                    <button wire:click="closeOutput" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="p-6 overflow-y-auto max-h-[60vh]">
                    <pre class="bg-slate-900 text-slate-100 p-4 rounded-lg text-sm overflow-x-auto whitespace-pre-wrap">{{ $lastOutput ?: __('operations.no_output') }}</pre>
                </div>
                <div class="px-6 py-4 border-t border-slate-200 flex justify-end">
                    <button wire:click="closeOutput" class="btn btn-secondary">
                        {{ __('operations.close') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
