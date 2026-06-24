@section('title', __('coaches.title'))
<x-layout>
    <div x-data="{
        activeTab: 'active'
    }">

        <!-- Page Header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-6">
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                    {{ __('coaches.title') }}
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    {{ __('coaches.manage_club_associations') }}
                </p>
            </div>
        </div>

        <!-- Main Card Container with Tabs -->
        <div class="bg-white dark:bg-slate-800 shadow-sm rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
            <!-- Tab Navigation -->
            <div class="border-b border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50">
                <nav class="flex gap-x-1 px-4" aria-label="Tabs">
                    <button
                        @click="activeTab = 'active'"
                        :class="activeTab === 'active'
                            ? 'border-teal-500 text-teal-600 dark:text-teal-400 bg-white dark:bg-slate-800'
                            : 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-slate-300 dark:hover:bg-slate-700/50'"
                        class="group relative py-3 px-4 border-b-2 -mb-px text-sm font-medium transition-all duration-200 rounded-t-lg flex items-center gap-2"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" />
                        </svg>
                        <span>{{ __('coaches.associated_entities') }}</span>
                        @if($activeCount > 0)
                            <span
                                :class="activeTab === 'active' ? 'bg-teal-100 text-teal-600 dark:bg-teal-800 dark:text-teal-300' : 'bg-slate-200 text-slate-600 dark:bg-slate-600 dark:text-slate-300'"
                                class="py-0.5 px-2 rounded-full text-xs font-semibold transition-colors"
                            >
                                {{ $activeCount }}
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                        </svg>
                        <span>{{ __('coaches.entity_invitations') }}</span>
                        @if($pendingCount > 0)
                            <span
                                :class="activeTab === 'pending' ? 'bg-amber-100 text-amber-600 dark:bg-amber-800 dark:text-amber-300' : 'bg-amber-100 text-amber-600 dark:bg-amber-900/50 dark:text-amber-400'"
                                class="py-0.5 px-2 rounded-full text-xs font-semibold transition-colors animate-pulse"
                            >
                                {{ $pendingCount }}
                            </span>
                        @endif
                    </button>

                    <button
                        @click="activeTab = 'history'"
                        :class="activeTab === 'history'
                            ? 'border-slate-500 text-slate-600 dark:text-slate-400 bg-white dark:bg-slate-800'
                            : 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-slate-300 dark:hover:bg-slate-700/50'"
                        class="group relative py-3 px-4 border-b-2 -mb-px text-sm font-medium transition-all duration-200 rounded-t-lg flex items-center gap-2"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ __('coaches.rejected') }}</span>
                        @if($historyCount > 0)
                            <span
                                :class="activeTab === 'history' ? 'bg-slate-200 text-slate-600 dark:bg-slate-600 dark:text-slate-300' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400'"
                                class="py-0.5 px-2 rounded-full text-xs font-semibold transition-colors"
                            >
                                {{ $historyCount }}
                            </span>
                        @endif
                    </button>
                </nav>
            </div>

            <!-- Tab Content -->
            <div class="p-5">
                <!-- Active Associations Tab -->
                <div
                    x-show="activeTab === 'active'"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-cloak
                >
                    @php
                        $activeInvites = $invites->filter(fn($invite) => $invite->isActive());
                    @endphp

                    @if($activeInvites->count() > 0)
                        <div class="overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-600">
                            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-600">
                                <thead>
                                    <tr class="bg-slate-50 dark:bg-slate-700/50">
                                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                            {{ __('events.entity') }}
                                        </th>
                                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                            {{ __('coaches.district') }}
                                        </th>
                                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                            {{ __('coaches.modality') }}
                                        </th>
                                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                            {{ __('coaches.joined') }}
                                        </th>
                                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                            {{ __('main.status') }}
                                        </th>
                                        <th scope="col" class="relative px-5 py-3">
                                            <span class="sr-only">{{ __('main.actions') }}</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 dark:divide-slate-600 bg-white dark:bg-slate-800">
                                    @foreach($activeInvites as $coach)
                                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                            <td class="px-5 py-4 whitespace-nowrap">
                                                <div class="flex items-center gap-3">
                                                    <div class="flex-shrink-0 w-9 h-9 rounded-lg bg-gradient-to-br from-teal-500 to-cyan-600 flex items-center justify-center shadow-sm">
                                                        <span class="text-white font-bold text-xs">
                                                            {{ strtoupper(substr($coach->entity->name, 0, 2)) }}
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <div class="text-sm font-medium text-slate-900 dark:text-white">
                                                            {{ $coach->entity->name }}
                                                        </div>
                                                        <div class="text-xs text-slate-500 dark:text-slate-400 font-mono">
                                                            {{ $coach->entity->member_code }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-5 py-4 whitespace-nowrap text-sm text-slate-600 dark:text-slate-300">
                                                {{ $coach->entity->district?->name ?? '-' }}
                                            </td>
                                            <td class="px-5 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-teal-50 text-teal-700 dark:bg-teal-900/30 dark:text-teal-300">
                                                    {{ __($coach->sport?->name ?? $coach->role_name) }}
                                                </span>
                                            </td>
                                            <td class="px-5 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">
                                                {{ \Carbon\Carbon::parse($coach->created_at)->format('d M Y') }}
                                            </td>
                                            <td class="px-5 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                    {{ __('coaches.active') }}
                                                </span>
                                            </td>
                                            <td class="px-5 py-4 whitespace-nowrap text-right">
                                                <form
                                                    action="{{ route('individual.coach.delete', $coach->id) }}"
                                                    method="POST"
                                                    class="inline"
                                                    onsubmit="return confirm('{{ __('coaches.confirm_leave_entity') }}')"
                                                >
                                                    @csrf
                                                    @method('DELETE')
                                                    <button
                                                        type="submit"
                                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-rose-600 hover:text-rose-700 dark:text-rose-400 dark:hover:text-rose-300 hover:bg-rose-50 dark:hover:bg-rose-900/20 rounded-md transition-colors"
                                                    >
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                                        </svg>
                                                        {{ __('coaches.leave_entity') }}
                                                    </button>
                                                </form>
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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <h3 class="text-base font-semibold text-slate-900 dark:text-white mb-1">
                                {{ __('coaches.no_active_associations') }}
                            </h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400 max-w-sm mx-auto">
                                {{ __('coaches.no_active_associations_desc') }}
                            </p>
                            @if($pendingCount > 0)
                                <button
                                    @click="activeTab = 'pending'"
                                    class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium rounded-lg transition-colors"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    {{ __('coaches.view_pending_invitations') }}
                                </button>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Pending Invitations Tab -->
                <div
                    x-show="activeTab === 'pending'"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-cloak
                >
                    @php
                        // Only show truly pending invitations (not rejected/canceled)
                        $pendingInvites = $invites->filter(
                            fn($invite) => $invite->status_class === \Domain\Entities\States\PendingEntityProfessionalRoleState::class
                        );
                    @endphp

                    @if($pendingInvites->count() > 0)
                        <div class="overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-600">
                            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-600">
                                <thead>
                                    <tr class="bg-slate-50 dark:bg-slate-700/50">
                                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                            {{ __('events.entity') }}
                                        </th>
                                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                            {{ __('coaches.district') }}
                                        </th>
                                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                            {{ __('coaches.modality') }}
                                        </th>
                                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                            {{ __('coaches.invitation_date') }}
                                        </th>
                                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                            {{ __('main.status') }}
                                        </th>
                                        <th scope="col" class="relative px-5 py-3">
                                            <span class="sr-only">{{ __('main.actions') }}</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 dark:divide-slate-600 bg-white dark:bg-slate-800">
                                    @foreach($pendingInvites as $coach)
                                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                            <td class="px-5 py-4 whitespace-nowrap">
                                                <div class="flex items-center gap-3">
                                                    <div class="flex-shrink-0 w-9 h-9 rounded-lg bg-gradient-to-br from-slate-600 to-slate-800 dark:from-slate-500 dark:to-slate-700 flex items-center justify-center shadow-sm">
                                                        <span class="text-white font-bold text-xs">
                                                            {{ strtoupper(substr($coach->entity->name, 0, 2)) }}
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <div class="text-sm font-medium text-slate-900 dark:text-white">
                                                            {{ $coach->entity->name }}
                                                        </div>
                                                        <div class="text-xs text-slate-500 dark:text-slate-400 font-mono">
                                                            {{ $coach->entity->member_code }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-5 py-4 whitespace-nowrap text-sm text-slate-600 dark:text-slate-300">
                                                {{ $coach->entity->district?->name ?? '-' }}
                                            </td>
                                            <td class="px-5 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-teal-50 text-teal-700 dark:bg-teal-900/30 dark:text-teal-300">
                                                    {{ __($coach->sport?->name ?? $coach->role_name) }}
                                                </span>
                                            </td>
                                            <td class="px-5 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">
                                                {{ \Carbon\Carbon::parse($coach->created_at)->format('d M Y') }}
                                            </td>
                                            <td class="px-5 py-4 whitespace-nowrap">
                                                <x-tables.badge :status="ucwords($coach->stateName())" :color="$coach->stateColor()" />
                                            </td>
                                            <td class="px-5 py-4 whitespace-nowrap text-right">
                                                @if($coach->status_class === \Domain\Entities\States\PendingEntityProfessionalRoleState::class)
                                                    <div class="flex items-center justify-end gap-2">
                                                        <form action="{{ route('individual.coach.response', $coach->id) }}" method="POST">
                                                            @csrf
                                                            @method('PUT')
                                                            <input type="hidden" name="status_class" value="{{ \Domain\Entities\States\ActiveEntityProfessionalRoleState::class }}">
                                                            <button
                                                                type="submit"
                                                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-medium rounded-md shadow-sm transition-colors"
                                                            >
                                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                                </svg>
                                                                {{ __('events.accept') }}
                                                            </button>
                                                        </form>
                                                        <form action="{{ route('individual.coach.response', $coach->id) }}" method="POST">
                                                            @csrf
                                                            @method('PUT')
                                                            <input type="hidden" name="status_class" value="{{ \Domain\Entities\States\RejectedEntityProfessionalRoleState::class }}">
                                                            <button
                                                                type="submit"
                                                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-white dark:bg-slate-600 hover:bg-rose-50 dark:hover:bg-rose-900/30 text-rose-600 dark:text-rose-400 text-xs font-medium rounded-md border border-slate-300 dark:border-slate-500 hover:border-rose-300 dark:hover:border-rose-700 transition-colors"
                                                            >
                                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                                </svg>
                                                                {{ __('events.refuse') }}
                                                            </button>
                                                        </form>
                                                    </div>
                                                @else
                                                    <form
                                                        action="{{ route('individual.coach.delete', $coach->id) }}"
                                                        method="POST"
                                                        class="inline"
                                                        onsubmit="return confirm('{{ __('coaches.confirm_delete_invitation') }}')"
                                                    >
                                                        @csrf
                                                        @method('DELETE')
                                                        <button
                                                            type="submit"
                                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-slate-500 hover:text-rose-600 dark:text-slate-400 dark:hover:text-rose-400 bg-white dark:bg-slate-600 hover:bg-rose-50 dark:hover:bg-rose-900/20 rounded-md border border-slate-200 dark:border-slate-500 transition-colors"
                                                        >
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                            {{ __('main.delete') }}
                                                        </button>
                                                    </form>
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
                            <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-emerald-50 dark:bg-emerald-900/20 mb-4">
                                <svg class="w-7 h-7 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h3 class="text-base font-semibold text-slate-900 dark:text-white mb-1">
                                {{ __('coaches.no_pending_invitations') }}
                            </h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400 max-w-sm mx-auto">
                                {{ __('coaches.all_caught_up') }}
                            </p>
                            @if($activeCount > 0)
                                <button
                                    @click="activeTab = 'active'"
                                    class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-teal-500 hover:bg-teal-600 text-white text-sm font-medium rounded-lg transition-colors"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    {{ __('coaches.view_active_associations') }}
                                </button>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- History Tab -->
                <div
                    x-show="activeTab === 'history'"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-cloak
                >
                    @php
                        // Only show rejected/canceled invitations
                        $historyInvites = $invites->filter(
                            fn($invite) => in_array($invite->status_class, [
                                \Domain\Entities\States\RejectedEntityProfessionalRoleState::class,
                                \Domain\Entities\States\CanceledEntityProfessionalRoleState::class,
                            ])
                        );
                    @endphp

                    @if($historyInvites->count() > 0)
                        <div class="overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-600">
                            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-600">
                                <thead>
                                    <tr class="bg-slate-50 dark:bg-slate-700/50">
                                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                            {{ __('events.entity') }}
                                        </th>
                                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                            {{ __('coaches.district') }}
                                        </th>
                                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                            {{ __('coaches.modality') }}
                                        </th>
                                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                            {{ __('coaches.invitation_date') }}
                                        </th>
                                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                            {{ __('main.status') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 dark:divide-slate-600 bg-white dark:bg-slate-800">
                                    @foreach($historyInvites as $coach)
                                        @php
                                            $isRejected = $coach->status_class === \Domain\Entities\States\RejectedEntityProfessionalRoleState::class;
                                            $statusColor = $isRejected
                                                ? 'bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-400'
                                                : 'bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300';
                                            $statusLabel = $isRejected ? __('entities.status_rejected') : __('entities.status_canceled');
                                        @endphp
                                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                            <td class="px-5 py-4 whitespace-nowrap">
                                                <div class="flex items-center gap-3">
                                                    <div class="flex-shrink-0 w-9 h-9 rounded-lg bg-slate-100 dark:bg-slate-700 flex items-center justify-center">
                                                        <span class="text-slate-600 dark:text-slate-400 font-bold text-xs">
                                                            {{ strtoupper(substr($coach->entity->name, 0, 2)) }}
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <div class="text-sm font-medium text-slate-900 dark:text-white">
                                                            {{ $coach->entity->name }}
                                                        </div>
                                                        <div class="text-xs text-slate-500 dark:text-slate-400 font-mono">
                                                            {{ $coach->entity->member_code }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-5 py-4 whitespace-nowrap text-sm text-slate-600 dark:text-slate-300">
                                                {{ $coach->entity->district?->name ?? '-' }}
                                            </td>
                                            <td class="px-5 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-teal-50 text-teal-700 dark:bg-teal-900/30 dark:text-teal-300">
                                                    {{ __($coach->sport?->name ?? $coach->role_name) }}
                                                </span>
                                            </td>
                                            <td class="px-5 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">
                                                {{ \Carbon\Carbon::parse($coach->created_at)->format('d M Y') }}
                                            </td>
                                            <td class="px-5 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                                    {{ $statusLabel }}
                                                </span>
                                                <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                                    {{ $coach->updated_at->format('d M Y, H:i') }}
                                                </div>
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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h3 class="text-base font-semibold text-slate-900 dark:text-white mb-1">
                                {{ __('coaches.no_rejected') }}
                            </h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400 max-w-sm mx-auto">
                                {{ __('coaches.no_rejected_desc') }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>
</x-layout>
