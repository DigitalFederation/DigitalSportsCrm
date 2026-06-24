@section('title', __('insurance.insurance_plan'))

<x-layout>
    <div class="previous-layout-classes">
        @php
            $namespace = Request::segment(1);

            // Individual uses singular 'insurance', others use plural 'insurances'
            $downloadRoute = match($namespace) {
                'individual' => route('individual.insurance.document.download', $insurance),
                default => route($namespace . '.insurances.document.download', $insurance)
            };

            // Conditions download route
            $conditionsRoute = match($namespace) {
                'individual' => route('individual.insurance.conditions.download', $insurance),
                'federation' => route('federation.insurances.document.conditions', $insurance),
                'cmas' => route('admin.insurances.document.conditions', $insurance),
                default => null
            };

            // Check if insurance plan has conditions document
            $hasConditionsDocument = $insurancePlan->getMedia('insurance_attachments')->count() > 0;

            // Different back routes based on namespace
            $backRoute = match($namespace) {
                'federation' => route('federation.individual-insurances.index'),
                'cmas' => route('admin.insurances.index'),
                'individual' => route('individual.insurance.index'),
                default => url()->previous()
            };

            // Insurance status
            $isActive = $insurance->isActive();
            $isExpired = $insurance->end_date && $insurance->end_date->isPast();
        @endphp

        <!-- Back Button -->
        <div class="mb-4">
            @if($backRoute)
                <a href="{{ $backRoute }}" class="inline-flex items-center text-sm text-slate-600 hover:text-slate-900 transition-colors">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    {{ __('main.back') }}
                </a>
            @endif
        </div>

        <!-- Individual Profile Header -->
        <x-individual.profile-hero :individual="$individual" />

        <!-- Insurance Status Banner -->
        <div class="mb-8 -mt-2">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <!-- Status Header -->
                <div class="px-6 py-5 {{ $isActive && !$isExpired ? 'bg-gradient-to-r from-slate-700 to-slate-600' : ($isExpired ? 'bg-gradient-to-r from-slate-600 to-slate-500' : 'bg-gradient-to-r from-amber-500 to-amber-400') }}">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <div>
                                <h1 class="text-xl sm:text-2xl font-bold text-white">{{ $insurancePlan->name }}</h1>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $isActive && !$isExpired ? 'bg-green-500 text-white' : ($isExpired ? 'bg-red-500 text-white' : 'bg-amber-100 text-amber-800') }}">
                                        @if($isActive && !$isExpired)
                                            <span class="w-1.5 h-1.5 bg-white rounded-full mr-1.5 animate-pulse"></span>
                                            {{ __('main.active') }}
                                        @elseif($isExpired)
                                            {{ __('main.expired') }}
                                        @else
                                            {{ __('main.pending') }}
                                        @endif
                                    </span>
                                    @if($policyNumber)
                                        <span class="text-white/80 text-sm font-mono">{{ $policyNumber }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <!-- Action Buttons -->
                        <div class="flex flex-wrap gap-2">
                            @if($downloadRoute)
                                <a href="{{ $downloadRoute }}" class="inline-flex items-center px-4 py-2 bg-white text-slate-800 rounded-lg font-medium text-sm hover:bg-slate-50 transition-colors shadow-sm">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    {{ __('insurance.download_insurance') }}
                                </a>
                            @endif
                            @if($hasConditionsDocument && $conditionsRoute)
                                <a href="{{ $conditionsRoute }}" class="inline-flex items-center px-4 py-2 bg-white/20 text-white rounded-lg font-medium text-sm hover:bg-white/30 transition-colors backdrop-blur-sm">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    {{ __('insurance.download_conditions') }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Warning Message -->
        @if(!$policyNumber)
            <div class="mb-6 bg-amber-50 border border-amber-200 rounded-xl p-4">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <svg class="w-5 h-5 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-semibold text-amber-800">{{ __('common.warning') }}</h4>
                        <p class="text-sm text-amber-700 mt-1">{{ __('main.insurance_policy_number_warning') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Insurance Details Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- Left Column: Personal Information -->
            <div class="space-y-6">

                <!-- Insured Personal Data & Contact -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-blue-100 rounded-lg">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-slate-800">{{ __('insurance.insured_data') }}</h3>
                                <p class="text-xs text-slate-500">{{ __('insurance.insured_personal_information') ?? 'Insured Data' }}</p>
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
                            <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-4">{{ __('insurance.insured_contacts') }}</h4>
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

            <!-- Right Column: Insurance Information -->
            <div class="space-y-6">

                <!-- Insurance Plan Details -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-green-100 rounded-lg">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-slate-800">{{ __('insurance.insurance_plan') }}</h3>
                                <p class="text-xs text-slate-500">{{ __('insurance.plan_details') ?? 'Plan Details' }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <dl class="space-y-4">
                            <div>
                                <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('insurance.plan_name') }}</dt>
                                <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $insurancePlan->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('main.policy_number') }}</dt>
                                <dd class="mt-1 text-sm font-mono {{ $policyNumber ? 'text-slate-900' : 'text-slate-400 italic' }}">{{ $policyNumber ?: __('main.not_available') }}</dd>
                            </div>
                            @if($insurancePlan->description)
                                <div>
                                    <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('main.description') }}</dt>
                                    <dd class="mt-1 text-sm text-slate-700 leading-relaxed">{{ $insurancePlan->description }}</dd>
                                </div>
                            @endif
                        </dl>

                        <!-- Insurance Period -->
                        <div class="mt-6 pt-6 border-t border-slate-100">
                            <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-4">{{ __('insurance.insurance_period') }}</h4>
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
                        </div>

                        <!-- Coverage Details -->
                        <div class="mt-6 pt-6 border-t border-slate-100">
                            <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-4">{{ __('insurance.coverage_details') ?? 'Coverage' }}</h4>
                            <div class="space-y-3">
                                @if($territorialScope)
                                    <div class="flex items-start gap-3 p-3 bg-slate-50 rounded-lg">
                                        <svg class="w-5 h-5 text-slate-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <div>
                                            <dt class="text-xs font-medium text-slate-500">{{ __('insurance.territorial_coverage') }}</dt>
                                            <dd class="text-sm font-medium text-slate-900">{{ $territorialScope }}</dd>
                                        </div>
                                    </div>
                                @endif
                                @if($insuredActivity)
                                    <div class="flex items-start gap-3 p-3 bg-slate-50 rounded-lg">
                                        <svg class="w-5 h-5 text-slate-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                        </svg>
                                        <div>
                                            <dt class="text-xs font-medium text-slate-500">{{ __('insurance.insured_activities') }}</dt>
                                            <dd class="text-sm font-medium text-slate-900">{{ $insuredActivity }}</dd>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Insurance Company Contact -->
                @if(($insurancePlan->insurance_company_name ?? false) || ($insurancePlan->insurance_company_phone ?? false) || ($insurancePlan->insurance_company_email ?? false))
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-purple-100 rounded-lg">
                                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-slate-800">{{ __('insurance.insurance_company_contacts') }}</h3>
                                    <p class="text-xs text-slate-500">{{ __('insurance.insurer_information') ?? 'Insurer Information' }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-6">
                            <dl class="space-y-3">
                                @if($insurancePlan->insurance_company_name ?? false)
                                    <div class="flex items-center gap-3">
                                        <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                        <div>
                                            <dt class="text-xs text-slate-500">{{ __('insurance.company_name') }}</dt>
                                            <dd class="text-sm font-semibold text-slate-900">{{ $insurancePlan->insurance_company_name }}</dd>
                                        </div>
                                    </div>
                                @endif
                                @if($insurancePlan->insurance_company_phone ?? false)
                                    <div class="flex items-center gap-3">
                                        <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                        </svg>
                                        <div>
                                            <dt class="text-xs text-slate-500">{{ __('main.phone') }}</dt>
                                            <dd class="text-sm text-slate-900">{{ $insurancePlan->insurance_company_phone }}</dd>
                                        </div>
                                    </div>
                                @endif
                                @if($insurancePlan->insurance_company_email ?? false)
                                    <div class="flex items-center gap-3">
                                        <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                        <div>
                                            <dt class="text-xs text-slate-500">{{ __('main.email') }}</dt>
                                            <dd class="text-sm text-slate-900">{{ $insurancePlan->insurance_company_email }}</dd>
                                        </div>
                                    </div>
                                @endif
                                @if($insurancePlan->insurance_company_address ?? false)
                                    <div class="flex items-start gap-3">
                                        <svg class="w-4 h-4 text-slate-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        <div>
                                            <dt class="text-xs text-slate-500">{{ __('main.address') }}</dt>
                                            <dd class="text-sm text-slate-900">{{ $insurancePlan->insurance_company_address }}</dd>
                                        </div>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
</x-layout>
