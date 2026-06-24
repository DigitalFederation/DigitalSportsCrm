<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 h-full overflow-hidden">
    <div class="flex items-center justify-between px-4 sm:px-5 py-3 sm:py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-blue-50 to-white dark:from-gray-800 dark:to-gray-800">
        <div class="flex items-center gap-2 sm:gap-3">
            <div class="flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9 rounded-lg bg-orange-100 dark:bg-orange-900/30">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-orange-600 dark:text-orange-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10 8a3 3 0 100-6 3 3 0 000 6zM3.465 14.493a1.23 1.23 0 00.41 1.412A9.957 9.957 0 0010 18c2.31 0 4.438-.784 6.131-2.1.43-.333.604-.903.408-1.41a7.002 7.002 0 00-13.074.003z" />
                </svg>
            </div>
            <h2 class="text-sm sm:text-base font-semibold text-gray-900 dark:text-white">{{ __('dashboard.pending_individual_approvals') }}</h2>
        </div>
        @if($pendingCount > 0)
            <span class="inline-flex items-center rounded-full bg-orange-100 dark:bg-orange-900/50 px-2.5 py-1 text-xs font-semibold text-orange-700 dark:text-orange-400">
                {{ $pendingCount }}
            </span>
        @endif
    </div>

    <div class="p-4 sm:p-5">
        @if($pendingIndividuals->count() > 0)
            <div class="space-y-3">
                @foreach($pendingIndividuals as $individualFederation)
                    <div wire:key="pending-individual-{{ $individualFederation->id }}" class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                {{ $individualFederation->individual->name ?? '-' }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                {{ __('dashboard.requested_at') }}: {{ $individualFederation->created_at->translatedFormat('d/m/Y') }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4">
                <a href="{{ route('admin.individual.index') }}"
                   class="inline-flex items-center justify-center w-full px-4 py-2.5 text-sm font-medium text-orange-700 dark:text-orange-400 bg-orange-50 dark:bg-orange-900/30 rounded-lg hover:bg-orange-100 dark:hover:bg-orange-900/50 transition-colors">
                    {{ __('dashboard.view_all') }}
                    <svg class="ml-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd" />
                    </svg>
                </a>
            </div>
        @else
            <div class="text-center py-8">
                <div class="mx-auto h-12 w-12 rounded-full bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center">
                    <svg class="h-6 w-6 text-emerald-500 dark:text-emerald-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('dashboard.no_pending') }}</p>
            </div>
        @endif
    </div>
</div>
