@props([
    'individual',
    'context' => null,
    'showUserAccount' => false,
    'showProfessionalRoles' => false,
    'showDocuments' => false,
    'loggedInFederation' => null,
    'official_documents' => null,
    'payment_documents' => null
])

@php
    $isFederationContext = $context === 'federation';
    $isAdminContext = $context === 'admin';
    $isEntityContext = $context === 'entity';
    
    // Calculate badges for tabs
    $certificationsCount = $individual->certificationsDivingAttributed->count() +
                          $individual->certificationsScientificAttributed->count() +
                          $individual->certificationsSportAttributed->count();

    $licensesCount = $individual->licensesDivingAttributed->count() +
                     $individual->licensesScientificAttributed->count() +
                     $individual->licensesSportAttributed->count();

    // For entity context, only count federations (don't show entities panel)
    // For admin/federation/cmas, count both federations and entities
    $affiliationsCount = $isEntityContext
        ? $individual->individualFederations->count()
        : $individual->individualFederations->count() + $individual->individualEntities->count();
    
    // Collect all insurances from member subscriptions (not just active ones)
    // The insurance-table component handles displaying different statuses
    $allInsurances = collect();
    if ($individual->memberSubscriptions) {
        $allInsurances = $individual->memberSubscriptions
            ->flatMap(function($subscription) {
                return $subscription->insurances ?? collect();
            })
            ->filter(function($insurance) {
                return $insurance !== null;
            });
    }
    $activeAffiliations = collect();
    if ($individual->memberSubscriptions) {
        $activeAffiliations = $individual->memberSubscriptions->flatMap(function($subscription) {
            return $subscription->affiliations()->with('federation', 'memberSubscription.membershipPackage.affiliationPlans')->get();
        })->filter(function($affiliation) {
            return $affiliation && method_exists($affiliation, 'isActive') && $affiliation->isActive();
        });
    }
    $insurancesCount = $allInsurances->count();
    $membershipAffiliationsCount = $activeAffiliations->count();
    
    // Define tabs configuration
    $tabsConfig = [
        'overview' => [
            'label' => __('main.Overview'),
            'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>'
        ],
        'affiliations' => [
            'label' => $isEntityContext ? __('main.Organizations') : __('main.Affiliations'),
            'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>',
            'badge' => $affiliationsCount > 0 ? $affiliationsCount : null
        ],
        'certifications' => [
            'label' => __('main.Certifications'),
            'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>',
            'badge' => $certificationsCount > 0 ? $certificationsCount : null
        ],
        'licenses' => [
            'label' => __('main.Licenses'),
            'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path></svg>',
            'badge' => $licensesCount > 0 ? $licensesCount : null
        ],
        'insurances' => [
            'label' => __('main.Insurances'),
            'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>',
            'badge' => $insurancesCount > 0 ? $insurancesCount : null
        ],
        'membership_affiliations' => [
            'label' => __('main.Membership Affiliations'),
            'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
            'badge' => $membershipAffiliationsCount > 0 ? $membershipAffiliationsCount : null
        ]
    ];
    
    // Add documents tab if allowed
    if ($showDocuments) {
        $tabsConfig['documents'] = [
            'label' => __('main.Documents'),
            'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>'
        ];
    }
    
    // Add invoices tab if in international or Federation context and has payment documents
    if (($isAdminContext || $isFederationContext) && $payment_documents && $payment_documents->isNotEmpty()) {
        $tabsConfig['invoices'] = [
            'label' => __('main.Invoices'),
            'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>',
            'badge' => $payment_documents->count()
        ];
    }
    
    // Add diving tabs if individual has diving data and in international context
    if ($isAdminContext) {
        $divingCertsCount = $individual->divingProfessionalCertifications->count();
        $divingDirectorCount = $individual->divingTechnicalDirectorAssignments()
            ->where('status_class', 'Domain\Diving\States\AssignedDivingTechnicalDirectorState')
            ->count();
        
        if ($divingCertsCount > 0 || $divingDirectorCount > 0) {
            $tabsConfig['diving'] = [
                'label' => __('diving.diving_certifications'),
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>',
                'badge' => $divingCertsCount + $divingDirectorCount
            ];
        }
    }
@endphp

<div class="w-full">
    <x-ui.tabs :tabs="$tabsConfig" defaultTab="overview">
        <!-- Overview Tab -->
        <x-slot name="tab_overview">
            <div class="space-y-6">
                <!-- Profile Info -->
                <x-individual.profile_info :individual="$individual" />
                
                <!-- User Account Info (if international context) -->
                @if ($showUserAccount && $isAdminContext && $individual->user)
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h3 class="font-semibold text-gray-900 mb-4">{{ __('main.User Account Information') }}</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">{{ __('main.Email') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $individual->user->email }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">{{ __('main.Account Status') }}</dt>
                                <dd class="mt-1">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $individual->user->active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $individual->user->active ? __('main.Active') : __('main.Inactive') }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">{{ __('main.Last Login') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ $individual->user->last_login_at ? $individual->user->last_login_at->format('d/m/Y H:i') : __('main.Never') }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">{{ __('main.Email Verified') }}</dt>
                                <dd class="mt-1">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $individual->user->email_verified_at ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ $individual->user->email_verified_at ? __('main.Verified') : __('main.Pending') }}
                                    </span>
                                </dd>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </x-slot>

        <!-- Affiliations Tab -->
        <x-slot name="tab_affiliations">
            <div class="space-y-6">
                <!-- Federations -->
                @if ($individual->individualFederations->count() > 0)
                    <x-individual.federations_panel :federations="$individual->individualFederations" :individual="$individual" />
                @endif

                <!-- Entities - Only show for admin/federation/cmas contexts, NOT for entity context -->
                @if (!$isEntityContext && $individual->individualEntities->count() > 0)
                    <x-individual.entities_panel :entities="$individual->individualEntities" />
                @endif

                <!-- Professional Roles -->
                @if ($showProfessionalRoles)
                    <div class="bg-gray-50 rounded-lg p-6">
                        @if ($isAdminContext)
                            @livewire('manage-all-professional-roles', ['individual' => $individual])
                        @elseif ($isFederationContext && $loggedInFederation)
                            @livewire('federation.manage-professional-roles', [
                                'individual' => $individual,
                                'federationId' => $loggedInFederation->id,
                                'federationName' => $loggedInFederation->name,
                            ])
                        @endif
                    </div>
                @endif

                @php
                    // For entity context, only check federations for empty state
                    // For other contexts, check both federations and entities
                    $hasNoAffiliations = $isEntityContext
                        ? $individual->individualFederations->count() == 0
                        : $individual->individualFederations->count() == 0 && $individual->individualEntities->count() == 0;
                @endphp

                @if ($hasNoAffiliations)
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('main.No affiliations') }}</h3>
                        <p class="mt-1 text-sm text-gray-500">{{ __('main.This individual has no current affiliations with federations or entities.') }}</p>
                    </div>
                @endif
            </div>
        </x-slot>

        <!-- Certifications Tab -->
        <x-slot name="tab_certifications">
            <div class="space-y-6">
                @if ($individual->certificationsDivingAttributed->count() > 0)
                    <x-individual.certifications_panel
                        :title="__('main.diving_certifications')"
                        committee="diving"
                        :individual="$individual"
                        :certifications="$individual->certificationsDivingAttributed" />
                @endif

                @if ($individual->certificationsScientificAttributed->count() > 0)
                    <x-individual.certifications_panel
                        :title="__('main.scientific_certifications')"
                        committee="scientific"
                        :individual="$individual"
                        :certifications="$individual->certificationsScientificAttributed" />
                @endif

                @if ($individual->certificationsSportAttributed->count() > 0)
                    <x-individual.certifications_panel
                        :title="__('main.sport_certifications')"
                        committee="sport"
                        :individual="$individual"
                        :certifications="$individual->certificationsSportAttributed" />
                @endif
                
                @if ($certificationsCount == 0)
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('main.No certifications') }}</h3>
                        <p class="mt-1 text-sm text-gray-500">{{ __('main.This individual has no certifications yet.') }}</p>
                    </div>
                @endif
            </div>
        </x-slot>

        <!-- Licenses Tab -->
        <x-slot name="tab_licenses">
            <div class="space-y-6">
                @if ($individual->licensesDivingAttributed->count() > 0)
                    <x-individual.licenses_panel
                        :title="__('main.diving_licenses')"
                        :individual="$individual"
                        :licenses="$individual->licensesDivingAttributed" />
                @endif

                @if ($individual->licensesScientificAttributed->count() > 0)
                    <x-individual.licenses_panel
                        :title="__('main.scientific_licenses')"
                        :individual="$individual"
                        :licenses="$individual->licensesScientificAttributed" />
                @endif

                @if ($individual->licensesSportAttributed->count() > 0)
                    <x-individual.licenses_panel
                        :title="__('main.sport_licenses')"
                        :individual="$individual"
                        :licenses="$individual->licensesSportAttributed" />
                @endif
                
                @if ($licensesCount == 0)
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('main.No licenses') }}</h3>
                        <p class="mt-1 text-sm text-gray-500">{{ __('main.This individual has no licenses yet.') }}</p>
                    </div>
                @endif
            </div>
        </x-slot>

        <!-- Insurances Tab -->
        <x-slot name="tab_insurances">
            <div class="space-y-6">
                @if($allInsurances->isNotEmpty())
                    <x-individual.insurance-table :insurances="$allInsurances" :showActions="$isAdminContext" :context="$context" />
                @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('main.No insurances') }}</h3>
                        <p class="mt-1 text-sm text-gray-500">{{ __('main.This individual has no insurance policies.') }}</p>
                    </div>
                @endif
            </div>
        </x-slot>

        <!-- Membership Affiliations Tab -->
        <x-slot name="tab_membership_affiliations">
            <div class="space-y-6">
                @if($activeAffiliations->isNotEmpty())
                    <x-individual.affiliation-table :affiliations="$activeAffiliations" :showActions="$isAdminContext" />
                @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('main.No active membership affiliations') }}</h3>
                        <p class="mt-1 text-sm text-gray-500">{{ __('main.This individual has no active membership affiliations.') }}</p>
                    </div>
                @endif
            </div>
        </x-slot>

        <!-- Documents Tab (if enabled) -->
        @if ($showDocuments)
            <x-slot name="tab_documents">
                <div class="space-y-6">
                    <!-- Document Upload (for international and Entity users) -->
                    @if ($isAdminContext || ($isEntityContext && auth()->check() && auth()->user()->entities()->exists()))
                        @livewire('individual.upload-official-document', ['individual' => $individual])
                    @endif
                    
                    <!-- Existing Documents -->
                    @if($official_documents && $official_documents->isNotEmpty())
                        <x-utility.official_documents_media :attachments="$official_documents" :model="$individual" />
                    @else
                        <div class="text-center py-12 bg-gray-50 rounded-lg">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('main.No documents') }}</h3>
                            <p class="mt-1 text-sm text-gray-500">{{ __('main.No official documents have been uploaded yet.') }}</p>
                        </div>
                    @endif
                </div>
            </x-slot>
        @endif
        
        <!-- Invoices Tab (if in international or Federation context and has payment documents) -->
        @if (($isAdminContext || $isFederationContext) && $payment_documents && $payment_documents->isNotEmpty())
            <x-slot name="tab_invoices">
                <div class="space-y-6">
                    <div class="bg-white border border-gray-200 rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">{{ __('main.Payment History') }}</h3>
                            <p class="mt-1 text-sm text-gray-500">{{ __('main.History of payments and invoices for this individual') }}</p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('main.Document') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('main.Type') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('main.Date') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('main.Amount') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('main.Status') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('main.Payment Method') }}</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('main.Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($payment_documents as $document)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $document->getDisplayName() }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $document->type->name ?? __('main.N/A') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $document->created_at->format('d/m/Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ money($document->total_value, $document->currency) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
                                                      style="background-color: {{ $document->stateColor() }}20; color: {{ $document->stateColor() }};">
                                                    {{ $document->stateName() }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $document->method->name ?? __('main.N/A') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                @if($isAdminContext)
                                                    <a href="{{ route('admin.document.download', $document->id) }}" 
                                                       target="_blank"
                                                       class="text-indigo-600 hover:text-indigo-900 inline-flex items-center">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                        </svg>
                                                        {{ __('main.Download') }}
                                                    </a>
                                                @elseif($isFederationContext)
                                                    <a href="{{ route('federation.document.download', $document->id) }}" 
                                                       target="_blank"
                                                       class="text-indigo-600 hover:text-indigo-900 inline-flex items-center">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                        </svg>
                                                        {{ __('main.Download') }}
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </x-slot>
        @endif
        
        <!-- Diving Tab (if in international context and has diving data) -->
        @if ($isAdminContext && ($individual->divingProfessionalCertifications->count() > 0 || $individual->divingTechnicalDirectorAssignments()->where('status_class', 'Domain\Diving\States\AssignedDivingTechnicalDirectorState')->count() > 0))
            <x-slot name="tab_diving">
                <div class="space-y-6">
                    <!-- non-international Diving Certifications -->
                    <x-individual.diving-certifications :individual="$individual" />
                    
                    <!-- Technical Director Positions -->
                    <x-individual.diving-technical-director-positions :individual="$individual" />
                </div>
            </x-slot>
        @endif
    </x-ui.tabs>
</div>