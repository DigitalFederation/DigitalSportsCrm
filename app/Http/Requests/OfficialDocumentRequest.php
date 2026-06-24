<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OfficialDocumentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'individual_id' => 'required|integer',
            'type' => 'required|string',
            'status_class' => 'required|string|max:200',
            'expiry_date' => 'required|date',
        ];
    }

    public function messages()
    {
        return [
            'individual_id.required' => 'Individual ID is required',
            'type.required' => 'Type is required',
            'status_class.required' => 'Status class is required',
            'expiry_date.required' => 'Expiry date is required',
        ];
    }
}
