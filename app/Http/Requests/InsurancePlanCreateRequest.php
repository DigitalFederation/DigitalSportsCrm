<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InsurancePlanCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'target_audience' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'individual_fee' => 'required_without:entity_fee|nullable|numeric|min:0',
            'entity_fee' => 'required_without:individual_fee|nullable|numeric|min:0',
            'attachments' => 'nullable|array',
            'attachments.*' => 'nullable|file|mimes:pdf|max:10240',
            'policy_number' => 'nullable|string|max:255',
            'policy_number_prefix' => 'nullable|string|max:50',
            'policy_number_sequence' => 'nullable|integer|min:0',
            'policy_number_format' => 'nullable|string|max:100',
            'period' => 'nullable|integer|min:1',
            'period_unit' => 'nullable|string|in:day,week,month,year',
            'description' => 'nullable|string',
            'insured_activity' => 'nullable|string|max:255',
            'territorial_scope' => 'nullable|string|max:255',
            'cmas_license_code' => 'nullable|string|max:255',
            'vat_rate' => 'required|integer|in:0,6,13,23',
            'requires_official_document' => 'nullable|boolean',
            'required_document_type' => 'nullable|string|max:255',
            'requires_active_affiliation' => 'nullable|boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'insurer_address' => 'nullable|string',
            'insurer_email' => 'nullable|email|max:255',
            'insurer_phone' => 'nullable|string|max:50',
            'applicable_deductibles' => 'nullable|string',
            'coverage_details' => 'nullable|string',
            'insurance_company_name' => 'nullable|string|max:255',
            'moloni_reference' => 'nullable|string|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'attachments.*.file' => 'Each attachments must be a valid file.',
            'attachments.*.mimes' => 'Each attachments must be a PDF file.',
            'attachments.*.max' => 'Each attachments must not be larger than 10MB.',
            'individual_fee.required_without' => 'Either Individual Fee or Entity Fee is required.',
            'entity_fee.required_without' => 'Either Individual Fee or Entity Fee is required.',
            'period_unit.in' => 'The period unit must be day, week, month or year.',
            'vat_rate.in' => 'The VAT rate must be one of the valid Portuguese VAT rates: 0%, 6%, 13%, or 23%.',
        ];
    }
}
