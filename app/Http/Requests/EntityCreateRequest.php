<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EntityCreateRequest extends FormRequest
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
            'federation_id' => 'sometimes',
            'name' => 'required|string|max:191',
            'legal_name' => 'required|string|max:100',
            'legal_responsible_person' => 'required|string|max:100',
            'vat_number' => 'required|string|max:20',
            'address' => 'required|string|max:191',
            'location' => 'required|string',
            'postal_code' => 'required|string|max:20',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'email' => 'required|email|max:100',
            'website' => 'nullable|string|max:191',
            'phone' => 'required|string|max:20',
            'user_email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'terms' => 'accepted',
            'data_sharing' => 'accepted',
            'national_federation_number' => 'nullable|string',
            'facebook_url' => 'nullable|url:http,https',
            'x_url' => 'nullable|url:http,https',
            'instagram_url' => 'nullable|url:http,https',
            'linkedin_url' => 'nullable|url:http,https',
            'district_id' => 'required|integer|exists:districts,id',
            'entity_types' => 'required|array|min:1',
            'entity_types.*' => 'required|string|in:sport,diving',
        ];
    }
}
