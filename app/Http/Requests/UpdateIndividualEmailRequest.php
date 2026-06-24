<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateIndividualEmailRequest extends FormRequest
{
    public function authorize()
    {
        return true; // We'll handle authorization in the controller
    }

    public function rules()
    {
        return [
            'public_email' => ['required', 'email'],
            'update_login_email' => ['nullable', 'boolean'],
            'login_email' => [
                'required_if:update_login_email,true',
                'nullable',
                'email',
            ],
        ];
    }

    public function messages()
    {
        return [
            'login_email.required_if' => 'The login email field is required when update login email is true.',
            'update_login_email.required' => 'The update login email field must be true or false.',
            'update_login_email.boolean' => 'The update login email field must be true or false.',
        ];
    }
}
