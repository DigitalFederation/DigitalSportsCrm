<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MergeUserAccountsRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('manage user roles');
    }

    public function rules()
    {
        return [
            'source_email' => 'required|exists:users,email',
            'target_email' => 'required|exists:users,email|different:source_email',
            'individual_choice' => 'required_if:source_has_individual,1|in:source,target',
        ];
    }

}
