<?php

namespace App\Http\Requests;

use App\Enums\MembershipTargetType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class MembershipPackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'target_type' => ['required', new Enum(MembershipTargetType::class)],
            'distribution_methods' => ['required', 'array', 'min:1'],
            'distribution_methods.*' => ['required', 'string', 'in:direct,entity_managed,federation_managed'],
            'is_active' => ['boolean'],
            'affiliation_plan_ids' => ['nullable', 'array'],
            'affiliation_plan_ids.*' => ['exists:affiliation_plans,id'],
            'insurance_plan_ids' => ['nullable', 'array'],
            'insurance_plan_ids.*' => ['exists:insurance_plans,id'],
            'federation_ids' => ['required', 'array'],
            'federation_ids.*' => ['exists:federation,id'],
        ];

        // Additional validation: if target_type is 'entity', distribution_methods can contain 'direct' or 'federation_managed'
        if ($this->input('target_type') === MembershipTargetType::ENTITY->value) {
            $rules['distribution_methods.*'] = ['required', 'string', 'in:direct,federation_managed'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'distribution_methods.required' => 'Please select at least one distribution method.',
            'distribution_methods.*.in' => 'Invalid distribution method selected.',
        ];
    }
}
