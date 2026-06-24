<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class FederationProfileEditRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $federationId = Auth::user()->federations()->first()->id;

        return [
            'parent_id' => 'nullable|integer|exists:federation,id|required_if:is_local,1',
            'is_local' => 'boolean|nullable',
            'legal_name' => 'max:200',
            'address' => 'max:200',
            'phone' => 'nullable|string|max:20',
            'location' => 'max:200',
            'latitude' => 'numeric|nullable|required_with:longitude|between:-90,90',
            'longitude' => 'numeric|nullable|required_with:latitude|between:-180,180',
            'website' => 'max:200',
            'email' => 'max:200',
            'board_members' => 'array|nullable',
            'vat_number' => 'nullable',
            'zip_code' => 'nullable',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'attachments' => 'array|nullable',
            'is_default_federation' => [
                'in:0,1',
                'sometimes',
                Rule::unique('federation')->where(function ($query) {
                    return $query->where('is_default_federation', true);
                })->ignore($federationId, 'id'), //  ignore the current federation
            ],
        ];
    }
}
