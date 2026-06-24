<?php

namespace App\Http\Requests\EventApplications;

use Illuminate\Foundation\Http\FormRequest;

class UploadDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'application_id' => 'required|integer|exists:event_applications,id',
            'file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:20480',
            'document_type' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'application_id.required' => __('event_applications.validation.application_id_required'),
            'application_id.exists' => __('event_applications.validation.application_id_exists'),
            'file.required' => __('event_applications.validation.file_required'),
            'file.mimes' => __('event_applications.validation.file_mimes'),
            'file.max' => __('event_applications.validation.file_max'),
            'document_type.required' => __('event_applications.validation.document_type_required'),
        ];
    }
}
