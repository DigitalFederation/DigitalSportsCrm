<?php

namespace App\Http\Requests;

use App\Enums\IndividualDocumentTypeEnum;
use Domain\Geographic\Models\District;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateIndividualProfileRequest extends FormRequest
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'logo' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif',
                'max:2048',
            ],
            'name' => 'required|string|max:45',
            'surname' => 'required|string|max:45',
            'first_name_latin' => 'nullable|string|max:45',
            'last_name_latin' => 'nullable|string|max:45',
            'native_name' => 'required|string|max:255',
            'country_id' => 'required|integer|exists:country,id',
            'birthdate' => 'required|date',
            'gender' => 'required|in:male,female',
            'district_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    if ($value === 'outside_portugal') {
                        return;
                    }
                    if (! District::where('id', $value)->exists()) {
                        $fail(__('validation.invalid_district'));
                    }
                },
            ],
            'address' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'vat_number' => 'required|string|max:30',
            'doc_ref_type' => ['required', 'string', Rule::in(IndividualDocumentTypeEnum::values()), 'max:45'],
            'doc_ref' => 'required|string|max:45',
            'doc_ref_validation_date' => 'required|date',
            'national_federation_number' => 'nullable|string|max:50',
            'facebook_url' => 'nullable|url:http,https',
            'x_url' => 'nullable|url:http,https',
            'instagram_url' => 'nullable|url:http,https',
            'linkedin_url' => 'nullable|url:http,https',
            'visible_in_coach_registry' => 'nullable|boolean',
            'visible_in_technical_official_registry' => 'nullable|boolean',
            'visible_in_diving_professional_registry' => 'nullable|boolean',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'visible_in_coach_registry' => $this->boolean('visible_in_coach_registry'),
            'visible_in_technical_official_registry' => $this->boolean('visible_in_technical_official_registry'),
            'visible_in_diving_professional_registry' => $this->boolean('visible_in_diving_professional_registry'),
        ]);
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'logo.required' => __('validation.photo_required'),
            'logo.image' => __('validation.file_must_be_image'),
            'logo.mimes' => __('validation.photo_must_be_jpeg_png'),
            'logo.max' => __('validation.photo_max_2mb'),
            'name.required' => __('validation.name_required'),
            'surname.required' => __('validation.surname_required'),
            'native_name.required' => __('validation.full_name_required'),
            'country_id.required' => __('validation.country_required'),
            'birthdate.required' => __('validation.birthdate_required'),
            'gender.required' => __('validation.sex_required'),
            'district_id.required' => __('validation.district_required'),
            'vat_number.required' => __('validation.vat_number_required'),
            'doc_ref_type.required' => __('validation.doc_type_required'),
            'doc_ref.required' => __('validation.doc_number_required'),
            'doc_ref_validation_date.required' => __('validation.doc_expiry_required'),
        ];
    }
}
