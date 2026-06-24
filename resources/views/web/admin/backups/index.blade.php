@section('title', __('backups.title'))
<x-layout>
    <div class="previous-layout-classes">
        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('backups.title') }}</h1>
                <p class="text-sm text-slate-500">{{ __('backups.subtitle') }}</p>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <!-- Total Backups -->
            <div class="bg-white rounded-lg shadow p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-slate-500">{{ __('backups.total_backups') }}</p>
                        <p class="text-2xl font-bold text-slate-800">{{ $stats['total_count'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Total Size -->
            <div class="bg-white rounded-lg shadow p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-slate-500">{{ __('backups.total_size') }}</p>
                        <p class="text-2xl font-bold text-slate-800">
                            @if($stats['total_size'] >= 1073741824)
                                {{ number_format($stats['total_size'] / 1073741824, 2) }} GB
                            @elseif($stats['total_size'] >= 1048576)
                                {{ number_format($stats['total_size'] / 1048576, 2) }} MB
                            @elseif($stats['total_size'] >= 1024)
                                {{ number_format($stats['total_size'] / 1024, 2) }} KB
                            @else
                                {{ $stats['total_size'] }} B
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Last Backup -->
            <div class="bg-white rounded-lg shadow p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-slate-500">{{ __('backups.last_backup') }}</p>
                        <p class="text-2xl font-bold text-slate-800">
                            {{ $stats['last_backup'] ? $stats['last_backup']->diffForHumans() : __('backups.never') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Backup Settings Component -->
        <div class="bg-white rounded-lg shadow mb-8">
            <div class="px-5 py-4 border-b border-slate-100">
                <h2 class="text-lg font-semibold text-slate-800">{{ __('backups.settings_title') }}</h2>
                <p class="text-sm text-slate-500">{{ __('backups.settings_subtitle') }}</p>
            </div>
            <div class="p-5">
                <livewire:admin.backup-settings />
            </div>
        </div>

        <!-- Backup Manager Component -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-5 py-4 border-b border-slate-100">
                <h2 class="text-lg font-semibold text-slate-800">{{ __('backups.manage_backups') }}</h2>
            </div>
            <div class="p-5">
                <livewire:admin.backup-manager />
            </div>
        </div>
    </div>
</x-layout>
