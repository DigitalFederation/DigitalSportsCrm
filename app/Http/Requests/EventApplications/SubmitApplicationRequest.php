<?php

namespace App\Http\Requests\EventApplications;

use Domain\EventApplications\Models\EventApplication;
use Illuminate\Foundation\Http\FormRequest;

class SubmitApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $application = $this->route('application');

        if (! $application instanceof EventApplication) {
            return false;
        }

        return in_array($application->status_class, [
            'Domain\EventApplications\States\DraftApplicationState',
            'Domain\EventApplications\States\ReturnedForCorrectionApplicationState',
        ], true);
    }

    public function rules(): array
    {
        return [];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $application = $this->route('application');
            if (! $application instanceof EventApplication) {
                return;
            }

            if (! $application->event_name) {
                $validator->errors()->add('event_name', __('event_applications.validation.event_name_required'));
            }

            if (! $application->event_type) {
                $validator->errors()->add('event_type', __('event_applications.validation.event_type_required'));
            }

            if (! $application->start_date) {
                $validator->errors()->add('start_date', __('event_applications.validation.start_date_required'));
            }

            if (! $application->end_date) {
                $validator->errors()->add('end_date', __('event_applications.validation.end_date_required'));
            }

            if (! $application->responsible_name) {
                $validator->errors()->add('responsible_name', __('event_applications.validation.responsible_name_required'));
            }

            if (! $application->responsible_phone) {
                $validator->errors()->add('responsible_phone', __('event_applications.validation.responsible_phone_required'));
            }
        });
    }
}
