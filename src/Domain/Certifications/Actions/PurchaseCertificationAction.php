<?php

namespace Domain\Certifications\Actions;

use App\Events\CertificationAttributedCreatedEvent;
use Domain\Certifications\Models\Certification;
use Domain\Certifications\Models\CertificationAttributed;
use Domain\Certifications\States\ActiveCertificationAttributedState;
use Domain\Certifications\States\DirectorApprovalCertificationAttributedState;
use Domain\Certifications\States\PendingCertificationAttributedState;
use Domain\Entities\Models\Entity;
use Domain\Individuals\Models\Individual;
use Domain\Memberships\Services\ValidationPlanPrivilegeService;
use Exception;
use Illuminate\Support\Facades\DB;

class PurchaseCertificationAction
{
    private CreateCertificationAttributedAction $createCertificationAttributedAction;
    private CalculateCertificationPriceAction $calculateCertificationPriceAction;
    private ValidationPlanPrivilegeService $validationPlanService;
    private CalculateCertificationValidityDatesAction $calculateValidityDatesAction;

    public function __construct(
        CreateCertificationAttributedAction $createCertificationAttributedAction,
        CalculateCertificationPriceAction $calculateCertificationPriceAction,
        ValidationPlanPrivilegeService $validationPlanService,
        CalculateCertificationValidityDatesAction $calculateValidityDatesAction
    ) {
        $this->createCertificationAttributedAction = $createCertificationAttributedAction;
        $this->calculateCertificationPriceAction = $calculateCertificationPriceAction;
        $this->validationPlanService = $validationPlanService;
        $this->calculateValidityDatesAction = $calculateValidityDatesAction;
    }

    /**
     * Handle direct certification purchase by individuals or entities.
     *
     * @param  Certification  $certification  The certification to purchase
     * @param  Individual|Entity  $purchaser  The individual or entity purchasing the certification
     * @param  array  $additionalData  Additional data for the certification
     * @return CertificationAttributed Returns the created CertificationAttributed
     *
     * @throws Exception
     */
    public function __invoke(Certification $certification, $purchaser, array $additionalData = []): CertificationAttributed
    {
        // Validate certification parameter
        if (! $certification) {
            throw new Exception('Certification parameter is null');
        }

        // Validate certification has required properties
        if (! $certification->id || ! $certification->name) {
            throw new Exception('Certification is missing required properties');
        }

        // Ensure certification has required relationships loaded
        if (! $certification->relationLoaded('committee')) {
            $certification->load('committee');
        }
        if (! $certification->relationLoaded('license')) {
            $certification->load('license');
        }

        // Validate purchaser is allowed
        $purchaserClass = get_class($purchaser);
        $purchaserModel = class_basename($purchaserClass); // 'Individual' or 'Entity'

        // Individuals cannot purchase certifications directly
        if ($purchaserModel === 'Individual') {
            throw new Exception('Individuals cannot purchase certifications directly. Certifications must be purchased by your entity or federation.');
        }

        if (! $certification->canBeRequestedBy($purchaserModel)) {
            throw new Exception("This certification cannot be purchased by {$purchaserModel}");
        }

        // Check if certification is international and validate entity role
        if ($certification->isInternationalCertification()) {
            // Validate committee is allowed for international certifications
            if (! $certification->isValidInternationalCommittee()) {
                throw new Exception('International certifications are only available for Diving and Scientific committees');
            }

            // Only entities need international operator role check
            if ($purchaserModel === 'Entity' && ! $purchaser->hasRole('entity-international')) {
                throw new Exception('Only entities with CMAS operator role can purchase international certifications');
            }
        }

        // Validate purchaser has active affiliation
        if (! $purchaser->hasActiveAffiliation()) {
            $memberType = $purchaserClass === Entity::class ? 'entity' : 'individual';
            throw new Exception("You must have an active affiliation (membership package) to purchase certifications. Please ensure your {$memberType} membership is active and paid.");
        }

        // Validate purchaser has validation plan privileges for certification requests
        if (! $this->validationPlanService->canRequestCertification($purchaser)) {
            $reason = $this->validationPlanService->getValidationPlanReason($purchaser, 'certification');
            throw new Exception("Certification request not authorized: {$reason}");
        }

        // Check if purchaser already has an active or pending certification of this type
        $existingCertification = CertificationAttributed::where('certification_id', $certification->id)
            ->where(function ($query) use ($purchaser, $purchaserClass) {
                if ($purchaserClass === Individual::class) {
                    $query->where('individual_id', $purchaser->id);
                } else {
                    $query->where('entity_id', $purchaser->id);
                }
            })
            ->whereIn('status_class', [
                ActiveCertificationAttributedState::class,
                PendingCertificationAttributedState::class,
                DirectorApprovalCertificationAttributedState::class,
            ])
            ->first();

        if ($existingCertification) {
            $statusName = class_basename($existingCertification->status_class);
            $statusName = str_replace('CertificationAttributedState', '', $statusName);
            throw new Exception(__('certifications.already_has_certification', ['status' => strtolower($statusName)]));
        }

        // Certifications always belong to the main federation
        $mainFederation = \Domain\Federations\Models\Federation::where('is_default_federation', true)->first();
        if (! $mainFederation) {
            throw new Exception('Main federation not found');
        }
        $federationId = $mainFederation->id;
        $federation = $mainFederation;

        // Determine entity
        $entityId = null;
        if ($purchaserClass === Individual::class) {
            $entity = $purchaser->entities()->first();
            if ($entity) {
                $entityId = $entity->id;
            }
        } else {
            $entityId = $purchaser->id;
        }

        DB::beginTransaction();

        try {
            // Calculate price based on purchaser type
            $price = ($this->calculateCertificationPriceAction)($certification, $purchaserModel);

            \Log::info('PurchaseCertificationAction: Calculating price', [
                'certification_id' => $certification->id,
                'certification_name' => $certification->name,
                'purchaser_class' => $purchaserClass,
                'purchaser_id' => $purchaser->id,
                'calculated_price' => $price,
                'unit_value' => $certification->unit_value,
                'unit_value_entity' => $certification->unit_value_entity,
                'unit_value_individual' => $certification->unit_value_individual,
            ]);

            if ($price === null) {
                throw new Exception('Certification price not configured for this purchaser type');
            }

            // Determine initial state based on price and validation requirements
            $isFree = $price === 0;
            $requiresValidation = $certification->requires_admin_validation ?? false;

            \Log::info('PurchaseCertificationAction: Certification state determination', [
                'is_free' => $isFree,
                'requires_validation' => $requiresValidation,
                'certification_id' => $certification->id,
            ]);

            if ($requiresValidation) {
                $initialState = DirectorApprovalCertificationAttributedState::class;
            } elseif ($isFree) {
                $initialState = ActiveCertificationAttributedState::class;
            } else {
                $initialState = PendingCertificationAttributedState::class;
            }

            // Calculate validity dates
            $validityDates = ($this->calculateValidityDatesAction)($certification);

            // If it's a free certification (immediately active), set activated_at
            if ($isFree && ! $requiresValidation) {
                $validityDates['activated_at'] = now();
            }

            // For direct purchase, we need to create the certification directly
            // since CreateCertificationAttributedAction handles batch creation
            $certificationAttributedData = [
                'status_class' => $initialState,
                'certification_id' => $certification->id,
                'federation_id' => $federationId,
                'entity_id' => $entityId,
                'individual_id' => $purchaserClass === Individual::class ? $purchaser->id : null,
                'certification_name' => $certification->name,
                'holder_name' => $purchaser->name,
                'federation_name' => $federation->legal_name ?? '',
                'entity_name' => $entityId ? ($purchaserClass === Entity::class ? $purchaser->name : ($entity?->name ?? null)) : null,
                'national_code' => null, // Will be generated on activation
                'international_code' => null, // Will be generated on activation if international
                'instructor_id' => $additionalData['instructor_id'] ?? null,
                ...$validityDates,
                ...$additionalData,
            ];

            // Create certification attributed record directly
            $certificationAttributed = CertificationAttributed::create($certificationAttributedData);

            \Log::info('PurchaseCertificationAction: Certification attributed created', [
                'certification_attributed_id' => $certificationAttributed->id,
                'status_class' => $certificationAttributed->status_class,
                'total_value' => $price,
            ]);

            DB::commit();

            // Only trigger document creation event for paid certifications that don't require validation
            if (! $isFree && ! $requiresValidation) {
                // Load the certification relationship before firing the event
                $certificationAttributed->load('certification');

                \Log::info('PurchaseCertificationAction: Firing CertificationAttributedCreatedEvent', [
                    'certification_attributed_id' => $certificationAttributed->id,
                    'reason' => 'Paid certification without validation requirement',
                ]);

                // Trigger event to create document for payment
                event(new CertificationAttributedCreatedEvent($certificationAttributed, $price));
            }

            activity('Certification')
                ->performedOn($certificationAttributed)
                ->event($isFree ? 'requested' : 'purchased')
                ->withProperties([
                    'price' => $price,
                    'purchaser' => $purchaser->name,
                    'is_free' => $isFree,
                ])
                ->log($isFree ? 'Free certification requested and activated' : 'Certification purchased directly');

            return $certificationAttributed;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
