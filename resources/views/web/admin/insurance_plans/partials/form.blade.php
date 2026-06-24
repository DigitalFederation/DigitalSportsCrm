<section class="card">

    <div class="sm:flex sm:items-top space-y-4 sm:space-y-0 sm:space-x-4 my-5">
        <div class="sm:w-1/3">
            <label class="block text-sm font-medium mb-1" for="name">{{ __('main.name') }}</label>
            <input type="text" class="form-input w-full {{ $errors->has('name') ? 'border-rose-300' : '' }}"
                name="name" id="name" value="{{ old('name', $insurance_plan->name ?? '') }}">

            @if ($errors->has('name'))
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $errors->first('name') }}
                </div>
            @endif

        </div>

        <div class="sm:w-1/3">
            <label class="block text-sm font-medium mb-1" for="target_audience">{{ __('main.target_audience') }}</label>

            <select name="target_audience" id="target_audience" class="form-select w-full">
                <option value="">{{ __('main.select_option') }}</option>
                @foreach (\App\Enums\InsurancePlansTargetAudienceEnum::cases() as $target_audience)
                    <option value="{{ $target_audience->value }}"
                        {{ old('target_audience', $insurance_plan->target_audience) == $target_audience->value ? 'selected' : '' }}>
                        {{ $target_audience->toString() }}
                    </option>
                @endforeach
            </select>


            @if ($errors->has('target_audience'))
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $errors->first('target_audience') }}
                </div>
            @endif
        </div>

        <div class="sm:w-1/3">
            <label class="block text-sm font-medium mb-1" for="type">{{ __('main.type') }}</label>

            <select name="type" id="type" class="form-select w-full">
                <option value="">{{ __('main.select_option') }}</option>
                @foreach (\App\Enums\InsurancePlansTypeEnum::cases() as $type)
                    <option value="{{ $type->value }}"
                        {{ old('type', $insurance_plan->type?->value) == $type->value ? 'selected' : '' }}>
                        {{ $type->toString() }}
                    </option>
                @endforeach
            </select>


            @if ($errors->has('type'))
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $errors->first('type') }}
                </div>
            @endif
        </div>

    </div>
    <div class="sm:flex sm:items-top space-y-4 sm:space-y-0 sm:space-x-4 my-5">
        <div class="sm:w-1/4">
            <label class="block text-sm font-medium mb-1" for="period">{{ __('main.duration_months') }}</label>
            <div class="flex items-center">
                <input type="number" class="form-input w-full {{ $errors->has('period') ? 'border-rose-300' : '' }}"
                    name="period" id="period" min="1"
                    value="{{ old('period', $insurance_plan->period ?? '') }}">
                <select name="period_unit" id="period_unit" class="form-select ml-2">
                    <option value="day"
                        {{ old('period_unit', $insurance_plan->period_unit) == 'day' ? 'selected' : '' }}>
                        {{ __('Day(s)') }}</option>
                    <option value="week"
                        {{ old('period_unit', $insurance_plan->period_unit) == 'week' ? 'selected' : '' }}>
                        {{ __('Week(s)') }}</option>
                    <option value="month"
                        {{ old('period_unit', $insurance_plan->period_unit) == 'month' ? 'selected' : '' }}>
                        {{ __('Month(s)') }}</option>
                    <option value="year"
                        {{ old('period_unit', $insurance_plan->period_unit) == 'year' ? 'selected' : '' }}>
                        {{ __('Year(s)') }}</option>
                </select>
            </div>
            @if ($errors->has('period'))
                <div class="text-xs mt-1 text-rose-500">
                    {{ $errors->first('period') }}
                </div>
            @endif
            @if ($errors->has('period_unit'))
                <div class="text-xs mt-1 text-rose-500">
                    {{ $errors->first('period_unit') }}
                </div>
            @endif
        </div>
    </div>
    <div class="sm:flex sm:items-top space-y-4 sm:space-y-0 sm:space-x-4 my-5">
        <div class="sm:w-1/5">
            <label class="block text-sm font-medium mb-1" for="individual_fee">{{ __('main.individual_fee') }}
                (€)</label>
            <input type="number"
                class="form-input w-full {{ $errors->has('individual_fee') ? 'border-rose-300' : '' }}"
                name="individual_fee" step="0.01" id="individual_fee"
                value="{{ old('individual_fee', $insurance_plan->individual_fee ?? '') }}">
            @if ($errors->has('individual_fee'))
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $errors->first('individual_fee') }}
                </div>
            @endif
        </div>
        <div class="sm:w-1/5">
            <label class="block text-sm font-medium mb-1" for="entity_fee">{{ __('main.entity_fee') }} (€)</label>
            <input type="number" class="form-input w-full {{ $errors->has('entity_fee') ? 'border-rose-300' : '' }}"
                name="entity_fee" step="0.01" id="entity_fee"
                value="{{ old('entity_fee', $insurance_plan->entity_fee ?? '') }}">
            @if ($errors->has('entity_fee'))
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $errors->first('entity_fee') }}
                </div>
            @endif
        </div>
        <div class="sm:w-1/5">
            <label class="block text-sm font-medium mb-1" for="vat_rate">{{ __('main.vat_rate') }}</label>
            <select name="vat_rate" id="vat_rate" class="form-select w-full {{ $errors->has('vat_rate') ? 'border-rose-300' : '' }}">
                @foreach(\Domain\Memberships\Enums\VatRate::options() as $value => $label)
                    <option value="{{ $value }}" {{ old('vat_rate', $insurance_plan->vat_rate ?? \Domain\Memberships\Enums\VatRate::default()->value) == $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            @if ($errors->has('vat_rate'))
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $errors->first('vat_rate') }}
                </div>
            @endif
        </div>
        <div class="sm:w-1/5">
            <label class="block text-sm font-medium mb-1" for="moloni_reference">{{ __('moloni.product_reference') }}</label>
            <input type="text" name="moloni_reference" id="moloni_reference"
                class="form-input w-full {{ $errors->has('moloni_reference') ? 'border-rose-300' : '' }}"
                value="{{ old('moloni_reference', $insurance_plan->moloni_reference ?? '') }}" maxlength="50">
            <p class="text-xs text-gray-500 mt-1">{{ __('moloni.product_reference_help') }}</p>
            @if ($errors->has('moloni_reference'))
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $errors->first('moloni_reference') }}
                </div>
            @endif
        </div>
    </div>
    <div class="sm:flex sm:items-top space-y-4 sm:space-y-0 sm:space-x-4 my-5">
        <div class="sm:w-1/5">
            <label class="block text-sm font-medium mb-1" for="start_date">{{ __('main.start_date') }}</label>
            <input type="date" class="form-input w-full {{ $errors->has('start_date') ? 'border-rose-300' : '' }}"
                name="start_date" id="start_date"
                value="{{ old('start_date', $insurance_plan->start_date ? $insurance_plan->start_date->format('Y-m-d') : '') }}">
            @if ($errors->has('start_date'))
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $errors->first('start_date') }}
                </div>
            @endif
        </div>
        <div class="sm:w-1/5">
            <label class="block text-sm font-medium mb-1" for="end_date">{{ __('main.end_date') }}</label>
            <input type="date" class="form-input w-full {{ $errors->has('end_date') ? 'border-rose-300' : '' }}"
                name="end_date" id="end_date"
                value="{{ old('end_date', $insurance_plan->end_date ? $insurance_plan->end_date->format('Y-m-d') : '') }}">
            @if ($errors->has('end_date'))
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $errors->first('end_date') }}
                </div>
            @endif
        </div>
    </div>

    <!-- New file upload section -->
    <div class="sm:flex sm:items-top gap-x-2 my-5">
        <div class="sm:w-1/2">
            <label class="block text-sm font-medium mb-1" for="attachments">{{ __('main.attachments') }}</label>
            <input type="file" class="form-input w-full {{ $errors->has('attachments') ? 'border-rose-300' : '' }}"
                name="attachments[]" id="attachments" accept="application/pdf" multiple>
            <p class="text-xs text-gray-500 mt-1">{{ __('main.upload_pdf_hint') }}</p>

            @if ($errors->has('attachments'))
                <div class="text-xs mt-1 text-rose-500">
                    {{ $errors->first('attachments') }}
                </div>
            @endif

            @if ($errors->has('attachments.*'))
                <div class="text-xs mt-1 text-rose-500">
                    {{ $errors->first('attachments.*') }}
                </div>
            @endif
        </div>
        <div class="sm:w-1/2">
            @if (!empty($insurance_plan->getMedia('insurance_attachments')))
                <div>
                    <label class="block text-sm font-medium mb-1"
                        for="attachments">{{ __('main.uploaded_attachments') }}</label>
                    @foreach ($insurance_plan->getMedia('insurance_attachments') as $media)
                        <div class="flex items-center gap-x-2 ml-0">
                            <input type="checkbox" name="keep_attachments[]" value="{{ $media->id }}" checked>
                            <span class="text-sm">{{ $media->file_name }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
    <!-- Display existing prospects if editing -->
    @if (isset($insurance_plan) && $insurance_plan->getMedia('prospects')->isNotEmpty())
        <div class="mt-4">
            <h3 class="text-sm font-medium mb-2">{{ __('Current Prospects:') }}</h3>
            <ul class="list-disc list-inside">
                @foreach ($insurance_plan->getMedia('prospects') as $prospect)
                    <li class="text-sm">
                        {{ $prospect->file_name }}
                        (<a href="{{ $prospect->getUrl() }}" target="_blank"
                            class="text-blue-600 hover:text-blue-800">{{ __('main.view') }}</a>)
                        <button type="button" class="text-red-600 hover:text-red-800 ml-2"
                            onclick="removeProspect({{ $prospect->id }})">{{ __('main.remove') }}</button>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Display Description-->
    <div class="sm:flex sm:items-top space-y-4 sm:space-y-0 sm:space-x-4 my-5">
        <div class="sm:w-1/2">
            <label class="block text-sm font-medium mb-1" for="description">{{ __('main.description') }}</label>
            <textarea name="description" id="description" rows="2"
                class="form-input w-full {{ $errors->has('description') ? 'border-rose-300' : '' }}">{{ old('description', $insurance_plan->description ?? '') }}</textarea>
            @if ($errors->has('description'))
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $errors->first('description') }}
                </div>
            @endif
        </div>
        <div class="sm:w-1/2">
            <label class="block text-sm font-medium mb-1" for="insured_activity">{{ __('main.insured_activity') }}</label>
            <textarea name="insured_activity" id="insured_activity" rows="2"
                class="form-input w-full {{ $errors->has('insured_activity') ? 'border-rose-300' : '' }}">{{ old('insured_activity', $insurance_plan->insured_activity ?? '') }}</textarea>
            @if ($errors->has('insured_activity'))
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $errors->first('insured_activity') }}
                </div>
            @endif
        </div>
    </div>
    <div class="sm:flex sm:items-top space-y-4 sm:space-y-0 sm:space-x-4 my-5">
        <div class="sm:w-1/2">
            <label class="block text-sm font-medium mb-1"
                for="territorial_scope">{{ __('main.territorial_scope') }}</label>
            <textarea name="territorial_scope" id="territorial_scope" rows="2"
                class="form-input w-full {{ $errors->has('territorial_scope') ? 'border-rose-300' : '' }}">{{ old('territorial_scope', $insurance_plan->territorial_scope ?? '') }}</textarea>
            @if ($errors->has('territorial_scope'))
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $errors->first('territorial_scope') }}
                </div>
            @endif
        </div>
        <div class="sm:w-1/2">
            <label class="block text-sm font-medium mb-1"
                for="cmas_license_code">{{ __('main.cmas_license_code') }}</label>
            <input type="text" name="cmas_license_code" id="cmas_license_code"
                class="form-input w-full {{ $errors->has('cmas_license_code') ? 'border-rose-300' : '' }}"
                value="{{ old('cmas_license_code', $insurance_plan->cmas_license_code ?? '') }}">
            @if ($errors->has('cmas_license_code'))
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $errors->first('cmas_license_code') }}
                </div>
            @endif
        </div>
    </div>
    <div class="sm:flex sm:items-top space-y-4 sm:space-y-0 sm:space-x-4 my-5">
        <div class="sm:w-1/2">
            <label class="block text-sm font-medium mb-1"
                for="policy_number">{{ __('main.policy_number') }}</label>
            <input type="text" name="policy_number" id="policy_number"
                class="form-input w-full {{ $errors->has('policy_number') ? 'border-rose-300' : '' }}"
                value="{{ old('policy_number', $insurance_plan->policy_number ?? '') }}">
            <p class="text-xs text-gray-500 mt-1">{{ __('main.policy_number_hint') }}</p>
            @if ($errors->has('policy_number'))
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $errors->first('policy_number') }}
                </div>
            @endif
        </div>
    </div>

    <!-- Insurer Contact Information Section -->
    <div class="border-t pt-4 my-6">
        <h3 class="text-sm font-medium text-gray-900 mb-4">{{ __('insurances.insurer_contact_information') }}</h3>
        <p class="text-xs text-gray-600 mb-4">{{ __('insurances.insurer_contact_information_description') }}</p>

        <div class="sm:flex sm:items-top space-y-4 sm:space-y-0 sm:space-x-4 mb-4">
            <div class="sm:w-1/2">
                <label class="block text-sm font-medium mb-1" for="insurance_company_name">
                    {{ __('insurances.insurance_company_name') }}
                </label>
                <input type="text" name="insurance_company_name" id="insurance_company_name"
                    class="form-input w-full {{ $errors->has('insurance_company_name') ? 'border-rose-300' : '' }}"
                    value="{{ old('insurance_company_name', $insurance_plan->insurance_company_name ?? '') }}"
                    placeholder="AIG, Allianz, Fidelidade, etc.">
                @if ($errors->has('insurance_company_name'))
                    <div class="text-xs mt-1 text-rose-500">
                        {{ $errors->first('insurance_company_name') }}
                    </div>
                @endif
            </div>
        </div>

        <div class="sm:flex sm:items-top space-y-4 sm:space-y-0 sm:space-x-4">
            <div class="sm:w-1/3">
                <label class="block text-sm font-medium mb-1" for="insurer_email">
                    {{ __('insurances.insurer_email') }}
                </label>
                <input type="email" name="insurer_email" id="insurer_email"
                    class="form-input w-full {{ $errors->has('insurer_email') ? 'border-rose-300' : '' }}"
                    value="{{ old('insurer_email', $insurance_plan->insurer_email ?? '') }}"
                    placeholder="insurance@example.test">
                @if ($errors->has('insurer_email'))
                    <div class="text-xs mt-1 text-rose-500">
                        {{ $errors->first('insurer_email') }}
                    </div>
                @endif
            </div>

            <div class="sm:w-1/3">
                <label class="block text-sm font-medium mb-1" for="insurer_phone">
                    {{ __('insurances.insurer_phone') }}
                </label>
                <input type="text" name="insurer_phone" id="insurer_phone"
                    class="form-input w-full {{ $errors->has('insurer_phone') ? 'border-rose-300' : '' }}"
                    value="{{ old('insurer_phone', $insurance_plan->insurer_phone ?? '') }}"
                    placeholder="+351 123 456 789">
                @if ($errors->has('insurer_phone'))
                    <div class="text-xs mt-1 text-rose-500">
                        {{ $errors->first('insurer_phone') }}
                    </div>
                @endif
            </div>
        </div>

        <div class="mt-4">
            <label class="block text-sm font-medium mb-1" for="insurer_address">
                {{ __('insurances.insurer_address') }}
            </label>
            <textarea name="insurer_address" id="insurer_address" rows="2"
                class="form-input w-full {{ $errors->has('insurer_address') ? 'border-rose-300' : '' }}">{{ old('insurer_address', $insurance_plan->insurer_address ?? '') }}</textarea>
            @if ($errors->has('insurer_address'))
                <div class="text-xs mt-1 text-rose-500">
                    {{ $errors->first('insurer_address') }}
                </div>
            @endif
        </div>
    </div>

    <!-- Coverage Details Section -->
    <div class="border-t pt-4 my-6">
        <h3 class="text-sm font-medium text-gray-900 mb-4">{{ __('insurances.coverage_information') }}</h3>
        <p class="text-xs text-gray-600 mb-4">{{ __('insurances.coverage_information_description') }}</p>

        <div class="sm:flex sm:items-top space-y-4 sm:space-y-0 sm:space-x-4">
            <div class="sm:w-1/2">
                <label class="block text-sm font-medium mb-1" for="applicable_deductibles">
                    {{ __('insurances.applicable_deductibles') }}
                </label>
                <textarea name="applicable_deductibles" id="applicable_deductibles" rows="3"
                    class="form-input w-full {{ $errors->has('applicable_deductibles') ? 'border-rose-300' : '' }}"
                    placeholder="{{ __('insurances.applicable_deductibles_placeholder') }}">{{ old('applicable_deductibles', $insurance_plan->applicable_deductibles ?? '') }}</textarea>
                @if ($errors->has('applicable_deductibles'))
                    <div class="text-xs mt-1 text-rose-500">
                        {{ $errors->first('applicable_deductibles') }}
                    </div>
                @endif
            </div>

            <div class="sm:w-1/2">
                <label class="block text-sm font-medium mb-1" for="coverage_details">
                    {{ __('insurances.coverage_details') }}
                </label>
                <textarea name="coverage_details" id="coverage_details" rows="3"
                    class="form-input w-full {{ $errors->has('coverage_details') ? 'border-rose-300' : '' }}"
                    placeholder="{{ __('insurances.coverage_details_placeholder') }}">{{ old('coverage_details', $insurance_plan->coverage_details ?? '') }}</textarea>
                @if ($errors->has('coverage_details'))
                    <div class="text-xs mt-1 text-rose-500">
                        {{ $errors->first('coverage_details') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="border-t pt-4 my-6">
        <h3 class="text-sm font-medium text-gray-900 mb-4">{{ __('main.sequential_policy_number_settings') }}</h3>
        <p class="text-xs text-gray-600 mb-4">{{ __('main.sequential_policy_number_description') }}</p>
        
        <div class="sm:flex sm:items-top space-y-4 sm:space-y-0 sm:space-x-4">
            <div class="sm:w-1/3">
                <label class="block text-sm font-medium mb-1"
                    for="policy_number_prefix">{{ __('main.policy_number_prefix') }}</label>
                <input type="text" name="policy_number_prefix" id="policy_number_prefix"
                    class="form-input w-full {{ $errors->has('policy_number_prefix') ? 'border-rose-300' : '' }}"
                    value="{{ old('policy_number_prefix', $insurance_plan->policy_number_prefix ?? '') }}"
                    placeholder="INS-2024">
                <p class="text-xs text-gray-500 mt-1">{{ __('main.policy_number_prefix_hint') }}</p>
                @if ($errors->has('policy_number_prefix'))
                    <div class="text-xs mt-1 text-rose-500 h-2">
                        {{ $errors->first('policy_number_prefix') }}
                    </div>
                @endif
            </div>
            
            <div class="sm:w-1/3">
                <label class="block text-sm font-medium mb-1"
                    for="policy_number_format">{{ __('main.policy_number_format') }}</label>
                <input type="text" name="policy_number_format" id="policy_number_format"
                    class="form-input w-full {{ $errors->has('policy_number_format') ? 'border-rose-300' : '' }}"
                    value="{{ old('policy_number_format', $insurance_plan->policy_number_format ?? '{prefix}-{sequence}') }}"
                    placeholder="{prefix}-{sequence}">
                <p class="text-xs text-gray-500 mt-1">{{ __('main.policy_number_format_hint') }}</p>
                @if ($errors->has('policy_number_format'))
                    <div class="text-xs mt-1 text-rose-500 h-2">
                        {{ $errors->first('policy_number_format') }}
                    </div>
                @endif
            </div>
            
            <div class="sm:w-1/3">
                <label class="block text-sm font-medium mb-1"
                    for="policy_number_sequence">{{ __('main.current_sequence_number') }}</label>
                <input type="number" name="policy_number_sequence" id="policy_number_sequence" min="0"
                    class="form-input w-full {{ $errors->has('policy_number_sequence') ? 'border-rose-300' : '' }}"
                    value="{{ old('policy_number_sequence', $insurance_plan->policy_number_sequence ?? 0) }}">
                <p class="text-xs text-gray-500 mt-1">{{ __('main.current_sequence_number_hint') }}</p>
                @if ($errors->has('policy_number_sequence'))
                    <div class="text-xs mt-1 text-rose-500 h-2">
                        {{ $errors->first('policy_number_sequence') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Official Document Requirements Section -->
    <div class="border-t pt-4 my-6">
        <h3 class="text-sm font-medium text-gray-900 mb-4">{{ __('main.official_document_requirements') }}</h3>
        <p class="text-xs text-gray-600 mb-4">{{ __('main.official_document_requirements_description') }}</p>
        
        <div class="sm:flex sm:items-top space-y-4 sm:space-y-0 sm:space-x-4">
            <div class="sm:w-1/2">
                <div class="flex items-center">
                    <input type="checkbox" name="requires_official_document" id="requires_official_document" 
                        value="1" class="form-checkbox text-blue-600" 
                        {{ old('requires_official_document', $insurance_plan->requires_official_document ?? false) ? 'checked' : '' }}>
                    <label class="ml-2 block text-sm font-medium" for="requires_official_document">
                        {{ __('main.requires_official_document') }}
                    </label>
                </div>
                <p class="text-xs text-gray-500 mt-1">{{ __('main.requires_official_document_hint') }}</p>
                @if ($errors->has('requires_official_document'))
                    <div class="text-xs mt-1 text-rose-500">
                        {{ $errors->first('requires_official_document') }}
                    </div>
                @endif
            </div>
            
            <div class="sm:w-1/2">
                <label class="block text-sm font-medium mb-1" for="required_document_type">
                    {{ __('main.required_document_type') }}
                </label>
                <select name="required_document_type" id="required_document_type" 
                    class="form-select w-full {{ $errors->has('required_document_type') ? 'border-rose-300' : '' }}">
                    <option value="">{{ __('main.select_document_type') }}</option>
                    @foreach (\App\Enums\OfficialDocumentTypeEnum::cases() as $documentType)
                        @if (str_starts_with($documentType->value, 'Insurance'))
                            <option value="{{ $documentType->value }}"
                                {{ old('required_document_type', $insurance_plan->required_document_type) == $documentType->value ? 'selected' : '' }}>
                                {{ \App\Enums\OfficialDocumentTypeEnum::toString($documentType) }}
                            </option>
                        @endif
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 mt-1">{{ __('main.required_document_type_hint') }}</p>
                @if ($errors->has('required_document_type'))
                    <div class="text-xs mt-1 text-rose-500">
                        {{ $errors->first('required_document_type') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Affiliation Requirements Section -->
    <div class="border-t pt-4 my-6">
        <h3 class="text-sm font-medium text-gray-900 mb-4">{{ __('main.affiliation_requirements') }}</h3>
        <p class="text-xs text-gray-600 mb-4">{{ __('main.affiliation_requirements_description') }}</p>
        
        <div class="sm:w-full">
            <div class="flex items-center">
                <input type="checkbox" name="requires_active_affiliation" id="requires_active_affiliation" 
                    value="1" class="form-checkbox text-blue-600" 
                    {{ old('requires_active_affiliation', $insurance_plan->requires_active_affiliation ?? true) ? 'checked' : '' }}>
                <label class="ml-2 block text-sm font-medium" for="requires_active_affiliation">
                    {{ __('main.requires_active_affiliation') }}
                </label>
            </div>
            <p class="text-xs text-gray-500 mt-1">{{ __('main.requires_active_affiliation_hint') }}</p>
            @if ($errors->has('requires_active_affiliation'))
                <div class="text-xs mt-1 text-rose-500">
                    {{ $errors->first('requires_active_affiliation') }}
                </div>
            @endif
        </div>
    </div>

    <x-forms.card-form-submit backRoute="admin.insurance-plans.index"
        :buttonText="__('main.save_record')"></x-forms.card-form-submit>
</section>
