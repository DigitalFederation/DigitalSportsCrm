@section('title', __('admin.license_details'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- International Header -->
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-6 flex items-center">
            <div>
                <h2 class="text-lg font-semibold text-blue-900 dark:text-blue-100">{{ __('admin.international_license') }}</h2>
                <p class="text-sm text-blue-700 dark:text-blue-300">{{ __('admin.official_international_license') }}</p>
            </div>
        </div>

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ $license->license_name }}</h1>
                <p class="text-slate-600 dark:text-slate-400">{{ __('admin.license_details_description') }}</p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('international.individual.licenses-attributed.index') }}"
                   class="btn btn-secondary">
                    ← {{ __('admin.back_to_licenses') }}
                </a>
            </div>
        </div>

        <!-- License Status Badge -->
        <div class="mb-6">
            <x-tables.badge :status="ucwords($license->stateName())"
                            :color="$license->stateColor()"
                            class="text-lg px-4 py-2" />
        </div>

        <!-- License Details -->
        <div class="bg-white dark:bg-slate-800 shadow rounded-lg overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700">
                <h3 class="text-lg font-medium text-slate-900 dark:text-slate-100">{{ __('admin.license_information') }}</h3>
            </div>

            <div class="px-6 py-5 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('admin.license_name') }}</label>
                        <p class="mt-1 text-sm text-slate-900 dark:text-slate-100">{{ $license->license_name }}</p>
                    </div>

                    @if($license->license && $license->license->committee)
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('admin.committee') }}</label>
                        <p class="mt-1 text-sm text-slate-900 dark:text-slate-100">{{ $license->license->committee->name }}</p>
                    </div>
                    @endif

                    @if($license->license && $license->license->sport)
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('admin.sport') }}</label>
                        <p class="mt-1 text-sm text-slate-900 dark:text-slate-100">{{ $license->license->sport->name }}</p>
                    </div>
                    @endif

                    @if($license->federation)
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('admin.federation') }}</label>
                        <p class="mt-1 text-sm text-slate-900 dark:text-slate-100">
                            {{ $license->federation->name }}
                            @if($license->federation->country)
                                ({{ $license->federation->country->name }})
                            @endif
                        </p>
                    </div>
                    @endif

                    @if($license->current_term_starts_at)
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('admin.start_date') }}</label>
                        <p class="mt-1 text-sm text-slate-900 dark:text-slate-100">{{ $license->current_term_starts_at->format('d/m/Y') }}</p>
                    </div>
                    @endif

                    @if($license->current_term_ends_at)
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('admin.expiry_date') }}</label>
                        <p class="mt-1 text-sm text-slate-900 dark:text-slate-100">{{ $license->current_term_ends_at->format('d/m/Y') }}</p>
                    </div>
                    @endif

                    @if($license->member_code)
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('admin.member_code') }}</label>
                        <p class="mt-1 text-sm text-slate-900 dark:text-slate-100">{{ $license->member_code }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

    </div>
</x-layout>
