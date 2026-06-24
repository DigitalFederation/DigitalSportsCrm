<div>
    <!-- Actions -->
    <div class="mb-6">
        <button
            wire:click="createBackup"
            wire:confirm="{{ __('backups.confirm_create') }}"
            class="btn btn-primary"
        >
            <span wire:loading.remove wire:target="createBackup">
                <svg class="w-4 h-4 mr-2 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                </svg>
                {{ __('backups.create_backup') }}
            </span>
            <span wire:loading wire:target="createBackup">
                <svg class="w-4 h-4 mr-2 inline-block animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                {{ __('backups.creating') }}...
            </span>
        </button>
    </div>

    <!-- Backup List -->
    @if($backups->isEmpty())
        <div class="text-center py-8">
            <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
            </svg>
            <p class="text-sm text-slate-500">{{ __('backups.no_backups') }}</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead>
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                            {{ __('backups.name') }}
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                            {{ __('backups.size') }}
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                            {{ __('backups.date') }}
                        </th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">
                            {{ __('backups.actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($backups as $backup)
                        <tr wire:key="backup-{{ $loop->index }}">
                            <td class="px-4 py-3 text-sm text-slate-800">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 text-slate-400 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    {{ $backup['name'] }}
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-600">
                                @if($backup['size'] >= 1073741824)
                                    {{ number_format($backup['size'] / 1073741824, 2) }} GB
                                @elseif($backup['size'] >= 1048576)
                                    {{ number_format($backup['size'] / 1048576, 2) }} MB
                                @elseif($backup['size'] >= 1024)
                                    {{ number_format($backup['size'] / 1024, 2) }} KB
                                @else
                                    {{ $backup['size'] }} B
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-600">
                                <span title="{{ $backup['date']->format('Y-m-d H:i:s') }}">
                                    {{ $backup['date']->diffForHumans() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-right">
                                <a
                                    href="{{ route('admin.backups.download', $backup['name']) }}"
                                    class="btn btn-sm btn-secondary"
                                >
                                    <svg class="w-4 h-4 mr-1 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                    </svg>
                                    {{ __('backups.download') }}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
