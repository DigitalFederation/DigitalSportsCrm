<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait ChecksInternationalLicenseAccess
{
    /**
     * Check if a model (Individual or Entity) has access to international licenses
     * based on user permissions
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     */
    protected function hasInternationalLicenseAccess($model): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        // Check permission (works for both Individual and Entity)
        return $user->can('access international licenses');
    }

    /**
     * Verify access to international licenses and abort if not authorized
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     */
    protected function requireInternationalLicenseAccess($model, ?string $message = null): void
    {
        if (! $this->hasInternationalLicenseAccess($model)) {
            $defaultMessage = match (get_class($model)) {
                'Domain\Individuals\Models\Individual' => __('You do not have access to international licenses. Only members of federations with international license agreements can access these.'),
                'Domain\Entities\Models\Entity' => __('Your entity does not have access to international licenses'),
                default => __('No access to international licenses')
            };

            abort(403, $message ?? $defaultMessage);
        }
    }
}
