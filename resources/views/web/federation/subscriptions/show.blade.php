<x-layout>
    <div class="previous-layout-classes">
        <!-- Page Header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-5 mt-5">
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('federation.subscription_details') }}</h1>
                <p class="text-gray-500 text-sm">{{ __('federation.entity_subscription_details_description') }}</p>
            </div>
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a href="{{ route('federation.entity-subscriptions.index') }}" class="btn btn-secondary">
                    {{ __('common.back') }}
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Subscription Information -->
                <div class="card">
                    <h2 class="text-xl leading-snug text-slate-800 font-bold mb-5">{{ __('federation.subscription_information') }}</h2>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">{{ __('federation.entity') }}</label>
                            <p class="text-sm text-slate-600">{{ $subscription->member->name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">{{ __('federation.entity_code') }}</label>
                            <p class="text-sm text-slate-600">{{ $subscription->member->code }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">{{ __('federation.package') }}</label>
                            <p class="text-sm text-slate-600">{{ $subscription->membershipPackage->name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">{{ __('common.status') }}</label>
                            @php
                                $statusClass = 'bg-gray-400';
                                $statusText = __('common.unknown');
                                
                                if(str_contains($subscription->status_class, 'Active')) {
                                    $statusClass = 'bg-admin_green';
                                    $statusText = __('common.active');
                                } elseif(str_contains($subscription->status_class, 'PendingPayment')) {
                                    $statusClass = 'bg-yellow-400';
                                    $statusText = __('federation.pending_payment');
                                }
                            @endphp
                            <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white rounded-full {{ $statusClass }}">
                                {{ $statusText }}
                            </span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">{{ __('federation.start_date') }}</label>
                            <p class="text-sm text-slate-600">{{ $subscription->start_date->format('d/m/Y') }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">{{ __('federation.end_date') }}</label>
                            <p class="text-sm text-slate-600">{{ $subscription->end_date->format('d/m/Y') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Package Details -->
                <div class="card">
                    <h2 class="text-xl leading-snug text-slate-800 font-bold mb-5">{{ __('federation.package_details') }}</h2>
                    
                    @if($subscription->membershipPackage->description)
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">{{ __('common.description') }}</label>
                            <p class="text-sm text-slate-600">{{ $subscription->membershipPackage->description }}</p>
                        </div>
                    @endif

                    @if($subscription->membershipPackage->affiliationPlans->count() > 0)
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">{{ __('federation.affiliation_plans') }}</label>
                            <div class="space-y-2">
                                @foreach($subscription->membershipPackage->affiliationPlans as $plan)
                                    <div class="panel-box">
                                        <h3 class="grow font-semibold text-slate-800 truncate mb-3">{{ $plan->name }}</h3>
                                        @if($plan->description)
                                            <p class="text-sm text-slate-600 mb-2">{{ $plan->description }}</p>
                                        @endif
                                        <div class="text-xs text-slate-500">
                                            {{ __('federation.entity_fee') }}: €{{ number_format($plan->entity_fee ?? 0, 2) }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($subscription->membershipPackage->insurancePlans->count() > 0)
                        <div>
                            <label class="block text-sm font-medium mb-1">{{ __('federation.insurance_plans') }}</label>
                            <div class="space-y-2">
                                @foreach($subscription->membershipPackage->insurancePlans as $plan)
                                    <div class="panel-box">
                                        <h3 class="grow font-semibold text-slate-800 truncate mb-3">{{ $plan->name }}</h3>
                                        @if($plan->description)
                                            <p class="text-sm text-slate-600 mb-2">{{ $plan->description }}</p>
                                        @endif
                                        <div class="text-xs text-slate-500">
                                            {{ __('federation.entity_fee') }}: €{{ number_format($plan->entity_fee ?? 0, 2) }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Actions -->
                <div class="card">
                    <h3 class="grow font-semibold text-slate-800 truncate mb-3">{{ __('common.actions') }}</h3>
                    
                    <div class="space-y-3">
                        <a href="{{ route('federation.entity-subscriptions.index') }}" class="btn btn-secondary w-full">
                            {{ __('common.back_to_list') }}
                        </a>
                    </div>
                </div>

                <!-- Summary -->
                <div class="card">
                    <h3 class="grow font-semibold text-slate-800 truncate mb-3">{{ __('federation.subscription_summary') }}</h3>
                    
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-slate-500">{{ __('federation.request_type') }}:</span>
                            <span class="text-slate-800">{{ __('federation.federation_facilitated') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">{{ __('federation.payment_responsibility') }}:</span>
                            <span class="text-slate-800">{{ $subscription->member->name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">{{ __('federation.document_recipient') }}:</span>
                            <span class="text-slate-800">{{ $subscription->member->name }}</span>
                        </div>
                        @if($subscription->membershipPackage->affiliationPlans->count() > 0)
                            <div class="flex justify-between">
                                <span class="text-slate-500">{{ __('federation.total_affiliation_fee') }}:</span>
                                <span class="text-slate-800">€{{ number_format($subscription->membershipPackage->affiliationPlans->sum('entity_fee'), 2) }}</span>
                            </div>
                        @endif
                        @if($subscription->membershipPackage->insurancePlans->count() > 0)
                            <div class="flex justify-between">
                                <span class="text-slate-500">{{ __('federation.total_insurance_fee') }}:</span>
                                <span class="text-slate-800">€{{ number_format($subscription->membershipPackage->insurancePlans->sum('entity_fee'), 2) }}</span>
                            </div>
                        @endif
                        <hr class="border-slate-200">
                        <div class="flex justify-between font-semibold">
                            <span class="text-slate-800">{{ __('federation.total_package_fee') }}:</span>
                            <span class="text-slate-800">€{{ number_format($subscription->membershipPackage->calculatePriceFor(\Domain\Entities\Models\Entity::class), 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Information Notice -->
                <div class="information-box">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-indigo-500 mt-0.5 mr-3 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                        <div class="text-sm">
                            <p class="text-slate-800 font-medium">{{ __('federation.facilitation_notice') }}</p>
                            <p class="text-slate-600 mt-1">{{ __('federation.entity_subscription_notice') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>