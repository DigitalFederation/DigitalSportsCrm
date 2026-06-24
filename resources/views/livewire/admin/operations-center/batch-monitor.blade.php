<div wire:poll.5s>
    <!-- Tabs -->
    <div class="bg-white rounded-lg shadow">
        <div class="border-b border-slate-200">
            <nav class="flex -mb-px">
                <button
                    wire:click="setTab('active')"
                    class="px-6 py-3 border-b-2 font-medium text-sm {{ $activeTab === 'active' ? 'border-blue-500 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}"
                >
                    {{ __('operations.active_batches') }} ({{ $activeBatches->count() }})
                </button>
                <button
                    wire:click="setTab('completed')"
                    class="px-6 py-3 border-b-2 font-medium text-sm {{ $activeTab === 'completed' ? 'border-blue-500 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}"
                >
                    {{ __('operations.completed_batches') }} ({{ $completedBatches->count() }})
                </button>
            </nav>
        </div>

        <!-- Content -->
        <div class="p-4">
            @if($activeTab === 'active')
                @if($activeBatches->isEmpty())
                    <div class="text-center py-8">
                        <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-slate-500">{{ __('operations.no_active_batches') }}</p>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($activeBatches as $batch)
                            <div class="border border-slate-200 rounded-lg p-4" wire:key="active-{{ $batch->id }}">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <h3 class="font-semibold text-slate-800">{{ $batch->name }}</h3>
                                        <p class="text-sm text-slate-500">{{ __('operations.started') }}: {{ $batch->created_at->diffForHumans() }}</p>
                                    </div>
                                    @if(!$batch->cancelled_at)
                                        <button
                                            wire:click="cancelBatch('{{ $batch->id }}')"
                                            wire:confirm="{{ __('operations.confirm_cancel_batch') }}"
                                            class="text-red-600 hover:text-red-800 text-sm font-medium"
                                        >
                                            {{ __('operations.cancel') }}
                                        </button>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            {{ __('operations.cancelled') }}
                                        </span>
                                    @endif
                                </div>

                                <!-- Progress Bar -->
                                <div class="mb-3">
                                    <div class="flex justify-between text-sm text-slate-600 mb-1">
                                        <span>{{ __('operations.progress') }}: {{ $batch->progress }}%</span>
                                        <span>{{ $batch->completed_jobs }} / {{ $batch->total_jobs }} {{ __('operations.jobs') }}</span>
                                    </div>
                                    <div class="w-full bg-slate-200 rounded-full h-2.5">
                                        <div class="h-2.5 rounded-full {{ $batch->failed_jobs > 0 ? 'bg-yellow-500' : 'bg-blue-600' }}" style="width: {{ $batch->progress }}%"></div>
                                    </div>
                                </div>

                                <!-- Stats -->
                                <div class="grid grid-cols-4 gap-4 text-center text-sm">
                                    <div>
                                        <p class="font-semibold text-slate-800">{{ $batch->total_jobs }}</p>
                                        <p class="text-slate-500">{{ __('operations.total') }}</p>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-blue-600">{{ $batch->pending_jobs }}</p>
                                        <p class="text-slate-500">{{ __('operations.pending') }}</p>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-green-600">{{ $batch->completed_jobs }}</p>
                                        <p class="text-slate-500">{{ __('operations.completed') }}</p>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-red-600">{{ $batch->failed_jobs }}</p>
                                        <p class="text-slate-500">{{ __('operations.failed') }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            @else
                @if($completedBatches->isEmpty())
                    <div class="text-center py-8">
                        <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-slate-500">{{ __('operations.no_completed_batches') }}</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('operations.batch_name') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('operations.total_jobs') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('operations.completed') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('operations.failed') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('operations.status') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('operations.finished_at') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-200">
                                @foreach($completedBatches as $batch)
                                    <tr wire:key="completed-{{ $batch->id }}">
                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-slate-800">{{ $batch->name }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-500">{{ $batch->total_jobs }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-green-600">{{ $batch->completed_jobs }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-red-600">{{ $batch->failed_jobs }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            @if($batch->success)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    {{ __('operations.success') }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    {{ __('operations.partial') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-500">{{ $batch->finished_at->diffForHumans() }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
