@section('title', __('athletes.title'))
<x-layout>
    <div x-data="{ showInviteModal: false, pendingCount: {{ $pendingInvitations->count() }} }"
         @pending-invitations-updated.window="pendingCount = $event.detail.count"
         @athlete-invited.window="showInviteModal = false; setTimeout(() => window.location.reload(), 500)">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-6">
            <!-- Left: Title with count badge -->
            <div class="mb-4 sm:mb-0">
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">{{ __('athletes.title') }}</h1>
                    @if($athletes->total() > 0)
                        <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300">
                            {{ $athletes->total() }}
                        </span>
                    @endif
                </div>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    {{ __('athletes.subtitle') }}
                </p>
            </div>

            <!-- Right: Actions -->
            <div class="flex items-center gap-3">
                @if($sportsWithLicenses->isNotEmpty())
                    <button
                        @click="showInviteModal = true"
                        class="btn btn-primary flex items-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                        </svg>
                        <span>{{ __('athletes.invite_new') }}</span>
                    </button>
                @endif
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <!-- Total Athletes -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 bg-indigo-50 dark:bg-indigo-900/30 rounded-lg">
                        <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ __('athletes.total_athletes') }}</p>
                        <p class="text-2xl font-semibold text-slate-900 dark:text-white">{{ $athletes->total() }}</p>
                    </div>
                </div>
            </div>

            <!-- Active Athletes -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 bg-emerald-50 dark:bg-emerald-900/30 rounded-lg">
                        <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ __('athletes.active') }}</p>
                        <p class="text-2xl font-semibold text-slate-900 dark:text-white">{{ $athletes->where('status_class', 'Domain\\Entities\\States\\ActiveEntityProfessionalRoleState')->count() }}</p>
                    </div>
                </div>
            </div>

            <!-- Pending Invitations -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 bg-amber-50 dark:bg-amber-900/30 rounded-lg">
                        <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ __('athletes.pending') }}</p>
                        <p class="text-2xl font-semibold text-slate-900 dark:text-white" x-text="pendingCount"></p>
                    </div>
                </div>
            </div>

            <!-- Available Licenses -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 bg-purple-50 dark:bg-purple-900/30 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ __('athletes.sports_available') }}</p>
                        <p class="text-2xl font-semibold text-slate-900 dark:text-white">{{ $sportsWithLicenses->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- License Status Alert -->
        @if($sportsWithLicenses->isEmpty() && isset($sportsWithPendingLicenses) && $sportsWithPendingLicenses->isNotEmpty())
            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-amber-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-amber-800 dark:text-amber-300">
                            {{ __('licenses.entity_has_pending_licenses') }}
                        </h3>
                        <div class="mt-2 text-sm text-amber-700 dark:text-amber-400">
                            <p>{{ __('licenses.pending_licenses_for_sports', ['sports' => $sportsWithPendingLicenses->pluck('name')->implode(', ')]) }}</p>
                            <p class="mt-1">{{ __('licenses.invitations_available_after_payment') }}</p>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('entity.license-attributed.index') }}" class="text-sm font-medium text-amber-800 dark:text-amber-300 hover:text-amber-900 dark:hover:text-amber-200">
                                {{ __('licenses.complete_payment') }} &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Main Card Container with Tabs -->
        <div class="bg-white dark:bg-slate-800 shadow-sm rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden" x-data="{ activeTab: 'active' }">
            <!-- Tab Navigation -->
            <div class="border-b border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50">
                <nav class="flex gap-x-1 px-4" aria-label="Tabs">
                    <button
                        @click="activeTab = 'active'"
                        :class="activeTab === 'active'
                            ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400 bg-white dark:bg-slate-800'
                            : 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-slate-300 dark:hover:bg-slate-700/50'"
                        class="group relative py-3 px-4 border-b-2 -mb-px text-sm font-medium transition-all duration-200 rounded-t-lg flex items-center gap-2"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <span>{{ __('athletes.active_athletes') }}</span>
                        @if($athletes->where('status_class', 'Domain\\Entities\\States\\ActiveEntityProfessionalRoleState')->count() > 0)
                            <span
                                :class="activeTab === 'active' ? 'bg-indigo-100 text-indigo-600 dark:bg-indigo-800 dark:text-indigo-300' : 'bg-slate-200 text-slate-600 dark:bg-slate-600 dark:text-slate-300'"
                                class="py-0.5 px-2 rounded-full text-xs font-semibold transition-colors"
                            >
                                {{ $athletes->where('status_class', 'Domain\\Entities\\States\\ActiveEntityProfessionalRoleState')->count() }}
                            </span>
                        @endif
                    </button>
                    <button
                        @click="activeTab = 'pending'"
                        :class="activeTab === 'pending'
                            ? 'border-amber-500 text-amber-600 dark:text-amber-400 bg-white dark:bg-slate-800'
                            : 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-slate-300 dark:hover:bg-slate-700/50'"
                        class="group relative py-3 px-4 border-b-2 -mb-px text-sm font-medium transition-all duration-200 rounded-t-lg flex items-center gap-2"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>{{ __('athletes.pending_invitations') }}</span>
                        <span
                            x-show="pendingCount > 0"
                            :class="activeTab === 'pending' ? 'bg-amber-100 text-amber-600 dark:bg-amber-800 dark:text-amber-300' : 'bg-amber-100 text-amber-600 dark:bg-amber-900/50 dark:text-amber-400'"
                            class="py-0.5 px-2 rounded-full text-xs font-semibold transition-colors animate-pulse"
                            x-text="pendingCount"
                        ></span>
                    </button>
                </nav>
            </div>

            <!-- Tab Content -->
            <div class="p-5">
                <!-- Active Athletes Tab -->
                <div x-show="activeTab === 'active'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-cloak>
                    @if($athletes->count() > 0)
                        <div class="overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-600">
                            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-600">
                                <thead>
                                    <tr class="bg-slate-50 dark:bg-slate-700/50">
                                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                            {{ __('athletes.athlete') }}
                                        </th>
                                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                            {{ __('athletes.sport') }}
                                        </th>
                                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                            {{ __('athletes.joined') }}
                                        </th>
                                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                            {{ __('athletes.license_status') }}
                                        </th>
                                        <th scope="col" class="relative px-5 py-3">
                                            <span class="sr-only">{{ __('main.actions') }}</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-600">
                                    @foreach($athletes as $athlete)
                                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                            <td class="px-5 py-4 whitespace-nowrap">
                                                <div class="flex items-center gap-3">
                                                    <div class="flex-shrink-0 w-9 h-9 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-sm">
                                                        <span class="text-white font-bold text-xs">
                                                            {{ substr($athlete->individual?->name ?? '', 0, 1) }}{{ substr($athlete->individual?->surname ?? '', 0, 1) }}
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <div class="text-sm font-medium text-slate-900 dark:text-white">
                                                            {{ $athlete->individual?->name }} {{ $athlete->individual?->surname }}
                                                        </div>
                                                        <div class="text-xs text-slate-500 dark:text-slate-400 font-mono">
                                                            {{ $athlete->individual?->member_code }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-5 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">
                                                    {{ $athlete->sport?->translated_name ?? $athlete->sport_name }}
                                                </span>
                                            </td>
                                            <td class="px-5 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">
                                                {{ $athlete->created_at->format('d M Y') }}
                                            </td>
                                            <td class="px-5 py-4 whitespace-nowrap">
                                                @php
                                                    $licenseColor = $athlete->getLicenseStatusColor();
                                                    $colorClasses = match($licenseColor) {
                                                        'active-state' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                                                        'pending-state', 'pending', '#F59E0B' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                                                        'canceled-state' => 'bg-rose-50 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400',
                                                        default => 'bg-slate-50 text-slate-700 dark:bg-slate-700 dark:text-slate-300',
                                                    };
                                                    $sportLicense = $athlete->getSportLicense();
                                                @endphp
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $colorClasses }}">
                                                    {{ $athlete->getLicenseStatusName() }}
                                                </span>
                                                @if($sportLicense && $sportLicense->current_term_ends_at)
                                                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                                        {{ __('athletes.license_expiry') }}: {{ \Carbon\Carbon::parse($sportLicense->current_term_ends_at)->format('d/m/Y') }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="px-5 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex items-center justify-end gap-2">
                                                    <x-dynamic-table-buttons
                                                        type="show"
                                                        :route="route(Request::segment(1).'.individual.show', $athlete->individual?->id)"
                                                    />
                                                    <x-dynamic-table-buttons
                                                        type="disassociate"
                                                        :route="route('entity.athlete.delete', $athlete->id)"
                                                        method="DELETE"
                                                        :confirmText="__('athletes.confirm_disassociate')"
                                                    />
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if($athletes->hasPages())
                            <div class="mt-5">
                                {{ $athletes->links() }}
                            </div>
                        @endif
                    @else
                        <!-- Empty State -->
                        <div class="text-center py-12">
                            <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-slate-100 dark:bg-slate-700 mb-4">
                                <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-base font-semibold text-slate-900 dark:text-white mb-1">{{ __('athletes.no_athletes') }}</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('athletes.start_by_inviting') }}</p>
                            @if($sportsWithLicenses->isNotEmpty())
                                <div class="mt-4">
                                    <button @click="showInviteModal = true" class="btn btn-primary">
                                        {{ __('athletes.invite_first_athlete') }}
                                    </button>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Pending Invitations Tab - Livewire component with auto-refresh -->
                <div x-show="activeTab === 'pending'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-cloak>
                    <livewire:pending-athlete-invitations />
                </div>
            </div>
        </div>

        <!-- Invite Modal -->
        @if($sportsWithLicenses->isNotEmpty())
            <div x-show="showInviteModal"
                 x-cloak
                 @keydown.escape.window="showInviteModal = false"
                 class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen px-4 sm:pl-72">
                    <!-- Overlay -->
                    <div x-show="showInviteModal"
                         @click="showInviteModal = false"
                         x-transition:enter="ease-out duration-300"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         x-transition:leave="ease-in duration-200"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         class="fixed inset-0 bg-slate-900/50 dark:bg-slate-900/70 transition-opacity"></div>

                    <!-- Modal Content -->
                    <div x-show="showInviteModal"
                         x-transition:enter="ease-out duration-300"
                         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave="ease-in duration-200"
                         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         class="relative mx-auto bg-white dark:bg-slate-800 rounded-xl shadow-xl transform transition-all sm:max-w-4xl sm:w-full border border-slate-200 dark:border-slate-700">

                        <!-- Modal Header -->
                        <div class="bg-slate-50 dark:bg-slate-800/50 px-6 py-4 border-b border-slate-200 dark:border-slate-700 rounded-t-xl">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
                                    {{ __('athletes.invite_new_athletes') }}
                                </h3>
                                <button @click="showInviteModal = false"
                                        class="text-slate-400 hover:text-slate-500 dark:hover:text-slate-300 focus:outline-none transition-colors">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Modal Body -->
                        <div class="px-6 py-4">
                            <!-- Info Alert -->
                            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-6">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-blue-500" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-blue-700 dark:text-blue-300">
                                            <strong>{{ __('athletes.requirements') }}:</strong>
                                            {{ __('athletes.requirements_desc') }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Livewire Component -->
                            <livewire:manage-entity-athletes :sportsWithLicenses="$sportsWithLicenses" />
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </div>
</x-layout>
