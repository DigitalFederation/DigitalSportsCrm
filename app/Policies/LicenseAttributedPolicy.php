<?php

namespace App\Policies;

use App\Models\User;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ActiveLicenseAttributedState;

class LicenseAttributedPolicy
{
    private const DIVING_COMMITTEE_CODE = 'DIVING';

    private const ADMIN_ROLES = ['admin', 'association-sport-admin', 'association-admin'];

    private const FEDERATION_ROLES = ['federation-admin', 'federation-user'];

    /**
     * Determine whether the user can view the license PDF.
     */
    public function viewPdf(User $user, LicenseAttributed $licenseAttributed): bool
    {
        if (! $this->isActiveDivingLicense($licenseAttributed)) {
            return false;
        }

        if ($user->hasAnyRole(self::ADMIN_ROLES)) {
            return true;
        }

        if ($user->hasAnyRole(self::FEDERATION_ROLES)) {
            return $this->userBelongsToLicenseFederation($user, $licenseAttributed);
        }

        return $this->userOwnsLicense($user, $licenseAttributed);
    }

    /**
     * Determine whether the user can view any license PDF (for admin validation context).
     */
    public function viewValidationPdf(User $user, LicenseAttributed $licenseAttributed): bool
    {
        return $user->hasAnyRole(self::ADMIN_ROLES)
            && $this->isDivingLicense($licenseAttributed);
    }

    private function isActiveDivingLicense(LicenseAttributed $licenseAttributed): bool
    {
        return $licenseAttributed->status_class === ActiveLicenseAttributedState::class
            && $this->isDivingLicense($licenseAttributed);
    }

    private function isDivingLicense(LicenseAttributed $licenseAttributed): bool
    {
        return $licenseAttributed->license->committee->code === self::DIVING_COMMITTEE_CODE;
    }

    private function userBelongsToLicenseFederation(User $user, LicenseAttributed $licenseAttributed): bool
    {
        $federation = $user->federations()->first();

        return $federation && $licenseAttributed->federation_id === $federation->id;
    }

    private function userOwnsLicense(User $user, LicenseAttributed $licenseAttributed): bool
    {
        $entity = $user->entities()->first();

        if (! $entity) {
            return false;
        }

        $modelTypeMatches = $licenseAttributed->model_type === $entity->getMorphClass()
            || $licenseAttributed->model_type === get_class($entity);

        return $licenseAttributed->model_id == $entity->id && $modelTypeMatches;
    }
}
