@section('title', __('federation.create_entity_subscription'))
<x-layout>
    <div class="previous-layout-classes">
        <!-- Page Header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-5 mt-5">
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('federation.add_entity_subscription') }}</h1>
                <p class="text-gray-500 text-sm">{{ __('federation.entity_subscriptions_section.create_subtitle') }}</p>
            </div>
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a href="{{ route('federation.entity-subscriptions.index') }}" class="btn btn-secondary">
                    <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                        <path d="M6.6 13.4L5.2 12l4-4-4-4 1.4-1.4L12 8z" />
                    </svg>
                    <span class="hidden xs:block ml-2">{{ __('federation.common.back_to_list') }}</span>
                </a>
            </div>
        </div>

        <!-- Information Notice -->
        <div class="information-box mb-8">
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

        @if(isset($availablePackages) && $availablePackages->count() > 0)
            <!-- Package Selection -->
            <div class="card mb-8">
                <div class="sm:flex sm:justify-between sm:items-center mb-5">
                    <h2 class="text-xl leading-snug text-slate-800 font-bold">{{ __('federation.entity_subscriptions_section.select_membership_package') }}</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($availablePackages as $package)
                        <div class="card">
                            <div class="flex justify-between items-start mb-3">
                                <h3 class="grow font-semibold text-slate-800 truncate mb-3">{{ $package->name }}</h3>
                                <span class="text-lg font-bold text-indigo-600">
                                    {{ money($package->calculatePriceFor(\Domain\Entities\Models\Entity::class)) }}
                                </span>
                            </div>
                            
                            @if($package->description)
                                <p class="text-sm text-slate-600 mb-4">{{ $package->description }}</p>
                            @endif

                            <div class="space-y-2 mb-4">
                                @if($package->affiliationPlans->count() > 0)
                                    <div class="text-xs">
                                        <span class="font-medium">{{ __('federation.affiliation_plans') }}:</span>
                                        @foreach($package->affiliationPlans as $plan)
                                            <span class="text-slate-600">{{ $plan->name }}</span>@if(!$loop->last), @endif
                                        @endforeach
                                    </div>
                                @endif
                                
                                @if($package->insurancePlans->count() > 0)
                                    <div class="text-xs">
                                        <span class="font-medium">{{ __('federation.insurance_coverage') }}:</span>
                                        <span class="text-slate-600">{{ __('federation.common.yes') }}</span>
                                    </div>
                                @endif
                            </div>

                            <button type="button" 
                                    x-data=""
                                    @click="$dispatch('select-package', { packageId: {{ $package->id }}, packageName: '{{ addslashes($package->name) }}' })"
                                    class="btn btn-primary w-full">
                                {{ __('federation.select_package') }}
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Subscription Form -->
            <div class="card" 
                 x-data="{ 
                     selectedPackage: null, 
                     selectedPackageName: '', 
                     showForm: false 
                 }"
                 @select-package.window="
                     selectedPackage = $event.detail.packageId; 
                     selectedPackageName = $event.detail.packageName;
                     showForm = true;
                 ">
                
                <div x-show="!showForm" class="text-center py-12">
                    <div class="text-slate-500">{{ __('federation.entity_subscriptions_section.select_package_to_continue') }}</div>
                </div>

                <div x-show="showForm" x-cloak>
                    <div class="sm:flex sm:justify-between sm:items-center mb-5">
                        <h2 class="text-xl leading-snug text-slate-800 font-bold">{{ __('federation.create_entity_subscription') }}</h2>
                    </div>

                    <form method="POST" action="{{ route('federation.entity-subscriptions.store') }}">
                        @csrf
                        <input type="hidden" name="membership_package_id" :value="selectedPackage">
                        
                        <div class="space-y-6">
                            <!-- Selected Package Info -->
                            <div class="panel-box">
                                <h4 class="font-semibold text-slate-800 mb-2">{{ __('federation.selected_package') }}</h4>
                                <div class="text-sm text-slate-600">
                                    <p><strong>{{ __('federation.package') }}:</strong> <span x-text="selectedPackageName"></span></p>
                                </div>
                            </div>

                            <!-- Entity Selection -->
                            <div>
                                <label class="block text-sm font-medium mb-1" for="entity_id">
                                    {{ __('federation.select_entity') }} <span class="text-rose-500">*</span>
                                </label>
                                <select id="entity_id" name="entity_id" class="form-select w-full" required>
                                    <option value="">{{ __('federation.common.select_entity') }}</option>
                                    @foreach($entities as $entity)
                                        <option value="{{ $entity->id }}" {{ old('entity_id') == $entity->id ? 'selected' : '' }}>
                                            {{ $entity->name }} ({{ $entity->code }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('entity_id')
                                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Payment Responsibility Notice -->
                            <div class="information-box">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-yellow-500 mt-0.5 mr-3 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    <div class="text-sm">
                                        <p class="text-slate-800 font-medium">{{ __('federation.payment_responsibility') }}</p>
                                        <p class="text-slate-600 mt-1">{{ __('federation.entity_will_receive_documents') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-wrap justify-end space-x-2 mt-6">
                            <a href="{{ route('federation.entity-subscriptions.index') }}" class="btn btn-secondary">
                                {{ __('federation.common.cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                {{ __('federation.create_subscription') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @else
            <!-- No Packages Available -->
            <div class="card">
                <div class="text-center py-12">
                    <div class="text-slate-500 mb-4">{{ __('federation.no_packages_available') }}</div>
                    <a href="{{ route('federation.entity-subscriptions.index') }}" class="btn btn-secondary">
                        {{ __('federation.common.back_to_list') }}
                    </a>
                </div>
            </div>
        @endif
    </div>
</x-layout>