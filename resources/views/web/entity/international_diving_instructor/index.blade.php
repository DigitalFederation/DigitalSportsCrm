@section('title', __('international_diving.instructor_leader_associated_entities'))
<x-layout>
    <div x-data="{ showInviteModal: false, pendingCount: {{ $pendingInvitations->count() }} }"
         @instructor-invited.window="showInviteModal = false; setTimeout(() => window.location.reload(), 500)">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-6">
            <!-- Left: Title with count badge -->
            <div class="mb-4 sm:mb-0">
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">{{ __('international_diving.international_diving_instructors_leaders') }}</h1>
                    @if($instructors->total() > 0)
                        <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300">
                            {{ $instructors->total() }}
                        </span>
                    @endif
                </div>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    {{ __('international_diving.associated_to') }} <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $entity->name }}</span>
                </p>
            </div>

            <!-- Right: Actions -->
            <div class="flex items-center gap-3">
                <button
                    @click="showInviteModal = true"
                    class="btn btn-primary flex items-center gap-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                    </svg>
                    <span>{{ __('international_diving.invite_instructor') }}</span>
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <!-- Total Instructors -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 bg-indigo-50 dark:bg-indigo-900/30 rounded-lg">
                        <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ __('international_diving.total_instructors') }}</p>
                        <p class="text-2xl font-semibold text-slate-900 dark:text-white">{{ $instructors->total() }}</p>
                    </div>
                </div>
            </div>

            <!-- Active Instructors -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 bg-emerald-50 dark:bg-emerald-900/30 rounded-lg">
                        <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ __('international_diving.active_instructors') }}</p>
                        <p class="text-2xl font-semibold text-slate-900 dark:text-white">{{ $instructors->where('status_class', 'Domain\Entities\States\ActiveEntityProfessionalRoleState')->count() }}</p>
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
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ __('international_diving.pending_invitations') }}</p>
                        <p class="text-2xl font-semibold text-slate-900 dark:text-white" x-text="pendingCount"></p>
                    </div>
                </div>
            </div>
        </div>

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
                        <span>{{ __('international_diving.associated_instructors') }}</span>
                        @if($instructors->total() > 0)
                            <span
                                :class="activeTab === 'active' ? 'bg-indigo-100 text-indigo-600 dark:bg-indigo-800 dark:text-indigo-300' : 'bg-slate-200 text-slate-600 dark:bg-slate-600 dark:text-slate-300'"
                                class="py-0.5 px-2 rounded-full text-xs font-semibold transition-colors"
                            >
                                {{ $instructors->total() }}
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
                        <span>{{ __('international_diving.pending_invitations_tab') }}</span>
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
                <!-- Associated Instructors Tab -->
                <div x-show="activeTab === 'active'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-cloak>
                    @if($instructors->count() > 0)
                        <div class="overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-600">
                            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-600">
                                <thead>
                                    <tr class="bg-slate-50 dark:bg-slate-700/50">
                                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                            {{ __('international_diving.instructor') }}
                                        </th>
                                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                            {{ __('international_diving.role') }}
                                        </th>
                                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                            {{ __('international_diving.joined') }}
                                        </th>
                                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                            {{ __('international_diving.status') }}
                                        </th>
                                        <th scope="col" class="relative px-5 py-3">
                                            <span class="sr-only">{{ __('main.actions') }}</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-600">
                                    @foreach($instructors as $instructor)
                                        @php
                                            $statusClassBase = class_basename($instructor->status_class);
                                            $colorClasses = match($statusClassBase) {
                                                'ActiveEntityProfessionalRoleState' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                                                'PendingEntityProfessionalRoleState' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                                                'CanceledEntityProfessionalRoleState' => 'bg-rose-50 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400',
                                                default => 'bg-slate-50 text-slate-700 dark:bg-slate-700 dark:text-slate-300',
                                            };
                                            $statusText = \Illuminate\Support\Str::headline(str_replace('EntityProfessionalRoleState', '', $statusClassBase));
                                        @endphp
                                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                            <td class="px-5 py-4 whitespace-nowrap">
                                                <div class="flex items-center gap-3">
                                                    <div class="flex-shrink-0 w-9 h-9 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-sm">
                                                        <span class="text-white font-bold text-xs">
                                                            {{ substr($instructor->individual?->name ?? '', 0, 1) }}{{ substr($instructor->individual?->surname ?? '', 0, 1) }}
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <div class="text-sm font-medium text-slate-900 dark:text-white">
                                                            {{ $instructor->individual?->name }} {{ $instructor->individual?->surname }}
                                                        </div>
                                                        <div class="text-xs text-slate-500 dark:text-slate-400 font-mono">
                                                            {{ $instructor->individual?->member_code }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-5 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">
                                                    {{ $instructor->professionalRole?->name ?? '-' }}
                                                </span>
                                            </td>
                                            <td class="px-5 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">
                                                {{ $instructor->created_at->format('d M Y') }}
                                            </td>
                                            <td class="px-5 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $colorClasses }}">
                                                    {{ $statusText }}
                                                </span>
                                            </td>
                                            <td class="px-5 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex items-center justify-end gap-2">
                                                    <x-dynamic-table-buttons
                                                        type="show"
                                                        :route="route(Request::segment(1).'.individual.show', $instructor->individual?->id)"
                                                    />
                                                    <button @click="$dispatch('open-deactivation-modal', { id: {{ $instructor->id }} })"
                                                            type="button"
                                                            class="p-1.5 rounded-lg text-slate-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition-colors"
                                                            title="{{ __('international_diving.manage') }}">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if($instructors->hasPages())
                            <div class="mt-5">
                                {{ $instructors->links() }}
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
                            <h3 class="text-base font-semibold text-slate-900 dark:text-white mb-1">{{ __('international_diving.no_instructors') }}</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('international_diving.start_by_inviting') }}</p>
                            <div class="mt-4">
                                <button @click="showInviteModal = true" class="btn btn-primary">
                                    {{ __('international_diving.invite_first_instructor') }}
                                </button>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Pending Invitations Tab -->
                <div x-show="activeTab === 'pending'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-cloak>
                    @if($pendingInvitations->count() > 0)
                        <div class="overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-600">
                            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-600">
                                <thead>
                                    <tr class="bg-slate-50 dark:bg-slate-700/50">
                                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                            {{ __('international_diving.instructor') }}
                                        </th>
                                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                            {{ __('international_diving.role') }}
                                        </th>
                                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                            {{ __('international_diving.invitation_sent') }}
                                        </th>
                                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                            {{ __('international_diving.expires') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-600">
                                    @foreach($pendingInvitations as $invitation)
                                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                            <td class="px-5 py-4 whitespace-nowrap">
                                                <div class="flex items-center gap-3">
                                                    <div class="flex-shrink-0 w-9 h-9 rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-sm">
                                                        <span class="text-white font-bold text-xs">
                                                            {{ substr($invitation->individual?->name ?? '', 0, 1) }}{{ substr($invitation->individual?->surname ?? '', 0, 1) }}
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <div class="text-sm font-medium text-slate-900 dark:text-white">
                                                            {{ $invitation->individual?->name }} {{ $invitation->individual?->surname }}
                                                        </div>
                                                        <div class="text-xs text-slate-500 dark:text-slate-400 font-mono">
                                                            {{ $invitation->individual?->member_code }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-5 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">
                                                    {{ $invitation->professionalRole?->name ?? '-' }}
                                                </span>
                                            </td>
                                            <td class="px-5 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">
                                                {{ $invitation->created_at->format('d M Y') }}
                                            </td>
                                            <td class="px-5 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">
                                                @if($invitation->expires_at)
                                                    {{ \Carbon\Carbon::parse($invitation->expires_at)->format('d M Y') }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <!-- Empty State -->
                        <div class="text-center py-12">
                            <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-slate-100 dark:bg-slate-700 mb-4">
                                <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-base font-semibold text-slate-900 dark:text-white mb-1">{{ __('international_diving.no_pending_invitations') }}</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('international_diving.no_pending_invitations_desc') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Invite Modal - Teleported to body to avoid stacking context issues with Filament modals --}}
        <template x-teleport="body">
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

                <!-- Modal Content - No transform transitions to avoid creating a new stacking context -->
                <div x-show="showInviteModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="relative mx-auto bg-white dark:bg-slate-800 rounded-xl shadow-xl sm:max-w-4xl sm:w-full border border-slate-200 dark:border-slate-700">

                    <!-- Modal Header -->
                    <div class="bg-slate-50 dark:bg-slate-800/50 px-6 py-4 border-b border-slate-200 dark:border-slate-700 rounded-t-xl">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
                                {{ __('international_diving.invite_new_instructors') }}
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
                                        <strong>{{ __('international_diving.conditions') }}:</strong>
                                        {{ __('international_diving.condition_active_license') }}
                                        {{ __('international_diving.condition_active_certification') }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Livewire Component for Inviting -->
                        <livewire:manage-entity-instructors :professionalRoles="$professionalRoles" :showAssociatedSection="false" :showInviteSection="true" :requiresInternational="true" />
                    </div>
                </div>
            </div>
        </div>
        </template>
    </div>

    <!-- Include deactivation modals for each instructor -->
    @foreach ($instructors as $instructor)
        <x-professional-deactivation-modal
            :professional-id="$instructor->id"
            :professional-name="$instructor->individual->full_name ?? ($instructor->individual->name . ' ' . $instructor->individual->surname)"
            :action="route('entity.international-diving-instructor.remove', $instructor->id)" />
    @endforeach
</x-layout>
