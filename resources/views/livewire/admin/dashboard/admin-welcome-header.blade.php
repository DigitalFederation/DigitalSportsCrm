<div class="relative bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 mb-8">
    <div class="flex items-center gap-6">
        <!-- Logo -->
        <div class="flex-shrink-0">
            <img class="w-20 h-20 sm:w-24 sm:h-24 rounded-xl object-cover ring-4 ring-white shadow-lg"
                 src="{{ $federation?->getFirstMediaUrl('profile', 'preview') ?: asset('img/user_placeholder.png') }}"
                 alt="{{ $federation?->name }}">
        </div>

        <!-- Nome da Federacao -->
        <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                {{ __('dashboard.admin_dashboard') }}
            </p>
            <h1 class="text-2xl md:text-3xl text-gray-900 dark:text-white font-semibold tracking-tight">
                {{ $federation?->name ?? __('dashboard.federation') }}
            </h1>
        </div>
    </div>
</div>
