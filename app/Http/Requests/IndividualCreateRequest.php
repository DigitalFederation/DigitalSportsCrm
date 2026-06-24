<?php

namespace App\Http\Requests;

use App\Enums\IndividualDocumentTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndividualCreateRequest extends FormRequest
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
            'federation_id' => 'required|integer|exists:federation,id',
            'entity_id' => 'nullable|integer|exists:entity,id',
            'name' => [
                'required',
                'string',
                'max:45',
            ],
            'surname' => [
                'required',
                'string',
                'max:45',
            ],
            'native_name' => 'required|string|max:255',
            'birthdate' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    $exists = \Domain\Individuals\Models\Individual::where('name', $this->name)
                        ->where('surname', $this->surname)
                        ->where('birthdate', $value)
                        ->where('country_id', $this->country_id)
                        ->whereNull('deleted_at')
                        ->exists();

                    if ($exists) {
                        $fail(__('validation.individual_already_exists'));
                    }
                },
            ],
            'country_id' => 'required|integer|exists:country,id',
            'gender' => 'required|in:male,female',
            'address' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'postal_code' => 'required|string|max:255',
            'vat_number' => 'required|string|max:30',
            'phone' => 'required|string|max:20',
            'doc_ref_type' => ['required', Rule::in(IndividualDocumentTypeEnum::values())],
            'doc_ref' => 'required|string|max:45',
            'doc_ref_validation_date' => 'required|date',
            'email' => 'required|email|unique:users,email',
            'logo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'committee_id' => 'sometimes|required|integer|exists:committee,id',
            'sport_id' => 'sometimes|required|integer|exists:sports,id',
            'national_federation_number' => 'nullable|string',
            'member_number' => ['nullable', 'integer', Rule::unique('individual', 'member_number')->whereNull('deleted_at')],
            'professional_role_ids' => 'nullable|array',
            'facebook_url' => 'nullable|url:http,https',
            'x_url' => 'nullable|url:http,https',
            'instagram_url' => 'nullable|url:http,https',
            'linkedin_url' => 'nullable|url:http,https',
            'district_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    if ($value === 'outside_portugal') {
                        return; // Valid special value
                    }
                    if (! is_numeric($value) || ! \Domain\Geographic\Models\District::where('id', $value)->exists()) {
                        $fail(__('validation.invalid_district'));
                    }
                },
            ],
            'zone_ids' => 'nullable|array',
            'zone_ids.*' => 'integer|exists:zones,id',
            'terms_accepted' => 'accepted',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'name.required' => __('validation.name_required'),
            'surname.required' => __('validation.surname_required'),
            'native_name.required' => __('validation.full_name_required'),
            'birthdate.required' => __('validation.birthdate_required'),
            'country_id.required' => __('validation.country_required'),
            'email.unique' => __('validation.email_already_registered'),
            'logo.required' => __('validation.photo_required'),
            'logo.image' => __('validation.file_must_be_image'),
            'logo.mimes' => __('validation.photo_must_be_jpeg_png'),
            'logo.max' => __('validation.photo_max_2mb'),
            'district_id.required' => __('validation.district_required'),
            'district_id.exists' => __('validation.invalid_district'),
            'gender.required' => __('validation.sex_required'),
            'vat_number.required' => __('validation.vat_number_required'),
            'phone.required' => __('validation.phone_required'),
            'address.required' => __('validation.address_required'),
            'location.required' => __('validation.location_required'),
            'postal_code.required' => __('validation.postal_code_required'),
            'doc_ref_type.required' => __('validation.doc_type_required'),
            'doc_ref.required' => __('validation.doc_number_required'),
            'doc_ref_validation_date.required' => __('validation.doc_expiry_required'),
            'terms_accepted.accepted' => __('individual.terms_privacy_required'),
            'member_number.unique' => __('individuals.member_number_already_taken'),
        ];
    }
}
