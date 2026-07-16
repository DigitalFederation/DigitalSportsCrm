<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700">{{ __('Name') }}</label>
        <input type="text" name="name" id="name" value="{{ old('name', $plan->name ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
    </div>

    <div class="flex justify-between gap-x-2">
        <div class="sm:w-1/3 w-full">
            <label for="duration_months" class="block text-sm font-medium text-gray-700">{{ __('Duration (Months)') }}</label>
            <input type="number" name="duration_months" id="duration_months" value="{{ old('duration_months', $plan->duration_months ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
        </div>
        <div class="sm:w-1/3 w-full">
            <label class="block text-sm font-medium mb-1"
                   for="start_date">{{ __('Start Date') }}</label>
            <input type="date"
                   class="form-input w-full {{ $errors->has('start_date') ? 'border-rose-300' : '' }}"
                   name="start_date"
                   id="start_date" value="{{ old('start_date', $plan->start_date ? $plan->start_date->format('Y-m-d') : '') }}">
            @if($errors->has('start_date'))
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $errors->first('start_date') }}
                </div>
            @endif
        </div>
        <div class="sm:w-1/3 w-full">
            <label class="block text-sm font-medium mb-1"
                   for="end_date">{{ __('End Date') }}</label>
            <input type="date"
                   class="form-input w-full {{ $errors->has('end_date') ? 'border-rose-300' : '' }}"
                   name="end_date"
                   id="end_date" value="{{ old('end_date', $plan->end_date ? $plan->end_date->format('Y-m-d') : '') }}">
            @if($errors->has('end_date'))
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $errors->first('end_date') }}
                </div>
            @endif
        </div>
    </div>

    <div class="flex gap-x-2">
        <div class="md:w-1/3">
            <label for="individual_fee" class="block text-sm font-medium text-gray-700">{{ __('Individual Fee') }}({{ currency_symbol() }})</label>
            <input type="number" name="individual_fee" id="individual_fee" value="{{ old('individual_fee', $plan->individual_fee ?? '') }}" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" >
        </div>
        <div class="md:w-1/3">
            <label for="entity_fee" class="block text-sm font-medium text-gray-700">{{ __('Entity Fee') }}({{ currency_symbol() }})</label>
            <input type="number" name="entity_fee" id="entity_fee" value="{{ old('entity_fee', $plan->entity_fee ?? '') }}" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" >
        </div>
        <div class="md:w-1/3">
            <label for="type" class="block text-sm font-medium text-gray-700">{{ __('Type') }}</label>
            <select name="type" id="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                <option value="">{{ __('Select type') }}</option>
                <option value="individual" {{ old('type', $plan->type ?? '') == 'individual' ? 'selected' : '' }}>{{ __('Individual') }}</option>
                <option value="entity" {{ old('type', $plan->type ?? '') == 'entity' ? 'selected' : '' }}>{{ __('Entity') }}</option>
            </select>
        </div>
    </div>

    <div class="flex gap-x-2">
        <div class="md:w-1/3">
            <label for="vat_rate" class="block text-sm font-medium text-gray-700">{{ __('VAT Rate') }}</label>
            <select name="vat_rate" id="vat_rate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                @foreach(\Domain\Memberships\Enums\PortugueseVatRate::options() as $value => $label)
                    <option value="{{ $value }}" {{ old('vat_rate', $plan->vat_rate ?? \Domain\Memberships\Enums\PortugueseVatRate::default()->value) == $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="md:w-1/3">
            <label for="moloni_reference" class="block text-sm font-medium text-gray-700">{{ __('moloni.product_reference') }}</label>
            <input type="text" name="moloni_reference" id="moloni_reference" value="{{ old('moloni_reference', $plan->moloni_reference ?? '') }}" maxlength="50" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
        </div>
        <div class="md:w-1/3">
            <div class="bg-indigo-50 p-3 rounded-lg border border-indigo-200">
                <label class="flex items-start">
                    <input type="checkbox" name="is_validation_plan" value="1"
                           {{ old('is_validation_plan', $plan->is_validation_plan ?? false) ? 'checked' : '' }}
                           class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <div class="ml-2">
                        <span class="text-sm font-medium text-gray-900">{{ __('memberships.validation_plan') }}</span>
                        <p class="text-xs text-gray-600 mt-1">{{ __('memberships.validation_plan_help') }}</p>
                    </div>
                </label>
            </div>
        </div>
    </div>

       <!-- New file upload section -->
    <div class="sm:flex sm:items-top space-y-4 sm:space-y-0 sm:space-x-4">
        <div class="sm:w-full">
            <label class="block text-sm font-medium mb-1" for="attachments">{{ __('Attachments (PDF)') }}</label>
            <input type="file"
                class="form-input w-full {{ $errors->has('attachments') ? 'border-rose-300' : '' }}"
                name="attachments[]"
                id="attachments"
                accept="application/pdf"
                multiple>
            <p class="text-xs text-gray-500 mt-1">{{ __('You can upload multiple PDF files. Max 10MB each.') }}</p>

            @if($errors->has('attachments'))
                <div class="text-xs mt-1 text-rose-500">
                    {{ $errors->first('attachments') }}
                </div>
            @endif

            @if($errors->has('attachments.*'))
                <div class="text-xs mt-1 text-rose-500">
                    {{ $errors->first('attachments.*') }}
                </div>
            @endif
        </div>
        @if(!empty($plan->getMedia('affiliation_attachments')))
        <div class="sm:w-full mt-4">
            @foreach($plan->getMedia('affiliation_attachments') as $media)
            <div class="flex items-center gap-x-2 ml-0">
                <input type="checkbox" name="keep_attachments[]" value="{{ $media->id }}" checked>
                <span class="text-sm">{{ $media->file_name }}</span>
            </div>
            @endforeach
        </div>
        @endif
    </div>

</div>

<div class="mt-6">
    <!-- Select for Federations list -->
    <div class="mt-6">
        <label for="federation_id" class="block text-sm font-medium text-gray-700">{{ __('Federation') }}</label>
        <select name="federation_id" id="federation_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
            <option value="" selected disabled>{{ __('Select one...') }}</option>
            @foreach($federations as $federation)
                <option value="{{ $federation->id }}" {{ old('federation_id', $plan->federation_id ?? '') == $federation->id ? 'selected' : '' }}>{{ $federation->name }}</option>
            @endforeach
        </select>
    </div>
    
</div>

<div class="mt-6">
    <label for="description" class="block text-sm font-medium text-gray-700">{{ __('Description') }}</label>
    <textarea name="description" id="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('description', $plan->description ?? '') }}</textarea>
</div>