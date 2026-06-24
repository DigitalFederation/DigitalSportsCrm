<?php

namespace Domain\Licenses\Actions;

use App\Events\LicenseAttributedCreatedEvent;
use Domain\Entities\Models\Entity;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\DataTransferObject\LicenseAttributedData;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Domain\Licenses\States\PendingLicenseAttributedState;
use Domain\Licenses\States\PendingTechnicalDirectorApprovalLicenseAttributedState;
use Domain\Licenses\States\PendingValidationLicenseAttributedState;
use Domain\Licenses\States\ProvisionalLicenseAttributedState;
use Domain\Licenses\States\WaitingApprovalLicenseAttributedState;
use Domain\Memberships\Services\ValidationPlanPrivilegeService;
use Exception;
use Illuminate\Support\Facades\DB;

class PurchaseLicenseAction
{
    private CreateLicenseAttributedAction $createLicenseAttributedAction;
    private CalculateLicensePriceAction $calculateLicensePriceAction;
    private ValidationPlanPrivilegeService $validationPlanService;
    private CalculateLicenseValidityDatesAction $calculateValidityDatesAction;
    private ValidateLicenseDocumentRequirementsAction $validateDocumentRequirementsAction;
    private ValidateLicenseCertificationRequirementsAction $validateCertificationRequirementsAction;
    private GetMorphTypeForModelAction $getMorphTypeForModelAction;

    public function __construct(
        CreateLicenseAttributedAction $createLicenseAttributedAction,
        CalculateLicensePriceAction $calculateLicensePriceAction,
        ValidationPlanPrivilegeService $validationPlanService,
        CalculateLicenseValidityDatesAction $calculateValidityDatesAction,
        ValidateLicenseDocumentRequirementsAction $validateDocumentRequirementsAction,
        ?ValidateLicenseCertificationRequirementsAction $validateCertificationRequirementsAction = null,
        ?GetMorphTypeForModelAction $getMorphTypeForModelAction = null
    ) {
        $this->createLicenseAttributedAction = $createLicenseAttributedAction;
        $this->calculateLicensePriceAction = $calculateLicensePriceAction;
        $this->validationPlanService = $validationPlanService;
        $this->calculateValidityDatesAction = $calculateValidityDatesAction;
        $this->validateDocumentRequirementsAction = $validateDocumentRequirementsAction;
        $this->validateCertificationRequirementsAction = $validateCertificationRequirementsAction ?: new ValidateLicenseCertificationRequirementsAction;
        $this->getMorphTypeForModelAction = $getMorphTypeForModelAction ?: new GetMorphTypeForModelAction;
    }

    /**
     * Handle direct license purchase by individuals or entities.
     *
     * @param  License  $license  The license to purchase
     * @param  Individual|Entity  $purchaser  The individual or entity purchasing the license
     * @param  array  $additionalData  Additional data for the license
     * @return LicenseAttributed Returns the created LicenseAttributed
     *
     * @throws Exception
     */
    public function __invoke(License $license, $purchaser, array $additionalData = []): LicenseAttributed
    {
        // Validate license parameter
        if (! $license) {
            throw new Exception(__('licenses.license_parameter_null'));
        }

        // Validate license has required properties
        if (! $license->id || ! $license->license_code) {
            throw new Exception(__('licenses.license_missing_properties'));
        }

        // Ensure license has required relationships loaded
        if (! $license->relationLoaded('committee')) {
            $license->load('committee');
        }
        if (! $license->relationLoaded('type')) {
            $license->load('type');
        }
        if (! $license->relationLoaded('requiredCertifications')) {
            $license->load('requiredCertifications');
        }

        // Validate purchaser is allowed
        // Use instanceof to handle both real models and mocks correctly
        $isEntity = $purchaser instanceof Entity;
        $purchaserClass = $isEntity ? Entity::class : get_class($purchaser);
        if (! $license->canBeRequestedBy($purchaserClass)) {
            $purchaserType = $isEntity ? __('licenses.entity') : __('licenses.individual');
            throw new Exception(__('licenses.license_cannot_be_purchased_by', ['type' => $purchaserType]));
        }

        // Validate purchaser has active affiliation
        if (! $purchaser->hasActiveAffiliation()) {
            if ($isEntity) {
                $message = __('licenses.Your entity must have an active affiliation (membership package) to purchase licenses. Please ensure your entity membership is active and paid before proceeding.');
            } else {
                $message = __('licenses.You must have an active affiliation (membership package) to purchase licenses. Please ensure your individual membership is active and paid before proceeding.');
            }
            throw new Exception($message);
        }

        // Validate purchaser has validation plan privileges for license requests
        if (! $this->validationPlanService->canRequestLicense($purchaser)) {
            $reason = $this->validationPlanService->getValidationPlanReason($purchaser, 'license');
            throw new Exception(__('licenses.license_request_not_authorized', ['reason' => $reason]));
        }

        // Validate required documents
        $documentValidation = ($this->validateDocumentRequirementsAction)($license, $purchaser);
        if (! $documentValidation['is_valid']) {
            $missingDocs = array_map(function ($doc) {
                return \App\Enums\OfficialDocumentTypeEnum::toString($doc);
            }, $documentValidation['missing_documents']);

            $message = __('licenses.missing_required_documents_detailed', [
                'documents' => implode(', ', $missingDocs),
            ]);
            throw new Exception($message);
        }

        // Validate required certifications
        $certificationValidation = ($this->validateCertificationRequirementsAction)($license, $purchaser);
        if (! $certificationValidation['is_valid']) {
            $missingCerts = array_map(function ($cert) {
                return $cert['acronym'] ? "{$cert['name']} ({$cert['acronym']})" : $cert['name'];
            }, $certificationValidation['missing_certifications']);

            $message = __('licenses.missing_required_certifications', [
                'certifications' => implode(', ', $missingCerts),
            ]);
            throw new Exception($message);
        }

        // Get the correct morph type for checking existing licenses
        $morphType = (new GetMorphTypeForModelAction)->execute($purchaser);

        // Skip duplicate validation for international diving licenses only
        // DIVING (international) can have multiple licenses of the same type
        // DIVINGSERVICES (non-international) should allow only one active/pending like SPORT
        $isDivingLicense = $license->committee && $license->committee->code === 'DIVING';

        // Determine federation for license attribution tracking
        // Since licenses no longer belong to a single federation, we need to determine
        // which federation to record in the license_attributed record
        $federationId = null;
        $federation = null;

        // Get purchaser's primary/first active federation
        $activeFederation = $purchaser->federations()
            ->wherePivot('active', true)
            ->first();

        if ($activeFederation) {
            $federationId = $activeFederation->id;
            $federation = $activeFederation;
        } else {
            // Fallback: use any federation the purchaser belongs to
            $federation = $purchaser->federations()->first();
            if ($federation) {
                $federationId = $federation->id;
            } else {
                // Last resort: use main federation
                $mainFederation = \Domain\Federations\Models\Federation::where('is_default_federation', true)->first();
                if ($mainFederation) {
                    $federationId = $mainFederation->id;
                    $federation = $mainFederation;
                }
            }
        }

        if (! $federationId || ! $federation) {
            throw new Exception(__('licenses.cannot_determine_federation'));
        }

        DB::beginTransaction();

        try {
            // CRITICAL: Lock the license record to serialize access and prevent race conditions
            // This prevents two simultaneous requests from creating duplicate licenses
            License::where('id', $license->id)->lockForUpdate()->first();

            // Re-check for existing license INSIDE the transaction after acquiring lock
            if (! $isDivingLicense) {
                $existingLicense = LicenseAttributed::where('license_id', $license->id)
                    ->where('model_type', $morphType)
                    ->where('model_id', $purchaser->id)
                    ->whereIn('status_class', [
                        ActiveLicenseAttributedState::class,
                        PendingLicenseAttributedState::class,
                        PendingTechnicalDirectorApprovalLicenseAttributedState::class,
                        PendingValidationLicenseAttributedState::class,
                        ProvisionalLicenseAttributedState::class,
                        WaitingApprovalLicenseAttributedState::class,
                    ])
                    ->lockForUpdate() // Also lock any existing license records
                    ->first();

                if ($existingLicense) {
                    $statusName = class_basename($existingLicense->status_class);
                    $statusName = str_replace('LicenseAttributedState', '', $statusName);
                    throw new Exception(__('licenses.already_has_license', ['status' => strtolower($statusName)]));
                }
            } else {
                \Log::info('PurchaseLicenseAction: Allowing duplicate diving license', [
                    'license_id' => $license->id,
                    'license_name' => $license->name,
                    'purchaser_id' => $purchaser->id,
                    'committee_code' => 'DIVING',
                ]);
            }

            // Calculate price based on purchaser type
            $price = ($this->calculateLicensePriceAction)($license, $purchaserClass);

            \Log::info('PurchaseLicenseAction: Calculating price', [
                'license_id' => $license->id,
                'license_name' => $license->name,
                'purchaser_class' => $purchaserClass,
                'purchaser_id' => $purchaser->id,
                'calculated_price' => $price,
                'unit_value' => $license->unit_value,
                'unit_value_entity' => $license->unit_value_entity,
                'unit_value_individual' => $license->unit_value_individual,
            ]);

            if ($price === null) {
                throw new Exception(__('licenses.license_price_not_configured'));
            }

            // Determine initial state based on price and validation requirements
            $isFree = $price == 0 || $price === 0.0;
            $requiresValidation = $license->requires_admin_validation ?? false;
            // Include both DIVING (international) and DIVINGSERVICES (non-international diving services)
            $isDivingLicense = $license->committee && in_array($license->committee->code, ['DIVING', 'DIVINGSERVICES']);
            // Use committee's is_international flag instead of license-level flag
            $isInternational = $license->committee && $license->committee->isInternational();

            \Log::info('PurchaseLicenseAction: License state determination', [
                'is_free' => $isFree,
                'requires_validation' => $requiresValidation,
                'is_diving_license' => $isDivingLicense,
                'is_international' => $isInternational,
                'license_id' => $license->id,
            ]);

            $isEntity = $purchaserClass === Entity::class;

            if ($isDivingLicense && $requiresValidation && $isEntity && ! $isInternational) {
                // Only ENTITIES go to TD approval for NON-INTERNATIONAL diving licenses (DIVINGSERVICES)
                $initialState = PendingTechnicalDirectorApprovalLicenseAttributedState::class;
            } elseif ($requiresValidation && ! $isInternational) {
                // Only NON-INTERNATIONAL licenses require admin validation
                // International licenses (international DIVING, international SCIENTIFIC) skip admin validation entirely
                $initialState = PendingValidationLicenseAttributedState::class;
            } elseif ($isFree) {
                $initialState = ActiveLicenseAttributedState::class;
            } else {
                // International licenses with requires_admin_validation go directly to payment
                $initialState = PendingLicenseAttributedState::class;
            }

            // Calculate validity dates based on license configuration
            $validityDates = [];
            $dates = $this->calculateValidityDatesAction->execute($license);

            $validityDates = [
                'current_term_starts_at' => $dates['start_date']->format('Y-m-d H:i:s'),
                'current_term_ends_at' => $dates['end_date']?->format('Y-m-d H:i:s'),
            ];

            // If it's a free license (immediately active), set activated_at
            if ($isFree) {
                $validityDates['activated_at'] = now()->format('Y-m-d H:i:s');
            }

            // Get the correct morph type using Laravel's morph map
            $morphType = $this->getMorphTypeForModelAction->execute($purchaser);

            // Prepare license attributed data
            $dataArray = [
                'status_class' => $initialState,
                'license_id' => $license->id,
                'federation_id' => $federationId,
                'model_type' => $morphType,
                'model_id' => $purchaser->id,
                'license_name' => $license->name,
                'holder_name' => $purchaser->name,
                'federation_name' => $federation->legal_name ?? '',
                'total_value' => $price,
                'requester_model_type' => $morphType,
                'license_code' => $license->license_code,
                'federation_code' => $federation->code ?? '',
                ...$validityDates,
                ...$additionalData,
            ];

            // Only set requested_by_id for entities due to foreign key constraint
            if ($purchaserClass === Entity::class) {
                $dataArray['requested_by_id'] = $purchaser->id;
            }

            $licenseAttributedData = LicenseAttributedData::fromArray($dataArray);

            // Create license attributed record
            $licenseAttributed = ($this->createLicenseAttributedAction)($licenseAttributedData);

            \Log::info('PurchaseLicenseAction: License attributed created', [
                'license_attributed_id' => $licenseAttributed->id,
                'status_class' => $licenseAttributed->status_class,
                'requester_model_type' => $licenseAttributed->requester_model_type,
                'requested_by_id' => $licenseAttributed->requested_by_id,
                'total_value' => $licenseAttributed->total_value,
            ]);

            DB::commit();

            // Trigger document creation event for paid licenses that go to PendingLicenseAttributedState
            // This includes:
            // 1. Licenses that don't require validation
            // 2. International licenses (they skip validation state regardless of requires_admin_validation flag)
            // Licenses that require validation AND are non-international will have documents created after approval
            if (! $isFree && $initialState === PendingLicenseAttributedState::class) {
                // Load the license relationship before firing the event
                $licenseAttributed->load('license');

                \Log::info('PurchaseLicenseAction: Firing LicenseAttributedCreatedEvent', [
                    'license_attributed_id' => $licenseAttributed->id,
                    'is_self_request' => true,
                    'reason' => 'Paid license without validation requirement',
                    'license_loaded' => $licenseAttributed->relationLoaded('license'),
                ]);

                // Trigger event to create document for payment
                // The document will be created by the existing listener
                event(new LicenseAttributedCreatedEvent([$licenseAttributed], true));
            } else {
                \Log::info('PurchaseLicenseAction: NOT firing LicenseAttributedCreatedEvent', [
                    'license_attributed_id' => $licenseAttributed->id,
                    'is_free' => $isFree,
                    'initial_state' => $initialState,
                    'reason' => $isFree ? 'Free license' : 'License requires validation/approval before payment',
                ]);
            }

            activity('License')
                ->performedOn($licenseAttributed)
                ->event($isFree ? 'requested' : 'purchased')
                ->withProperties([
                    'price' => $price,
                    'purchaser' => $purchaser->name,
                    'is_free' => $isFree,
                ])
                ->log($isFree ? 'Free license requested and activated' : 'License purchased directly');

            return $licenseAttributed;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
