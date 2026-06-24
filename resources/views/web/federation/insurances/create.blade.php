@section('title', __('federation.create_entity_insurance'))
<x-layout>
    <div class="previous-layout-classes">
        <!-- Page Header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-5 mt-5">
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('federation.add_entity_insurance') }}</h1>
                <p class="text-gray-500 text-sm">{{ __('federation.entity_insurances_section.create_subtitle') }}</p>
            </div>
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a href="{{ route('federation.entity-insurances.index') }}" class="btn btn-secondary">
                    <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                        <path d="M6.6 13.4L5.2 12l4-4-4-4 1.4-1.4L12 8z" />
                    </svg>
                    <span class="hidden xs:block ml-2">{{ __('federation.common.back_to_list') }}</span>
                </a>
            </div>
        </div>


        @if(isset($availableInsurancePackages) && $availableInsurancePackages->count() > 0)
            <!-- Available Insurance Packages -->
            <div class="card mb-8">
                <div class="sm:flex sm:justify-between sm:items-center mb-5">
                    <h2 class="text-xl leading-snug text-slate-800 font-bold">{{ __('federation.available_insurance_packages') }}</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($availableInsurancePackages as $package)
                        <div class="card">
                            <div class="flex justify-between items-start mb-3">
                                <h3 class="grow font-semibold text-slate-800 truncate mb-3">{{ $package->name }}</h3>
                                <span class="text-lg font-bold text-emerald-600">
                                    €{{ number_format($package->calculated_price, 2) }}
                                </span>
                            </div>
                            
                            @if($package->description)
                                <p class="text-sm text-slate-600 mb-4">{{ $package->description }}</p>
                            @endif

                            <div class="space-y-2 mb-4">
                                @if($package->insurancePlans->count() > 0)
                                    <div class="text-xs">
                                        <span class="font-medium">{{ __('federation.insurance_plans') }}:</span>
                                        @foreach($package->insurancePlans as $plan)
                                            <span class="text-slate-600">{{ $plan->name }}</span>@if(!$loop->last), @endif
                                        @endforeach
                                    </div>
                                @endif

                                <div class="text-xs">
                                    <span class="font-medium">{{ __('federation.package_type') }}:</span>
                                    <span class="text-slate-600">{{ __('federation.insurance_only') }}</span>
                                </div>
                            </div>

                            <button type="button" 
                                    x-data=""
                                    @click="$dispatch('open-insurance-modal', { packageId: {{ $package->id }}, packageName: '{{ addslashes($package->name) }}' })"
                                    class="btn btn-primary w-full">
                                {{ __('federation.subscribe_entity_to_insurance') }}
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Insurance Subscription Modal -->
            <div 
                x-data="{ 
                    isOpen: false, 
                    packageId: null, 
                    packageName: '' 
                }"
                @open-insurance-modal.window="
                    isOpen = true; 
                    packageId = $event.detail.packageId; 
                    packageName = $event.detail.packageName;
                "
                @keydown.escape.window="isOpen = false"
                x-show="isOpen"
                x-cloak
                class="fixed inset-0 bg-slate-900 bg-opacity-50 z-50 transition-opacity duration-200">
                
                <div class="flex items-center justify-center min-h-screen px-4" @click="isOpen = false">
                    <div class="card max-w-2xl w-full" @click.stop>
                        <div class="flex justify-between items-center mb-5">
                            <h3 class="text-xl font-bold text-slate-800">{{ __('federation.subscribe_entity_to_insurance_package') }}</h3>
                            <button type="button" @click="isOpen = false" class="text-slate-400 hover:text-slate-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <form method="POST" action="{{ route('federation.entity-insurances.store') }}" class="card">
                            @csrf
                            <input type="hidden" name="membership_package_id" :value="packageId">
                            
                            <div class="grid grid-cols-1 gap-4">
                                <!-- Package Info -->
                                <div class="information-box">
                                    <h4 class="font-semibold text-slate-800 mb-2">{{ __('federation.selected_insurance_package') }}</h4>
                                    <div class="text-sm text-slate-600">
                                        <p><strong>{{ __('federation.package') }}:</strong> <span x-text="packageName"></span></p>
                                        <p><strong>{{ __('federation.package_id') }}:</strong> <span x-text="packageId"></span></p>
                                    </div>
                                </div>

                                <!-- Entity Selection -->
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="insurance_entity_id">
                                        {{ __('federation.select_entity') }} <span class="text-rose-500">*</span>
                                    </label>
                                    <select id="insurance_entity_id" name="entity_id" class="form-select w-full" required>
                                        <option value="">{{ __('federation.common.select_entity') }}</option>
                                        @foreach($entities as $entity)
                                            <option value="{{ $entity->id }}">{{ $entity->name }} ({{ $entity->code }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="information-box">
                                    <div class="flex items-start">
                                        <svg class="w-5 h-5 text-yellow-500 mt-0.5 mr-3 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                        <div class="text-sm">
                                            <p class="text-slate-800 font-medium">{{ __('federation.insurance_payment_responsibility') }}</p>
                                            <p class="text-slate-600 mt-1">{{ __('federation.entity_will_receive_insurance_documents') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-wrap justify-end space-x-2 mt-6">
                                <button type="button" @click="isOpen = false" class="btn btn-secondary">
                                    {{ __('federation.common.cancel') }}
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    {{ __('federation.create_insurance_subscription') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @else
            <!-- No Packages Available -->
            <div class="card">
                <div class="text-center py-12">
                    <div class="text-slate-500 mb-4">{{ __('federation.no_insurance_packages_available') }}</div>
                    <a href="{{ route('federation.entity-insurances.index') }}" class="btn btn-secondary">
                        {{ __('federation.common.back_to_list') }}
                    </a>
                </div>
            </div>
        @endif
    </div>
</x-layout>