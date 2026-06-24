<?php

namespace App\Http\Requests;

use http\Env\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FederationEditRequest extends FormRequest
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
    public function rules(\Illuminate\Http\Request $request): array
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
            'legal_name' => 'max:200',
            'address' => 'max:200',
            'location' => 'max:200',
            'latitude' => 'numeric|nullable|required_with:longitude|between:-90,90',
            'longitude' => 'numeric|nullable|required_with:latitude|between:-180,180',
            'website' => 'max:200',
            'email' => 'max:200',
            'member_code' => [
                'required',
                Rule::unique('federation', 'member_code')->ignore($request->route('federation') ?: $request->get('id')), // ignore the current federation
            ],
            'vat_number' => 'nullable|string|max:20',
            'zip_code' => 'nullable',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'attachments' => 'array|nullable',
            'is_default_federation' => [
                'in:0,1',
                'required',
                Rule::unique('federation')->where(function ($query) {
                    return $query->where('is_default_federation', true);
                })->ignore($this->route('federation'), 'id'), //  ignore the current federation
            ],
            'can_issue_certifications' => 'boolean|nullable',
            'zone_ids' => 'nullable|array',
            'zone_ids.*' => 'exists:zones,id',
        ];
    }
}
