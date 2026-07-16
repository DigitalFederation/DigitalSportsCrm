<?php

namespace App\Http\Requests;

use App\Enums\CertificationCategoryEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class CertificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        // Get the raw input data
        $input = $this->all();

        // Handle the checkbox values
        $input['is_available'] = $this->has('is_available') &&
                                    ($this->is_available === '1' || $this->is_available === 1 || $this->is_available === true);

        $input['allow_entity_group_request'] = $this->has('allow_entity_group_request') &&
                                    ($this->allow_entity_group_request === '1' || $this->allow_entity_group_request === 1 || $this->allow_entity_group_request === true);

        $input['requires_admin_validation'] = $this->has('requires_admin_validation') &&
                                    ($this->requires_admin_validation === '1' || $this->requires_admin_validation === 1 || $this->requires_admin_validation === true);

        // Replace the input data with our processed data
        $this->replace($input);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'parent_id' => 'nullable|array|exists:certification,id',
            'name' => 'required|string|max:45',
            'acronym' => 'nullable|string|max:10',
            'committee_id' => 'required|integer|exists:committee,id',
            'professional_role_id' => 'nullable|integer',
            'license_id' => 'nullable|integer|exists:license,id',
            'certification_view' => 'nullable|file|mimes:png,jpg,jpeg|max:1024',
            'certification_category' => ['nullable', 'string', new Enum(CertificationCategoryEnum::class)],
            'offset_initial' => ['nullable', 'integer', 'min:0'],
            'offset_current' => ['nullable', 'integer', 'min:0'],
            'minimum_age' => ['nullable', 'string'],
            'confined_water_sessions' => ['nullable', 'string'],
            'open_water_sessions' => ['nullable', 'string'],
            'theoretical_sessions' => ['nullable', 'string'],
            'parents' => ['nullable', 'array'],
            'parents.*' => ['integer', 'exists:certification,id'],
            // Pricing fields
            'is_available' => 'nullable|boolean',
            'unit_value' => ['nullable', 'numeric', 'min:0', 'max:9999.99', new \App\Rules\CurrencyScale],
            'unit_value_individual' => ['nullable', 'numeric', 'min:0', 'max:9999.99', new \App\Rules\CurrencyScale],
            'unit_value_entity' => ['nullable', 'numeric', 'min:0', 'max:9999.99', new \App\Rules\CurrencyScale],
            'tax_value' => ['nullable', 'numeric', 'min:0', 'max:999.99', new \App\Rules\CurrencyScale],
            'tax_percentage' => 'nullable|numeric|min:0|max:100',
            'moloni_reference' => 'nullable|string|max:50',
            // New pricing fields
            'digital_price' => ['nullable', 'numeric', 'min:0', 'max:9999.99', new \App\Rules\CurrencyScale],
            'digital_plus_card_price' => ['nullable', 'numeric', 'min:0', 'max:9999.99', new \App\Rules\CurrencyScale],
            'requester_model' => 'nullable|string|in:Individual,Entity,all',
            'allow_entity_group_request' => 'nullable|boolean',
            'requires_admin_validation' => 'nullable|boolean',
            'roles' => 'nullable|array',
            'roles.*' => 'integer|exists:roles,id',
        ];
    }
}
