<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class PublicSearchCertificationRequest extends FormRequest
{
    protected $redirectRoute = 'public.certification.index';

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
     * @return array<string, Rule|array|string>
     */
    public function rules(): array
    {
        // Base rules: Check types and maximums, allow nullable initially.
        // The main requirement logic is handled in the after() hook.
        return [
            'name' => 'nullable|string|max:45',
            'surname' => 'nullable|string|max:45',
            'birthdate' => 'nullable|date',
            'member_code' => 'nullable|string|max:7',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $data = $validator->getData();
            $hasCode = ! empty($data['member_code']);
            $hasNameGroup = ! empty($data['name']) && ! empty($data['surname']) && ! empty($data['birthdate']);

            // If neither search method is complete, add errors.
            if (! $hasCode && ! $hasNameGroup) {
                $errorAdded = false;
                // Check which form was likely submitted to provide specific field errors
                if (array_key_exists('member_code', $data) && ! $hasNameGroup) {
                    // Submitted Form 1 (Member Code) but it's empty
                    $validator->errors()->add('member_code', __('The International Code field is required.'));
                    $errorAdded = true; // Mark that a specific error was added
                } elseif (array_key_exists('name', $data) || array_key_exists('surname', $data) || array_key_exists('birthdate', $data)) {
                    // Submitted Form 2 (Manual) but it's incomplete
                    if (empty($data['name'])) {
                        $validator->errors()->add('name', __('The Name field is required for manual search.'));
                        $errorAdded = true;
                    }
                    if (empty($data['surname'])) {
                        $validator->errors()->add('surname', __('The Surname field is required for manual search.'));
                        $errorAdded = true;
                    }
                    if (empty($data['birthdate'])) {
                        $validator->errors()->add('birthdate', __('The Date of Birth field is required for manual search.'));
                        $errorAdded = true;
                    }
                }

                // Fallback: If no specific errors were added (e.g., form submitted completely empty), add a general one.
                if (! $errorAdded) {
                    $validator->errors()->add(
                        'search_criteria', // General error key
                        __('Please provide either an International Code or the full Name, Surname, and Date of Birth.')
                    );
                }
            }
        });
    }
}
