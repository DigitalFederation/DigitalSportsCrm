<?php

namespace App\Http\Requests;

use Domain\Insurance\Models\Insurance;
use Illuminate\Foundation\Http\FormRequest;

class InsuranceUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {

        return [
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'fee' => ['required', 'numeric', 'min:0'],
            'is_external' => ['required', 'boolean'],
            'policy_number' => [
                'nullable',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    $insurance = $this->route('insurance');
                    if (! $insurance instanceof Insurance) {
                        return;
                    }

                    if ($insurance->insurancePlan->isGroupPlan() && $value !== $insurance->insurancePlan->policy_number) {
                        $fail('The policy number cannot be changed for group insurance plans.');
                    }
                },
            ],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'start_date' => 'start date',
            'end_date' => 'end date',
            'fee' => 'fee',
            'is_external' => 'external insurance status',
            'policy_number' => 'policy number',
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
            'end_date.after' => 'The end date must be after the start date.',
            'fee.min' => 'The fee must be a non-negative number.',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'is_external' => $this->toBoolean($this->is_external),
        ]);
    }

    /**
     * Convert to boolean
     *
     * @return bool
     */
    private function toBoolean($booleable)
    {
        return filter_var($booleable, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }
}
