<!-- Enhanced Business-Scenario-Driven Form -->
@php
    // Determine current business scenario based on existing plan data
    $currentScenario = old('business_scenario', '');
    if (!$currentScenario && isset($plan) && $plan->id) {
        if ($plan->type === 'individual') {
            $currentScenario = 'direct_individual';
        } elseif ($plan->type === 'entity') {
            if ($plan->individual_fee !== null && $plan->entity_fee === null) {
                $currentScenario = 'entity_for_individuals';
            } elseif ($plan->entity_fee !== null && $plan->individual_fee === null) {
                $currentScenario = 'direct_entity';
            } elseif ($plan->individual_fee !== null && $plan->entity_fee !== null) {
                $currentScenario = 'flexible';
            } else {
                $currentScenario = 'direct_entity'; // fallback
            }
        }
    }
@endphp

<div x-data="{
    selectedScenario: '{{ $currentScenario }}',
    scenarios: @js($businessScenarios),
    showFeeFields: function(scenario) {
        if (!scenario || !this.scenarios[scenario]) return false;
        return true;
    },
    getFeeStructure: function(scenario) {
        if (!scenario || !this.scenarios[scenario]) return 'none';
        return this.scenarios[scenario].fee_structure;
    }
}" class="space-y-6">

    <!-- Basic Information -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">{{ __('memberships.plan_name') }}</label>
            <input type="text" name="name" id="name" value="{{ old('name', $plan->name ?? '') }}" 
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
            <p class="text-xs text-gray-500 mt-1">{{ __('memberships.plan_name_help') }}</p>
        </div>
        
        <div>
            <label for="federation_id" class="block text-sm font-medium text-gray-700">{{ __('Federation') }}</label>
            <select name="federation_id" id="federation_id" 
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                <option value="">{{ __('memberships.select_federation') }}</option>
                @foreach($federations as $federation)
                    <option value="{{ $federation->id }}" {{ old('federation_id', $plan->federation_id ?? '') == $federation->id ? 'selected' : '' }}>
                        {{ $federation->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Business Scenario Selection -->
    <div class="bg-blue-50 p-6 rounded-lg border border-blue-200">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('memberships.choose_business_scenario') }}</h3>
        <p class="text-sm text-gray-600 mb-4">{{ __('memberships.business_scenario_help') }}</p>
        
        <div class="space-y-4">
            @foreach($businessScenarios as $key => $scenario)
                <div class="relative">
                    <label class="flex items-start p-4 border rounded-lg cursor-pointer hover:bg-blue-25 transition-colors"
                           :class="selectedScenario === '{{ $key }}' ? 'border-blue-500 bg-blue-25' : 'border-gray-300'">
                        <input type="radio" name="business_scenario" value="{{ $key }}" 
                               x-model="selectedScenario"
                               class="mt-1 text-blue-600 focus:ring-blue-500"
                               {{ $currentScenario === $key ? 'checked' : '' }}>
                        <div class="ml-3 flex-1">
                            <div class="font-medium text-gray-900">{{ $scenario['label'] }}</div>
                            <div class="text-sm text-gray-600 mt-1">{{ $scenario['description'] }}</div>
                            <div class="text-xs text-blue-600 mt-2 italic">{{ $scenario['example'] }}</div>
                        </div>
                    </label>
                </div>
            @endforeach
        </div>
        
        <!-- Hidden fields for type -->
        <template x-for="(scenario, key) in scenarios" :key="key">
            <input type="hidden" 
                   :name="selectedScenario === key ? 'type' : ''"
                   :value="selectedScenario === key ? scenario.type : ''">
        </template>
    </div>

    <!-- Pricing Section - Shows based on selected scenario -->
    <div x-show="showFeeFields(selectedScenario)" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         class="bg-green-50 p-6 rounded-lg border border-green-200">
        
        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('memberships.pricing') }}</h3>
        
        <!-- Individual Fee Field -->
        <div x-show="getFeeStructure(selectedScenario) === 'individual_only' || getFeeStructure(selectedScenario) === 'both'"
             class="mb-4">
            <label for="individual_fee" class="block text-sm font-medium text-gray-700">
                {{ __('Individual Fee') }} ({{ currency_symbol() }})
            </label>
            <input type="number" name="individual_fee" id="individual_fee" 
                   value="{{ old('individual_fee', $plan->individual_fee ?? '') }}" 
                   step="0.01" min="0"
                   class="mt-1 block w-full max-w-xs rounded-md border-gray-300 shadow-sm focus:border-green-300 focus:ring focus:ring-green-200 focus:ring-opacity-50">
            <p class="text-xs text-gray-500 mt-1">
                <span x-show="getFeeStructure(selectedScenario) === 'individual_only'">
                    {{ __('memberships.fee_individual_member') }}
                </span>
                <span x-show="getFeeStructure(selectedScenario) === 'both'">
                    {{ __('memberships.fee_individual_subscription') }}
                </span>
            </p>
        </div>

        <!-- Entity Fee Field -->
        <div x-show="getFeeStructure(selectedScenario) === 'entity_only' || getFeeStructure(selectedScenario) === 'both'"
             class="mb-4">
            <label for="entity_fee" class="block text-sm font-medium text-gray-700">
                {{ __('Entity Fee') }} ({{ currency_symbol() }})
            </label>
            <input type="number" name="entity_fee" id="entity_fee" 
                   value="{{ old('entity_fee', $plan->entity_fee ?? '') }}" 
                   step="0.01" min="0"
                   class="mt-1 block w-full max-w-xs rounded-md border-gray-300 shadow-sm focus:border-green-300 focus:ring focus:ring-green-200 focus:ring-opacity-50">
            <p class="text-xs text-gray-500 mt-1">
                <span x-show="getFeeStructure(selectedScenario) === 'entity_only'">
                    {{ __('memberships.fee_entity_institution') }}
                </span>
                <span x-show="getFeeStructure(selectedScenario) === 'both'">
                    {{ __('memberships.fee_entity_subscription') }}
                </span>
            </p>
        </div>

        <!-- VAT Rate Field -->
        <div class="mb-4">
            <label for="vat_rate" class="block text-sm font-medium text-gray-700">
                {{ __('VAT Rate') }}
            </label>
            <select name="vat_rate" id="vat_rate"
                    class="mt-1 block w-full max-w-xs rounded-md border-gray-300 shadow-sm focus:border-green-300 focus:ring focus:ring-green-200 focus:ring-opacity-50">
                @foreach(\Domain\Memberships\Enums\VatRate::options() as $value => $label)
                    <option value="{{ $value }}" {{ old('vat_rate', $plan->vat_rate ?? \Domain\Memberships\Enums\VatRate::default()->value) == $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            <p class="text-xs text-gray-500 mt-1">{{ __('Select the applicable VAT rate for this plan') }}</p>
        </div>

        <!-- Moloni Reference Field -->
        <div class="mb-4">
            <label for="moloni_reference" class="block text-sm font-medium text-gray-700">
                {{ __('moloni.product_reference') }}
            </label>
            <input type="text" name="moloni_reference" id="moloni_reference"
                   value="{{ old('moloni_reference', $plan->moloni_reference ?? '') }}"
                   maxlength="50"
                   class="mt-1 block w-full max-w-xs rounded-md border-gray-300 shadow-sm focus:border-green-300 focus:ring focus:ring-green-200 focus:ring-opacity-50">
            <p class="text-xs text-gray-500 mt-1">{{ __('moloni.product_reference_help') }}</p>
        </div>

        <!-- Validation Plan Option -->
        <div class="mb-4">
            <div class="bg-indigo-50 p-4 rounded-lg border border-indigo-200">
                <label class="flex items-start">
                    <input type="checkbox" name="is_validation_plan" value="1" 
                           {{ old('is_validation_plan', $plan->is_validation_plan ?? false) ? 'checked' : '' }}
                           class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <div class="ml-3">
                        <span class="text-sm font-medium text-gray-900">{{ __('memberships.validation_plan') }}</span>
                        <p class="text-xs text-gray-600 mt-1">{{ __('memberships.validation_plan_help') }}</p>
                        <div class="text-xs text-indigo-600 mt-2">
                            <strong>{{ __('memberships.validation_plan_enables') }}:</strong>
                            <ul class="list-disc list-inside mt-1 space-y-1">
                                <li>{{ __('memberships.insurance_requests') }}</li>
                                <li>{{ __('memberships.license_requests') }}</li>
                                <li>{{ __('memberships.entity_member_licenses') }}</li>
                            </ul>
                        </div>
                    </div>  
                </label>
            </div>
        </div>

        <!-- Free Plan Option -->
        <div class="bg-white p-3 rounded border border-gray-200">
            <label class="flex items-center">
                <input type="checkbox" class="rounded border-gray-300 text-green-600 shadow-sm focus:border-green-300 focus:ring focus:ring-green-200 focus:ring-opacity-50">
                <span class="ml-2 text-sm text-gray-700">{{ __('memberships.free_plan_option') }}</span>
            </label>
        </div>
    </div>

    <!-- Duration and Dates -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
            <label for="duration_months" class="block text-sm font-medium text-gray-700">{{ __('Duration (Months)') }}</label>
            <input type="number" name="duration_months" id="duration_months" 
                   value="{{ old('duration_months', $plan->duration_months ?? 12) }}" 
                   min="1"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
        </div>
        
        <div>
            <label for="start_date" class="block text-sm font-medium text-gray-700">{{ __('Start Date') }}</label>
            <input type="date" name="start_date" id="start_date" 
                   value="{{ old('start_date', $plan->start_date ? $plan->start_date->format('Y-m-d') : '') }}"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            <p class="text-xs text-gray-500 mt-1">{{ __('memberships.immediate_availability') }}</p>
        </div>
        
        <div>
            <label for="end_date" class="block text-sm font-medium text-gray-700">{{ __('End Date') }}</label>
            <input type="date" name="end_date" id="end_date" 
                   value="{{ old('end_date', $plan->end_date ? $plan->end_date->format('Y-m-d') : '') }}"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            <p class="text-xs text-gray-500 mt-1">{{ __('memberships.no_expiration') }}</p>
        </div>
    </div>

    <!-- Description -->
    <div>
        <label for="description" class="block text-sm font-medium text-gray-700">{{ __('Description') }}</label>
        <textarea name="description" id="description" rows="4" 
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                  placeholder="{{ __('memberships.description_help') }}">{{ old('description', $plan->description ?? '') }}</textarea>
    </div>

    <!-- Attachments -->
    <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Attachments') }}</h3>
        
        <div>
            <label for="attachments" class="block text-sm font-medium text-gray-700">{{ __('memberships.pdf_documents') }}</label>
            <input type="file" name="attachments[]" id="attachments" 
                   accept="application/pdf" multiple
                   class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            <p class="text-xs text-gray-500 mt-1">{{ __('memberships.upload_documents_help') }}</p>
        </div>

        @if(!empty($plan->getMedia('affiliation_attachments')))
            <div class="mt-4">
                <h4 class="text-sm font-medium text-gray-700 mb-2">{{ __('memberships.current_attachments') }}</h4>
                @foreach($plan->getMedia('affiliation_attachments') as $media)
                    <div class="flex items-center space-x-3 p-2 bg-white rounded border">
                        <input type="checkbox" name="keep_attachments[]" value="{{ $media->id }}" checked
                               class="rounded border-gray-300 text-blue-600">
                        <span class="text-sm">{{ $media->file_name }}</span>
                        <span class="text-xs text-gray-500">({{ number_format($media->size / 1024, 1) }} KB)</span>
                    </div>
                @endforeach
                <p class="text-xs text-gray-500 mt-1">{{ __('memberships.uncheck_remove_files') }}</p>
            </div>
        @endif
    </div>

    <!-- Summary Box -->
    <div x-show="selectedScenario" 
         class="bg-yellow-50 p-6 rounded-lg border border-yellow-200">
        <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ __('memberships.plan_summary') }}</h3>
        <template x-for="(scenario, key) in scenarios" :key="key">
            <div x-show="selectedScenario === key">
                <p class="text-sm text-gray-700"><strong>{{ __('Type') }}:</strong> <span x-text="scenario.label"></span></p>
                <p class="text-sm text-gray-700 mt-1"><strong>{{ __('memberships.usage') }}:</strong> <span x-text="scenario.description"></span></p>
            </div>
        </template>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    // Auto-clear irrelevant fee fields when scenario changes
    Alpine.data('formData', () => ({
        init() {
            this.$watch('selectedScenario', (value) => {
                const feeStructure = this.getFeeStructure(value);
                
                // Clear individual fee if not needed
                if (feeStructure !== 'individual_only' && feeStructure !== 'both') {
                    document.getElementById('individual_fee').value = '';
                }
                
                // Clear entity fee if not needed  
                if (feeStructure !== 'entity_only' && feeStructure !== 'both') {
                    document.getElementById('entity_fee').value = '';
                }
            });
        }
    }));
});
</script>