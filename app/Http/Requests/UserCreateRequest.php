<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserCreateRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        $userId = $this->route('user');

        // Rules for updating an existing user
        $emailRules = $userId ? [
            'sometimes',
            'email',
            'max:150',
            Rule::unique('users')->ignore($userId),
        ] : [
            'required',
            'email',
            'max:150',
            Rule::unique('users'),
        ];

        return [
            'name' => 'required|string|max:100',
            'email' => $emailRules,
            'group_id' => 'required|exists:user_group,id',
            'federation' => 'nullable|exists:federation,id',
            'entity' => 'nullable|exists:entity,id',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ];

    }
}
