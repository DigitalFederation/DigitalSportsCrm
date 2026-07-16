<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MembershipPlanRequest extends FormRequest
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

            'committee_id' => 'nullable',
            'name' => 'required|string|max:45',
            'friendly_name' => 'nullable|string|max:45',
            'price' => ['nullable', 'numeric', new \App\Rules\CurrencyScale],
            'interval' => 'required|integer',
            'interval_unit' => ['required', 'in' => config('enum.interval_unit')],
            'licenses' => 'nullable|array',

        ];
    }

    public function messages(): array
    {
        return [
            'committee_id.required' => 'The committee field is required.',
            'name.required' => 'The name field is required.',
            'interval.required' => 'The interval field is required.',
            'interval_unit.required' => 'The interval unit field is required.',
        ];
    }
}
