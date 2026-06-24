<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MemberSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'membership_package_id' => 'required|exists:membership_packages,id',
            'member_type' => ['required', Rule::in(['individual', 'entity'])],
            'individual_id' => 'required_if:member_type,individual|nullable|exists:individual,id',
            'entity_id' => 'required_if:member_type,entity|nullable|exists:entity,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ];
    }
}
