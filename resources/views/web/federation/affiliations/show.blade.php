@section('title', __('federation.affiliation_plan_details'))
<x-layout>
    <div class="previous-layout-classes">
        <!-- Page Header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-5 mt-5">
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('federation.affiliation_plan_details') }}</h1>
                <p class="text-gray-500 text-sm">{{ __('federation.affiliation_details_description') }}</p>
            </div>
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a href="{{ route('federation.entity-affiliations.index') }}" class="btn btn-secondary">
                    {{ __('common.back') }}
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">
            <!-- Main Content -->
            <div class="xl:col-span-3 space-y-6">
                <!-- 1. Informacao da Entidade -->
                <div class="card">
                    <div class="flex items-center gap-5">
                        <!-- Entity Logo -->
                        <div class="shrink-0">
                            @if($affiliation->member->getFirstMediaUrl('profile', 'thumb'))
                                <img class="h-16 w-16 rounded-xl object-cover border border-slate-200"
                                     src="{{ $affiliation->member->getFirstMediaUrl('profile', 'thumb') }}"
                                     alt="{{ $affiliation->member->name }}">
                            @else
                                <div class="h-16 w-16 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center">
                                    <svg class="h-8 w-8 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" />
                                    </svg>
                                </div>
                            @endif
                        </div>

                        <!-- Entity Details -->
                        <div class="flex-1 min-w-0">
                            <h2 class="text-xl leading-snug text-slate-800 font-bold">{{ __('federation.entity_information') }}</h2>
                            <a href="{{ route('federation.entity.show', $affiliation->member->id) }}" class="text-base font-medium text-indigo-600 hover:text-indigo-700 mt-1 block">
                                {{ $affiliation->member->name }}
                            </a>
                            <div class="flex flex-wrap items-center gap-x-6 gap-y-1 mt-2">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm text-slate-500">{{ __('federation.entity_affiliate_number') }}:</span>
                                    <span class="text-sm font-medium text-slate-800">{{ $affiliation->member->member_number ?? '-' }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm text-slate-500">{{ __('federation.entity_district') }}:</span>
                                    <span class="text-sm font-medium text-slate-800">{{ $affiliation->member->district->name ?? '-' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Detalhes do Plano de Filiacao -->
                <div class="card">
                    <h2 class="text-xl leading-snug text-slate-800 font-bold mb-5">{{ __('federation.affiliation_plan_details') }}</h2>

                    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
                        <div class="bg-slate-50 rounded-lg p-4">
                            <label class="block text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">{{ __('federation.plan_name') }}</label>
                            <p class="text-sm font-medium text-slate-800">{{ $affiliation->affiliationPlan->name ?? '-' }}</p>
                        </div>
                        <div class="bg-slate-50 rounded-lg p-4">
                            <label class="block text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">{{ __('federation.start_date') }}</label>
                            <p class="text-sm font-medium text-slate-800">{{ $affiliation->start_date?->format('d/m/Y') ?? '-' }}</p>
                        </div>
                        <div class="bg-slate-50 rounded-lg p-4">
                            <label class="block text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">{{ __('federation.end_date') }}</label>
                            <p class="text-sm font-medium text-slate-800">{{ $affiliation->end_date?->format('d/m/Y') ?? '-' }}</p>
                        </div>
                        <div class="bg-slate-50 rounded-lg p-4">
                            <label class="block text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">{{ __('federation.activation_date') }}</label>
                            <p class="text-sm font-medium text-slate-800">{{ $affiliation->activation_date?->format('d/m/Y') ?? '-' }}</p>
                        </div>
                        <div class="bg-slate-50 rounded-lg p-4">
                            <label class="block text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">{{ __('federation.organization') }}</label>
                            <p class="text-sm font-medium text-slate-800">{{ $affiliation->affiliationPlan->federation->name ?? '-' }}</p>
                        </div>
                        @if($affiliation->memberSubscription)
                        <div class="bg-slate-50 rounded-lg p-4">
                            <label class="block text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">{{ __('federation.package_name') }}</label>
                            <p class="text-sm font-medium text-slate-800">{{ $affiliation->memberSubscription->membershipPackage->name ?? '-' }}</p>
                        </div>
                        @endif
                    </div>

                    @if($affiliation->affiliationPlan && $affiliation->affiliationPlan->description)
                        <div class="mt-4 bg-slate-50 rounded-lg p-4">
                            <label class="block text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">{{ __('common.description') }}</label>
                            <p class="text-sm text-slate-700">{{ $affiliation->affiliationPlan->description }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Sidebar -->
            <div class="xl:col-span-1 space-y-6">
                <!-- 3. Resumo do Pagamento -->
                <div class="card">
                    <h3 class="text-lg font-semibold text-slate-800 mb-4">{{ __('federation.payment_summary') }}</h3>

                    <div class="space-y-3">
                        @if($affiliation->requester)
                        <div>
                            <span class="block text-xs text-slate-500 mb-0.5">{{ __('federation.requested_by') }}</span>
                            <span class="text-sm font-medium text-slate-800">{{ $affiliation->requester->name }}</span>
                        </div>
                        @endif
                        <div>
                            <span class="block text-xs text-slate-500 mb-0.5">{{ __('common.created_at') }}</span>
                            <span class="text-sm font-medium text-slate-800">{{ $affiliation->created_at->format('d/m/Y') }}</span>
                        </div>
                        <div class="border-t border-slate-200 pt-3 mt-3">
                            <span class="block text-xs text-slate-500 mb-0.5">{{ __('federation.affiliation_fee') }}</span>
                            <span class="text-xl font-bold text-indigo-600">{{ number_format($affiliation->entity_fee ?? 0, 2) }}&#8364;</span>
                        </div>
                    </div>
                </div>

                <!-- 4. Informacao da Filiacao -->
                <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4">
                    <div class="flex items-start gap-3">
                        <div class="shrink-0 w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-indigo-900">{{ __('federation.affiliation_information') }}</p>
                            <p class="text-sm text-indigo-700 mt-1">{{ __('federation.affiliation_info_notice') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>
