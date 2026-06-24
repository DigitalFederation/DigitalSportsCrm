<?php

namespace App\Rules;

use Domain\Memberships\Models\Membership;
use Illuminate\Contracts\Validation\Rule;

class FederationMembershipPlanIsNotActiveRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($federation)
    {
        $this->federation = $federation;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     */
    public function passes($attribute, $value): bool
    {
        $membership = Membership::where(['federation_id' => $this->federation, $attribute => $value])->first();
        if (! empty($membership)) {
            return ! $membership->isActive();
        }

        return true;
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return __('This plan is already activated on this federation.');
    }
}
