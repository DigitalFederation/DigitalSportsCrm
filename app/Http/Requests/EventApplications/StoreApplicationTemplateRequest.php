<?php

namespace App\Http\Requests\EventApplications;

use Illuminate\Foundation\Http\FormRequest;

class StoreApplicationTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'event_type' => 'required|string|max:100',
            'sport_id' => 'nullable|integer|exists:evt_sports,id',
            'event_category' => 'nullable|string|max:255',
            'registration_type' => 'nullable|string|in:entities,entities_individuals,individuals,federations,federations_entities,federations_entities_individuals',
            'category' => 'nullable|string|in:A,B,C,D',
            'age_group' => 'nullable|string|max:255',
            'submission_start_date' => 'required|date',
            'submission_end_date' => 'required|date|after:submission_start_date',
            'event_start_date' => 'nullable|date|after_or_equal:submission_end_date',
            'event_end_date' => 'nullable|date|after_or_equal:event_start_date',
            'description' => 'nullable|string|max:2000',
            'target_audience' => 'required|string|in:federations,entities,both',
            'max_applications' => 'nullable|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('event_applications.validation.name_required'),
            'event_type.required' => __('event_applications.validation.event_type_required'),
            'submission_start_date.required' => __('event_applications.validation.submission_start_date_required'),
            'submission_end_date.required' => __('event_applications.validation.submission_end_date_required'),
            'submission_end_date.after' => __('event_applications.validation.submission_end_date_after'),
            'event_start_date.after_or_equal' => __('event_applications.validation.event_start_date_after_or_equal'),
            'event_end_date.after_or_equal' => __('event_applications.validation.event_end_date_after_or_equal'),
        ];
    }
}
