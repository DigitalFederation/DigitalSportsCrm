<?php

namespace App\Http\Requests;

use Domain\Geographic\Models\District;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DistrictRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $district = $this->route('district');
        $districtId = $district instanceof District ? $district->id : null;

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('districts', 'code')->ignore($districtId),
            ],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The district name is required.',
            'name.max' => 'The district name may not be greater than 255 characters.',
            'code.unique' => 'This district code is already taken.',
            'code.max' => 'The district code may not be greater than 50 characters.',
            'description.string' => 'The description must be a string.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'district name',
            'code' => 'district code',
            'description' => 'description',
            'is_active' => 'active status',
        ];
    }
}
