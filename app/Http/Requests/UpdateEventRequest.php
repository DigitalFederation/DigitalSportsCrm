<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Adjust based on your authorization logic
    }

    public function rules()
    {
        return [
            'id' => 'required|exists:evt_events,id',
            'field' => 'required|string',
            'value' => 'required',
        ];
    }
}
