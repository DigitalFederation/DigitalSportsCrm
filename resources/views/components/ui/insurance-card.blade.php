@props([
    'insurance' => null,
    'package' => null,
    'type' => 'current', // current, available, package
    'showActions' => true,
    'showPrice' => true,
    'showDetails' => true,
    'actionType' => 'subscribe', // subscribe, renew, manage, view
    'compact' => false,
    'context' => null, // individual, entity - will be auto-detected if null
])

@php
    // Auto-detect context if not provided
    if (!$context) {
        $context = Request::is('entity/*') ? 'entity' : 'individual';
    }

    // Determine what we're displaying
    $isPackage = $type === 'package' || $type === 'available';
    $item = $isPackage ? $package : $insurance;
    $plan = $isPackage ? $package : $insurance->insurancePlan;

    // Get status information - use isActive() method which properly checks state pattern
    $isActive = true;
    $isExpired = false;
    $isCurrent = false;

    if (!$isPackage && $insurance) {
        $isActive = $insurance->isActive();
        $isExpired = $insurance->end_date && $insurance->end_date->isPast();
        $isCurrent = $isActive && !$isExpired;
    }

    // Get pricing info - only for packages/available, not for current insurances
    $price = null;
    if ($isPackage && $package) {
        $price = $package->calculated_price ?? $package->insurancePlans->sum($context === 'entity' ? 'entity_fee' : 'individual_fee');
    }

    // Build card classes - remove ring/border for current since we have header
    $cardClasses = 'flex flex-col h-full border border-slate-200 hover:border-slate-300 hover:shadow-md transition-all duration-200 overflow-hidden';
    if ($isExpired) {
        $cardClasses .= ' opacity-75';
    }

    // Header background color based on status
    $headerBg = $isCurrent ? 'bg-green-600' : ($isExpired ? 'bg-slate-600' : '');
@endphp

@if($isCurrent || $isExpired)
    {{-- Card with colored header for current/expired insurances --}}
    <div class="bg-white rounded-lg shadow-lg border border-slate-200 overflow-hidden hover:shadow-xl transition-all duration-300 h-full flex flex-col {{ $isExpired ? 'opacity-75' : '' }}">
        <!-- Colored Header -->
        <div class="{{ $headerBg }} px-6 py-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-white">
                    {{ $plan->name }}
                </h3>
                <div class="flex items-center text-white">
                    @if($isCurrent)
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    @else
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    @endif
                </div>
            </div>
            <div class="mt-1">
                <span class="text-sm text-white/90">
                    @if($isCurrent)
                        {{ __('main.active') }}
                    @else
                        {{ __('main.expired') }}
                    @endif
                </span>
            </div>
        </div>

        <!-- Card Content -->
        <div class="flex-1 p-6 flex flex-col">
@else
    {{-- Original card layout for packages/available --}}
    <x-ui.card
        variant="outlined"
        :size="$compact ? 'compact' : 'default'"
        class="{{ $cardClasses }}"
    >
        <!-- Card Header -->
        <div class="flex-shrink-0">
            <div class="flex items-start justify-between mb-3">
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-slate-900 leading-tight">
                        {{ $plan->name }}
                    </h3>

                    <!-- Status Badges -->
                    <div class="flex items-center gap-2 mt-2 flex-wrap">
                        @if($isPackage)
                            <x-ui.badge variant="blue" size="sm">
                                {{ __('main.available') }}
                            </x-ui.badge>
                        @endif

                        @if($isPackage && $package->insurancePlans->count() > 1)
                            <x-ui.badge variant="gray" size="sm">
                                {{ __('main.package') }}
                            </x-ui.badge>
                        @endif

                        @if($isPackage && $context === 'individual' && $package->hasDocumentRequirements())
                            <x-ui.badge variant="amber" size="sm">
                                <x-heroicon-o-shield-check class="w-3 h-3 mr-1" />
                                {{ __('main.requires_documents') }}
                            </x-ui.badge>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Price Display - only for packages -->
            @if($showPrice && $price && $isPackage)
                <div class="mb-4 pb-4 border-b border-slate-100">
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl font-bold text-slate-900">
                            {{ money($price) }}
                        </span>
                        <span class="text-sm text-slate-500">{{ __('main.per_year') }}</span>
                    </div>
                </div>
            @endif
        </div>
@endif
    
    <!-- Card Body -->
    <div class="flex-1 space-y-4">
        <!-- Description -->
        @if($showDetails && $plan->description)
            <div class="text-sm text-slate-600 leading-relaxed">
                {{ Str::limit($plan->description, $compact ? 80 : 150) }}
            </div>
        @endif
        
        <!-- Insurance Details -->
        @if(!$compact)
            <div class="space-y-3">
                <!-- Policy Information for Current Insurance -->
                @if(!$isPackage && $insurance)
                    <div class="bg-slate-50 rounded-lg p-3">
                        <div class="text-sm text-slate-700 space-y-2">
                            <!-- Policy Number Warning -->
                            @if(!$insurance->policy_number)
                                <div class="bg-amber-50 border border-amber-200 rounded-md p-2 mb-2">
                                    <div class="flex items-start gap-2">
                                        <x-heroicon-s-exclamation-triangle class="w-4 h-4 text-amber-500 flex-shrink-0 mt-0.5" />
                                        <div class="text-xs text-amber-700">
                                            <div class="font-medium">{{ __('common.warning') }}</div>
                                            <div>{{ __('insurances.policy_number_required_warning') }}</div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            
                            <!-- Policy Number -->
                            @if($insurance->policy_number || $insurance->id)
                                <div class="flex items-center gap-2">
                                    <x-svg.files class="w-4 h-4 text-slate-500" />
                                    <span class="font-medium">{{ __('main.policy_number') }}:</span>
                                    <span>{{ $insurance->policy_number ?? 'INS-' . str_pad($insurance->id, 6, '0', STR_PAD_LEFT) }}</span>
                                </div>
                            @endif
                            
                            <!-- Dates -->
                            @if($insurance->start_date)
                                <div class="flex items-center gap-2">
                                    <x-svg.queue-list class="w-4 h-4 text-slate-500" />
                                    <span class="font-medium">{{ __('main.start_date') }}:</span>
                                    <span>{{ $insurance->start_date->format('d/m/Y') }}</span>
                                </div>
                            @endif
                            @if($insurance->end_date)
                                <div class="flex items-center gap-2">
                                    <x-svg.queue-list class="w-4 h-4 text-slate-500" />
                                    <span class="font-medium">{{ __('main.expiration_date') }}:</span>
                                    <span>{{ $insurance->end_date->format('d/m/Y') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
                
                <!-- Package Plans for Available Packages -->
                @if($isPackage && $package->insurancePlans->isNotEmpty())
                    <div class="bg-slate-50 rounded-lg p-3">
                        <div class="text-sm text-slate-700">
                            <div class="font-medium mb-2">{{ __('main.included_plans') }}:</div>
                            <div class="space-y-1">
                                @foreach($package->insurancePlans->take(3) as $insurancePlan)
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-1">
                                            <span>• {{ $insurancePlan->name }}</span>
                                            @if($insurancePlan->requires_official_document)
                                                <x-heroicon-o-shield-check class="w-4 h-4 text-amber-600" title="{{ __('main.requires_official_document') }}" />
                                            @endif
                                        </div>
                                        @if($context === 'entity' && $insurancePlan->entity_fee)
                                            <span class="text-xs text-slate-500">{{ money($insurancePlan->entity_fee) }}</span>
                                        @elseif($context === 'individual' && $insurancePlan->individual_fee)
                                            <span class="text-xs text-slate-500">{{ money($insurancePlan->individual_fee) }}</span>
                                        @endif
                                    </div>
                                @endforeach
                                
                                @if($package->insurancePlans->count() > 3)
                                    <div class="text-xs text-slate-500">
                                        {{ __('main.and_count_more', ['count' => $package->insurancePlans->count() - 3]) }}
                                    </div>
                                @endif
                            </div>
                            
                            @if($context === 'individual' && $package->hasDocumentRequirements())
                                <div class="mt-3 pt-3 border-t border-slate-200">
                                    <div class="flex items-start gap-2">
                                        <x-heroicon-o-information-circle class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5" />
                                        <p class="text-xs text-amber-700">
                                            {{ __('main.official_document_required_notice') }}
                                        </p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
                
                <!-- Plan Details -->
                @if($plan->territorial_scope || $plan->insured_activity)
                    <div class="space-y-2">
                        @if($plan->territorial_scope)
                            <div class="flex items-center gap-2 text-sm text-slate-600">
                                <x-svg.geo-alt class="w-4 h-4 text-slate-500" />
                                <span class="font-medium">{{ __('main.coverage_area') }}:</span>
                                <span>{{ $plan->territorial_scope }}</span>
                            </div>
                        @endif
                        
                        @if($plan->insured_activity)
                            <div class="flex items-center gap-2 text-sm text-slate-600">
                                <x-svg.toggles class="w-4 h-4 text-slate-500" />
                                <span class="font-medium">{{ __('main.activities') }}:</span>
                                <span>{{ $plan->insured_activity }}</span>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        @endif
    </div>
    
    <!-- Card Footer -->
    @if($showActions)
        <div class="flex-shrink-0 pt-4 mt-auto border-t border-slate-100">
            <div class="flex gap-2">
                @if($isPackage)
                    <!-- Available Package Actions -->
                    @if($actionType === 'subscribe')
                        <div class="flex-1"
                             x-data="{
                                 showConfirmModal: false,
                                 packageName: {{ json_encode($package->name) }},
                                 packagePrice: {{ json_encode(number_format($price, 2)) }}
                             }">
                            <x-ui.button
                                variant="primary"
                                size="sm"
                                class="w-full"
                                @click="showConfirmModal = true"
                            >
                                {{ __('main.subscribe') }}
                            </x-ui.button>

                            <!-- Confirmation Modal -->
                            <div x-show="showConfirmModal"
                                 x-cloak
                                 class="fixed inset-0 z-50 overflow-y-auto"
                                 aria-labelledby="modal-title"
                                 role="dialog"
                                 aria-modal="true">
                                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                    <!-- Background overlay -->
                                    <div x-show="showConfirmModal"
                                         x-transition:enter="ease-out duration-300"
                                         x-transition:enter-start="opacity-0"
                                         x-transition:enter-end="opacity-100"
                                         x-transition:leave="ease-in duration-200"
                                         x-transition:leave-start="opacity-100"
                                         x-transition:leave-end="opacity-0"
                                         @click="showConfirmModal = false"
                                         class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

                                    <!-- Modal panel -->
                                    <div x-show="showConfirmModal"
                                         x-transition:enter="ease-out duration-300"
                                         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                         x-transition:leave="ease-in duration-200"
                                         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                         class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                            <div class="sm:flex sm:items-start">
                                                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                                    <svg class="h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                                                    </svg>
                                                </div>
                                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                                        {{ __('Confirmar Subscrição de Seguro') }}
                                                    </h3>
                                                    <div class="mt-4">
                                                        <p class="text-sm text-gray-500 mb-3">
                                                            {{ __('Está prestes a subscrever o seguinte pacote de seguro:') }}
                                                        </p>
                                                        <div class="bg-gray-50 rounded-md p-3 mb-3">
                                                            <div class="text-sm">
                                                                <div class="font-medium text-gray-900" x-text="packageName"></div>
                                                                <div class="mt-1 text-gray-600">
                                                                    {{ __('Valor Total') }}: <span class="font-semibold">{{ currency_symbol() }}<span x-text="packagePrice"></span></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <p class="text-sm text-gray-500">
                                                            {{ __('Após confirmação, será gerado um documento de pagamento.') }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                            <form action="{{ route($context === 'individual' ? 'individual.subscriptions.membership-packages.subscribe' : $context . '.membership-packages.subscribe', $package->id) }}"
                                                  method="POST"
                                                  class="sm:ml-3">
                                                @csrf
                                                <button type="submit"
                                                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:w-auto sm:text-sm">
                                                    {{ __('Confirmar Subscrição') }}
                                                </button>
                                            </form>
                                            <button type="button"
                                                    @click="showConfirmModal = false"
                                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                                {{ __('Cancelar') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    <x-ui.button 
                        variant="outline" 
                        size="sm"
                        @click="
                            detailType = 'package';
                            detailName = {{ json_encode($package->name) }};
                            detailDescription = {{ json_encode($package->description ?? __('common.no_description_available')) }};
                            showDetailsModal = true;
                        "
                    >
                        {{ __('main.details') }}
                    </x-ui.button>
                    
                @else
                    <!-- Current Insurance Actions -->
                    @if($isCurrent)
                        <div class="flex flex-col gap-2 w-full">
                            @if(Route::has($context . '.insurance.document.show'))
                                <x-ui.button
                                    variant="outline"
                                    size="sm"
                                    class="w-full"
                                    href="{{ route($context . '.insurance.document.show', $insurance->id) }}"
                                >
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    {{ __('main.view_insurance') }}
                                </x-ui.button>
                            @endif
                            @if(Route::has($context . '.insurance.document.download'))
                                <x-ui.button
                                    variant="primary"
                                    size="sm"
                                    class="w-full"
                                    href="{{ route($context . '.insurance.document.download', $insurance->id) }}"
                                >
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    {{ __('main.download_pdf') }}
                                </x-ui.button>
                            @endif
                        </div>

                    @elseif($isExpired)
                        <x-ui.button
                            variant="primary"
                            size="sm"
                            href="{{ route($context . '.subscriptions.index') }}"
                        >
                            {{ __('main.renew') }}
                        </x-ui.button>

                        <x-ui.button
                            variant="ghost"
                            size="sm"
                            @click="
                                detailType = 'insurance';
                                detailName = {{ json_encode($insurance->insurancePlan->name) }};
                                detailDescription = {{ json_encode($insurance->insurancePlan->description ?? __('common.no_description_available')) }};
                                detailFiles = {{ json_encode($insurance->insurancePlan->files ?? []) }};
                                showDetailsModal = true;
                            "
                        >
                            {{ __('main.information') }}
                        </x-ui.button>
                    @endif
                @endif
            </div>
        </div>
    @endif

@if($isCurrent || $isExpired)
        </div>
    </div>
@else
</x-ui.card>
@endif