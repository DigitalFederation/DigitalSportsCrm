@section('title', __('menu.dynamic.admin.menu_management'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('menu.dynamic.admin.menu_management') }}</h1>
                <p class="text-slate-600 mt-2">{{ __('menu.dynamic.admin.management_description') }}</p>
            </div>

        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
            <!-- Total Menus -->
            <div class="bg-white border border-slate-200 rounded-lg shadow-sm">
                <div class="px-5 py-4">
                    <h2 class="text-sm font-medium text-slate-600 mb-2">{{ __('menu.dynamic.admin.total_menus') }}</h2>
                    <div class="text-3xl font-bold text-slate-800">{{ number_format($stats['total_menus']) }}</div>
                </div>
            </div>

            <!-- Total Items -->
            <div class="bg-white border border-slate-200 rounded-lg shadow-sm">
                <div class="px-5 py-4">
                    <h2 class="text-sm font-medium text-slate-600 mb-2">{{ __('menu.dynamic.admin.total_items') }}</h2>
                    <div class="text-3xl font-bold text-slate-800">{{ number_format($stats['total_items']) }}</div>
                </div>
            </div>

            <!-- Enabled Menus -->
            <div class="bg-white border border-slate-200 rounded-lg shadow-sm">
                <div class="px-5 py-4">
                    <h2 class="text-sm font-medium text-slate-600 mb-2">{{ __('menu.dynamic.admin.enabled_menus') }}</h2>
                    <div class="text-3xl font-bold text-slate-800">{{ number_format($stats['enabled_menus']) }}</div>
                </div>
            </div>
        </div>

        <!-- Feature Flag Information -->
        <div class="mb-8">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">{{ __('menu.dynamic.admin.feature_flags_title') }}</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <p>{{ __('menu.dynamic.admin.feature_flags_description') }}</p>
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach(['cmas', 'federation', 'entity', 'individual'] as $menuType)
                                    @if(\App\Services\FeatureFlagService::isDynamicMenuEnabledFor($menuType))
                                        <span class="inline-flex items-center px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ ucfirst($menuType) }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 bg-gray-100 text-gray-800 text-xs font-medium rounded-full">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ ucfirst($menuType) }}
                                        </span>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Menu Management Interface -->
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm">
            <div class="bg-indigo-600 text-white px-5 py-3 rounded-t-lg">
                <h2 class="text-lg font-semibold flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                    </svg>
                    {{ __('menu.dynamic.admin.manage_menu_items') }}
                </h2>
            </div>
            <div class="p-5">
                <livewire:admin.menu-management />
            </div>
        </div>

    </div>
</x-layout>