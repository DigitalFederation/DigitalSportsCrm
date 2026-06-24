@section('title', __('Dashboard'))
<x-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-6 sm:py-8 w-full max-w-9xl mx-auto">

        <!-- Welcome Banner with Federation Header -->
        <livewire:admin.dashboard.admin-welcome-header />

        <!-- ============================================ -->
        <!-- AREA 1: Actions & Approvals (No Title) -->
        <!-- ============================================ -->

        <!-- Recent Activity -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 mb-5 sm:mb-6 overflow-hidden">
            <div class="flex items-center justify-between px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-blue-50 to-white dark:from-gray-800 dark:to-gray-800">
                <div class="flex items-center gap-2 sm:gap-3">
                    <div class="flex items-center justify-center w-8 h-8 sm:w-10 sm:h-10 rounded-lg bg-indigo-100 dark:bg-indigo-900/30">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-indigo-600 dark:text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h2 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white">
                        {{ __('dashboard.recent_actions') }}
                    </h2>
                </div>
            </div>
            <div class="p-4 sm:p-6">
                <livewire:widgets.activity-log />
            </div>
        </div>

    </div>
</x-layout>
