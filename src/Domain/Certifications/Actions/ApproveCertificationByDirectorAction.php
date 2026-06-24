<?php

namespace Domain\Certifications\Actions;

use App\Events\CertificationAttributedCreatedEvent;
use Domain\Certifications\Models\CertificationAttributed;
use Domain\Certifications\States\ApprovalToApprovedCertificationAttributedTransition;
use Domain\Certifications\States\DirectorApprovalCertificationAttributedState;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Class ApproveCertificationByDirectorAction
 *
 * Responsible for approving a certification by the Course Director.
 * This transitions the certification from DirectorApproval to DirectorApproved state
 * and generates the payment document for the entity.
 */
class ApproveCertificationByDirectorAction
{
    /**
     * Approve a certification by the Course Director.
     *
     * This method will:
     * 1. Validate the certification is in DirectorApproval state
     * 2. Transition to DirectorApproved (Waiting NF) state
     * 3. Fire the event to generate a payment document if price > 0
     *
     * @param  CertificationAttributed  $certificationAttributed  The certification to approve
     * @return CertificationAttributed Returns the approved certification
     *
     * @throws Exception If certification is not in the correct state
     */
    public function __invoke(CertificationAttributed $certificationAttributed): CertificationAttributed
    {
        // Validate current state
        if ($certificationAttributed->status_class !== DirectorApprovalCertificationAttributedState::class) {
            throw new Exception('Certification is not in a state that can be approved by director');
        }

        // Transition to DirectorApproved (Waiting NF)
        $transition = new ApprovalToApprovedCertificationAttributedTransition;
        $certificationAttributed = $transition($certificationAttributed);

        Log::info('ApproveCertificationByDirectorAction: Certification approved by director', [
            'certification_attributed_id' => $certificationAttributed->id,
            'new_status_class' => $certificationAttributed->status_class,
        ]);

        // Generate payment document if price > 0
        $price = $certificationAttributed->price_paid ?? 0;
        if ($price > 0) {
            $certificationAttributed->load('certification');

            Log::info('ApproveCertificationByDirectorAction: Firing CertificationAttributedCreatedEvent for payment document', [
                'certification_attributed_id' => $certificationAttributed->id,
                'price' => $price,
            ]);

            event(new CertificationAttributedCreatedEvent($certificationAttributed, $price));
        }

        return $certificationAttributed;
    }
}
