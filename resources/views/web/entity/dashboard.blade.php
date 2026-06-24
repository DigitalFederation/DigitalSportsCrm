<x-layout>
    <div class="previous-layout-classes">
        @if(isset($entity))
            <!-- Dashboard Header -->
            <x-entity.dashboard-header :entity="$entity" />

            <!-- Dashboard Grid -->
            <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column: Recent Actions (2/3 width on large screens) -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Recent Actions -->
                    <livewire:widgets.activity-log />
                </div>

                <!-- Right Column: Quick Info Blocks (1/3 width on large screens) -->
                <div class="space-y-6">
                    <!-- Members to Approve -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                            <div class="flex items-center gap-3">
                                <div class="h-8 w-8 rounded-full bg-amber-50 flex items-center justify-center">
                                    <svg class="h-4 w-4 text-amber-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M10 9a3 3 0 100-6 3 3 0 000 6zM6 8a2 2 0 11-4 0 2 2 0 014 0zM1.49 15.326a.78.78 0 01-.358-.442 3 3 0 014.308-3.516 6.484 6.484 0 00-1.905 3.959c-.023.222-.014.442.025.654a4.97 4.97 0 01-2.07-.655zM16.44 15.98a4.97 4.97 0 002.07-.654.78.78 0 00.357-.442 3 3 0 00-4.308-3.517 6.484 6.484 0 011.907 3.96 2.32 2.32 0 01-.026.654zM18 8a2 2 0 11-4 0 2 2 0 014 0zM5.304 16.19a.844.844 0 01-.277-.71 5 5 0 019.947 0 .843.843 0 01-.277.71A6.975 6.975 0 0110 18a6.974 6.974 0 01-4.696-1.81z" />
                                    </svg>
                                </div>
                                <h3 class="text-sm font-semibold text-gray-900">{{ __('entity.dashboard.members_to_approve') }}</h3>
                            </div>
                            @if(isset($pendingMembersCount) && $pendingMembersCount > 0)
                                <span class="inline-flex items-center rounded-full bg-amber-50 px-2 py-1 text-xs font-medium text-amber-700 ring-1 ring-inset ring-amber-600/10">
                                    {{ $pendingMembersCount }}
                                </span>
                            @endif
                        </div>
                        <div class="divide-y divide-gray-100">
                            @forelse($pendingMembers ?? [] as $pendingMember)
                                <div class="px-5 py-3 hover:bg-gray-50 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <div class="flex-shrink-0">
                                            <img class="h-8 w-8 rounded-full object-cover"
                                                 src="{{ $pendingMember->individual?->getFirstMediaUrl('profile', 'thumb') ?: asset('img/user_placeholder.png') }}"
                                                 alt="">
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate">
                                                {{ $pendingMember->individual?->name }} {{ $pendingMember->individual?->surname }}
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                {{ $pendingMember->created_at?->diffForHumans() }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="px-5 py-8 text-center">
                                    <svg class="mx-auto h-8 w-8 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <p class="mt-2 text-sm text-gray-500">{{ __('entity.dashboard.no_pending_members') }}</p>
                                </div>
                            @endforelse
                        </div>
                        @if(isset($pendingMembersCount) && $pendingMembersCount > 0)
                            <div class="px-5 py-3 bg-gray-50 border-t border-gray-100">
                                <a href="{{ route('entity.individual-approve.index') }}" class="text-sm font-medium text-primary hover:text-primary/80 flex items-center justify-center gap-1">
                                    {{ __('entity.view_all') }}
                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd" />
                                    </svg>
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- Entity Affiliations -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                            <div class="flex items-center gap-3">
                                <div class="h-8 w-8 rounded-full bg-blue-50 flex items-center justify-center">
                                    <svg class="h-4 w-4 text-blue-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M3.5 2A1.5 1.5 0 002 3.5V5c0 1.149.15 2.263.43 3.326a13.022 13.022 0 009.244 9.244c1.063.28 2.177.43 3.326.43h1.5a1.5 1.5 0 001.5-1.5v-1.148a1.5 1.5 0 00-1.175-1.465l-3.223-.716a1.5 1.5 0 00-1.767 1.052l-.267.933c-.117.41-.555.643-.95.48a11.542 11.542 0 01-6.254-6.254c-.163-.395.07-.833.48-.95l.933-.267a1.5 1.5 0 001.052-1.767l-.716-3.223A1.5 1.5 0 004.648 2H3.5z" />
                                    </svg>
                                </div>
                                <h3 class="text-sm font-semibold text-gray-900">{{ __('entity.dashboard.entity_affiliations') }}</h3>
                            </div>
                            @if(isset($affiliationsCount) && $affiliationsCount > 0)
                                <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-600/10">
                                    {{ $affiliationsCount }}
                                </span>
                            @endif
                        </div>
                        <div class="divide-y divide-gray-100">
                            @forelse($affiliations ?? [] as $affiliation)
                                <div class="px-5 py-3 hover:bg-gray-50 transition-colors">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate">
                                                {{ $affiliation->federation?->name ?? '---' }}
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                {{ $affiliation->start_date?->format('d/m/Y') }} - {{ $affiliation->end_date?->format('d/m/Y') }}
                                            </p>
                                        </div>
                                        <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium
                                            {{ $affiliation->status_class === \Domain\Memberships\States\ActiveAffiliationState::class ? 'bg-green-50 text-green-700' : 'bg-yellow-50 text-yellow-700' }}">
                                            {{ $affiliation->status_class === \Domain\Memberships\States\ActiveAffiliationState::class ? __('main.Active') : __('main.Pending') }}
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <div class="px-5 py-8 text-center">
                                    <svg class="mx-auto h-8 w-8 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                                    </svg>
                                    <p class="mt-2 text-sm text-gray-500">{{ __('entity.dashboard.no_affiliations') }}</p>
                                </div>
                            @endforelse
                        </div>
                        @if(isset($affiliationsCount) && $affiliationsCount > 0)
                            <div class="px-5 py-3 bg-gray-50 border-t border-gray-100">
                                <a href="{{ route('entity.subscriptions.index') }}" class="text-sm font-medium text-primary hover:text-primary/80 flex items-center justify-center gap-1">
                                    {{ __('entity.view_all') }}
                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd" />
                                    </svg>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Second Row: Licenses -->
            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Sport Licenses -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="h-8 w-8 rounded-full bg-orange-50 flex items-center justify-center">
                                <svg class="h-4 w-4 text-orange-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-11.25a.75.75 0 00-1.5 0v2.5h-2.5a.75.75 0 000 1.5h2.5v2.5a.75.75 0 001.5 0v-2.5h2.5a.75.75 0 000-1.5h-2.5v-2.5z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <h3 class="text-sm font-semibold text-gray-900">{{ __('entity.sport_licenses') }}</h3>
                        </div>
                        @if(isset($sportLicensesCount) && $sportLicensesCount > 0)
                            <span class="inline-flex items-center rounded-full bg-orange-50 px-2 py-1 text-xs font-medium text-orange-700 ring-1 ring-inset ring-orange-600/10">
                                {{ $sportLicensesCount }} {{ __('entity.active') }}
                            </span>
                        @endif
                    </div>
                    <div class="divide-y divide-gray-100">
                        @forelse($sportLicenses ?? [] as $license)
                            <div class="px-5 py-3 hover:bg-gray-50 transition-colors">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">
                                            {{ $license->license?->name ?? '---' }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ $license->end_date ? __('main.expires_on') . ' ' . $license->end_date->format('d/m/Y') : '---' }}
                                        </p>
                                    </div>
                                    <span class="inline-flex items-center rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700">
                                        {{ __('main.Active') }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="px-5 py-8 text-center">
                                <svg class="mx-auto h-8 w-8 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.336a6.721 6.721 0 01-3.17.789 6.721 6.721 0 01-3.168-.789 3.376 3.376 0 016.338 0z" />
                                </svg>
                                <p class="mt-2 text-sm text-gray-500">{{ __('entity.dashboard.no_sport_licenses') }}</p>
                            </div>
                        @endforelse
                    </div>
                    <div class="px-5 py-3 bg-gray-50 border-t border-gray-100">
                        <a href="{{ route('entity.license-attributed.index', ['filter[committee]' => 'sport']) }}" class="text-sm font-medium text-primary hover:text-primary/80 flex items-center justify-center gap-1">
                            {{ __('entity.view_all') }}
                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Diving Licenses -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="h-8 w-8 rounded-full bg-cyan-50 flex items-center justify-center">
                                <svg class="h-4 w-4 text-cyan-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M13.5 4.938a7 7 0 11-9.006 1.737c.202-.257.59-.218.793.039.278.352.594.672.943.954.332.269.786-.049.773-.476a5.977 5.977 0 01.602-2.682l.146.044a7 7 0 006.749.384z" clip-rule="evenodd" />
                                    <path fill-rule="evenodd" d="M14.5 7.5a.5.5 0 01.5.5v2a.5.5 0 01-1 0V8a.5.5 0 01.5-.5z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <h3 class="text-sm font-semibold text-gray-900">{{ __('entity.diving_licenses') }}</h3>
                        </div>
                        @if(isset($divingLicensesCount) && $divingLicensesCount > 0)
                            <span class="inline-flex items-center rounded-full bg-cyan-50 px-2 py-1 text-xs font-medium text-cyan-700 ring-1 ring-inset ring-cyan-600/10">
                                {{ $divingLicensesCount }} {{ __('entity.active') }}
                            </span>
                        @endif
                    </div>
                    <div class="divide-y divide-gray-100">
                        @forelse($divingLicenses ?? [] as $license)
                            <div class="px-5 py-3 hover:bg-gray-50 transition-colors">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">
                                            {{ $license->license?->name ?? '---' }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ $license->end_date ? __('main.expires_on') . ' ' . $license->end_date->format('d/m/Y') : '---' }}
                                        </p>
                                    </div>
                                    <span class="inline-flex items-center rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700">
                                        {{ __('main.Active') }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="px-5 py-8 text-center">
                                <svg class="mx-auto h-8 w-8 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.336a6.721 6.721 0 01-3.17.789 6.721 6.721 0 01-3.168-.789 3.376 3.376 0 016.338 0z" />
                                </svg>
                                <p class="mt-2 text-sm text-gray-500">{{ __('entity.dashboard.no_diving_licenses') }}</p>
                            </div>
                        @endforelse
                    </div>
                    <div class="px-5 py-3 bg-gray-50 border-t border-gray-100">
                        <a href="{{ route('entity.diving_licenses.index') }}" class="text-sm font-medium text-primary hover:text-primary/80 flex items-center justify-center gap-1">
                            {{ __('entity.view_all') }}
                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        @else
            <!-- Error State -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">{{ __('entity.dashboard.no_entity_found') }}</h3>
                <p class="mt-2 text-sm text-gray-500">{{ __('entity.dashboard.no_entity_associated') }}</p>
            </div>
        @endif
    </div>
</x-layout>
