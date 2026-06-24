<?php

namespace App\Http\Requests;

use App\Enums\IndividualDocumentTypeEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreatePublicIndividualRequest extends FormRequest
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
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
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
                        ->where('country_id', $this->individual_country_id)
                        ->whereNull('deleted_at')
                        ->exists();

                    if ($exists) {
                        $fail(__('individual.duplicate_individual_exists'));
                    }
                },
            ],
            'individual_country_id' => 'required|integer|exists:country,id',
            'district_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    if ($value === 'outside_portugal') {
                        return;
                    }
                    if (! \Domain\Geographic\Models\District::where('id', $value)->exists()) {
                        $fail(__('individual.invalid_district'));
                    }
                },
            ],
            'gender' => 'required|in:male,female',
            'address' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:255',
            'vat_number' => 'required|string|max:30',
            'phone' => 'nullable|string|max:20',
            'doc_ref_type' => ['required', Rule::in(IndividualDocumentTypeEnum::values())],
            'doc_ref' => 'required|string|max:45',
            'doc_ref_validation_date' => 'required|date',
            'email' => 'required|email|max:150|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string|min:8',
            'terms' => 'accepted',
            'data_sharing' => 'accepted',
            'entity_id' => 'nullable|exists:entity,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'logo.required' => __('individual.validation.photo_required'),
            'logo.image' => __('individual.validation.file_must_be_image'),
            'logo.mimes' => __('individual.validation.photo_mimes'),
            'logo.max' => __('individual.validation.photo_max_size'),
            'name.required' => __('individual.validation.name_required'),
            'surname.required' => __('individual.validation.surname_required'),
            'native_name.required' => __('individual.validation.full_name_required'),
            'birthdate.required' => __('individual.validation.birthdate_required'),
            'individual_country_id.required' => __('individual.validation.country_required'),
            'district_id.required' => __('individual.validation.district_required'),
            'district_id.exists' => __('individual.validation.district_invalid'),
            'gender.required' => __('individual.validation.gender_required'),
            'vat_number.required' => __('individual.validation.vat_number_required'),
            'doc_ref_type.required' => __('individual.validation.doc_type_required'),
            'doc_ref.required' => __('individual.validation.doc_number_required'),
            'doc_ref_validation_date.required' => __('individual.validation.doc_validity_required'),
            'email.unique' => __('individual.validation.email_already_registered'),
            'terms.accepted' => __('individual.validation.terms_accepted'),
            'data_sharing.accepted' => __('individual.validation.data_sharing_accepted'),
            'entity_id.exists' => __('individual.validation.entity_invalid'),
        ];
    }
}
