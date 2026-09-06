<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NormalizesMonetaryInput;
use Illuminate\Foundation\Http\FormRequest;

class AffiliationPlanRequest extends FormRequest
{
    use NormalizesMonetaryInput;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->normalizeMonetaryInput([
            'individual_fee',
            'entity_fee',
        ]);
    }

    public function rules(): array
    {
        return [
            'federation_id' => 'required|exists:federation,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_months' => 'required|integer|min:1',
            'individual_fee' => 'nullable|numeric|min:0',
            'entity_fee' => 'nullable|numeric|min:0',
            'moloni_reference' => 'nullable|string|max:50',
            'type' => 'required|in:individual,entity',
            'vat_rate' => 'required|integer|in:0,6,13,23',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_validation_plan' => 'boolean',
        ];
    }
}
