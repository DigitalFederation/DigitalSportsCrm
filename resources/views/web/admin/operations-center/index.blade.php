@section('title', __('operations.title'))
<x-layout>
    <div class="previous-layout-classes">
        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('operations.title') }}</h1>
                <p class="text-sm text-slate-500">{{ __('operations.subtitle') }}</p>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a class="btn btn-secondary" href="{{ route('admin.operations.queue') }}">
                    {{ __('operations.queue_details') }}
                </a>
                <a class="btn btn-secondary" href="{{ route('admin.operations.commands') }}">
                    {{ __('operations.command_center') }}
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <!-- Pending Jobs -->
            <div class="bg-white rounded-lg shadow p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-slate-500">{{ __('operations.pending_jobs') }}</p>
                        <p class="text-2xl font-bold text-slate-800">{{ $queueStats['pending'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Processing Jobs -->
            <div class="bg-white rounded-lg shadow p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-yellow-600 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-slate-500">{{ __('operations.processing') }}</p>
                        <p class="text-2xl font-bold text-slate-800">{{ $queueStats['processing'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Failed Jobs -->
            <div class="bg-white rounded-lg shadow p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 rounded-full {{ $queueStats['failed'] > 0 ? 'bg-red-100' : 'bg-green-100' }} flex items-center justify-center">
                            @if($queueStats['failed'] > 0)
                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            @else
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            @endif
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-slate-500">{{ __('operations.failed_jobs') }}</p>
                        <p class="text-2xl font-bold {{ $queueStats['failed'] > 0 ? 'text-red-600' : 'text-slate-800' }}">{{ $queueStats['failed'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Active Batches -->
            <div class="bg-white rounded-lg shadow p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-slate-500">{{ __('operations.active_batches') }}</p>
                        <p class="text-2xl font-bold text-slate-800">{{ $queueStats['active_batches'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Health -->
        <div class="bg-white rounded-lg shadow p-5 mb-8">
            <h2 class="text-lg font-semibold text-slate-800 mb-4">{{ __('operations.system_health') }}</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <!-- Database -->
                <div class="flex items-center">
                    <span class="w-3 h-3 rounded-full mr-2 {{ $systemHealth['database']['status'] === 'ok' ? 'bg-green-500' : 'bg-red-500' }}"></span>
                    <span class="text-sm text-slate-600">{{ __('operations.database') }}: {{ $systemHealth['database']['message'] }}</span>
                </div>
                <!-- Queue -->
                <div class="flex items-center">
                    <span class="w-3 h-3 rounded-full mr-2 {{ $systemHealth['queue']['status'] === 'ok' ? 'bg-green-500' : 'bg-red-500' }}"></span>
                    <span class="text-sm text-slate-600">{{ __('operations.queue') }}: {{ $systemHealth['queue']['message'] }}</span>
                </div>
                <!-- Storage -->
                <div class="flex items-center">
                    <span class="w-3 h-3 rounded-full mr-2 {{ $systemHealth['storage']['status'] === 'ok' ? 'bg-green-500' : 'bg-red-500' }}"></span>
                    <span class="text-sm text-slate-600">{{ __('operations.storage') }}: {{ $systemHealth['storage']['message'] }}</span>
                </div>
                <!-- Failed Jobs Alert -->
                <div class="flex items-center">
                    @php
                        $alertColor = match($systemHealth['failed_jobs_alert']['status']) {
                            'ok' => 'bg-green-500',
                            'warning' => 'bg-yellow-500',
                            default => 'bg-red-500'
                        };
                    @endphp
                    <span class="w-3 h-3 rounded-full mr-2 {{ $alertColor }}"></span>
                    <span class="text-sm text-slate-600">{{ $systemHealth['failed_jobs_alert']['message'] }}</span>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Scheduled Tasks -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-5 py-4 border-b border-slate-100">
                    <div class="flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-slate-800">{{ __('operations.scheduled_tasks') }}</h2>
                        <a href="{{ route('admin.operations.scheduler') }}" class="text-sm text-blue-600 hover:text-blue-800">
                            {{ __('operations.view_all') }}
                        </a>
                    </div>
                </div>
                <div class="p-5">
                    @forelse($scheduledTasks->take(5) as $task)
                        <div class="flex items-center justify-between py-3 {{ !$loop->last ? 'border-b border-slate-100' : '' }}">
                            <div>
                                <p class="text-sm font-medium text-slate-800">{{ $task['signature'] }}</p>
                                <p class="text-xs text-slate-500">{{ $task['human_readable'] }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-slate-500">{{ __('operations.next_run') }}</p>
                                <p class="text-sm text-slate-600">{{ $task['next_run']->diffForHumans() }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500 text-center py-4">{{ __('operations.no_scheduled_tasks') }}</p>
                    @endforelse
                </div>
            </div>

            <!-- Recent Failed Jobs -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-5 py-4 border-b border-slate-100">
                    <div class="flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-slate-800">{{ __('operations.recent_failed_jobs') }}</h2>
                        <a href="{{ route('admin.operations.queue') }}" class="text-sm text-blue-600 hover:text-blue-800">
                            {{ __('operations.view_all') }}
                        </a>
                    </div>
                </div>
                <div class="p-5">
                    @forelse($failedJobs as $job)
                        <div class="flex items-center justify-between py-3 {{ !$loop->last ? 'border-b border-slate-100' : '' }}">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-slate-800 truncate">{{ $job->job_class }}</p>
                                <p class="text-xs text-red-500 truncate">{{ Str::limit($job->exception, 60) }}</p>
                            </div>
                            <div class="text-right ml-4 flex-shrink-0">
                                <p class="text-xs text-slate-500">{{ $job->failed_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <svg class="w-12 h-12 text-green-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-sm text-slate-500">{{ __('operations.no_failed_jobs') }}</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow mt-8">
            <div class="px-5 py-4 border-b border-slate-100">
                <h2 class="text-lg font-semibold text-slate-800">{{ __('operations.quick_actions') }}</h2>
            </div>
            <div class="p-5">
                <livewire:admin.operations-center.quick-actions />
            </div>
        </div>
    </div>
</x-layout>
