<?php

namespace App\Http\Requests;

use App\Enums\IndividualDocumentTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndividualEditRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'entity_id' => 'sometimes|nullable|integer|exists:entity,id',
            'name' => 'required|string|max:45',
            'surname' => 'nullable|string|max:45',
            'country_id' => 'required|integer|exists:country,id',
            'birthdate' => 'nullable|date',
            'address' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:255',
            'vat_number' => 'nullable|string|max:30',
            'phone' => 'nullable|string|max:20',
            'gender' => 'nullable|in:male,female',
            'doc_ref_type' => ['nullable', Rule::in(IndividualDocumentTypeEnum::values())],
            'doc_ref' => 'nullable|string|max:45',
            'doc_ref_validation_date' => 'nullable|date',
            'national_federation_number' => 'nullable|string',
            'member_number' => ['nullable', 'integer', Rule::unique('individual', 'member_number')->ignore($this->route('individual'))->whereNull('deleted_at')],
            'email' => 'nullable|email',
            'logo' => 'nullable|image',
            'committee_id' => 'sometimes|required|integer|exists:committee,id',
            'sport_id' => 'sometimes|required|integer|exists:sports,id',
            'federation_id' => 'nullable|array',
            'federation_id.*' => 'integer|exists:federation,id',
            'professional_role_ids' => 'nullable|array',
            'facebook_url' => 'nullable|url:http,https',
            'x_url' => 'nullable|url:http,https',
            'instagram_url' => 'nullable|url:http,https',
            'linkedin_url' => 'nullable|url:http,https',
            'district_id' => 'nullable|integer|exists:districts,id',
            'zone_ids' => 'nullable|array',
            'zone_ids.*' => 'integer|exists:zones,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'member_number.unique' => __('individuals.member_number_already_taken'),
        ];
    }
}
