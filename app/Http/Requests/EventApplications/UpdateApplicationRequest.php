<?php

namespace App\Http\Requests\EventApplications;

use Domain\EventApplications\Models\EventApplication;
use Illuminate\Foundation\Http\FormRequest;

class UpdateApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $application = $this->route('application');

        if (! $application instanceof EventApplication) {
            return false;
        }

        return in_array($application->status_class, [
            'Domain\EventApplications\States\DraftApplicationState',
            'Domain\EventApplications\States\ReturnedApplicationState',
        ], true);
    }

    public function rules(): array
    {
        return [
            'event_name' => 'required|string|max:255',
            'event_type' => 'required|string|max:100',
            'sport_id' => 'nullable|integer|exists:sport,id',
            'event_category_id' => 'nullable|integer',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'district_id' => 'nullable|integer|exists:districts,id',
            'municipality' => 'nullable|string|max:255',
            'responsible_name' => 'nullable|string|max:255',
            'responsible_phone' => 'nullable|string|max:20',
            'target_audience' => 'nullable|string|max:1000',
            'expected_participants' => 'nullable|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'event_name.required' => __('event_applications.validation.event_name_required'),
            'event_type.required' => __('event_applications.validation.event_type_required'),
            'start_date.required' => __('event_applications.validation.start_date_required'),
            'end_date.required' => __('event_applications.validation.end_date_required'),
            'end_date.after_or_equal' => __('event_applications.validation.end_date_after_or_equal'),
            'district_id.exists' => __('event_applications.validation.district_id_invalid'),
            'municipality.max' => __('event_applications.validation.municipality_max'),
            'responsible_name.max' => __('event_applications.validation.responsible_name_max'),
            'responsible_phone.max' => __('event_applications.validation.responsible_phone_max'),
            'target_audience.max' => __('event_applications.validation.target_audience_max'),
            'expected_participants.integer' => __('event_applications.validation.expected_participants_integer'),
            'expected_participants.min' => __('event_applications.validation.expected_participants_min'),
        ];
    }
}
