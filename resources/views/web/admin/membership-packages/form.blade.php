@csrf

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Name -->
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700">{{ __('Name') }}</label>
        <input type="text" name="name" id="name" value="{{ old('name', $package->name ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
    </div>

    <!-- Target Type -->
    <div>
        <label for="target_type" class="block text-sm font-medium text-gray-700">{{ __('Target Type') }}</label>
        <select name="target_type" id="target_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
            @foreach(\App\Enums\MembershipTargetType::cases() as $targetType)
                @if(in_array($targetType->value, ['individual', 'entity']))
                    <option value="{{ $targetType->value }}" {{ old('target_type', $package->target_type->value ?? '') === $targetType->value ? 'selected' : '' }}>
                        {{ $targetType->label() }}
                    </option>
                @endif
            @endforeach
        </select>
    </div>

    <!-- Distribution Methods -->
    <div id="distribution-methods-container">
        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Distribution Methods') }}</label>
        <div class="space-y-2">
            <label class="inline-flex items-center">
                <input type="checkbox" name="distribution_methods[]" value="direct" 
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                    {{ in_array('direct', old('distribution_methods', $package->distribution_methods ?? ['direct'])) ? 'checked' : '' }}>
                <span class="ml-2">{{ __('Direct (Members can subscribe directly)') }}</span>
            </label>
            <label class="inline-flex items-center" id="entity-managed-option">
                <input type="checkbox" name="distribution_methods[]" value="entity_managed" 
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                    {{ in_array('entity_managed', old('distribution_methods', $package->distribution_methods ?? [])) ? 'checked' : '' }}>
                <span class="ml-2">{{ __('Entity Managed (Entities can assign to members)') }}</span>
            </label>
            <label class="inline-flex items-center" id="federation-managed-option">
                <input type="checkbox" name="distribution_methods[]" value="federation_managed" 
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                    {{ in_array('federation_managed', old('distribution_methods', $package->distribution_methods ?? [])) ? 'checked' : '' }}>
                <span class="ml-2">{{ __('Federation Managed (Federations can assign to members)') }}</span>
            </label>
        </div>
    </div>

    <!-- Associated Federation -->
    <div>
        <label for="federation_ids" class="block text-sm font-medium text-gray-700">{{ __('Associated Federation') }}</label>
        <livewire:input.select-multiple
                wire:model.live="federation_ids"
                :items="$availableFederations->pluck('name', 'id')"
                inputId="federation_ids"
                inputName="federation_ids[]"
                identifier="federation_ids"
                :multiple="true"
                :inputSelected="$package?->federations->pluck('id')->toArray() ?? []"
        />
    </div>
</div>

<!-- Description -->
<div class="mt-6">
    <label for="description" class="block text-sm font-medium text-gray-700">{{ __('Description') }}</label>
    <textarea name="description" id="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('description', $package->description ?? '') }}</textarea>
</div>

<!-- Is Active -->
<div class="mt-6">
    <label class="inline-flex items-center">
        <input type="checkbox" name="is_active" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" value="1" {{ old('is_active', $package->is_active ?? true) ? 'checked' : '' }}>
        <span class="ml-2">{{ __('Active') }}</span>
    </label>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
    <!-- Affiliation Plans -->
    <div>
        <label for="affiliation_plan_ids" class="block text-sm font-medium text-gray-700">{{ __('Affiliation Plans') }}</label>
        <livewire:input.select-multiple
                wire:model.live="affiliation_plans"
                :items="$affiliationPlans"
                inputId="affiliation_plans"
                inputName="affiliation_plan_ids[]"
                identifier="affiliation_plans"
                :multiple="true"
                :inputSelected="$package?->affiliationPlans->pluck('id')->toArray() ?? []"/>
    </div>

    <!-- Insurance Plans -->
    <div>
        <label for="insurance_plan_ids" class="block text-sm font-medium text-gray-700">{{ __('main.insurance_plans') }}</label>
        <livewire:input.select-multiple
                wire:model.live="insurance_plans"
                :items="$insurancePlans->pluck('name', 'id')"
                inputId="insurance_plans"
                inputName="insurance_plan_ids[]"
                identifier="insurance_plans"
                :multiple="true"
                :inputSelected="$package?->insurancePlans->pluck('id')->toArray() ?? []"/>
    </div>
</div>

<script>
function updateDistributionMethods(isUserAction = false) {
    const targetType = document.getElementById('target_type').value;
    const entityManagedOption = document.getElementById('entity-managed-option');
    const federationManagedOption = document.getElementById('federation-managed-option');
    const entityManagedCheckbox = entityManagedOption.querySelector('input[type="checkbox"]');
    const federationManagedCheckbox = federationManagedOption.querySelector('input[type="checkbox"]');
    const directCheckbox = document.querySelector('input[name="distribution_methods[]"][value="direct"]');
    
    if (targetType === 'entity') {
        // Hide entity-managed option for entity packages, but show federation-managed
        entityManagedOption.style.display = 'none';
        federationManagedOption.style.display = 'flex';
        entityManagedCheckbox.checked = false;
        
        // Only auto-check direct on user action (target type change), not on page load
        if (isUserAction) {
            // Check if at least one checkbox is selected, if not, select direct
            if (!directCheckbox.checked && !federationManagedCheckbox.checked) {
                directCheckbox.checked = true;
            }
        }
    } else {
        // Show entity-managed and federation-managed options for individual packages
        entityManagedOption.style.display = 'flex';
        federationManagedOption.style.display = 'flex';
    }
}

// Run on page load (not a user action)
document.addEventListener('DOMContentLoaded', function() {
    updateDistributionMethods(false);
    
    // Add event listener for target type changes (user action)
    document.getElementById('target_type').addEventListener('change', function() {
        updateDistributionMethods(true);
    });
});
</script>