<?php

namespace Domain\Certifications\Actions;

use App\Jobs\NotifyInstructorNewCertificationJob;
use Domain\Certifications\DataTransferObject\CertificationAttributedData;
use Domain\Certifications\Models\CertificationAttributed;
use Domain\Certifications\States\DirectorApprovalCertificationAttributedState;
use Domain\Certifications\States\PendingCertificationAttributedState;
use Domain\Certifications\States\ProvisionalCertificationAttributedState;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Exception;
use Support\UtilityMethods;

class CreateCertificationAttributedAction
{
    private ActivateCertificationAttributedByFederationAction $activateCertificationByFederationAction;

    public function __construct(ActivateCertificationAttributedByFederationAction $activateCertificationByFederationAction)
    {
        $this->activateCertificationByFederationAction = $activateCertificationByFederationAction;
    }

    /**
     * Handles the creation and state management of a certification attributed to an individual.
     *
     * This action is utilized by entities such as international, federations, or instructors
     * to attribute a certification to individuals. It encompasses several critical steps:
     * - Verifying if the individual already possesses the certification.
     * - Determining and setting the appropriate state of the certification based on specific conditions.
     * - Creating the certification record in the database.
     * - Associating instructors and assistants, if provided.
     * - Activating the certification or dispatching a notification job based on predefined criteria.
     *
     * State Management:
     * The action manages two primary states for a certification - Provisional and Director Approval.
     * 1. Provisional State: Assigned to certifications attributed by local federations. This state
     *    indicates a temporary or conditional status pending further verification or requirements.
     * 2. Director Approval State: Assigned when an instructor is involved in the certification process.
     *    This state requires approval from a director-level entity before the certification is fully recognized.
     *
     * The action also handles the transition to an 'Active' state under certain conditions, such as
     * federation approval or meeting specific date criteria.
     *
     * Usage:
     * Invoke this action with a CertificationAttributedData object, containing all necessary
     * information for processing the certification attribution.
     *
     * Exception Handling:
     * The action throws an exception if any critical operation within the certification
     * attribution process encounters an issue.
     *
     * @param  CertificationAttributedData  $certificationAttributedData  The data object containing all necessary information for the certification process.
     * @return array Returns an array with a list of individuals who already have the certification.
     *
     * @throws Exception Throws an exception if any operation within the certification attribution process fails.
     *
     * Note:
     * The action is designed to accommodate the varying requirements of different federation types and instructor involvement, ensuring flexible and robust certification management.
     */
    public function __invoke(
        CertificationAttributedData $certificationAttributedData,
        ?string $source_type = null
    ): array {
        $checkIndividualHasCertification = new CheckIndividualHasCertificationAction;
        $attributeInstructorAction = new AttributeInstructorToCertificationAction;

        $individualsWithTheCertification = [];
        $createdCertifications = [];

        // Certifications always belong to the main federation
        $federation = Federation::where('is_default_federation', true)->first();
        if (! $federation) {
            throw new \RuntimeException('Main federation not found');
        }
        $certificationId = $certificationAttributedData->certification_id;
        $directorId = $certificationAttributedData->director_instructor_id;
        $assistantIds = $certificationAttributedData->assistant_instructor_ids;

        foreach ($certificationAttributedData->individual_ids as $individualId) {
            $individual = Individual::find($individualId);
            if (! $individual) {
                continue;
            }

            if (! $checkIndividualHasCertification($individualId, $certificationId)) {
                $individualCertData = $certificationAttributedData->toArray();

                $individualCertData['individual_id'] = $individualId;
                $individualCertData['holder_name'] = $individual->full_name;
                $individualCertData['federation_id'] = $federation->id;
                $individualCertData['federation_name'] = $federation->legal_name ?? $federation->name;
                $individualCertData['international_code'] = UtilityMethods::generateCertificationCmasInternationalNumber(date('Y'), $federation->id);
                $individualCertData['status_class'] = PendingCertificationAttributedState::class;

                unset(
                    $individualCertData['individual_ids'],
                    $individualCertData['assistant_instructor_ids'],
                    $individualCertData['director_instructor_id']
                );

                if ($directorId) {
                    $individualCertData['status_class'] = DirectorApprovalCertificationAttributedState::class;
                }

                $certificationAttributed = CertificationAttributed::create($individualCertData);
                $createdCertifications[] = $certificationAttributed;

                if ($directorId) {
                    $attributeInstructorAction($certificationAttributed->id, $directorId, true);
                }
                if (! empty($assistantIds)) {
                    foreach ($assistantIds as $assistant_id) {
                        $attributeInstructorAction($certificationAttributed->id, $assistant_id, false);
                    }
                }

                if (auth()->user()?->isFederation()) {
                    $this->activateCertificationByFederationAction->__invoke($certificationAttributed);
                    activity('CertificationAttributed')->performedOn($certificationAttributed)->event('activated')->log('Certification activated by Federation');

                    continue;
                }

                // Handle admin users - automatically activate certifications
                if (auth()->user()?->isAdmin()) {
                    $this->activateCertificationByFederationAction->__invoke($certificationAttributed);
                    activity('CertificationAttributed')->performedOn($certificationAttributed)->event('activated')->log('Certification activated by CMAS');

                    continue;
                }

                if (
                    $certificationAttributedData->approved_by_federation ||
                    (date('Y', strtotime($certificationAttributedData->current_term_starts_at)) < 2024 && $source_type !== 'entity')
                ) {
                    if ($federation && $federation->is_local) {
                        $certificationAttributed->status_class = ProvisionalCertificationAttributedState::class;
                        $certificationAttributed->save();

                        activity('CertificationAttributed')->performedOn($certificationAttributed)->event('provisional')->log('Certification set to Provisional state');
                    } else {
                        $this->activateCertificationByFederationAction->__invoke($certificationAttributed);
                        activity('CertificationAttributed')->performedOn($certificationAttributed)->event('activated')->log('Certification activated by NTC approval/Date');
                    }
                } elseif ($source_type === 'entity' && $individualCertData['status_class'] === PendingCertificationAttributedState::class) {
                    activity('CertificationAttributed')->performedOn($certificationAttributed)->event('pending')->log('Certification created by Entity, remains Pending');
                } elseif ($individualCertData['status_class'] === DirectorApprovalCertificationAttributedState::class) {
                    activity('CertificationAttributed')->performedOn($certificationAttributed)->event('pending_director')->log('Certification created, pending Director Approval');
                } else {
                    NotifyInstructorNewCertificationJob::dispatch($certificationAttributed);
                    activity('CertificationAttributed')->performedOn($certificationAttributed)->event('notification_dispatched')->log('Notification job dispatched for instructor');
                }
            } else {
                $individualsWithTheCertification[] = $individual->full_name ?? $individualId;
            }
        }

        return ['individualsWithTheCertification' => $individualsWithTheCertification];
    }
}
