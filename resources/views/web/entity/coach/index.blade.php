@section('title', __('coaches.title'))
<x-layout>
    <div x-data="{ showInviteModal: false, activeTab: 'active' }"
         @coach-invited.window="showInviteModal = false; setTimeout(() => window.location.reload(), 500)"
    >

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-6">
            <!-- Left: Title with count badge -->
            <div class="mb-4 sm:mb-0">
                <div class="flex items-center gap-3">
                    <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">{{ __('coaches.title') }}</h1>
                    @if($coaches->count() > 0)
                        <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300">
                            {{ $coaches->count() }}
                        </span>
                    @endif
                </div>
                <p class="text-slate-600 dark:text-slate-400 mt-1 text-sm">
                    {{ __('coaches.subtitle') }}
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
                        <span>{{ __('coaches.invite_new') }}</span>
                    </button>
                @endif
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <!-- Total Coaches -->
            <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-2 sm:p-3 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3 sm:ml-4">
                        <p class="text-xs sm:text-sm font-medium text-slate-600 dark:text-slate-400">{{ __('coaches.total_coaches') }}</p>
                        <p class="text-xl sm:text-2xl font-semibold text-slate-900 dark:text-white">{{ $coaches->count() }}</p>
                    </div>
                </div>
            </div>

            <!-- Active Coaches -->
            <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-2 sm:p-3 bg-green-100 dark:bg-green-900/30 rounded-lg">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3 sm:ml-4">
                        <p class="text-xs sm:text-sm font-medium text-slate-600 dark:text-slate-400">{{ __('coaches.active') }}</p>
                        <p class="text-xl sm:text-2xl font-semibold text-slate-900 dark:text-white">{{ $coaches->count() }}</p>
                    </div>
                </div>
            </div>

            <!-- Pending Invitations -->
            <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-2 sm:p-3 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3 sm:ml-4">
                        <p class="text-xs sm:text-sm font-medium text-slate-600 dark:text-slate-400">{{ __('coaches.pending') }}</p>
                        <p class="text-xl sm:text-2xl font-semibold text-slate-900 dark:text-white">{{ $pendingInvitations->count() }}</p>
                    </div>
                </div>
            </div>

            <!-- Available Licenses -->
            <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-2 sm:p-3 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                        </svg>
                    </div>
                    <div class="ml-3 sm:ml-4">
                        <p class="text-xs sm:text-sm font-medium text-slate-600 dark:text-slate-400">{{ __('coaches.sports_available') }}</p>
                        <p class="text-xl sm:text-2xl font-semibold text-slate-900 dark:text-white">{{ $sportsWithLicenses->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- License Status Alert -->
        @if($sportsWithLicenses->isEmpty() && isset($sportsWithPendingLicenses) && $sportsWithPendingLicenses->isNotEmpty())
            <div class="bg-amber-50 dark:bg-amber-900/20 border-l-4 border-amber-400 dark:border-amber-500 p-4 mb-6 rounded-r-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-amber-400 dark:text-amber-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-amber-800 dark:text-amber-200">
                            {{ __('licenses.entity_has_pending_licenses') }}
                        </h3>
                        <div class="mt-2 text-sm text-amber-700 dark:text-amber-300">
                            <p>{{ __('licenses.pending_licenses_for_sports', ['sports' => $sportsWithPendingLicenses->pluck('name')->implode(', ')]) }}</p>
                            <p class="mt-1">{{ __('licenses.invitations_available_after_payment') }}</p>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('entity.license-attributed.index') }}" class="text-sm font-medium text-amber-800 dark:text-amber-200 hover:text-amber-900 dark:hover:text-amber-100">
                                {{ __('licenses.complete_payment') }} &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Main Card Container with Tabs -->
        <div class="bg-white dark:bg-slate-800 shadow-sm rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
            <!-- Tabs Navigation -->
            <div class="border-b border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50">
                <nav class="flex px-2 sm:px-4" aria-label="Tabs">
                    <button
                        @click="activeTab = 'active'"
                        :class="activeTab === 'active'
                            ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400 bg-white dark:bg-slate-800'
                            : 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-slate-300 dark:hover:bg-slate-700/50'"
                        class="flex-1 sm:flex-none py-3 px-2 sm:px-4 border-b-2 -mb-px text-sm font-medium transition-all duration-200 rounded-t-lg flex items-center justify-center sm:justify-start gap-1.5 sm:gap-2"
                    >
                        <span class="hidden sm:inline">{{ __('coaches.active_coaches') }}</span>
                        <span class="sm:hidden text-xs">{{ __('coaches.active') }}</span>
                        @if($coaches->count() > 0)
                            <span
                                :class="activeTab === 'active' ? 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-300' : 'bg-slate-200 text-slate-600 dark:bg-slate-600 dark:text-slate-300'"
                                class="py-0.5 px-1.5 sm:px-2 rounded-full text-xs font-semibold transition-colors"
                            >
                                {{ $coaches->count() }}
                            </span>
                        @endif
                    </button>
                    <button
                        @click="activeTab = 'pending'"
                        :class="activeTab === 'pending'
                            ? 'border-amber-500 text-amber-600 dark:text-amber-400 bg-white dark:bg-slate-800'
                            : 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-slate-300 dark:hover:bg-slate-700/50'"
                        class="flex-1 sm:flex-none py-3 px-2 sm:px-4 border-b-2 -mb-px text-sm font-medium transition-all duration-200 rounded-t-lg flex items-center justify-center sm:justify-start gap-1.5 sm:gap-2"
                    >
                        <span class="hidden sm:inline">{{ __('coaches.pending_invitations') }}</span>
                        <span class="sm:hidden text-xs">{{ __('coaches.pending') }}</span>
                        @if($pendingInvitations->count() > 0)
                            <span
                                :class="activeTab === 'pending' ? 'bg-amber-100 text-amber-600 dark:bg-amber-900/50 dark:text-amber-300' : 'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400'"
                                class="py-0.5 px-1.5 sm:px-2 rounded-full text-xs font-semibold transition-colors"
                            >
                                {{ $pendingInvitations->count() }}
                            </span>
                        @endif
                    </button>
                    <button
                        @click="activeTab = 'history'"
                        :class="activeTab === 'history'
                            ? 'border-slate-500 text-slate-600 dark:text-slate-400 bg-white dark:bg-slate-800'
                            : 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-slate-300 dark:hover:bg-slate-700/50'"
                        class="flex-1 sm:flex-none py-3 px-2 sm:px-4 border-b-2 -mb-px text-sm font-medium transition-all duration-200 rounded-t-lg flex items-center justify-center sm:justify-start gap-1.5 sm:gap-2"
                    >
                        <span class="hidden sm:inline">{{ __('coaches.rejected') }}</span>
                        <span class="sm:hidden text-xs">{{ __('coaches.rejected') }}</span>
                        @if($historyInvitations->count() > 0)
                            <span
                                :class="activeTab === 'history' ? 'bg-slate-200 text-slate-600 dark:bg-slate-600 dark:text-slate-300' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400'"
                                class="py-0.5 px-1.5 sm:px-2 rounded-full text-xs font-semibold transition-colors"
                            >
                                {{ $historyInvitations->count() }}
                            </span>
                        @endif
                    </button>
                </nav>
            </div>

            <!-- Tab Content -->
            <div class="p-3 sm:p-5">
                <!-- Active Coaches Tab -->
                <div x-show="activeTab === 'active'" x-cloak>
                    @if($coaches->count() > 0)
                        <div class="overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-600">
                            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-600">
                                <thead class="bg-slate-50 dark:bg-slate-700/50">
                                    <tr>
                                        <th scope="col" class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                            {{ __('coaches.coach') }}
                                        </th>
                                        <th scope="col" class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider hidden sm:table-cell">
                                            {{ __('coaches.modality') }}
                                        </th>
                                        <th scope="col" class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider hidden md:table-cell">
                                            {{ __('coaches.joined') }}
                                        </th>
                                        <th scope="col" class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                            {{ __('coaches.license_status') }}
                                        </th>
                                        <th scope="col" class="relative px-4 sm:px-6 py-3">
                                            <span class="sr-only">{{ __('main.actions') }}</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-600">
                                    @foreach($coaches as $coach)
                                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-9 w-9 sm:h-10 sm:w-10">
                                                        <div class="h-9 w-9 sm:h-10 sm:w-10 rounded-full bg-gradient-to-br from-teal-500 to-cyan-600 flex items-center justify-center shadow-sm">
                                                            <span class="text-white font-medium text-xs sm:text-sm">
                                                                {{ substr($coach->individual?->name ?? '', 0, 1) }}{{ substr($coach->individual?->surname ?? '', 0, 1) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="ml-3 sm:ml-4">
                                                        <div class="text-sm font-medium text-slate-900 dark:text-white">
                                                            {{ $coach->individual?->name }} {{ $coach->individual?->surname }}
                                                        </div>
                                                        <div class="text-xs text-slate-500 dark:text-slate-400 font-mono">
                                                            {{ $coach->individual?->member_code }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap hidden sm:table-cell">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-teal-100 text-teal-800 dark:bg-teal-900/30 dark:text-teal-300">
                                                    {{ __($coach->sport?->name ?? 'coaches.coach_general') }}
                                                </span>
                                            </td>
                                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400 hidden md:table-cell">
                                                {{ $coach->created_at->format('d M Y') }}
                                            </td>
                                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                                                @php
                                                    $licenseColor = $coach->getLicenseStatusColor();
                                                    $colorClasses = match($licenseColor) {
                                                        'active-state' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                                                        'pending-state', 'pending', '#F59E0B' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                                                        'canceled-state' => 'bg-rose-50 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400',
                                                        default => 'bg-slate-50 text-slate-700 dark:bg-slate-700 dark:text-slate-300',
                                                    };
                                                    $sportLicense = $coach->getSportLicense();
                                                @endphp
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $colorClasses }}">
                                                    {{ $coach->getLicenseStatusName() }}
                                                </span>
                                                @if($sportLicense && $sportLicense->current_term_ends_at)
                                                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                                        {{ __('coaches.license_expiry') }}: {{ \Carbon\Carbon::parse($sportLicense->current_term_ends_at)->format('d/m/Y') }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex items-center justify-end gap-3">
                                                    <a href="{{ route(Request::segment(1).'.individual.show', $coach->individual?->id) }}"
                                                       class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                                        {{ __('main.view') }}
                                                    </a>
                                                    <form action="{{ route('entity.coach.delete', $coach->id) }}"
                                                          method="POST"
                                                          class="inline"
                                                          onsubmit="return confirm('{{ __('coaches.confirm_remove_coach') }}')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                                            {{ __('coaches.remove') }}
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                    @else
                        <div class="text-center py-8 sm:py-12">
                            <div class="inline-flex items-center justify-center w-12 sm:w-14 h-12 sm:h-14 rounded-full bg-slate-100 dark:bg-slate-700 mb-3 sm:mb-4">
                                <svg class="w-6 sm:w-7 h-6 sm:h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-sm sm:text-base font-semibold text-slate-900 dark:text-white mb-1">{{ __('coaches.no_coaches') }}</h3>
                            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">{{ __('coaches.start_by_inviting') }}</p>
                            @if($sportsWithLicenses->isNotEmpty())
                                <div class="mt-6">
                                    <button @click="showInviteModal = true" class="btn btn-primary">
                                        {{ __('coaches.invite_first_coach') }}
                                    </button>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Pending Invitations Tab -->
                <div x-show="activeTab === 'pending'" x-cloak>
                    @if($pendingInvitations && $pendingInvitations->count() > 0)
                        <div class="overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-600">
                            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-600">
                                <thead class="bg-slate-50 dark:bg-slate-700/50">
                                    <tr>
                                        <th scope="col" class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                            {{ __('coaches.coach') }}
                                        </th>
                                        <th scope="col" class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider hidden sm:table-cell">
                                            {{ __('coaches.invitation_date') }}
                                        </th>
                                        <th scope="col" class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                            {{ __('coaches.status') }}
                                        </th>
                                        <th scope="col" class="relative px-4 sm:px-6 py-3">
                                            <span class="sr-only">{{ __('main.actions') }}</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-600">
                                    @foreach($pendingInvitations as $invitation)
                                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-9 w-9 sm:h-10 sm:w-10">
                                                        <div class="h-9 w-9 sm:h-10 sm:w-10 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                                                            <span class="text-amber-600 dark:text-amber-400 font-medium text-xs sm:text-sm">
                                                                {{ substr($invitation->individual?->name ?? '', 0, 1) }}{{ substr($invitation->individual?->surname ?? '', 0, 1) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="ml-3 sm:ml-4">
                                                        <div class="text-sm font-medium text-slate-900 dark:text-white">
                                                            {{ $invitation->individual?->name }} {{ $invitation->individual?->surname }}
                                                        </div>
                                                        <div class="text-xs text-slate-500 dark:text-slate-400 font-mono">
                                                            {{ $invitation->individual?->member_code }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400 hidden sm:table-cell">
                                                {{ $invitation->created_at->format('d M Y, H:i') }}
                                            </td>
                                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">
                                                    {{ __('coaches.waiting_response') }}
                                                </span>
                                            </td>
                                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex items-center justify-end gap-3">
                                                    <button class="text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200">
                                                        {{ __('coaches.resend') }}
                                                    </button>
                                                    <form action="{{ route('entity.coach.cancel-invitation', $invitation->id) }}" method="POST" class="inline"
                                                          onsubmit="return confirm('{{ __('coaches.confirm_cancel_invitation') }}')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                                            {{ __('coaches.cancel') }}
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8 sm:py-12">
                            <div class="inline-flex items-center justify-center w-12 sm:w-14 h-12 sm:h-14 rounded-full bg-emerald-50 dark:bg-emerald-900/20 mb-3 sm:mb-4">
                                <svg class="w-6 sm:w-7 h-6 sm:h-7 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-sm sm:text-base font-semibold text-slate-900 dark:text-white mb-1">{{ __('coaches.no_pending_invitations') }}</h3>
                            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">{{ __('coaches.all_invitations_accepted') }}</p>
                        </div>
                    @endif
                </div>

                <!-- History Tab -->
                <div x-show="activeTab === 'history'" x-cloak>
                    @if($historyInvitations && $historyInvitations->count() > 0)
                        <div class="overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-600">
                            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-600">
                                <thead class="bg-slate-50 dark:bg-slate-700/50">
                                    <tr>
                                        <th scope="col" class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                            {{ __('coaches.coach') }}
                                        </th>
                                        <th scope="col" class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider hidden sm:table-cell">
                                            {{ __('coaches.modality') }}
                                        </th>
                                        <th scope="col" class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider hidden md:table-cell">
                                            {{ __('coaches.invitation_date') }}
                                        </th>
                                        <th scope="col" class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                            {{ __('coaches.status') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-600">
                                    @foreach($historyInvitations as $invitation)
                                        @php
                                            $isRejected = $invitation->status_class === \Domain\Entities\States\RejectedEntityProfessionalRoleState::class;
                                            $statusColor = $isRejected
                                                ? 'bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-400'
                                                : 'bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300';
                                            $statusLabel = $isRejected ? __('entities.status_rejected') : __('entities.status_canceled');
                                        @endphp
                                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-9 w-9 sm:h-10 sm:w-10">
                                                        <div class="h-9 w-9 sm:h-10 sm:w-10 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center">
                                                            <span class="text-slate-600 dark:text-slate-400 font-medium text-xs sm:text-sm">
                                                                {{ substr($invitation->individual?->name ?? '', 0, 1) }}{{ substr($invitation->individual?->surname ?? '', 0, 1) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="ml-3 sm:ml-4">
                                                        <div class="text-sm font-medium text-slate-900 dark:text-white">
                                                            {{ $invitation->individual?->name }} {{ $invitation->individual?->surname }}
                                                        </div>
                                                        <div class="text-xs text-slate-500 dark:text-slate-400 font-mono">
                                                            {{ $invitation->individual?->member_code }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap hidden sm:table-cell">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-teal-100 text-teal-800 dark:bg-teal-900/30 dark:text-teal-300">
                                                    {{ __($invitation->sport?->name ?? 'coaches.coach_general') }}
                                                </span>
                                            </td>
                                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400 hidden md:table-cell">
                                                {{ $invitation->created_at->format('d M Y, H:i') }}
                                            </td>
                                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColor }}">
                                                    {{ $statusLabel }}
                                                </span>
                                                <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                                    {{ $invitation->updated_at->format('d M Y, H:i') }}
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8 sm:py-12">
                            <div class="inline-flex items-center justify-center w-12 sm:w-14 h-12 sm:h-14 rounded-full bg-slate-100 dark:bg-slate-700 mb-3 sm:mb-4">
                                <svg class="w-6 sm:w-7 h-6 sm:h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-sm sm:text-base font-semibold text-slate-900 dark:text-white mb-1">{{ __('coaches.no_rejected') }}</h3>
                            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">{{ __('coaches.no_rejected_desc') }}</p>
                        </div>
                    @endif
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
                         class="fixed inset-0 bg-slate-500 dark:bg-slate-900 bg-opacity-75 dark:bg-opacity-80 transition-opacity"></div>

                    <!-- Modal Content -->
                    <div x-show="showInviteModal"
                         x-transition:enter="ease-out duration-300"
                         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave="ease-in duration-200"
                         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         class="relative mx-auto bg-white dark:bg-slate-800 rounded-lg shadow-xl transform transition-all sm:max-w-4xl sm:w-full">

                        <!-- Modal Header -->
                        <div class="bg-slate-50 dark:bg-slate-700/50 px-6 py-4 border-b border-slate-200 dark:border-slate-600 rounded-t-lg">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
                                    {{ __('coaches.invite_new_coaches') }}
                                </h3>
                                <button @click="showInviteModal = false"
                                        class="text-slate-400 hover:text-slate-500 dark:hover:text-slate-300 focus:outline-none">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Modal Body -->
                        <div class="px-6 py-4">
                            <!-- Info Alert -->
                            <div class="bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-400 dark:border-blue-500 p-4 mb-6 rounded-r-lg">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-blue-400 dark:text-blue-500" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-blue-700 dark:text-blue-300">
                                            <strong>{{ __('coaches.requirements') }}:</strong>
                                            {{ __('coaches.requirements_desc') }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Livewire Component -->
                            <livewire:manage-entity-coaches :professionalRoles="$coachRoles" :sportsWithLicenses="$sportsWithLicenses" />
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </div>
</x-layout>
