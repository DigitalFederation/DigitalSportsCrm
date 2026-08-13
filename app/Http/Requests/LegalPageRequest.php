<?php

namespace App\Http\Requests;

use App\Models\LegalPage;
use App\Services\LegalHtmlSanitizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LegalPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('access settings') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(LegalPage::types())],
            'locale' => ['required', Rule::in(config('app.locales', []))],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'effective_date' => ['nullable', 'date'],
        ];
    }

    /**
     * Sanitize BEFORE validation runs.
     *
     * This must not move to passedValidation(): validated() returns the data the
     * validator captured, so sanitizing afterwards leaves the controller holding
     * the raw submission and stores unsanitized HTML. Doing it here means no code
     * path can reach the model with unsanitized input.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('body')) {
            $this->merge([
                'body' => app(LegalHtmlSanitizer::class)->sanitize($this->input('body')),
            ]);
        }
    }
}
