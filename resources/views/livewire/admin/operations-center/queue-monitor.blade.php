<div wire:poll.5s>
    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-blue-50 rounded-lg p-4">
            <p class="text-sm text-blue-600 font-medium">{{ __('operations.pending_jobs') }}</p>
            <p class="text-2xl font-bold text-blue-800">{{ $stats['pending'] }}</p>
        </div>
        <div class="bg-yellow-50 rounded-lg p-4">
            <p class="text-sm text-yellow-600 font-medium">{{ __('operations.processing') }}</p>
            <p class="text-2xl font-bold text-yellow-800">{{ $stats['processing'] }}</p>
        </div>
        <div class="bg-red-50 rounded-lg p-4">
            <p class="text-sm text-red-600 font-medium">{{ __('operations.failed_jobs') }}</p>
            <p class="text-2xl font-bold text-red-800">{{ $stats['failed'] }}</p>
        </div>
        <div class="bg-purple-50 rounded-lg p-4">
            <p class="text-sm text-purple-600 font-medium">{{ __('operations.active_batches') }}</p>
            <p class="text-2xl font-bold text-purple-800">{{ $stats['active_batches'] }}</p>
        </div>
    </div>

    <!-- Tabs -->
    <div class="bg-white rounded-lg shadow">
        <div class="border-b border-slate-200">
            <nav class="flex -mb-px">
                <button
                    wire:click="setTab('pending')"
                    class="px-6 py-3 border-b-2 font-medium text-sm {{ $activeTab === 'pending' ? 'border-blue-500 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}"
                >
                    {{ __('operations.pending_jobs') }} ({{ $stats['pending'] }})
                </button>
                <button
                    wire:click="setTab('failed')"
                    class="px-6 py-3 border-b-2 font-medium text-sm {{ $activeTab === 'failed' ? 'border-blue-500 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}"
                >
                    {{ __('operations.failed_jobs') }} ({{ $stats['failed'] }})
                </button>
            </nav>
        </div>

        <!-- Search and Actions -->
        <div class="p-4 border-b border-slate-200 flex flex-wrap gap-4 items-center justify-between">
            <div class="flex-1 max-w-md">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="{{ __('operations.search_jobs') }}"
                    class="form-input w-full rounded-md border-slate-300"
                />
            </div>
            @if($activeTab === 'failed' && $stats['failed'] > 0)
                <button
                    wire:click="purgeAllFailed"
                    wire:confirm="{{ __('operations.confirm_purge_all') }}"
                    class="btn btn-danger"
                >
                    {{ __('operations.purge_all_failed') }}
                </button>
            @endif
        </div>

        <!-- Content -->
        <div class="p-4">
            @if($activeTab === 'pending')
                @if($pendingJobs->isEmpty())
                    <div class="text-center py-8">
                        <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-slate-500">{{ __('operations.no_pending_jobs') }}</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('operations.job_class') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('operations.queue') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('operations.status') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('operations.attempts') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('operations.created_at') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-200">
                                @foreach($pendingJobs as $job)
                                    <tr wire:key="pending-{{ $job->id }}">
                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-slate-800">{{ $job->job_class }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-500">{{ $job->queue }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            @if($job->is_processing)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    <svg class="w-3 h-3 mr-1 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                                    </svg>
                                                    {{ __('operations.processing') }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    {{ __('operations.pending') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-500">{{ $job->attempts }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-500">{{ $job->created_at->diffForHumans() }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            @else
                @if($failedJobs->isEmpty())
                    <div class="text-center py-8">
                        <svg class="w-16 h-16 text-green-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-slate-500">{{ __('operations.no_failed_jobs') }}</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('operations.job_class') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('operations.queue') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('operations.error') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('operations.failed_at') }}</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('operations.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-200">
                                @foreach($failedJobs as $job)
                                    <tr wire:key="failed-{{ $job->uuid }}">
                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-slate-800">{{ $job->job_class }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-500">{{ $job->queue }}</td>
                                        <td class="px-4 py-3 text-sm text-red-600 max-w-xs truncate">
                                            <button
                                                wire:click="showJobDetails('{{ $job->uuid }}', '{{ addslashes($job->exception_full) }}')"
                                                class="text-left hover:underline"
                                            >
                                                {{ Str::limit($job->exception, 50) }}
                                            </button>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-500">{{ $job->failed_at->diffForHumans() }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                            <button
                                                wire:click="retryJob('{{ $job->uuid }}')"
                                                wire:loading.attr="disabled"
                                                wire:confirm="{{ __('operations.confirm_retry') }}"
                                                class="text-blue-600 hover:text-blue-800"
                                            >
                                                {{ __('operations.retry') }}
                                            </button>
                                            <button
                                                wire:click="deleteJob('{{ $job->uuid }}')"
                                                wire:loading.attr="disabled"
                                                wire:confirm="{{ __('operations.confirm_delete') }}"
                                                class="text-red-600 hover:text-red-800"
                                            >
                                                {{ __('operations.delete') }}
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            @endif
        </div>
    </div>

    <!-- Job Details Modal -->
    @if($selectedJobUuid)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" wire:click.self="closeJobDetails">
            <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full mx-4 max-h-[80vh] overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-slate-800">{{ __('operations.job_details') }}</h3>
                    <button wire:click="closeJobDetails" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="p-6 overflow-y-auto max-h-[60vh]">
                    <p class="text-sm text-slate-500 mb-2">UUID: {{ $selectedJobUuid }}</p>
                    <pre class="bg-slate-900 text-slate-100 p-4 rounded-lg text-xs overflow-x-auto whitespace-pre-wrap">{{ $selectedJobException }}</pre>
                </div>
                <div class="px-6 py-4 border-t border-slate-200 flex justify-end gap-2">
                    <button wire:click="closeJobDetails" class="btn btn-secondary">
                        {{ __('operations.close') }}
                    </button>
                    <button
                        wire:click="retryJob('{{ $selectedJobUuid }}')"
                        wire:confirm="{{ __('operations.confirm_retry') }}"
                        class="btn btn-primary"
                    >
                        {{ __('operations.retry') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
