@props([
    'entity',
    'context' => null,
    'showUserAccount' => false,
    'showDocuments' => false,
    'documents' => null
])

@php
    $isFederationContext = $context === 'federation';
    $isAdminContext = $context === 'admin';
    $namespace = Request::segment(1);

    $individualsCount = $entity->individuals->count();
    $federationsCount = $entity->federations->count();
    $documentsCount = $documents ? $documents->count() : 0;

    // Collect all insurances from member subscriptions
    $allInsurances = collect();
    if ($entity->memberSubscriptions) {
        $allInsurances = $entity->memberSubscriptions
            ->flatMap(fn($subscription) => $subscription->insurances ?? collect())
            ->filter(fn($insurance) => $insurance !== null);
    }
    $insurancesCount = $allInsurances->count();

    // Collect all active affiliations from member subscriptions
    $activeAffiliations = collect();
    if ($entity->memberSubscriptions) {
        $activeAffiliations = $entity->memberSubscriptions->flatMap(function($subscription) {
            return $subscription->affiliations()->with('federation', 'memberSubscription.membershipPackage.affiliationPlans')->get();
        })->filter(function($affiliation) {
            return $affiliation && method_exists($affiliation, 'isActive') && $affiliation->isActive();
        });
    }
    $membershipAffiliationsCount = $activeAffiliations->count();

    // Define tabs configuration
    $tabsConfig = [
        'overview' => [
            'label' => __('main.Overview'),
            'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>'
        ],
        'individuals' => [
            'label' => __('entity.individuals'),
            'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>',
            'badge' => $individualsCount > 0 ? $individualsCount : null
        ],
        'associations' => [
            'label' => __('entity.federation_and_associations'),
            'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"></path></svg>',
            'badge' => $federationsCount > 0 ? $federationsCount : null
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

    // Always add documents tab when showDocuments is true
    if ($showDocuments) {
        $tabsConfig['documents'] = [
            'label' => __('entity.documents_invoices'),
            'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>',
            'badge' => $documentsCount > 0 ? $documentsCount : null
        ];
    }
@endphp

<div class="w-full">
    <x-ui.tabs :tabs="$tabsConfig" defaultTab="overview">
        <!-- Overview Tab -->
        <x-slot name="tab_overview">
            <div class="space-y-6">
                <!-- Profile Info -->
                <x-entity.profile-info :entity="$entity" />

                <!-- User Account Info (if international context) -->
                @if ($showUserAccount && $isAdminContext && $entity->users->first())
                    @php $entityUser = $entity->users->first(); @endphp
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h3 class="font-semibold text-gray-900 mb-4">{{ __('main.User Account Information') }}</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">{{ __('main.Email') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $entityUser->email }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">{{ __('main.Account Status') }}</dt>
                                <dd class="mt-1">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $entityUser->active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $entityUser->active ? __('main.Active') : __('main.Inactive') }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">{{ __('main.Last Login') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ $entityUser->last_login_at ? $entityUser->last_login_at->format('d/m/Y H:i') : __('main.Never') }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">{{ __('main.Email Verified') }}</dt>
                                <dd class="mt-1">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $entityUser->email_verified_at ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ $entityUser->email_verified_at ? __('main.Verified') : __('main.Pending') }}
                                    </span>
                                </dd>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </x-slot>

        <!-- Individuals Tab -->
        <x-slot name="tab_individuals">
            <div class="space-y-6">
                <!-- Individuals Section -->
                <div class="bg-white border border-gray-200 rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">{{ __('entity.individuals') }}</h3>
                            <p class="mt-1 text-sm text-gray-500">{{ $individualsCount }} {{ __('entity.active') }}</p>
                        </div>
                        @if($individualsCount > 0)
                        <a href="{{ route($namespace . '.individual.index', ['filter[filter_entity]' => $entity->id]) }}"
                           class="btn btn-outline btn-sm">
                            {{ __('entity.view_all') }}
                        </a>
                        @endif
                    </div>

                    @if($entity->individuals->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('entity.table_name') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('main.Member Code') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($entity->individuals->take(10) as $individual)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <img class="h-10 w-10 rounded-full object-cover"
                                                     src="{{ $individual->getFirstMediaUrl('profile', 'thumb') ?: asset('img/user_placeholder.png') }}"
                                                     alt="">
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $individual->name }} {{ $individual->surname }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $individual->member_code }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('entity.no_individuals_yet') }}</h3>
                    </div>
                    @endif
                </div>
            </div>
        </x-slot>

        <!-- Associations Tab -->
        <x-slot name="tab_associations">
            <div class="space-y-6">
                @if($entity->federations->count() > 0)
                <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">{{ __('entity.federation_and_associations') }}</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('entity.table_association') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('entity.table_type') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('entity.table_status') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($entity->federations as $federation)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $federation->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($federation->isTerritorialAssociation())
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                                {{ __('entity.association_type_territorial') }}
                                            </span>
                                        @elseif($federation->isMainFederation())
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary/10 text-primary">
                                                {{ __('entity.association_type_nacional') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ __('entity.association_type_modalidade') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <x-ux-badge-component :status="$entity->getFederationStateNameAttribute($federation)" :color="$entity->getFederationStateColorAttribute($federation)" />
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('entity.no_association_memberships') }}</h3>
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
                        <p class="mt-1 text-sm text-gray-500">{{ __('main.This entity has no active membership affiliations.') }}</p>
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
                        <p class="mt-1 text-sm text-gray-500">{{ __('main.This entity has no insurance policies.') }}</p>
                    </div>
                @endif
            </div>
        </x-slot>

        <!-- Documents Tab -->
        @if($showDocuments)
        <x-slot name="tab_documents">
            <div class="space-y-6">
                <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">{{ __('entity.documents_invoices') }}</h3>
                            @if($documentsCount > 0)
                                <p class="mt-1 text-sm text-gray-500">{{ __('entity.showing_last_documents', ['count' => $documentsCount]) }}</p>
                            @endif
                        </div>
                        <a href="{{ route($namespace . '.document.index', ['filter[owner_id]' => $entity->id]) }}"
                           class="btn btn-outline btn-sm inline-flex items-center gap-2 group">
                            <span>{{ __('entity.view_all') }}</span>
                            <svg class="w-4 h-4 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    </div>

                    @if($documentsCount > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50/80">
                                <tr>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">{{ __('entity.table_number') }}</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">{{ __('entity.table_date') }}</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">{{ __('entity.table_status') }}</th>
                                    <th class="px-6 py-3.5 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">{{ __('entity.table_total') }}</th>
                                    <th class="px-6 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">{{ __('entity.table_actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @foreach($documents as $document)
                                <tr class="hover:bg-gray-50/50 transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm font-semibold text-gray-900 font-mono tracking-tight">
                                            {{ $document->invoice_extended ?? $document->number_extended }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ optional($document->created_at)->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <x-tables.badge :color="$document->stateColor()" :status="__('documents.states.' . $document->stateName())" />
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <span class="text-sm font-semibold text-gray-900">{{ number_format($document->total_value, 2, ',', '.') }}</span>
                                        <span class="text-xs text-gray-500 ml-0.5">EUR</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <a href="{{ route($namespace . '.document.show', $document->id) }}"
                                           class="inline-flex items-center gap-1 text-primary hover:text-primary/80 text-sm font-medium transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            {{ __('entity.view') }}
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-16 px-6">
                        <div class="mx-auto w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-medium text-gray-900 mb-1">{{ __('entity.no_documents_found') }}</h3>
                        <p class="text-sm text-gray-500 max-w-sm mx-auto">{{ __('entity.no_documents_description') }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </x-slot>
        @endif
    </x-ui.tabs>
</div>
