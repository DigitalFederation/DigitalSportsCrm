<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HomePageSettingsRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'app_name' => ['nullable', 'string', 'max:100'],
            'federation_name' => ['nullable', 'string', 'max:200'],
            'federation_about' => ['nullable', 'string', 'max:2000'],
            'federation_address' => ['nullable', 'string', 'max:255'],
            'federation_support_email' => ['nullable', 'email', 'max:150'],
            'currency' => ['nullable', \Illuminate\Validation\Rule::enum(\App\Enums\CurrencyEnum::class)],
            'hero_background' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,svg,webp', 'max:2048'],
            'remove_hero_background' => ['nullable', 'boolean'],
            'remove_logo' => ['nullable', 'boolean'],
        ];
    }
}
