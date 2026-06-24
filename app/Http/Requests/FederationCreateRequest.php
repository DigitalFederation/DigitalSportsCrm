<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FederationCreateRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'country_id' => 'required|integer|exists:country,id',
            'parent_id' => 'nullable|integer|exists:federation,id|required_if:is_local,1',
            'name' => 'string|required|max:200',
            'is_local' => 'boolean|nullable',
            'category' => 'nullable|string|in:' . implode(',', [
                \Domain\Federations\Enums\SportOrClassAssociationCategory::class,
                \Domain\Federations\Enums\TerritorialAssociationCategory::class,
            ]),
            'is_manual' => 'boolean|nullable',
            'legal_name' => 'string|required|max:200',
            'address' => 'max:200',
            'location' => 'max:200',
            'latitude' => 'numeric|nullable|required_with:longitude|between:-90,90',
            'longitude' => 'numeric|nullable|required_with:latitude|between:-180,180',
            'website' => 'max:200',
            'email' => 'max:200',
            'phone' => 'max:20',
            'member_code' => 'required|unique:federation,member_code',
            'vat_number' => 'nullable|string|max:20',
            'zip_code' => 'nullable',
            'board_members' => 'array|nullable',
            'user_email' => 'required|email|max:150|unique:users,email',
            'confirm_user_email' => 'required|email|max:150|same:user_email',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'attachments' => 'array|nullable',
            'is_default_federation' => [
                'in:0,1',
                'required',
                Rule::unique('federation')->where(function ($query) {
                    return $query->where('is_default_federation', true);
                })->ignore($this->id, 'id'), //  ignore the current federation
            ],
            'can_issue_certifications' => 'boolean|nullable',
            'zone_ids' => 'nullable|array',
            'zone_ids.*' => 'exists:zones,id',

        ];
    }

    public function messages()
    {
        return [
            'is_default_federation.unique' => 'The default federation already exists.',
        ];
    }
}
