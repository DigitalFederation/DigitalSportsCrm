<?php

namespace Domain\Diving\Actions;

use Domain\Diving\Models\DivingEntityTechnicalDirector;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\Scopes\ExcludeInternationalScope;
use Domain\Licenses\States\PendingTechnicalDirectorApprovalLicenseAttributedState;
use Domain\Licenses\Transitions\TechnicalDirectorApprovalToCanceledTransition;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RejectTechnicalDirectorLicenseAction
{
    /**
     * Reject the license as a technical director
     *
     * @throws \Exception
     */
    public function execute(DivingEntityTechnicalDirector $technicalDirector, string $rejectionReason): LicenseAttributed
    {
        // Validate that the technical director is assigned to this license
        if (! $technicalDirector->isAssigned()) {
            throw new \Exception(__('diving.technical_director_not_assigned'));
        }

        // Check if already approved or rejected
        if ($technicalDirector->hasApproved()) {
            throw new \Exception(__('diving.technical_director_already_approved'));
        }

        if ($technicalDirector->hasRejected()) {
            throw new \Exception(__('diving.technical_director_already_rejected'));
        }

        // Validate rejection reason is provided and not just whitespace
        if (empty(trim($rejectionReason))) {
            throw new \Exception(__('diving.rejection_reason_required'));
        }

        // Load the license attributed without the international scope
        // since diving licenses are international but still need to be rejected
        $licenseAttributed = LicenseAttributed::withoutGlobalScope(ExcludeInternationalScope::class)
            ->find($technicalDirector->license_attributed_id);

        // Validate that the license exists
        if (! $licenseAttributed) {
            throw new \Exception(__('diving.license_not_found'));
        }

        // Validate license is in the correct state
        if ($licenseAttributed->status_class !== PendingTechnicalDirectorApprovalLicenseAttributedState::class) {
            throw new \Exception(__('diving.license_not_pending_technical_director_approval'));
        }

        DB::beginTransaction();

        try {
            // Mark this technical director as rejected
            $technicalDirector->rejected_at = now();
            $technicalDirector->rejection_reason = $rejectionReason;
            $technicalDirector->save();

            // Log the rejection
            activity('technical_director_rejection')
                ->performedOn($licenseAttributed)
                ->causedBy(auth()->user())
                ->withProperties([
                    'technical_director_id' => $technicalDirector->id,
                    'individual_id' => $technicalDirector->individual_id,
                    'rejection_reason' => $rejectionReason,
                ])
                ->log('Technical director rejected the license');

            // When any technical director rejects, the license is canceled
            $transition = new TechnicalDirectorApprovalToCanceledTransition($licenseAttributed, $rejectionReason);
            $transition->handle();

            // Send notification to entity
            $notificationClass = 'App\\Notifications\\TechnicalDirectorRejectedLicenseNotification';
            if (
                class_exists($notificationClass)
                && $licenseAttributed->owner
                && method_exists($licenseAttributed->owner, 'notify')
            ) {
                $licenseAttributed->owner->notify(new $notificationClass(
                    $licenseAttributed,
                    $technicalDirector,
                    $rejectionReason
                ));
            }

            DB::commit();

            return $licenseAttributed->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to reject license by technical director: ' . $e->getMessage(), [
                'technical_director_id' => $technicalDirector->id,
                'license_attributed_id' => $licenseAttributed->id,
            ]);
            throw $e;
        }
    }
}
