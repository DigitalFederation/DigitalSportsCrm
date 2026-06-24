<div>


    <form wire:submit.prevent="submit">
        <div class="space-y-6">
            <!-- Package Selection -->
            <div>
                <label class="block text-sm font-medium mb-2">
                    {{ __('federation.individual_insurances.insurance_package') }} <span class="text-rose-500">*</span>
                </label>
                <select wire:model.live="selectedPackageId" 
                        class="form-select w-full"
                        required>
                    <option value="">{{ __('federation.individual_insurances.select_insurance_package') }}</option>
                    @foreach($availablePackages as $package)
                        <option value="{{ $package->id }}">
                            {{ $package->name }} - {{ $package->description }}
                        </option>
                    @endforeach
                </select>
                @error('selectedPackageId')
                    <div class="text-rose-500 text-sm mt-1">{{ $message }}</div>
                @enderror
                
                @if($availablePackages->isEmpty())
                    <div class="mt-2 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                        <p class="text-sm text-amber-800">
                            {{ __('federation.individual_insurances.no_packages_available') }}
                        </p>
                    </div>
                @endif
            </div>

            <!-- Package Details (shown when package is selected) -->
            @if($selectedPackage)
                <div wire:transition class="bg-slate-50 rounded-lg p-4">
                    <h3 class="font-medium text-slate-800 mb-3">{{ __('federation.individual_insurances.package_details') }}</h3>
                    
                    <!-- Insurance Plans -->
                    <div class="mb-4">
                        <h4 class="text-sm font-medium text-slate-700 mb-2">{{ __('federation.individual_insurances.included_insurance_plans') }}</h4>
                        <div class="flex flex-wrap gap-2">
                            @foreach($selectedPackage->insurancePlans as $plan)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    {{ $plan->name }}
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <!-- Confirmation that no affiliation plans -->
                    <div class="bg-blue-50 border border-blue-200 rounded p-3">
                        <p class="text-sm text-blue-800">
                            <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            {{ __('federation.individual_insurances.insurance_only_confirmation') }}
                        </p>
                    </div>
                </div>
            @endif

            <!-- Entity Selection (Optional) -->
            <div>
                <label class="block text-sm font-medium mb-2">
                    {{ __('federation.individual_insurances.entity_payment_responsible') }} 
                    <span class="text-slate-400">{{ __('federation.common.optional') }}</span>
                </label>
                <select wire:model.live="selectedEntityId" 
                        class="form-select w-full">
                    <option value="">{{ __('federation.individual_insurances.direct_to_individual') }}</option>
                    @foreach($entities as $entity)
                        <option value="{{ $entity->id }}">
                            {{ $entity->name }} ({{ $entity->code_internal }})
                        </option>
                    @endforeach
                </select>
                @error('selectedEntityId')
                    <div class="text-rose-500 text-sm mt-1">{{ $message }}</div>
                @enderror
                <p class="text-xs text-slate-500 mt-1">
                    @if($selectedEntityId)
                        {{ __('federation.individual_insurances.entity_payment_note') }}
                    @else
                        {{ __('federation.individual_insurances.federation_direct_note') }}
                    @endif
                </p>
            </div>

            <!-- Individual Selection -->
            @if($selectedPackageId)
                <div>
                    <label class="block text-sm font-medium mb-2">
                        {{ __('federation.individual_insurances.select_individuals') }} <span class="text-rose-500">*</span>
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
                            <p>{{ __('federation.individual_insurances.no_eligible_individuals') }}</p>
                            <p class="text-sm mt-1">
                                @if($selectedEntityId)
                                    {{ __('federation.individual_insurances.individuals_already_insured') }}
                                @else
                                    {{ __('federation.individual_insurances.federation_individuals_already_insured') }}
                                @endif
                            </p>
                        </div>
                    @endif

                    @error('selectedIndividuals')
                        <div class="text-rose-500 text-sm mt-1">{{ $message }}</div>
                    @enderror
                    @error('selectedIndividuals.*')
                        <div class="text-rose-500 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>
            @endif

            <!-- Selected Count -->
            @if(count($selectedIndividuals) > 0)
                <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                    <p class="text-sm text-green-800">
                        {{ count($selectedIndividuals) }} 
                        {{ __('federation.individual_insurances.individuals_selected') }}
                    </p>
                </div>
            @endif
        </div>

        <!-- General Error Display -->
        @error('general')
        <div class="flex justify-end space-x-3 pt-6 mt-2 border-t">
            <div class="mb-4 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg">
                <div class="flex">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ $message }}</span>
                </div>
            </div>
        </div>
        @enderror


        <!-- Form Actions -->
        <div class="flex justify-end space-x-3 pt-6 border-t">
            <a href="{{ route('federation.individual-insurances.index') }}" 
               class="btn btn-secondary">
                {{ __('federation.common.cancel') }}
            </a>
            <button type="submit" 
                    wire:loading.attr="disabled"
                    wire:target="submit"
                    :disabled="!$wire.selectedPackageId || $wire.selectedIndividuals.length === 0 || $wire.isSubmitting"
                    class="btn btn-primary disabled:opacity-50 disabled:cursor-not-allowed">
                <span wire:loading.remove wire:target="submit">{{ __('federation.individual_insurances.create_button') }}</span>
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