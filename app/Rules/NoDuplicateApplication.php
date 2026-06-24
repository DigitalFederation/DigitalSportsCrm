<?php

namespace App\Rules;

use Domain\EventApplications\Actions\CheckDuplicateApplicationAction;
use Illuminate\Contracts\Validation\Rule;

class NoDuplicateApplication implements Rule
{
    protected int $entityId;

    public function __construct(int $entityId)
    {
        $this->entityId = $entityId;
    }

    public function passes($attribute, $value): bool
    {
        if (! $value) {
            return true;
        }

        $checkDuplicate = app(CheckDuplicateApplicationAction::class);

        return ! $checkDuplicate->execute($this->entityId, (int) $value);
    }

    public function message(): string
    {
        return __('event_applications.validation.already_applied_to_template');
    }
}
