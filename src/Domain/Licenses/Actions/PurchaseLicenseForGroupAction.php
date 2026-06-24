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
use Domain\Licenses\States\PendingValidationLicenseAttributedState;
use Domain\Licenses\States\ProvisionalLicenseAttributedState;
use Domain\Licenses\States\WaitingApprovalLicenseAttributedState;
use Domain\Memberships\Services\ValidationPlanPrivilegeService;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PurchaseLicenseForGroupAction
{
    private CreateLicenseAttributedAction $createLicenseAttributedAction;
    private CalculateLicensePriceAction $calculateLicensePriceAction;
    private ValidationPlanPrivilegeService $validationPlanService;
    private ValidateLicenseDocumentRequirementsAction $validateDocumentRequirementsAction;
    private CalculateLicenseValidityDatesAction $calculateValidityDatesAction;
    private ValidateLicenseCertificationRequirementsAction $validateCertificationRequirementsAction;

    public function __construct(
        CreateLicenseAttributedAction $createLicenseAttributedAction,
        CalculateLicensePriceAction $calculateLicensePriceAction,
        ValidationPlanPrivilegeService $validationPlanService,
        ValidateLicenseDocumentRequirementsAction $validateDocumentRequirementsAction,
        CalculateLicenseValidityDatesAction $calculateValidityDatesAction,
        ?ValidateLicenseCertificationRequirementsAction $validateCertificationRequirementsAction = null
    ) {
        $this->createLicenseAttributedAction = $createLicenseAttributedAction;
        $this->calculateLicensePriceAction = $calculateLicensePriceAction;
        $this->validationPlanService = $validationPlanService;
        $this->validateDocumentRequirementsAction = $validateDocumentRequirementsAction;
        $this->calculateValidityDatesAction = $calculateValidityDatesAction;
        $this->validateCertificationRequirementsAction = $validateCertificationRequirementsAction ?: new ValidateLicenseCertificationRequirementsAction;
    }

    /**
     * Handle group license purchase by entities for their members.
     *
     * @param  License  $license  The license to purchase
     * @param  Entity  $entity  The entity purchasing licenses for members
     * @param  Collection  $individuals  Collection of individuals to purchase licenses for
     * @param  array  $additionalData  Additional data for the licenses
     * @return Collection<LicenseAttributed> Returns collection of LicenseAttributed
     *
     * @throws Exception
     */
    public function __invoke(License $license, Entity $entity, Collection $individuals, array $additionalData = []): Collection
    {
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

        // Validate entity can purchase group licenses
        if (! $license->allow_entity_group_request) {
            throw new Exception('This license does not allow group purchases by entities');
        }

        // Validate entity has active affiliation
        if (! $entity->hasActiveAffiliation()) {
            throw new Exception('Your entity must have an active affiliation (membership package) to purchase licenses for members. Please ensure your entity membership is active and paid.');
        }

        // Validate entity has active entity license for the sport (if sport-specific)
        if (! $entity->hasActiveEntityLicenseForSport($license->sport_id)) {
            throw new Exception(__('licenses.entity_sport_license_required'));
        }

        // Validate entity has validation plan privileges for requesting licenses for members
        if (! $this->validationPlanService->canRequestLicenseForMembers($entity)) {
            $reason = $this->validationPlanService->getValidationPlanReason($entity, 'entity_member_licenses');
            throw new Exception("Entity member license request not authorized: {$reason}");
        }

        // Validate all individuals belong to the entity
        $invalidIndividuals = $individuals->filter(function ($individual) use ($entity) {
            return ! $individual->entities()->where('entity_id', $entity->id)->exists();
        });

        if ($invalidIndividuals->isNotEmpty()) {
            throw new Exception('Some individuals do not belong to this entity');
        }

        // Validate all individuals have active affiliations
        $individualsWithoutAffiliation = $individuals->filter(function ($individual) {
            return ! $individual->hasActiveAffiliation();
        });

        if ($individualsWithoutAffiliation->isNotEmpty()) {
            throw new Exception(__('licenses.members_must_have_active_affiliations'));
        }

        // Validate all individuals have required documents
        $individualsWithMissingDocuments = collect();
        foreach ($individuals as $individual) {
            $documentValidation = ($this->validateDocumentRequirementsAction)($license, $individual);
            if (! $documentValidation['is_valid']) {
                $individualsWithMissingDocuments->push([
                    'individual' => $individual,
                    'missing_documents' => $documentValidation['missing_documents'],
                ]);
            }
        }

        if ($individualsWithMissingDocuments->isNotEmpty()) {
            $names = $individualsWithMissingDocuments->take(3)->map(function ($item) {
                $docs = array_map(function ($doc) {
                    return \App\Enums\OfficialDocumentTypeEnum::toString($doc);
                }, $item['missing_documents']);

                return $item['individual']->name . ' (missing: ' . implode(', ', $docs) . ')';
            })->implode('; ');

            $remaining = $individualsWithMissingDocuments->count() - 3;
            $message = "The following members are missing required documents: {$names}";
            if ($remaining > 0) {
                $message .= " and {$remaining} others";
            }
            throw new Exception($message);
        }

        // Validate each individual has required certifications
        $individualsWithMissingCertifications = collect();
        foreach ($individuals as $individual) {
            $certificationValidation = ($this->validateCertificationRequirementsAction)($license, $individual);
            if (! $certificationValidation['is_valid']) {
                $individualsWithMissingCertifications->push([
                    'individual' => $individual,
                    'missing_certifications' => $certificationValidation['missing_certifications'],
                ]);
            }
        }

        if ($individualsWithMissingCertifications->isNotEmpty()) {
            $names = $individualsWithMissingCertifications->take(3)->map(function ($item) {
                $certs = array_map(function ($cert) {
                    return $cert['acronym'] ? "{$cert['name']} ({$cert['acronym']})" : $cert['name'];
                }, $item['missing_certifications']);

                return $item['individual']->name . ' (faltam: ' . implode(', ', $certs) . ')';
            })->implode('; ');

            $remaining = $individualsWithMissingCertifications->count() - 3;
            $message = __('licenses.members_missing_required_certifications', [
                'members' => $names,
            ]);
            if ($remaining > 0) {
                $message .= __(' e mais :count outros', ['count' => $remaining]);
            }
            throw new Exception($message);
        }

        // Validate federation exists - get from entity since committee is global
        $federation = $entity->federations()->first();
        if (! $federation) {
            throw new Exception('Cannot determine federation for license purchase');
        }
        $federationId = $federation->id;

        DB::beginTransaction();

        try {
            // CRITICAL: Lock the license record to serialize access and prevent race conditions
            // This prevents two simultaneous bulk requests from creating duplicate licenses
            License::where('id', $license->id)->lockForUpdate()->first();

            // Re-check for existing licenses INSIDE the transaction after acquiring lock
            $individualsWithExistingLicenses = collect();
            foreach ($individuals as $individual) {
                $existingLicense = LicenseAttributed::where('license_id', $license->id)
                    ->where('model_type', 'individual')
                    ->where('model_id', $individual->id)
                    ->whereIn('status_class', [
                        ActiveLicenseAttributedState::class,
                        PendingLicenseAttributedState::class,
                        PendingValidationLicenseAttributedState::class,
                        ProvisionalLicenseAttributedState::class,
                        WaitingApprovalLicenseAttributedState::class,
                    ])
                    ->lockForUpdate() // Also lock any existing license records
                    ->first();

                if ($existingLicense) {
                    $individualsWithExistingLicenses->push($individual);
                }
            }

            if ($individualsWithExistingLicenses->isNotEmpty()) {
                throw new Exception(__('licenses.members_already_have_licenses'));
            }

            $licenses = collect();
            $totalPrice = 0;

            // Calculate price per individual (might have bulk discounts in future)
            $pricePerLicense = ($this->calculateLicensePriceAction)($license, Individual::class);

            if ($pricePerLicense === null) {
                throw new Exception('License price not configured for individuals');
            }

            // Determine if it's a free license
            // Use loose comparison because CalculateLicensePriceAction returns float (0.0, not 0)
            $isFree = $pricePerLicense == 0 || $pricePerLicense === 0.0;

            // Create license for each individual
            foreach ($individuals as $individual) {
                // Calculate validity dates based on license configuration
                $dates = $this->calculateValidityDatesAction->execute($license);
                $validityDates = [
                    'current_term_starts_at' => $dates['start_date']->format('Y-m-d H:i:s'),
                    'current_term_ends_at' => $dates['end_date']?->format('Y-m-d H:i:s'),
                ];

                // If it's a free license (immediately active), set activated_at
                if ($isFree) {
                    $validityDates['activated_at'] = now()->format('Y-m-d H:i:s');
                }

                // Determine initial state based on price
                $initialState = $isFree ? ActiveLicenseAttributedState::class : PendingLicenseAttributedState::class;

                $licenseAttributedData = LicenseAttributedData::fromArray([
                    'status_class' => $initialState,
                    'license_id' => $license->id,
                    'federation_id' => $federationId,
                    'model_type' => 'individual',
                    'model_id' => $individual->id,
                    'license_name' => $license->name,
                    'holder_name' => $individual->name,
                    'federation_name' => $federation->legal_name ?? '',
                    'total_value' => $pricePerLicense,
                    'requester_model_type' => 'entity',
                    'license_code' => $license->license_code,
                    'federation_code' => $federation->code ?? '',
                    'request_type' => 'entity_group',
                    'requested_by_id' => $entity->id,
                    ...$validityDates,
                    ...$additionalData,
                ]);

                $licenseAttributed = ($this->createLicenseAttributedAction)($licenseAttributedData);
                $licenses->push($licenseAttributed);
                $totalPrice += $pricePerLicense;
            }

            DB::commit();

            // Only trigger document creation event for paid licenses
            if (! $isFree) {
                // Load the license relationship for all licenses before firing the event
                $licenses->each(function ($licenseAttributed) {
                    $licenseAttributed->load('license');
                });

                // Trigger event to create document for payment
                // The document will be created by the existing listener
                // For group purchases, the entity is the requester
                event(new LicenseAttributedCreatedEvent($licenses->all(), false));
            }

            activity('License')
                ->performedOn($entity)
                ->event($isFree ? 'group_requested' : 'group_purchased')
                ->withProperties([
                    'total_price' => $totalPrice,
                    'licenses_count' => $licenses->count(),
                    'license_ids' => $licenses->pluck('id')->toArray(),
                ])
                ->log($isFree ?
                    "Entity requested {$licenses->count()} free licenses for members" :
                    "Entity purchased {$licenses->count()} licenses for members");

            return $licenses;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
