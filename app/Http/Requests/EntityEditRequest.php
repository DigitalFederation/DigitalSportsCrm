<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class EntityEditRequest extends FormRequest
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
    public function rules(Request $request): array
    {
        $rules = [
            'federation_id' => $request->routeIs('entity.profile.update') ? 'nullable|array' : 'required|array',
            'federation_id.*' => $request->routeIs('entity.profile.update') ? 'nullable|integer|exists:federation,id' : 'required|integer|exists:federation,id',
            'name' => 'required|string|max:191',
            'legal_name' => 'required|string|max:100',
            'legal_responsible_person' => 'nullable|string|max:100',
            'committee_id' => 'nullable|array',
            'vat_number' => 'nullable|string|max:20',
            'country_id' => 'required|integer|exists:country,id',
            'address' => 'nullable|string|max:191',
            'location' => 'nullable|string',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'email' => 'nullable|email|max:100',
            'website' => 'nullable|string|max:191',
            'phone' => 'nullable|string|max:20',
            'postal_code' => 'nullable|max:30',
            'logo' => 'nullable|image',
            'national_federation_number' => 'nullable|string',
            'facebook_url' => 'nullable|url:http,https',
            'x_url' => 'nullable|url:http,https',
            'instagram_url' => 'nullable|url:http,https',
            'linkedin_url' => 'nullable|url:http,https',
            'public_description' => 'nullable|string',
            'entity_background' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'district_id' => 'nullable|integer|exists:districts,id',
            'zone_ids' => 'nullable|array',
            'zone_ids.*' => 'integer|exists:zones,id',
        ];

        // Remove federation validation for profile editing
        if ($request->routeIs('entity.profile.update') || $request->routeIs('entity.profile.edit')) {
            $rules['federation_id'] = 'nullable|array';
        }

        return $rules;
    }
}
