<?php

namespace Domain\Certifications\Actions;

use Domain\Certifications\Models\Certification;
use Domain\Certifications\Models\CertificationAttributed;
use Domain\Entities\Models\Entity;
use Domain\Individuals\Models\Individual;
use Exception;
use Illuminate\Support\Facades\DB;

class PurchaseCertificationForGroupAction
{
    private PurchaseCertificationAction $purchaseCertificationAction;

    public function __construct(PurchaseCertificationAction $purchaseCertificationAction)
    {
        $this->purchaseCertificationAction = $purchaseCertificationAction;
    }

    /**
     * Handle certification purchase for a group of individuals by an entity.
     *
     * @throws Exception
     */
    public function __invoke(
        Certification $certification,
        Entity $entity,
        array $individualIds,
        array $additionalData = []
    ): array {
        // Validate entity can purchase for groups
        if (! $certification->allow_entity_group_request) {
            throw new Exception(__('certifications.group_purchase_not_allowed'));
        }

        // Validate all individuals belong to the entity
        $validIndividuals = $entity->individuals()
            ->whereIn('id', $individualIds)
            ->where('status', 'active')
            ->pluck('id')
            ->toArray();

        if (count($validIndividuals) !== count($individualIds)) {
            throw new Exception(__('certifications.invalid_individuals_for_group_purchase'));
        }

        $results = [
            'created' => [],
            'failed' => [],
            'already_has' => [],
        ];

        DB::beginTransaction();

        try {
            foreach ($individualIds as $individualId) {
                $individual = Individual::find($individualId);

                if (! $individual) {
                    $results['failed'][] = [
                        'id' => $individualId,
                        'error' => 'Individual not found',
                    ];

                    continue;
                }

                try {
                    // Check if individual already has this certification
                    $existingCert = CertificationAttributed::where('certification_id', $certification->id)
                        ->where('individual_id', $individual->id)
                        ->whereIn('status_class', [
                            'Domain\Certifications\States\ActiveCertificationAttributedState',
                            'Domain\Certifications\States\PendingCertificationAttributedState',
                            'Domain\Certifications\States\DirectorApprovalCertificationAttributedState',
                        ])
                        ->first();

                    if ($existingCert) {
                        $results['already_has'][] = [
                            'id' => $individual->id,
                            'name' => $individual->full_name,
                        ];

                        continue;
                    }

                    // Purchase certification for the individual
                    $certificationAttributed = ($this->purchaseCertificationAction)(
                        $certification,
                        $individual,
                        array_merge($additionalData, [
                            'purchased_by_entity_id' => $entity->id,
                            'entity_id' => $entity->id, // Maintain entity association
                        ])
                    );

                    $results['created'][] = [
                        'id' => $individual->id,
                        'name' => $individual->full_name,
                        'certification_attributed_id' => $certificationAttributed->id,
                    ];

                } catch (Exception $e) {
                    $results['failed'][] = [
                        'id' => $individual->id,
                        'name' => $individual->full_name,
                        'error' => $e->getMessage(),
                    ];
                }
            }

            // If all failed, rollback
            if (empty($results['created']) && ! empty($results['failed'])) {
                DB::rollBack();
                throw new Exception(__('certifications.all_group_purchases_failed'));
            }

            DB::commit();

            // Log activity
            activity('Certification')
                ->performedOn($entity)
                ->event('group_purchase')
                ->withProperties([
                    'certification_id' => $certification->id,
                    'certification_name' => $certification->name,
                    'total_individuals' => count($individualIds),
                    'successful' => count($results['created']),
                    'failed' => count($results['failed']),
                    'already_has' => count($results['already_has']),
                ])
                ->log('Group certification purchase completed');

            return $results;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
