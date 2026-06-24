<?php

namespace App\Http\Requests;

use Domain\Geographic\Models\Zone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ZoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $zone = $this->route('zone');
        $zoneId = $zone instanceof Zone ? $zone->id : null;

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('zones', 'code')->ignore($zoneId),
            ],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'district_ids' => ['nullable', 'array'],
            'district_ids.*' => ['exists:districts,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The zone name is required.',
            'name.max' => 'The zone name may not be greater than 255 characters.',
            'code.unique' => 'This zone code is already taken.',
            'code.max' => 'The zone code may not be greater than 50 characters.',
            'description.string' => 'The description must be a string.',
            'district_ids.array' => 'Districts must be provided as an array.',
            'district_ids.*.exists' => 'One or more selected districts are invalid.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'zone name',
            'code' => 'zone code',
            'description' => 'description',
            'is_active' => 'active status',
            'district_ids' => 'districts',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Ensure at least one district is selected when creating/updating a zone
            if ($this->isMethod('post') || $this->isMethod('put')) {
                $districtIds = $this->input('district_ids', []);
                if (empty($districtIds)) {
                    $validator->errors()->add('district_ids', 'At least one district must be selected for the zone.');
                }
            }
        });
    }
}
