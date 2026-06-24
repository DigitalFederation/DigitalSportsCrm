<?php

namespace Domain\Licenses\Actions;

use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Domain\Licenses\States\ActiveToSuspendedTransition;
use Domain\Licenses\States\SuspendedLicenseAttributedState;
use Domain\Licenses\States\SuspendedToActiveTransition;
use Exception;
use Illuminate\Support\Carbon;

class ManageLicenseAction
{
    private ActiveToSuspendedTransition $suspendTransition;
    private SuspendedToActiveTransition $activateTransition;

    public function __construct(
        ActiveToSuspendedTransition $suspendTransition,
        SuspendedToActiveTransition $activateTransition
    ) {
        $this->suspendTransition = $suspendTransition;
        $this->activateTransition = $activateTransition;
    }

    /**
     * Suspend a license with optional reason.
     *
     * @throws Exception
     */
    public function suspend(LicenseAttributed $license, string $reason = ''): LicenseAttributed
    {
        if ($license->status_class !== ActiveLicenseAttributedState::class) {
            throw new Exception('Only active licenses can be suspended');
        }

        return ($this->suspendTransition)($license, $reason);
    }

    /**
     * Reactivate a suspended license.
     *
     * @throws Exception
     */
    public function reactivate(LicenseAttributed $license): LicenseAttributed
    {
        if ($license->status_class !== SuspendedLicenseAttributedState::class) {
            throw new Exception('Only suspended licenses can be reactivated');
        }

        return ($this->activateTransition)($license);
    }

    /**
     * Add admin notes to a license.
     */
    public function addNotes(LicenseAttributed $license, string $notes): LicenseAttributed
    {
        $existingNotes = $license->notes ? $license->notes . "\n\n" : '';
        $license->notes = $existingNotes . '[' . now()->format('Y-m-d H:i:s') . '] ' . $notes;
        $license->save();

        activity('License')
            ->performedOn($license)
            ->event('notes_added')
            ->withProperties(['notes' => $notes])
            ->log('Admin notes added to license');

        return $license;
    }

    /**
     * Update license expiration date.
     */
    public function updateExpiration(LicenseAttributed $license, string $newExpirationDate): LicenseAttributed
    {
        $oldExpiration = $license->date_expire;
        $expiresAt = Carbon::parse($newExpirationDate);
        $license->date_expire = $expiresAt;
        $license->current_term_ends_at = $expiresAt;
        $license->save();

        activity('License')
            ->performedOn($license)
            ->event('expiration_updated')
            ->withProperties([
                'old_expiration' => $oldExpiration,
                'new_expiration' => $newExpirationDate,
            ])
            ->log('License expiration date updated by admin');

        return $license;
    }

    /**
     * Soft delete a license (admin only).
     */
    public function deleteLicense(LicenseAttributed $license, string $reason = ''): bool
    {
        activity('License')
            ->performedOn($license)
            ->event('deleted')
            ->withProperties(['reason' => $reason])
            ->log('License deleted by admin: ' . $reason);

        return $license->delete();
    }
}
