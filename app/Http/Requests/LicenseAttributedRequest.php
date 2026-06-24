<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LicenseAttributedRequest extends FormRequest
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
            'license_id' => 'required|integer',
            'license_type_name' => 'required|string',
            'federation_id' => 'nullable|integer|exists:federation,id',
            'entity_id' => 'required_without:individual|integer|exists:entity,id',
            'individual' => 'required_without:entity_id|array',
            'requester_model_type' => 'nullable|string',
            'notes' => 'nullable|string',
            'current_term_starts_at' => 'nullable|date',
            'current_term_ends_at' => 'nullable|date|after_or_equal:current_term_starts_at',
        ];
    }

    public function messages(): array
    {
        return [
            'individual.required' => 'The individual CMAS code has to be inserted.',
            'individual.array' => 'The individual list must be an array',
        ];
    }
}
