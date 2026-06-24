@section('title', __('federation.insurance_details'))
<x-layout>
    <div class="previous-layout-classes">
        
        <!-- Page Header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-5 mt-5">
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('federation.insurance_details') }}</h1>
            </div>
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                @if($insurance->member_type === 'individual')
                    <a href="{{ route('admin.insurances.document.download', $insurance->id) }}" class="btn btn-success">
                        {{ __('federation.download_insurance_document') }}
                    </a>
                @endif
                <a href="{{ route('federation.entity-insurances.index') }}" class="btn btn-secondary">
                    {{ __('common.back') }}
                </a>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Left Column - Insurance Information -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Entity Information Card -->
                <div class="card">
                    <h2 class="text-xl leading-snug text-slate-800 font-bold mb-5">{{ __('federation.entity_information') }}</h2>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">{{ __('federation.entity_name') }}</label>
                            <p class="text-sm text-slate-600">{{ $entity->name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">{{ __('federation.entity_code') }}</label>
                            <p class="text-sm text-slate-600">{{ $entity->code ?? __('common.na') }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">{{ __('federation.entity_type') }}</label>
                            <p class="text-sm text-slate-600">{{ __('federation.' . strtolower($entity->type)) }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">{{ __('federation.location') }}</label>
                            <p class="text-sm text-slate-600">{{ $entity->location ?? __('common.na') }}</p>
                        </div>
                    </div>

                    @if($entity->address || $entity->postal_code)
                        <div class="mt-4">
                            <label class="block text-sm font-medium mb-1">{{ __('federation.address') }}</label>
                            <p class="text-sm text-slate-600">
                                {{ $entity->address }}<br>
                                {{ $entity->postal_code }} {{ $entity->location }}
                            </p>
                        </div>
                    @endif
                </div>

                <!-- Insurance Plan Information Card -->
                <div class="card">
                    <h2 class="text-xl leading-snug text-slate-800 font-bold mb-5">{{ __('federation.insurance_plan_information') }}</h2>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">{{ __('federation.plan_name') }}</label>
                            <p class="text-sm text-slate-600 font-medium">{{ $insurance->insurancePlan->name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">{{ __('federation.policy_number') }}</label>
                            <p class="text-sm text-slate-600 font-medium">{{ $insurance->policy_number }}</p>
                        </div>
                    </div>

                    @if($insurance->insurancePlan->description)
                        <div class="mt-4">
                            <label class="block text-sm font-medium mb-1">{{ __('federation.plan_description') }}</label>
                            <p class="text-sm text-slate-600">{{ $insurance->insurancePlan->description }}</p>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">{{ __('federation.individual_fee') }}</label>
                            <p class="text-sm text-slate-600">
                                @if($insurance->individual_fee)
                                    € {{ number_format($insurance->individual_fee, 2, ',', '.') }}
                                @else
                                    {{ __('common.na') }}
                                @endif
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">{{ __('federation.entity_fee') }}</label>
                            <p class="text-sm text-slate-600">
                                @if($insurance->entity_fee)
                                    € {{ number_format($insurance->entity_fee, 2, ',', '.') }}
                                @else
                                    {{ __('common.na') }}
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Coverage Period Card -->
                <div class="card">
                    <h2 class="text-xl leading-snug text-slate-800 font-bold mb-5">{{ __('federation.coverage_details') }}</h2>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">{{ __('federation.start_date') }}</label>
                            <p class="text-sm text-slate-600">{{ __('federation.from_time', ['date' => $startDateFormatted]) }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">{{ __('federation.end_date') }}</label>
                            <p class="text-sm text-slate-600">{{ __('federation.until_time', ['date' => $endDateFormatted]) }}</p>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="block text-sm font-medium mb-1">{{ __('federation.coverage_duration') }}</label>
                        <p class="text-sm text-slate-600">
                            {{ (int) $insurance->start_date->diffInDays($insurance->end_date) }} {{ __('federation.days') }}
                        </p>
                    </div>
                </div>

            </div>

            <!-- Right Column - Status and Additional Info -->
            <div class="space-y-6">
                
                <!-- Status Card -->
                <div class="card">
                    <h3 class="grow font-semibold text-slate-800 truncate mb-3">{{ __('federation.status_information') }}</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">{{ __('federation.insurance_status') }}</label>
                            @php
                                $statusClass = class_basename($insurance->status_class);
                                $statusColor = match($statusClass) {
                                    'ActiveInsuranceState' => 'bg-emerald-100 text-emerald-600',
                                    'InactiveInsuranceState' => 'bg-slate-100 text-slate-600',
                                    'ExpiredInsuranceState' => 'bg-red-100 text-red-600',
                                    default => 'bg-slate-100 text-slate-600'
                                };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                {{ $insurance->stateName() }}
                            </span>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">{{ __('federation.request_type') }}</label>
                            <p class="text-sm text-slate-600">{{ __('federation.request_type_' . $insurance->request_type) }}</p>
                        </div>

                        @if($insurance->is_external)
                            <div>
                                <label class="block text-sm font-medium mb-1">{{ __('federation.insurance_type') }}</label>
                                <p class="text-sm text-slate-600">{{ __('federation.external_insurance') }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Related Subscription Card (if exists) -->
                @if($insurance->memberSubscription)
                    <div class="card">
                        <h3 class="grow font-semibold text-slate-800 truncate mb-3">{{ __('federation.related_subscription') }}</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">{{ __('federation.package_name') }}</label>
                                <p class="text-sm text-slate-600">{{ $insurance->memberSubscription->membershipPackage->name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">{{ __('federation.subscription_status') }}</label>
                                @php
                                    $subStatusClass = class_basename($insurance->memberSubscription->status_class);
                                    $subStatusColor = match($subStatusClass) {
                                        'ActiveMemberSubscriptionState' => 'bg-emerald-100 text-emerald-600',
                                        'PendingPaymentMemberSubscriptionState' => 'bg-amber-100 text-amber-600',
                                        'ExpiredMemberSubscriptionState' => 'bg-slate-100 text-slate-600',
                                        default => 'bg-slate-100 text-slate-600'
                                    };
                                @endphp
                                @php
                                    $subStatusText = match($subStatusClass) {
                                        'ActiveMemberSubscriptionState' => __('common.active'),
                                        'PendingPaymentMemberSubscriptionState' => __('federation.pending_payment'),
                                        'ExpiredMemberSubscriptionState' => __('common.expired'),
                                        default => __('common.inactive')
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $subStatusColor }}">
                                    {{ $subStatusText }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Timestamps Card -->
                <div class="card">
                    <h3 class="grow font-semibold text-slate-800 truncate mb-3">{{ __('federation.record_information') }}</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">{{ __('common.created_at') }}</label>
                            <p class="text-sm text-slate-600">{{ $insurance->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">{{ __('common.updated_at') }}</label>
                            <p class="text-sm text-slate-600">{{ $insurance->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</x-layout>