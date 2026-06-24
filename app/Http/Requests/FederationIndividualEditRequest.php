<?php

namespace App\Http\Requests;

use App\Enums\IndividualDocumentTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FederationIndividualEditRequest extends FormRequest
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
            'name' => 'nullable',
            'surname' => 'nullable',
            'birthdate' => 'nullable',
            'gender' => 'nullable',
            'country_id' => 'nullable',
            'address' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:255',
            'vat_number' => 'nullable|string|max:30',
            'phone' => 'nullable|string|max:20',
            'doc_ref_type' => ['nullable', Rule::in(IndividualDocumentTypeEnum::values())],
            'doc_ref' => 'nullable|string|max:45',
            'doc_ref_validation_date' => 'nullable|date',
            'email' => 'nullable|email',
            'federation_id' => 'nullable|integer|exists:federation,id',
            'national_federation_number' => 'nullable|string',
            'logo' => 'nullable|image',
            'committee_id' => 'sometimes|required|integer|exists:committee,id',
            'sport_id' => 'sometimes|required|integer|exists:sports,id',
            'facebook_url' => 'nullable|url:http,https',
            'x_url' => 'nullable|url:http,https',
            'instagram_url' => 'nullable|url:http,https',
            'linkedin_url' => 'nullable|url:http,https',
            'professional_role_ids' => 'nullable|array',
        ];
    }
}
