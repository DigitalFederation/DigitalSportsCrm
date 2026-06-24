<?php

namespace Domain\EventApplications\Actions;

use Carbon\Carbon;

class ValidateApplicationDatesAction
{
    public function execute(?string $startDate, ?string $endDate): array
    {
        $errors = [];

        if ($startDate && $endDate) {
            $start = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);

            if ($end->lt($start)) {
                $errors[] = 'End date must be after or equal to start date';
            }
        }

        if ($startDate) {
            $start = Carbon::parse($startDate);
            if ($start->isPast()) {
                $errors[] = 'Start date cannot be in the past';
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
}
