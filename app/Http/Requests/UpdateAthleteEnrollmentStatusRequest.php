<?php

namespace App\Http\Requests;

use App\Enums\EvtAthleteEnrollmentStatusEnum;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAthleteEnrollmentStatusRequest extends FormRequest
{
    public function rules()
    {
        return [
            'new_status' => ['required', 'string', function ($attribute, $value, $fail) {
                if (! in_array($value, array_column(EvtAthleteEnrollmentStatusEnum::cases(), 'value'))) {
                    $fail('The ' . $attribute . ' is invalid.');
                }
            }],
        ];
    }

    public function authorize(): bool
    {
        return $this->user()->group->code === 'ADMIN';
    }
}
