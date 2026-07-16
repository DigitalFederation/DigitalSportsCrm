<x-layout>
    <div x-data="{
        showDetailsModal: false,
        detailType: '',
        detailName: '',
        detailDescription: '',
        detailPlans: [],
        detailPrice: 0,
        detailPeriod: '',
        detailFee: '',

        showSubscriptionModal: false,
        subscriptionPackage: null,

        showInsuranceDetails(insurance) {
            this.detailType = 'insurance';
            this.detailName = insurance.insurance_plan?.name || '';
            this.detailDescription = insurance.insurance_plan?.description || '';
            this.detailPeriod = this.formatDate(insurance.start_date) + ' - ' + this.formatDate(insurance.end_date);
            this.detailFee = (insurance.individual_fee || insurance.fee || 0).toFixed(2);
            this.showDetailsModal = true;
        },

        showPackageDetails(pkg) {
            this.detailType = 'package';
            this.detailName = pkg.name || '';
            this.detailDescription = pkg.description || '';
            this.detailPlans = pkg.insurance_plans || [];
            this.detailPrice = (pkg.calculated_price || 0).toFixed(2);
            this.showDetailsModal = true;
        },

        openSubscriptionModal(pkg) {
            this.subscriptionPackage = pkg;
            this.showSubscriptionModal = true;
        },

        closeSubscriptionModal() {
            this.showSubscriptionModal = false;
            this.subscriptionPackage = null;
        },

        formatDate(dateStr) {
            if (!dateStr) return '';
            const date = new Date(dateStr);
            return date.toLocaleDateString('pt-PT');
        },

        closeModal() {
            this.showDetailsModal = false;
        }
    }"
         x-on:show-package-details.window="showPackageDetails($event.detail.package)"
         x-on:open-subscription-modal.window="openSubscriptionModal($event.detail.package)"
         x-on:keydown.escape.window="showDetailsModal && closeModal(); showSubscriptionModal && closeSubscriptionModal()"
         class="previous-layout-classes">

        <!-- Page Header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-5 mt-5">
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('main.insurances') }}</h1>
            </div>
        </div>

        <!-- Pending Insurances Section -->
        @if($pendingInsurances->isNotEmpty())
            <div class="mb-8">
                <div class="mb-4">
                    <h2 class="text-xl leading-snug text-amber-800 font-bold">{{ __('main.pending_payment_insurances') }}</h2>
                    <p class="text-sm text-amber-700 mt-1">{{ __('main.complete_payment_to_activate_insurances') }}</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                    @foreach($pendingInsurances as $insurance)
                        <div class="card relative" wire:key="pending-{{ $insurance->id }}">
                            <!-- Pending Badge -->
                            <div class="absolute top-3 right-3 z-10">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800 border border-amber-200">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ __('main.pending_payment') }}
                                </span>
                            </div>

                            <!-- Card Content -->
                            <div class="pt-8">
                                <h3 class="text-lg font-semibold text-slate-800 mb-2">
                                    {{ $insurance->insurancePlan->name }}
                                </h3>

                                @if($insurance->insurancePlan->description)
                                    <p class="text-sm text-slate-600 mb-4">
                                        {{ Str::limit($insurance->insurancePlan->description, 100) }}
                                    </p>
                                @endif

                                <div class="bg-slate-50 rounded-lg p-3 mb-4">
                                    <div class="text-sm text-slate-700 space-y-2">
                                        @if($insurance->start_date)
                                            <div class="flex items-center gap-2">
                                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                <span class="font-medium">{{ __('main.start_date') }}:</span>
                                                <span>{{ $insurance->start_date->format('d/m/Y') }}</span>
                                            </div>
                                        @endif
                                        @if($insurance->end_date)
                                            <div class="flex items-center gap-2">
                                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                <span class="font-medium">{{ __('main.expiration_date') }}:</span>
                                                <span>{{ $insurance->end_date->format('d/m/Y') }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                @if($insurance->memberSubscription && $insurance->memberSubscription->documents()->first())
                                    <a href="{{ route('individual.document.show', $insurance->memberSubscription->documents()->first()->id) }}"
                                       class="btn btn-warning w-full">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                        </svg>
                                        {{ __('main.complete_payment') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Current Insurances Section -->
        @if($currentInsurances->isNotEmpty())
            <div class="mb-8">
                <div class="mb-4">
                    <h2 class="text-xl leading-snug text-slate-800 font-bold">{{ __('main.my_insurances') }}</h2>
                    <p class="text-sm text-slate-600 mt-1">{{ __('insurances.your_active_coverage') }}</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                    @foreach($currentInsurances as $insurance)
                        <div class="card-no-padding overflow-hidden" wire:key="current-{{ $insurance->id }}">
                            <!-- Active Header -->
                            <div class="bg-emerald-600 px-5 py-4">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-semibold text-white">
                                        {{ $insurance->insurancePlan->name }}
                                    </h3>
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <span class="text-sm text-emerald-100">{{ __('main.active') }}</span>
                            </div>

                            <!-- Card Content -->
                            <div class="p-5">
                                @if($insurance->insurancePlan->description)
                                    <p class="text-sm text-slate-600 mb-4">
                                        {{ Str::limit($insurance->insurancePlan->description, 120) }}
                                    </p>
                                @endif

                                <div class="bg-slate-50 rounded-lg p-3 mb-4">
                                    <div class="text-sm text-slate-700 space-y-2">
                                        <!-- Policy Number Warning -->
                                        @if(!$insurance->policy_number)
                                            <div class="bg-amber-50 border border-amber-200 rounded-md p-2 mb-2">
                                                <div class="flex items-start gap-2">
                                                    <svg class="w-4 h-4 text-amber-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                    </svg>
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
                                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                <span class="font-medium">{{ __('main.policy_number') }}:</span>
                                                <span>{{ $insurance->policy_number ?? 'INS-' . str_pad($insurance->id, 6, '0', STR_PAD_LEFT) }}</span>
                                            </div>
                                        @endif

                                        <!-- Dates -->
                                        @if($insurance->start_date)
                                            <div class="flex items-center gap-2">
                                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                <span class="font-medium">{{ __('main.start_date') }}:</span>
                                                <span>{{ $insurance->start_date->format('d/m/Y') }}</span>
                                            </div>
                                        @endif
                                        @if($insurance->end_date)
                                            <div class="flex items-center gap-2">
                                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                <span class="font-medium">{{ __('main.expiration_date') }}:</span>
                                                <span>{{ $insurance->end_date->format('d/m/Y') }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex flex-col gap-2">
                                    @if(Route::has('individual.insurance.document.show'))
                                        <a href="{{ route('individual.insurance.document.show', $insurance->id) }}"
                                           class="btn btn-secondary w-full">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            {{ __('main.view_insurance') }}
                                        </a>
                                    @endif
                                    @if(Route::has('individual.insurance.document.download'))
                                        <a href="{{ route('individual.insurance.document.download', $insurance->id) }}"
                                           class="btn btn-primary w-full">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            {{ __('main.download_pdf') }}
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @elseif($pendingInsurances->isEmpty())
            <div class="mb-8">
                <x-ui.no-insurances-empty-state context="individual" />
            </div>
        @endif

        <!-- Available Insurance Packages Section -->
        @if($availableInsurancePackages->isNotEmpty())
            <div class="mb-8">
                <div class="mb-4">
                    <h2 class="text-xl leading-snug text-slate-800 font-bold">{{ __('main.available_insurances') }}</h2>
                    <p class="text-sm text-slate-600 mt-1">{{ __('main.explore_insurance_options') }}</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                    @foreach($availableInsurancePackages as $package)
                        <div class="card flex flex-col h-full"
                             wire:key="package-{{ $package->id }}">

                            <!-- Package Header -->
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-slate-800">
                                        {{ $package->name }}
                                    </h3>
                                    <div class="flex items-center gap-2 mt-2 flex-wrap">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ __('main.available') }}
                                        </span>
                                        @if($package->insurancePlans->count() > 1)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-700">
                                                {{ __('main.package') }}
                                            </span>
                                        @endif
                                        @if($package->hasDocumentRequirements())
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                                </svg>
                                                {{ __('main.requires_documents') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Price -->
                            <div class="mb-4 pb-4 border-b border-slate-100">
                                <div class="flex items-baseline gap-2">
                                    <span class="text-2xl font-bold text-slate-900">
                                        {{ number_format($package->calculated_price, 2) }}
                                    </span>
                                    <span class="text-sm text-slate-500">{{ __('main.per_year') }}</span>
                                </div>
                            </div>

                            <!-- Description -->
                            @if($package->description)
                                <p class="text-sm text-slate-600 mb-4 flex-grow">
                                    {{ Str::limit($package->description, 150) }}
                                </p>
                            @endif

                            <!-- Included Plans -->
                            @if($package->insurancePlans->isNotEmpty())
                                <div class="bg-slate-50 rounded-lg p-3 mb-4">
                                    <div class="text-sm text-slate-700">
                                        <div class="font-medium mb-2">{{ __('main.included_plans') }}:</div>
                                        <div class="space-y-1">
                                            @foreach($package->insurancePlans->take(3) as $insurancePlan)
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center gap-1">
                                                        <span class="text-slate-400">&#8226;</span>
                                                        <span>{{ $insurancePlan->name }}</span>
                                                        @if($insurancePlan->requires_official_document)
                                                            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" title="{{ __('main.requires_official_document') }}">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                                            </svg>
                                                        @endif
                                                    </div>
                                                    @if($insurancePlan->individual_fee)
                                                        <span class="text-xs text-slate-500">{{ number_format($insurancePlan->individual_fee, 2) }}</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                            @if($package->insurancePlans->count() > 3)
                                                <div class="text-xs text-slate-500 pt-1">
                                                    {{ __('main.and_count_more', ['count' => $package->insurancePlans->count() - 3]) }}
                                                </div>
                                            @endif
                                        </div>

                                        @if($package->hasDocumentRequirements())
                                            <div class="mt-3 pt-3 border-t border-slate-200">
                                                <div class="flex items-start gap-2">
                                                    <svg class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    <p class="text-xs text-amber-700">
                                                        {{ __('main.official_document_required_notice') }}
                                                    </p>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <!-- Actions -->
                            <div class="mt-auto pt-4 border-t border-slate-100 flex gap-2">
                                <button type="button"
                                        @click="$dispatch('open-subscription-modal', { package: {{ json_encode($package) }} })"
                                        class="btn btn-primary flex-1">
                                    {{ __('main.subscribe') }}
                                </button>
                                <button type="button"
                                        @click="$dispatch('show-package-details', { package: {{ json_encode($package) }} })"
                                        class="btn btn-secondary">
                                    {{ __('main.details') }}
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="mb-8">
                <x-ui.empty-state-card
                    :title="__('main.no_insurance_packages_available')"
                    :description="__('main.no_insurance_packages_description')"
                />
            </div>
        @endif

        <!-- Package Details Modal (Global) -->
        <div x-show="showDetailsModal"
             x-cloak
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto"
             aria-labelledby="detail-modal-title"
             role="dialog"
             aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-900 bg-opacity-50 transition-opacity" @click="closeModal()"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="showDetailsModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">

                    <!-- Close Button -->
                    <div class="absolute top-0 right-0 pt-4 pr-4">
                        <button @click="closeModal()"
                                type="button"
                                class="bg-white rounded-md text-slate-400 hover:text-slate-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <span class="sr-only">{{ __('main.close') }}</span>
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                                <h3 class="text-xl leading-6 font-bold text-slate-900 mb-4"
                                    id="detail-modal-title"
                                    x-text="detailName"></h3>

                                <template x-if="detailDescription">
                                    <div class="mb-4">
                                        <h4 class="text-sm font-medium text-slate-700 mb-2">{{ __('main.description') }}</h4>
                                        <p class="text-sm text-slate-600" x-text="detailDescription"></p>
                                    </div>
                                </template>

                                <!-- Insurance Details -->
                                <template x-if="detailType === 'insurance'">
                                    <div class="bg-slate-50 px-4 py-3 rounded-md">
                                        <div class="flex justify-between text-sm mb-2">
                                            <span class="font-medium text-slate-500">{{ __('main.policy_period') }}</span>
                                            <span class="text-slate-900" x-text="detailPeriod"></span>
                                        </div>
                                        <div class="flex justify-between text-sm">
                                            <span class="font-medium text-slate-500">{{ __('main.fee') }}</span>
                                            <span class="text-slate-900" x-text="detailFee + ' ' + @js(currency_symbol())"></span>
                                        </div>
                                    </div>
                                </template>

                                <!-- Package Plans -->
                                <template x-if="detailType === 'package' && detailPlans.length > 0">
                                    <div>
                                        <h4 class="text-sm font-medium text-slate-700 mb-3">{{ __('main.insurance_plans_included') }}</h4>
                                        <div class="space-y-3 mb-4">
                                            <template x-for="plan in detailPlans" :key="plan.id">
                                                <div class="border border-slate-200 rounded-lg p-4">
                                                    <div class="flex justify-between items-start mb-2">
                                                        <h5 class="font-medium text-slate-900" x-text="plan.name"></h5>
                                                        <span class="text-sm font-medium text-blue-600" x-text="(plan.individual_fee || 0).toFixed(2) + ' ' + @js(currency_symbol())"></span>
                                                    </div>
                                                    <p x-show="plan.description" class="text-sm text-slate-600" x-text="plan.description"></p>
                                                </div>
                                            </template>
                                        </div>

                                        <div class="bg-slate-50 px-4 py-3 rounded-md">
                                            <div class="flex justify-between items-center">
                                                <span class="text-lg font-medium text-slate-900">{{ __('main.total_annual_price') }}</span>
                                                <span class="text-2xl font-bold text-blue-600" x-text="detailPrice + ' ' + @js(currency_symbol())"></span>
                                            </div>
                                            <p class="text-xs text-slate-500 mt-1">{{ __('main.price_includes_all_insurance_plans') }}</p>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button"
                                @click="closeModal()"
                                class="btn btn-secondary w-full sm:w-auto">
                            {{ __('main.close') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Subscription Confirmation Modal (Global) -->
        <div x-show="showSubscriptionModal"
             x-cloak
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto"
             aria-labelledby="subscription-modal-title"
             role="dialog"
             aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-900 bg-opacity-50 transition-opacity" @click="closeSubscriptionModal()"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="showSubscriptionModal"
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
                                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-slate-900" id="subscription-modal-title">
                                    {{ __('insurances.confirm_subscription') }}
                                </h3>
                                <div class="mt-4">
                                    <p class="text-sm text-slate-500 mb-3">
                                        {{ __('insurances.about_to_subscribe') }}
                                    </p>
                                    <div class="bg-slate-50 rounded-md p-3 mb-3">
                                        <div class="text-sm">
                                            <div class="font-medium text-slate-900" x-text="subscriptionPackage?.name"></div>
                                            <div class="mt-1 text-slate-600">
                                                {{ __('insurances.total_value') }}: <span class="font-semibold" x-text="subscriptionPackage ? Number(subscriptionPackage.calculated_price).toFixed(2) : ''"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-sm text-slate-500">
                                        {{ __('insurances.payment_document_generated') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                        <form x-bind:action="subscriptionPackage ? '{{ url('individual/subscriptions/membership-packages') }}/' + subscriptionPackage.id + '/subscribe' : ''" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary w-full sm:w-auto">
                                {{ __('insurances.confirm_subscription_button') }}
                            </button>
                        </form>
                        <button type="button"
                                @click="closeSubscriptionModal()"
                                class="btn btn-secondary w-full sm:w-auto mt-2 sm:mt-0">
                            {{ __('main.cancel') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-layout>
