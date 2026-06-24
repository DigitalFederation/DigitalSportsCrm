<?php

namespace Domain\Diving\Actions;

use Domain\Diving\Models\DivingEntityTechnicalDirector;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\Scopes\ExcludeInternationalScope;
use Domain\Licenses\States\PendingTechnicalDirectorApprovalLicenseAttributedState;
use Domain\Licenses\Transitions\TechnicalDirectorApprovalToPendingValidationTransition;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApproveTechnicalDirectorLicenseAction
{
    /**
     * Approve the license as a technical director
     *
     * @throws \Exception
     */
    public function execute(DivingEntityTechnicalDirector $technicalDirector, ?string $approvalNotes = null): LicenseAttributed
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

        // Load the license attributed without the international scope
        // since diving licenses are international but still need to be approved
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
            // Mark this technical director as approved
            $technicalDirector->approved_at = now();
            $technicalDirector->approval_notes = $approvalNotes;
            $technicalDirector->save();

            // Log the approval
            activity('technical_director_approval')
                ->performedOn($licenseAttributed)
                ->causedBy(auth()->user())
                ->withProperties([
                    'technical_director_id' => $technicalDirector->id,
                    'individual_id' => $technicalDirector->individual_id,
                    'approval_notes' => $approvalNotes,
                ])
                ->log('Technical director approved the license');

            // Check if all technical directors have approved
            $allDirectors = $licenseAttributed->divingTechnicalDirectors()
                ->where('status_class', \Domain\Diving\States\AssignedDivingTechnicalDirectorState::class)
                ->get();

            $allApproved = true;
            foreach ($allDirectors as $director) {
                if (! $director->hasApproved()) {
                    $allApproved = false;
                    break;
                }
            }

            // If all technical directors have approved, transition to pending validation
            if ($allApproved) {
                $transition = new TechnicalDirectorApprovalToPendingValidationTransition($licenseAttributed);
                $transition->handle();

                // Send notification to entity
                if ($licenseAttributed->owner && method_exists($licenseAttributed->owner, 'notify')) {
                    $licenseAttributed->owner->notify(new \App\Notifications\AllTechnicalDirectorsApprovedNotification($licenseAttributed));
                }
            }

            DB::commit();

            return $licenseAttributed->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to approve license by technical director: ' . $e->getMessage(), [
                'technical_director_id' => $technicalDirector->id,
                'license_attributed_id' => $licenseAttributed->id,
            ]);
            throw $e;
        }
    }
}
