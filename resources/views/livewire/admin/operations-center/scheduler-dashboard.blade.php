<div>
    <!-- Tasks Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200">
            <h2 class="text-lg font-semibold text-slate-800">{{ __('operations.scheduled_tasks') }}</h2>
            <p class="text-sm text-slate-500">{{ __('operations.scheduler_info') }}</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('operations.command') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('operations.frequency') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('operations.next_run') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('operations.last_run') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('operations.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-200">
                    @forelse($tasks as $task)
                        <tr wire:key="task-{{ $loop->index }}">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-slate-900">{{ $task['signature'] }}</div>
                                <div class="text-sm text-slate-500">{{ $task['description'] }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $task['human_readable'] }}
                                </span>
                                <div class="text-xs text-slate-400 mt-1">{{ $task['expression'] }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-slate-900">{{ $task['next_run']->format('M d, Y') }}</div>
                                <div class="text-sm text-slate-500">{{ $task['next_run']->format('H:i') }} ({{ $task['next_run']->diffForHumans() }})</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($task['last_run'])
                                    <div class="text-sm text-slate-900">{{ $task['last_run']['time']->format('M d, Y H:i') }}</div>
                                    <div class="text-sm text-slate-500">
                                        {{ $task['last_run']['ago'] }}
                                        @if($task['last_run']['manual'])
                                            <span class="text-xs text-amber-600">({{ __('operations.manual') }})</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-sm text-slate-400">{{ __('operations.never_run') }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button
                                    wire:click="runTask('{{ $task['signature'] }}')"
                                    wire:loading.attr="disabled"
                                    wire:confirm="{{ __('operations.confirm_run_task', ['task' => $task['signature']]) }}"
                                    class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                                    {{ $isRunning ? 'disabled' : '' }}
                                >
                                    @if($isRunning && $runningTask === $task['signature'])
                                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        {{ __('operations.running') }}
                                    @else
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        {{ __('operations.run_now') }}
                                    @endif
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-500">
                                {{ __('operations.no_scheduled_tasks') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Output Modal -->
    @if($showOutput)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" wire:click.self="closeOutput">
            <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full mx-4 max-h-[80vh] overflow-hidden">
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
