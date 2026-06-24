<div class="bg-white dark:bg-slate-800 shadow-sm rounded-xl border border-slate-200 dark:border-slate-700">
    <!-- Tabs Navigation -->
    <div class="border-b border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50">
        <nav class="flex px-2 sm:px-4" aria-label="Tabs">
            <button
                wire:click="setTab('individual')"
                class="py-3 sm:py-4 px-3 sm:px-4 text-sm font-medium border-b-2 transition-colors rounded-t-lg -mb-px {{ $activeTab === 'individual' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400 bg-white dark:bg-slate-800' : 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-slate-300 dark:hover:bg-slate-700/50' }}"
            >
                <span class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    {{ __('diagnostics.tab_individual_profile') }}
                </span>
            </button>

            <button
                wire:click="setTab('event')"
                class="py-3 sm:py-4 px-3 sm:px-4 text-sm font-medium border-b-2 transition-colors rounded-t-lg -mb-px {{ $activeTab === 'event' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400 bg-white dark:bg-slate-800' : 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-slate-300 dark:hover:bg-slate-700/50' }}"
            >
                <span class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    {{ __('diagnostics.tab_event_enrollment') }}
                </span>
            </button>

        </nav>
    </div>

    <!-- Tab Content -->
    <div class="p-4 sm:p-6">
        {{-- Individual Profile Tab --}}
        @if($activeTab === 'individual')
            <div>
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-slate-800 dark:text-white mb-2">
                        {{ __('diagnostics.individual_profile_title') }}
                    </h2>
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        {{ __('diagnostics.individual_profile_description') }}
                    </p>
                </div>

                {{-- Search Input --}}
                <div class="relative mb-6">
                    <div class="flex gap-2">
                        <div class="relative flex-1">
                            <input
                                type="text"
                                wire:model.live.debounce.300ms="individualSearch"
                                placeholder="{{ __('diagnostics.search_placeholder') }}"
                                class="w-full px-4 py-2.5 pr-10 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            >
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                        </div>
                        @if($selectedIndividualId)
                            <button
                                wire:click="clearIndividualSelection"
                                class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors"
                            >
                                {{ __('common.clear') }}
                            </button>
                        @endif
                    </div>

                    {{-- Search Results Dropdown --}}
                    @if(strlen($individualSearch) >= 2 && $this->searchResults->count() > 0)
                        <div class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                            @foreach($this->searchResults as $individual)
                                <button
                                    wire:click="selectIndividual('{{ $individual->id }}')"
                                    class="w-full px-4 py-3 text-left hover:bg-slate-50 dark:hover:bg-slate-600 border-b border-slate-100 dark:border-slate-600 last:border-b-0"
                                >
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="font-medium text-slate-900 dark:text-white">{{ $individual->full_name }}</p>
                                            <p class="text-sm text-slate-500 dark:text-slate-400">{{ $individual->member_code }} - {{ $individual->email }}</p>
                                        </div>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Selected Individual Profile --}}
                @if($profileDiagnostic)
                    <div class="space-y-6">
                        {{-- Individual Header --}}
                        <div class="bg-slate-50 dark:bg-slate-900/50 rounded-lg p-4 border border-slate-200 dark:border-slate-700">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
                                        {{ $profileDiagnostic['individual']['name'] }}
                                    </h3>
                                    <p class="text-sm text-slate-600 dark:text-slate-400">
                                        {{ $profileDiagnostic['individual']['member_code'] }}
                                        @if($profileDiagnostic['individual']['email'])
                                            | {{ $profileDiagnostic['individual']['email'] }}
                                        @endif
                                    </p>
                                    <p class="text-sm text-slate-500 dark:text-slate-500 mt-1">
                                        {{ $profileDiagnostic['individual']['gender'] }}
                                        @if($profileDiagnostic['individual']['birthday'])
                                            | {{ __('common.birthday') }}: {{ $profileDiagnostic['individual']['birthday'] }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Quick Status Cards --}}
                        <div>
                            <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3 uppercase tracking-wide">
                                {{ __('diagnostics.quick_status') }}
                            </h4>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                @foreach(['athlete', 'coach', 'referee', 'official'] as $role)
                                    @php
                                        $status = $profileDiagnostic['quickStatus'][$role] ?? ['eligible' => false, 'reason' => __('diagnostics.not_checked')];
                                    @endphp
                                    <div class="p-4 rounded-lg border {{ $status['eligible'] ? 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800' : 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800' }}">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-xs font-semibold uppercase tracking-wide {{ $status['eligible'] ? 'text-green-700 dark:text-green-400' : 'text-red-700 dark:text-red-400' }}">
                                                {{ __('diagnostics.role_' . $role) }}
                                            </span>
                                            @if($status['eligible'])
                                                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                </svg>
                                            @else
                                                <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                                </svg>
                                            @endif
                                        </div>
                                        <p class="text-sm {{ $status['eligible'] ? 'text-green-800 dark:text-green-300' : 'text-red-800 dark:text-red-300' }}">
                                            {{ $status['reason'] }}
                                        </p>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Federation Memberships --}}
                        <div>
                            <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3 uppercase tracking-wide">
                                {{ __('diagnostics.federation_memberships') }}
                            </h4>
                            @if(count($profileDiagnostic['federationMemberships']) > 0)
                                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
                                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                                        <thead class="bg-slate-50 dark:bg-slate-900/50">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">{{ __('diagnostics.federation') }}</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">{{ __('diagnostics.type') }}</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">{{ __('diagnostics.status') }}</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">{{ __('diagnostics.since') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                            @foreach($profileDiagnostic['federationMemberships'] as $membership)
                                                <tr>
                                                    <td class="px-4 py-3 text-sm text-slate-900 dark:text-white">{{ $membership['name'] }}</td>
                                                    <td class="px-4 py-3">
                                                        @if($membership['is_local'])
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                                                {{ __('diagnostics.local') }}
                                                            </span>
                                                        @elseif($membership['is_default'])
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300">
                                                                {{ __('diagnostics.main') }}
                                                            </span>
                                                        @else
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300">
                                                                {{ __('diagnostics.modalidade') }}
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $membership['status'] === 'Active' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300' }}">
                                                            {{ $membership['status'] }}
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-400">{{ $membership['since'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-sm text-slate-500 dark:text-slate-400 italic">{{ __('diagnostics.no_federation_memberships') }}</p>
                            @endif
                        </div>

                        {{-- Entity Memberships --}}
                        <div>
                            <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3 uppercase tracking-wide">
                                {{ __('diagnostics.entity_memberships') }}
                            </h4>
                            @if(count($profileDiagnostic['entityMemberships']) > 0)
                                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
                                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                                        <thead class="bg-slate-50 dark:bg-slate-900/50">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">{{ __('diagnostics.entity') }}</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">{{ __('diagnostics.status') }}</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">{{ __('diagnostics.sports') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                            @foreach($profileDiagnostic['entityMemberships'] as $membership)
                                                <tr>
                                                    <td class="px-4 py-3 text-sm text-slate-900 dark:text-white">{{ $membership['name'] }}</td>
                                                    <td class="px-4 py-3">
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $membership['status'] === 'Active' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300' }}">
                                                            {{ $membership['status'] }}
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-400">{{ $membership['sports'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-sm text-slate-500 dark:text-slate-400 italic">{{ __('diagnostics.no_entity_memberships') }}</p>
                            @endif
                        </div>

                        {{-- Professional Roles --}}
                        <div>
                            <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3 uppercase tracking-wide">
                                {{ __('diagnostics.professional_roles') }}
                            </h4>
                            @if(count($profileDiagnostic['professionalRoles']) > 0)
                                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
                                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                                        <thead class="bg-slate-50 dark:bg-slate-900/50">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">{{ __('diagnostics.role') }}</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">{{ __('diagnostics.source') }}</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">{{ __('diagnostics.status') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                            @foreach($profileDiagnostic['professionalRoles'] as $role)
                                                <tr>
                                                    <td class="px-4 py-3 text-sm font-medium text-slate-900 dark:text-white">{{ $role['role'] }}</td>
                                                    <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-400">{{ $role['source'] }}</td>
                                                    <td class="px-4 py-3">
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $role['status'] === 'Active' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300' }}">
                                                            {{ $role['status'] }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-sm text-slate-500 dark:text-slate-400 italic">{{ __('diagnostics.no_professional_roles') }}</p>
                            @endif
                        </div>

                        {{-- Certifications (Referee Check) --}}
                        <div>
                            <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3 uppercase tracking-wide">
                                {{ __('diagnostics.certifications') }}
                            </h4>
                            @if(count($profileDiagnostic['certifications']) > 0)
                                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
                                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                                        <thead class="bg-slate-50 dark:bg-slate-900/50">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">{{ __('diagnostics.certification') }}</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">{{ __('diagnostics.status') }}</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">{{ __('diagnostics.grants_role') }}</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">{{ __('diagnostics.action_needed') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                            @foreach($profileDiagnostic['certifications'] as $cert)
                                                <tr class="{{ isset($cert['action_needed']) && $cert['action_needed'] ? 'bg-amber-50 dark:bg-amber-900/10' : '' }}">
                                                    <td class="px-4 py-3 text-sm text-slate-900 dark:text-white">{{ $cert['name'] }}</td>
                                                    <td class="px-4 py-3">
                                                        @php
                                                            $statusColors = match($cert['status']) {
                                                                'Active' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                                                                'Pending' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300',
                                                                'Expired' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
                                                                default => 'bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300',
                                                            };
                                                        @endphp
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors }}">
                                                            {{ $cert['status'] }}
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-400">
                                                        {{ $cert['grants_role'] ?? '-' }}
                                                    </td>
                                                    <td class="px-4 py-3 text-sm">
                                                        @if(isset($cert['action_needed']) && $cert['action_needed'])
                                                            <span class="text-amber-700 dark:text-amber-400 font-medium">
                                                                {{ $cert['action_needed'] }}
                                                            </span>
                                                        @else
                                                            <span class="text-slate-400">-</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-sm text-slate-500 dark:text-slate-400 italic">{{ __('diagnostics.no_certifications') }}</p>
                            @endif
                        </div>

                        {{-- Active Licenses --}}
                        <div>
                            <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3 uppercase tracking-wide">
                                {{ __('diagnostics.active_licenses') }}
                            </h4>
                            @if(count($profileDiagnostic['activeLicenses']) > 0)
                                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
                                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                                        <thead class="bg-slate-50 dark:bg-slate-900/50">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">{{ __('diagnostics.license') }}</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">{{ __('diagnostics.status') }}</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">{{ __('diagnostics.expires') }}</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">{{ __('diagnostics.federation') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                            @foreach($profileDiagnostic['activeLicenses'] as $license)
                                                <tr>
                                                    <td class="px-4 py-3 text-sm text-slate-900 dark:text-white">{{ $license['name'] }}</td>
                                                    <td class="px-4 py-3">
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $license['status'] === 'Active' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300' }}">
                                                            {{ $license['status'] }}
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-400">{{ $license['expires'] ?? '-' }}</td>
                                                    <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-400">{{ $license['federation'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-sm text-slate-500 dark:text-slate-400 italic">{{ __('diagnostics.no_active_licenses') }}</p>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-slate-900 dark:text-white">{{ __('diagnostics.no_individual_selected') }}</h3>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('diagnostics.search_to_start') }}</p>
                    </div>
                @endif
            </div>
        @endif

        {{-- Event Enrollment Tab --}}
        @if($activeTab === 'event')
            <div>
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-slate-800 dark:text-white mb-2">
                        {{ __('diagnostics.event_enrollment_title') }}
                    </h2>
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        {{ __('diagnostics.event_enrollment_description') }}
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    {{-- Event Selection --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            {{ __('diagnostics.select_event') }}
                        </label>
                        <select
                            wire:model.live="selectedEventId"
                            class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        >
                            <option value="">{{ __('diagnostics.select_event_placeholder') }}</option>
                            @foreach($this->availableEvents as $event)
                                <option value="{{ $event->id }}">{{ $event->name }} ({{ $event->start_date?->format('Y-m-d') }})</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Competition Selection --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            {{ __('diagnostics.select_competition') }}
                        </label>
                        <select
                            wire:model.live="selectedCompetitionId"
                            class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent disabled:opacity-50"
                            {{ !$selectedEventId ? 'disabled' : '' }}
                        >
                            <option value="">{{ __('diagnostics.all_competitions') }}</option>
                            @foreach($this->availableCompetitions as $competition)
                                <option value="{{ $competition->id }}">{{ $competition->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Role Selection --}}
                <div class="mb-6">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        {{ __('diagnostics.select_role') }}
                    </label>
                    <div class="flex flex-wrap gap-4">
                        @foreach(['athlete', 'coach', 'referee', 'official'] as $role)
                            <label class="flex items-center">
                                <input
                                    type="radio"
                                    wire:model.live="selectedRole"
                                    value="{{ $role }}"
                                    class="h-4 w-4 text-indigo-600 border-slate-300 focus:ring-indigo-500"
                                >
                                <span class="ml-2 text-sm text-slate-700 dark:text-slate-300">{{ __('diagnostics.role_' . $role) }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Individual Search --}}
                <div class="relative mb-6">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        {{ __('diagnostics.search_individual') }}
                    </label>
                    <div class="flex gap-2">
                        <div class="relative flex-1">
                            <input
                                type="text"
                                wire:model.live.debounce.300ms="eventIndividualSearch"
                                placeholder="{{ __('diagnostics.search_placeholder') }}"
                                class="w-full px-4 py-2.5 pr-10 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            >
                        </div>
                        <button
                            wire:click="runEventDiagnostic"
                            class="px-6 py-2.5 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                            {{ !$selectedEventId || !$eventSelectedIndividualId ? 'disabled' : '' }}
                        >
                            {{ __('diagnostics.run_diagnostic') }}
                        </button>
                    </div>

                    {{-- Search Results Dropdown --}}
                    @if(strlen($eventIndividualSearch) >= 2 && $this->eventSearchResults->count() > 0)
                        <div class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                            @foreach($this->eventSearchResults as $individual)
                                <button
                                    wire:click="selectEventIndividual('{{ $individual->id }}')"
                                    class="w-full px-4 py-3 text-left hover:bg-slate-50 dark:hover:bg-slate-600 border-b border-slate-100 dark:border-slate-600 last:border-b-0"
                                >
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="font-medium text-slate-900 dark:text-white">{{ $individual->full_name }}</p>
                                            <p class="text-sm text-slate-500 dark:text-slate-400">{{ $individual->member_code }}</p>
                                        </div>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Selected Individual Badge --}}
                @if($eventSelectedIndividualId)
                    @php
                        $selectedIndividual = \Domain\Individuals\Models\Individual::find($eventSelectedIndividualId);
                    @endphp
                    @if($selectedIndividual)
                        <div class="mb-6 flex items-center gap-2">
                            <span class="text-sm text-slate-600 dark:text-slate-400">{{ __('diagnostics.selected') }}:</span>
                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300">
                                {{ $selectedIndividual->full_name }} ({{ $selectedIndividual->member_code }})
                                <button wire:click="clearEventDiagnostic" class="ml-2 hover:text-indigo-600">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                </button>
                            </span>
                        </div>
                    @endif
                @endif

                {{-- Diagnostic Results --}}
                @if($eventDiagnosticResult)
                    <div class="space-y-4">
                        {{-- Result Header --}}
                        <div class="p-4 rounded-lg {{ $eventDiagnosticResult['isEligible'] ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800' : 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800' }}">
                            <div class="flex items-center">
                                @if($eventDiagnosticResult['isEligible'])
                                    <svg class="w-6 h-6 text-green-600 dark:text-green-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="text-lg font-semibold text-green-800 dark:text-green-300">
                                        {{ __('diagnostics.eligible_as_role', ['role' => __('diagnostics.role_' . $selectedRole)]) }}
                                    </span>
                                @else
                                    <svg class="w-6 h-6 text-red-600 dark:text-red-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="text-lg font-semibold text-red-800 dark:text-red-300">
                                        {{ __('diagnostics.not_eligible_as_role', ['role' => __('diagnostics.role_' . $selectedRole)]) }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Individual Checks --}}
                        <div class="space-y-3">
                            @foreach($eventDiagnosticResult['checks'] as $check)
                                <div class="p-4 rounded-lg border {{ $check['passed'] ? 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700' : 'bg-red-50 dark:bg-red-900/10 border-red-200 dark:border-red-800' }}">
                                    <div class="flex items-start">
                                        @if($check['passed'])
                                            <svg class="w-5 h-5 text-green-600 dark:text-green-400 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                        @else
                                            <svg class="w-5 h-5 text-red-600 dark:text-red-400 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                            </svg>
                                        @endif
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between">
                                                <span class="font-medium {{ $check['passed'] ? 'text-slate-900 dark:text-white' : 'text-red-900 dark:text-red-200' }}">
                                                    {{ $check['label'] }}
                                                </span>
                                                <span class="text-xs px-2 py-1 rounded {{ $check['passed'] ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' }}">
                                                    {{ $check['passed'] ? __('diagnostics.passed') : __('diagnostics.failed') }}
                                                </span>
                                            </div>
                                            <p class="text-sm mt-1 {{ $check['passed'] ? 'text-slate-600 dark:text-slate-400' : 'text-red-700 dark:text-red-300' }}">
                                                {{ $check['message'] }}
                                            </p>
                                            @if($check['suggestion'] && !$check['passed'])
                                                <p class="text-sm mt-2 text-amber-700 dark:text-amber-400 font-medium">
                                                    {{ $check['suggestion'] }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Suggestions Summary --}}
                        @if(count($eventDiagnosticResult['suggestions']) > 0)
                            <div class="p-4 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800">
                                <h4 class="font-semibold text-amber-800 dark:text-amber-300 mb-2">{{ __('diagnostics.suggestions') }}</h4>
                                <ul class="list-disc list-inside space-y-1 text-sm text-amber-700 dark:text-amber-400">
                                    @foreach($eventDiagnosticResult['suggestions'] as $suggestion)
                                        <li>{{ $suggestion }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                @elseif(!$selectedEventId)
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-slate-900 dark:text-white">{{ __('diagnostics.select_event_first') }}</h3>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('diagnostics.select_event_to_start') }}</p>
                    </div>
                @endif
            </div>
        @endif

    </div>
</div>
