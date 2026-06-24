<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LicenseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'is_school_license' => $this->has('is_school_license'),
            'allow_entity_group_request' => $this->has('allow_entity_group_request'),
            'requires_official_documents' => $this->has('requires_official_documents'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            // Removed federation_id - licenses are now associated with federations via many-to-many relationship
            'committee_id' => 'required|integer',
            'type_id' => 'required|integer',
            'function_id' => 'required_if:type_id,2|nullable|exists:professional_roles,id',
            'sport_ids' => 'nullable|array',
            'sport_ids.*' => 'exists:sports,id',
            'unit_value' => 'nullable|string',
            'unit_value_individual' => 'nullable|string',
            'unit_value_entity' => 'nullable|string',
            'unit_value_federation' => 'nullable|string',
            'tax_percentage' => 'nullable|numeric|max:100',
            'tax_value' => 'nullable|numeric|max:100',
            'moloni_reference' => 'nullable|string|max:50',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'license_code' => 'nullable|string|max:200',
            'interval' => 'nullable|numeric|max:100',
            'interval_unit' => 'nullable|string|max:200',
            'validity_type' => 'nullable|in:fixed_duration,calendar_year',
            'requester_model' => 'nullable|array',
            'requester_model.*' => 'required|string|in:Individual,Entity,Federation',
            'is_school_license' => 'boolean',
            'allow_entity_group_request' => 'boolean',
            'requires_official_documents' => 'boolean',
            'required_document_types' => 'nullable|array',
            'required_document_types.*' => 'string|in:' . implode(',', array_column(\App\Enums\OfficialDocumentTypeEnum::cases(), 'value')),
            'required_athlete_documents' => 'nullable|array',
            'required_athlete_documents.*' => 'string|in:' . implode(',', array_column(\App\Enums\OfficialDocumentTypeEnum::cases(), 'value')),
            'required_coach_documents' => 'nullable|array',
            'required_coach_documents.*' => 'string|in:' . implode(',', array_column(\App\Enums\OfficialDocumentTypeEnum::cases(), 'value')),
            'required_official_documents' => 'nullable|array',
            'required_official_documents.*' => 'string|in:' . implode(',', array_column(\App\Enums\OfficialDocumentTypeEnum::cases(), 'value')),
            'required_diving_professional_documents' => 'nullable|array',
            'required_diving_professional_documents.*' => 'string|in:' . implode(',', array_column(\App\Enums\OfficialDocumentTypeEnum::cases(), 'value')),
            'required_certifications' => 'nullable|array',
            'required_certifications.*' => 'exists:certification,id',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
            'federation_ids' => 'nullable|array',
            'federation_ids.*' => 'exists:federation,id',
        ];
    }

    public function messages(): array
    {
        return [
            'function_id.required_if' => __('The category role is required when type is individual.'),
        ];
    }
}
