@section('title', __('federation.individual_affiliation_details'))

<x-layout>
    <div class="min-h-screen">
        @php
            $affiliationPlan = $affiliation->affiliationPlan;
            $memberSubscription = $affiliation->memberSubscription;
            $membershipPackage = $memberSubscription?->membershipPackage;
            $isActive = $affiliation->isActive();
            $isExpired = $affiliation->end_date && $affiliation->end_date->isPast();
            $memberId = $individual->federations->first()?->pivot?->national_federation_number ?? $individual->code_internal ?? null;
            $address = $individual->address ?? null;
            $postalCode = $individual->postal_code ?? null;
            $district = $individual->district?->name ?? $individual->location ?? null;
            $includedInsurances = $memberSubscription?->insurances ?? collect();
        @endphp

        <!-- Back Button -->
        <div class="mb-4">
            <a href="{{ route('federation.individual-affiliations.index') }}" class="inline-flex items-center text-sm text-slate-600 hover:text-slate-900 transition-colors">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                {{ __('main.back') }}
            </a>
        </div>

        <!-- Individual Profile Header -->
        <x-individual.profile-hero :individual="$individual" />

        <!-- Affiliation Status Banner -->
        <div class="mb-8 -mt-2">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <!-- Status Header -->
                <div class="px-6 py-5 {{ $isActive && !$isExpired ? 'bg-gradient-to-r from-slate-700 to-slate-600' : ($isExpired ? 'bg-gradient-to-r from-slate-600 to-slate-500' : 'bg-gradient-to-r from-amber-500 to-amber-400') }}">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div>
                                <h1 class="text-xl sm:text-2xl font-bold text-white">{{ $affiliationPlan->name }}</h1>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $isActive && !$isExpired ? 'bg-green-500 text-white' : ($isExpired ? 'bg-red-500 text-white' : 'bg-amber-100 text-amber-800') }}">
                                        @if($isActive && !$isExpired)
                                            <span class="w-1.5 h-1.5 bg-white rounded-full mr-1.5 animate-pulse"></span>
                                            {{ __('main.active') }}
                                        @elseif($isExpired)
                                            {{ __('main.expired') }}
                                        @else
                                            {{ $affiliation->stateName() }}
                                        @endif
                                    </span>
                                    @if($affiliation->federation)
                                        <span class="text-white/80 text-sm">{{ $affiliation->federation->name }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Affiliation Details Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- Left Column: Personal Information -->
            <div class="space-y-6">

                <!-- Member Personal Data & Contact -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-blue-100 rounded-lg">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-slate-800">{{ __('federation.member_data') }}</h3>
                                <p class="text-xs text-slate-500">{{ __('federation.member_personal_information') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                            <div>
                                <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('main.name') }}</dt>
                                <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $individual->name }} {{ $individual->surname ?? '' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('main.membership_id') }}</dt>
                                <dd class="mt-1 text-sm font-mono font-semibold text-slate-900">{{ $memberId ?: __('main.not_available') }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('main.birthdate') }}</dt>
                                <dd class="mt-1 text-sm text-slate-900">{{ $individual->birthdate ? \Carbon\Carbon::parse($individual->birthdate)->format('d/m/Y') : __('main.not_available') }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('main.nationality') }}</dt>
                                <dd class="mt-1 text-sm text-slate-900 flex items-center gap-2">
                                    @if($individual->country)
                                        <img class="w-4 h-4 rounded-full" src="{{ asset('img/flags/' . strtolower($individual->country->iso) . '.svg') }}" alt="{{ $individual->country->name }}">
                                    @endif
                                    {{ $individual->country->name ?? __('main.not_available') }}
                                </dd>
                            </div>
                        </dl>

                        <!-- Contact Info Divider -->
                        <div class="mt-6 pt-6 border-t border-slate-100">
                            <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-4">{{ __('federation.member_contacts') }}</h4>
                            <dl class="grid grid-cols-1 gap-y-3">
                                <div class="flex items-start gap-3">
                                    <svg class="w-4 h-4 text-slate-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <div class="flex-1 min-w-0">
                                        <dt class="text-xs text-slate-500">{{ __('main.address') }}</dt>
                                        <dd class="text-sm text-slate-900">{{ $address ?: __('main.not_available') }}</dd>
                                        @if($postalCode || $district)
                                            <dd class="text-sm text-slate-600">{{ $postalCode }} {{ $district }}</dd>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    <div class="flex-1 min-w-0">
                                        <dt class="text-xs text-slate-500">{{ __('main.email') }}</dt>
                                        <dd class="text-sm text-slate-900 truncate">{{ $individual->email ?? __('main.not_available') }}</dd>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                    <div class="flex-1 min-w-0">
                                        <dt class="text-xs text-slate-500">{{ __('main.phone') }}</dt>
                                        <dd class="text-sm text-slate-900">{{ $individual->phone ?? __('main.not_available') }}</dd>
                                    </div>
                                </div>
                            </dl>
                        </div>

                    </div>
                </div>

            </div>

            <!-- Right Column: Affiliation Information -->
            <div class="space-y-6">

                <!-- Affiliation Plan Details -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-emerald-100 rounded-lg">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-slate-800">{{ __('federation.affiliation_plan') }}</h3>
                                <p class="text-xs text-slate-500">{{ __('federation.plan_details') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <dl class="space-y-4">
                            <div>
                                <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('federation.plan_name') }}</dt>
                                <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $affiliationPlan->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('federation.fee') }}</dt>
                                <dd class="mt-1 text-sm font-semibold text-slate-900">
                                    @if($affiliation->individual_fee)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700">
                                            {{ number_format($affiliation->individual_fee, 2, ',', '.') }} &euro;
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600">
                                            {{ __('federation.free') }}
                                        </span>
                                    @endif
                                </dd>
                            </div>
                            @if($affiliationPlan->description)
                                <div>
                                    <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('main.description') }}</dt>
                                    <dd class="mt-1 text-sm text-slate-700 leading-relaxed">{{ $affiliationPlan->description }}</dd>
                                </div>
                            @endif
                        </dl>

                        <!-- Affiliation Period -->
                        <div class="mt-6 pt-6 border-t border-slate-100">
                            <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-4">{{ __('federation.affiliation_period') }}</h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="flex items-start gap-3 p-3 bg-slate-50 rounded-lg">
                                    <svg class="w-5 h-5 text-slate-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <div>
                                        <dt class="text-xs font-medium text-slate-500">{{ __('main.start_date') }}</dt>
                                        <dd class="text-sm font-medium text-slate-900">{{ $startDateFormatted }}</dd>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3 p-3 bg-slate-50 rounded-lg">
                                    <svg class="w-5 h-5 text-slate-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <div>
                                        <dt class="text-xs font-medium text-slate-500">{{ __('main.end_date') }}</dt>
                                        <dd class="text-sm font-medium text-slate-900">{{ $endDateFormatted }}</dd>
                                    </div>
                                </div>
                            </div>
                            @php
                                $daysRemaining = (int) $affiliation->end_date->diffInDays(now(), false);
                            @endphp
                            <div class="mt-3 text-sm {{ $daysRemaining < 0 ? 'text-green-600' : ($daysRemaining == 0 ? 'text-amber-600' : 'text-red-600') }}">
                                @if($daysRemaining < 0)
                                    {{ __('federation.individual_insurances.days_remaining', ['days' => abs($daysRemaining)]) }}
                                @elseif($daysRemaining == 0)
                                    {{ __('federation.individual_insurances.expires_today') }}
                                @else
                                    {{ __('federation.individual_insurances.expired_days_ago', ['days' => $daysRemaining]) }}
                                @endif
                            </div>
                        </div>

                        <!-- Federation Info -->
                        @if($affiliation->federation)
                            <div class="mt-6 pt-6 border-t border-slate-100">
                                <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-4">{{ __('federation.affiliated_to') }}</h4>
                                <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-lg">
                                    <div class="p-2 bg-blue-100 rounded-lg">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <dd class="text-sm font-semibold text-slate-900">{{ $affiliation->federation->name }}</dd>
                                        @if($affiliation->federation->abbreviation)
                                            <dd class="text-xs text-slate-500">{{ $affiliation->federation->abbreviation }}</dd>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Related Subscription & Insurances -->
                @if($memberSubscription)
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-purple-100 rounded-lg">
                                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-slate-800">{{ __('federation.membership_package') }}</h3>
                                    <p class="text-xs text-slate-500">{{ __('federation.subscription_details') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-6">
                            <dl class="space-y-4">
                                <div>
                                    <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('federation.package_name') }}</dt>
                                    <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $membershipPackage->name ?? __('main.not_available') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('federation.request_type') }}</dt>
                                    <dd class="mt-1 text-sm text-slate-700">{{ __('federation.request_type_' . ($memberSubscription->request_type ?? 'direct')) }}</dd>
                                </div>
                                @if($affiliation->requester)
                                    <div>
                                        <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('federation.requested_by') }}</dt>
                                        <dd class="mt-1 text-sm text-slate-700">{{ $affiliation->requester->name ?? __('main.not_available') }}</dd>
                                    </div>
                                @endif
                            </dl>

                            <!-- Included Insurances -->
                            @if($includedInsurances->count() > 0)
                                <div class="mt-6 pt-6 border-t border-slate-100">
                                    <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-4">{{ __('federation.included_insurances') }}</h4>
                                    <div class="space-y-2">
                                        @foreach($includedInsurances as $insurance)
                                            <a href="{{ route('federation.individual-insurances.show', $insurance) }}" class="flex items-center justify-between p-3 bg-slate-50 rounded-lg hover:bg-slate-100 transition-colors">
                                                <div class="flex items-center gap-3">
                                                    <div class="p-1.5 bg-green-100 rounded">
                                                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <dd class="text-sm font-medium text-slate-900">{{ $insurance->insurancePlan->name ?? __('main.not_available') }}</dd>
                                                        <dd class="text-xs text-slate-500">
                                                            @if($insurance->isActive())
                                                                <span class="text-green-600">{{ __('main.active') }}</span>
                                                            @else
                                                                <span class="text-slate-500">{{ $insurance->stateName() }}</span>
                                                            @endif
                                                        </dd>
                                                    </div>
                                                </div>
                                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                </svg>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Record Information -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-slate-200 rounded-lg">
                                <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-slate-800">{{ __('federation.record_information') }}</h3>
                                <p class="text-xs text-slate-500">{{ __('federation.timestamps') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <dl class="grid grid-cols-2 gap-4">
                            <div>
                                <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('common.created_at') }}</dt>
                                <dd class="mt-1 text-sm text-slate-900">{{ $affiliation->created_at->format('d/m/Y H:i') }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('common.updated_at') }}</dt>
                                <dd class="mt-1 text-sm text-slate-900">{{ $affiliation->updated_at->format('d/m/Y H:i') }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-layout>
