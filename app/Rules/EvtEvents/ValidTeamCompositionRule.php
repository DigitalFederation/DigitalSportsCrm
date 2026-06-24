<?php

namespace App\Rules\EvtEvents;

use App\Enums\EvtDisciplineEnrollmentTypeEnum;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidTeamCompositionRule implements ValidationRule
{
    private string $enrollmentType;

    public function __construct(string $enrollmentType)
    {
        $this->enrollmentType = $enrollmentType;
    }

    public function validate($attribute, $value, $fail): void
    {
        if ($this->enrollmentType !== EvtDisciplineEnrollmentTypeEnum::relay->value) {
            return;
        }

        if (empty($value)) {
            $fail('Team composition is required for relay disciplines.');

            return;
        }

        $decoded = json_decode($value, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $fail('The team composition is invalid JSON.');

            return;
        }

        foreach ($decoded as $gender => $count) {
            if (! in_array($gender, ['male', 'female'])) {
                $fail("Invalid gender type: {$gender}");

                return;
            }
            if (! is_numeric($count) || $count <= 0) {
                $fail("Invalid count for {$gender}: must be a positive number");

                return;
            }
        }

        $totalCount = array_sum($decoded);
        if ($totalCount < 1) {
            $fail('A Relay Discipline needs to have Team Composition');

            return;
        }
    }

}
