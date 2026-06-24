<div>
    {{-- Header Section --}}
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <h1 class="page-first-title">{{ __('federation.create_entity_subscription') }}</h1>
            <a href="{{ route('federation.entity-subscriptions.index') }}" class="btn btn-secondary">
                @svg('heroicon-o-arrow-left', 'w-5 h-5 mr-2')
                {{ __('federation.back_to_list') }}
            </a>
        </div>
        
        <div class="information-box mt-4">
            <div class="flex items-center">
                @svg('heroicon-o-information-circle', 'w-5 h-5 text-blue-600 mr-2')
                <span class="text-sm text-blue-800">
                    {{ __('federation.entity_subscription_creation_info') }}
                </span>
            </div>
        </div>
    </div>

    {{-- Entity Selection Section --}}
    <x-ui.card class="mb-6">
        <div class="p-6">
            <h2 class="text-lg font-medium mb-4 flex items-center text-gray-900">
                @svg('heroicon-o-building-office', 'w-5 h-5 mr-2')
                {{ __('federation.select_entity') }}
            </h2>

            <div class="max-w-md">
                <select wire:model.live="selectedEntityId" class="form-select w-full">
                    <option value="">{{ __('federation.choose_entity') }}</option>
                    @foreach($entities as $entity)
                        <option value="{{ $entity->id }}">{{ $entity->name }}</option>
                    @endforeach
                </select>
            </div>

            @if($selectedEntity)
                <div class="mt-4 p-4 bg-blue-50 rounded-lg">
                    <div class="flex items-center">
                        @svg('heroicon-o-information-circle', 'w-5 h-5 text-blue-600 mr-2')
                        <span class="text-sm text-blue-800">
                            {{ __('federation.facilitating_subscription_for_entity', ['entity' => $selectedEntity->name]) }}
                        </span>
                    </div>
                </div>
            @endif
        </div>
    </x-ui.card>

    @if($selectedEntity)
        {{-- Validation Plan Message --}}
        @if(!$hasValidationPlanPrivileges)
            <div class="mb-6 rounded-lg bg-yellow-50 border border-yellow-200 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">
                            {{ __('federation.validation_plan_required') }}
                        </h3>
                        <div class="mt-2 text-sm text-yellow-700">
                            <p>{{ $validationPlanMessage }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Package Selection Section --}}
        <x-ui.card class="mb-6">
            <div class="p-6">
                <h2 class="text-lg font-medium mb-4 flex items-center text-gray-900">
                    @svg('heroicon-o-cube', 'w-5 h-5 mr-2')
                    {{ __('federation.select_package') }}
                </h2>

                @if($packages->isEmpty())
                    <div class="text-center py-8 text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2m0 0v-4h1m11 4h2m-6 0V9l4 0" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('federation.no_packages_available') }}</h3>
                        <p class="mt-1 text-sm text-gray-500">{{ __('federation.no_packages_for_entity') }}</p>
                    </div>
                @else
                    <x-ui.card-grid columns="3" gap="lg">
                        @foreach($packages as $package)
                            <div class="bg-white rounded-lg shadow-lg border border-slate-200 overflow-hidden transition-all duration-300 h-full flex flex-col {{ $hasValidationPlanPrivileges ? 'hover:shadow-xl cursor-pointer' : 'opacity-60 cursor-not-allowed' }} {{ $selectedPackageId === $package->id ? 'ring-2 ring-blue-500 bg-blue-50' : '' }}"
                                 @if($hasValidationPlanPrivileges) wire:click="$set('selectedPackageId', {{ $package->id }})" @endif>
                                <!-- Blue Header -->
                                <div class="bg-blue-600 px-6 py-4">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-lg font-semibold text-white">
                                            {{ $package->name }}
                                        </h3>
                                        <div class="flex items-center text-white">
                                            @if($selectedPackageId === $package->id)
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            @else
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Card Content -->
                                <div class="flex-1 p-6 flex flex-col">
                                    <!-- Price Section -->
                                    @php
                                        $price = $package->calculatePriceForType('entity');
                                    @endphp
                                    @if($price > 0)
                                        <div class="mb-4">
                                            <div class="flex items-baseline">
                                                <span class="text-2xl font-bold text-slate-900">€{{ number_format($price, 2) }}</span>
                                                <span class="text-sm text-slate-500 ml-1">/ ano</span>
                                            </div>
                                        </div>
                                    @else
                                        <div class="mb-4">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                                {{ __('federation.free') }}
                                            </span>
                                        </div>
                                    @endif

                                    <!-- Description -->
                                    @if($package->description)
                                        <div class="mb-4">
                                            <p class="text-sm text-slate-600 leading-relaxed">{{ $package->description }}</p>
                                        </div>
                                    @endif

                                    <!-- Package Contents -->
                                    <div class="flex-1">
                                        <h4 class="text-sm font-semibold text-slate-900 mb-3 flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                            </svg>
                                            {{ __('federation.whats_included') }}
                                        </h4>
                                        
                                        <div class="space-y-3">
                                            <!-- Affiliation Plans -->
                                            @if($package->affiliationPlans->isNotEmpty())
                                                <div>
                                                    <div class="flex items-center text-xs font-medium text-slate-700 mb-2">
                                                        {{ __('federation.affiliations') }}
                                                    </div>
                                                    <div class="space-y-1">
                                                        @foreach($package->affiliationPlans as $plan)
                                                            <div class="flex items-center text-sm text-slate-600">
                                                                <svg class="w-3 h-3 mr-2 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                                </svg>
                                                                <span class="flex-1">{{ $plan->name }}</span>
                                                                @if($plan->entity_fee > 0)
                                                                    <span class="text-xs text-slate-500">€{{ number_format($plan->entity_fee, 2) }}</span>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif

                                            <!-- Insurance Plans -->
                                            @if($package->insurancePlans->isNotEmpty())
                                                <div>
                                                    <div class="flex items-center text-xs font-medium text-slate-700 mb-2">
                                                        {{ __('federation.insurances') }}
                                                    </div>
                                                    <div class="space-y-1">
                                                        @foreach($package->insurancePlans as $plan)
                                                            <div class="flex items-center text-sm text-slate-600">
                                                                <svg class="w-3 h-3 mr-2 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                                </svg>
                                                                <span class="flex-1">{{ $plan->name }}</span>
                                                                @if($plan->entity_fee > 0)
                                                                    <span class="text-xs text-slate-500">€{{ number_format($plan->entity_fee, 2) }}</span>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Selection Status -->
                                    @if($selectedPackageId === $package->id)
                                        <div class="mt-6 pt-4 border-t border-blue-200">
                                            <div class="flex items-center text-blue-600">
                                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                                <span class="text-sm font-medium">{{ __('federation.selected') }}</span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </x-ui.card-grid>
                @endif
            </div>
        </x-ui.card>

        @if($selectedPackageId)
            {{-- Action Buttons --}}
            <div class="flex justify-end space-x-3">
                <a href="{{ route('federation.entity-subscriptions.index') }}" class="btn btn-secondary">
                    {{ __('federation.actions_buttons.cancel') }}
                </a>

                <button
                    type="button"
                    wire:click="confirmSubscription"
                    @class([
                        'inline-flex items-center btn-primary',
                        'bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500' => $hasValidationPlanPrivileges,
                        'bg-gray-300 cursor-not-allowed' => !$hasValidationPlanPrivileges,
                    ])
                    @disabled(!$hasValidationPlanPrivileges)
                >
                    {{ __('federation.actions_buttons.create_subscription') }}
                </button>
            </div>
        @endif
    @endif

    {{-- Confirmation Modal --}}
    <x-dialog-modal wire:model.live="confirmingSubscription">
        <x-slot name="title">
            {{ __('federation.modal.confirm_subscription_title') }}
        </x-slot>

        <x-slot name="content">
            <div class="space-y-4">
                <p>{{ __('federation.modal.confirm_subscription_message_for_entity', ['entity' => $selectedEntity?->name]) }}</p>

                @if($this->selectedPackage)
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="font-medium text-gray-900">{{ $this->selectedPackage->name }}</h3>
                        <p class="text-sm text-gray-500 mt-1">{{ $this->selectedPackage->description }}</p>
                        <div class="mt-2 text-sm">
                            <span class="font-medium text-gray-900">{{ __('federation.modal.price') }}</span>
                            <span class="text-gray-600">€{{ number_format($this->selectedPackage->calculatePriceForType('entity'), 2) }}</span>
                        </div>
                    </div>
                @endif

                @if($errors->has('subscription'))
                    <div class="text-sm text-red-600">{{ $errors->first('subscription') }}</div>
                @endif
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('confirmingSubscription', false)" wire:loading.attr="disabled">
                {{ __('federation.actions_buttons.cancel') }}
            </x-secondary-button>

            <x-button class="ml-3" wire:click="processSubscription" wire:loading.attr="disabled">
                {{ __('federation.actions_buttons.confirm') }}
            </x-button>
        </x-slot>
    </x-dialog-modal>
</div>