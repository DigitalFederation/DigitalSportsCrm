@section('title', __('admin.role_mappings.dashboard_title'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('admin.role_mappings.dashboard_title') }}</h1>
            </div>

        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <!-- Total Roles -->
            <div class="bg-white border border-slate-200 rounded-lg shadow-sm">
                <div class="px-5 py-4">
                    <h2 class="text-sm font-medium text-slate-600 mb-2">{{ __('admin.role_mappings.total_roles') }}</h2>
                    <div class="text-3xl font-bold text-slate-800">{{ number_format($stats['total_roles']) }}</div>
                </div>
            </div>

            <!-- License Mappings -->
            <div class="bg-white border border-slate-200 rounded-lg shadow-sm">
                <div class="px-5 py-4">
                    <h2 class="text-sm font-medium text-slate-600 mb-2">{{ __('admin.role_mappings.license_mappings') }}</h2>
                    <div class="text-3xl font-bold text-slate-800">{{ number_format($stats['license_mappings']) }}</div>
                    <div class="text-sm text-slate-500 mt-1">{{ __('admin.role_mappings.active_licenses', ['count' => $stats['active_licenses']]) }}</div>
                </div>
            </div>

            <!-- Certification Mappings -->
            <div class="bg-white border border-slate-200 rounded-lg shadow-sm">
                <div class="px-5 py-4">
                    <h2 class="text-sm font-medium text-slate-600 mb-2">{{ __('admin.role_mappings.certification_mappings') }}</h2>
                    <div class="text-3xl font-bold text-slate-800">{{ number_format($stats['certification_mappings']) }}</div>
                    <div class="text-sm text-slate-500 mt-1">{{ __('admin.role_mappings.active_certifications', ['count' => $stats['active_certifications']]) }}</div>
                </div>
            </div>

            <!-- Federation Mappings -->
            <div class="bg-white border border-slate-200 rounded-lg shadow-sm">
                <div class="px-5 py-4">
                    <h2 class="text-sm font-medium text-slate-600 mb-2">{{ __('admin.role_mappings.federation_mappings') }}</h2>
                    <div class="text-3xl font-bold text-slate-800">{{ number_format($stats['federation_mappings']) }}</div>
                </div>
            </div>
        </div>

        <!-- Management Links -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- License Roles -->
            <div class="bg-white border border-slate-200 rounded-lg shadow-sm">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <svg class="w-8 h-8 text-indigo-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.829 2M9 14a3.001 3.001 0 00-2.829 2M15 11h3m-3 4h2"></path>
                        </svg>
                        <h3 class="text-lg font-semibold text-slate-800">{{ __('admin.role_mappings.manage_license_roles') }}</h3>
                    </div>
                    <p class="text-slate-600 mb-4">{{ __('admin.role_mappings.license_roles_description') }}</p>
                    <a href="{{ route('admin.role-mappings.licenses.index') }}" class="btn btn-primary w-full">
                        <span>{{ __('admin.role_mappings.manage') }}</span>
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Certification Roles -->
            <div class="bg-white border border-slate-200 rounded-lg shadow-sm">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <svg class="w-8 h-8 text-indigo-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                        </svg>
                        <h3 class="text-lg font-semibold text-slate-800">{{ __('admin.role_mappings.manage_certification_roles') }}</h3>
                    </div>
                    <p class="text-slate-600 mb-4">{{ __('admin.role_mappings.certification_roles_description') }}</p>
                    <a href="{{ route('admin.role-mappings.certifications.index') }}" class="btn btn-primary w-full">
                        <span>{{ __('admin.role_mappings.manage') }}</span>
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Federation Roles -->
            <div class="bg-white border border-slate-200 rounded-lg shadow-sm">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <svg class="w-8 h-8 text-indigo-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h3 class="text-lg font-semibold text-slate-800">{{ __('admin.role_mappings.manage_federation_roles') }}</h3>
                    </div>
                    <p class="text-slate-600 mb-4">{{ __('admin.role_mappings.federation_roles_description') }}</p>
                    <a href="{{ route('admin.role-mappings.federations.index') }}" class="btn btn-primary w-full">
                        <span>{{ __('admin.role_mappings.manage') }}</span>
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        <!-- Info Alert -->
        <div class="mt-8">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">{{ __('admin.role_mappings.sync_info') }}</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-layout>