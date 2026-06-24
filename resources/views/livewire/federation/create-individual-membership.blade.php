<div>
    <!-- General Error Display -->
    @error('general')
        <div class="information-box mb-4">
            <div class="flex">
                <svg class="w-5 h-5 mr-2 text-rose-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <span>{{ $message }}</span>
            </div>
        </div>
    @enderror

    <form wire:submit.prevent="submit" class="card">
        <div class="grid grid-cols-1 gap-4">
            <!-- Package Selection -->
            <div>
                <label class="block text-sm font-medium mb-1">
                    {{ __('federation.individual_memberships.membership_package') }} <span class="text-rose-500">*</span>
                </label>
                <select wire:model.live="selectedPackageId" 
                        class="form-select w-full"
                        required>
                    <option value="">{{ __('federation.individual_memberships.select_membership_package') }}</option>
                    @foreach($availablePackages as $package)
                        <option value="{{ $package->id }}">
                            {{ $package->name }} - {{ $package->description }}
                        </option>
                    @endforeach
                </select>
                @error('selectedPackageId')
                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                @enderror
                
                @if($availablePackages->isEmpty())
                    <div class="information-box mt-2">
                        <p class="text-sm">
                            {{ __('federation.individual_memberships.no_packages_available') }}
                        </p>
                    </div>
                @endif
            </div>

            <!-- Package Details (shown when package is selected) -->
            @if($selectedPackage)
                <div wire:transition class="panel-box p-4">
                    <h3 class="font-medium text-slate-800 mb-3">{{ __('federation.individual_memberships.package_details') }}</h3>
                    
                    <!-- Affiliation Plans -->
                    <div class="mb-4">
                        <h4 class="text-sm font-medium text-slate-700 mb-2">{{ __('federation.individual_memberships.included_affiliation_plans') }}</h4>
                        <div class="flex flex-wrap gap-2">
                            @foreach($selectedPackage->affiliationPlans as $plan)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $plan->name }}
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <!-- Insurance Plans (if any) -->
                    @if($selectedPackage->insurancePlans->isNotEmpty())
                        <div class="mb-4">
                            <h4 class="text-sm font-medium text-slate-700 mb-2">{{ __('federation.individual_memberships.included_insurance_plans') }}</h4>
                            <div class="flex flex-wrap gap-2">
                                @foreach($selectedPackage->insurancePlans as $plan)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ $plan->name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Confirmation that package includes affiliation -->
                    <div class="information-box">
                        <p class="text-sm">
                            <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            {{ __('federation.individual_memberships.membership_requires_affiliation') }}
                        </p>
                    </div>
                </div>
            @endif

            <!-- Entity Selection -->
            <div>
                <label class="block text-sm font-medium mb-1">
                    {{ __('federation.individual_memberships.entity_payment_responsible') }} <span class="text-xs text-slate-500">{{ __('federation.common.optional') }}</span>
                </label>
                <select wire:model.live="selectedEntityId" 
                        class="form-select w-full">
                    <option value="">{{ __('federation.individual_memberships.no_entity_selected') }}</option>
                    @foreach($entities as $entity)
                        <option value="{{ $entity->id }}">
                            {{ $entity->name }} ({{ $entity->code_internal }})
                        </option>
                    @endforeach
                </select>
                @error('selectedEntityId')
                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                @enderror
                <p class="text-gray-500 text-sm mt-1">
                    @if($selectedEntityId)
                        {{ __('federation.individual_memberships.entity_payment_note') }}
                    @else
                        {{ __('federation.individual_memberships.direct_to_individual') }}
                    @endif
                </p>
            </div>

            <!-- Individual Selection -->
            @if($selectedPackageId)
                <div>
                    <label class="block text-sm font-medium mb-1">
                        {{ __('federation.individual_memberships.select_individuals') }} <span class="text-rose-500">*</span>
                    </label>
                    
                    <!-- Loading state -->
                    @if($loadingIndividuals)
                        <div class="text-center py-4">
                            <div class="inline-flex items-center">
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ __('federation.common.loading_individuals') }}
                            </div>
                        </div>
                    @endif

                    <!-- Individuals list -->
                    @if(!$loadingIndividuals && count($availableIndividuals) > 0)
                        <div class="border rounded-lg max-h-64 overflow-y-auto">
                            <div class="p-3 border-b bg-slate-50">
                                <label class="flex items-center">
                                    <input type="checkbox" 
                                           wire:click="toggleAllIndividuals"
                                           @if(count($selectedIndividuals) === count($availableIndividuals) && count($availableIndividuals) > 0) checked @endif
                                           class="form-checkbox">
                                    <span class="ml-2 text-sm font-medium">{{ __('federation.common.select_all') }}</span>
                                </label>
                            </div>
                            @foreach($availableIndividuals as $individual)
                                <div class="p-3 border-b last:border-b-0 hover:bg-slate-50">
                                    <label class="flex items-center">
                                        <input type="checkbox" 
                                               wire:model.live="selectedIndividuals"
                                               value="{{ $individual->id }}"
                                               class="form-checkbox">
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-slate-800">{{ $individual->name }}</div>
                                            <div class="text-xs text-slate-500">{{ $individual->member_code }}</div>
                                            <div class="text-xs text-slate-500">{{ $individual->email }}</div>
                                        </div>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- No individuals message -->
                    @if(!$loadingIndividuals && count($availableIndividuals) === 0 && $selectedPackageId)
                        <div class="text-center py-8 text-slate-500">
                            <svg class="w-12 h-12 mx-auto mb-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <p>{{ __('federation.individual_memberships.no_eligible_individuals') }}</p>
                            <p class="text-sm mt-1">{{ __('federation.individual_memberships.individuals_already_subscribed') }}</p>
                        </div>
                    @endif

                    @error('selectedIndividuals')
                        <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                    @enderror
                    @error('selectedIndividuals.*')
                        <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                    @enderror
                </div>
            @endif

            <!-- Selected Count -->
            @if(count($selectedIndividuals) > 0)
                <div class="information-box">
                    <p class="text-sm">
                        {{ count($selectedIndividuals) }} 
                        {{ __('federation.individual_memberships.individuals_selected') }}
                    </p>
                </div>
            @endif
        </div>

        <!-- Form Actions -->
        <div class="flex flex-wrap justify-end space-x-2 mt-6">
            <a href="{{ route('federation.individual-affiliations.index') }}" 
               class="btn btn-secondary">
                {{ __('federation.common.cancel') }}
            </a>
            <button type="submit" 
                    wire:loading.attr="disabled"
                    wire:target="submit"
                    :disabled="!$wire.selectedPackageId || $wire.selectedIndividuals.length === 0 || $wire.isSubmitting"
                    class="btn btn-primary disabled:opacity-50 disabled:cursor-not-allowed">
                <span wire:loading.remove wire:target="submit">{{ __('federation.individual_memberships.create_button') }}</span>
                <span wire:loading wire:target="submit">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ __('federation.common.processing') }}...
                </span>
            </button>
        </div>
    </form>
</div>