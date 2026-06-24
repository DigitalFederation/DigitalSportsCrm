<?php

namespace App\Http\Controllers\Common;

use App\Events\LicenseAttributedCreatedEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\LicenseAttributedRequest;
use Domain\Entities\Models\Entity;
use Domain\Entities\States\ActiveEntityFederationState;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\States\ActiveIndividualFederationState;
use Domain\Licenses\Actions\CalculateLicensePriceAction;
use Domain\Licenses\Actions\CalculateLicenseValidityDatesAction;
use Domain\Licenses\Actions\CreateLicenseAttributedAction;
use Domain\Licenses\DataTransferObject\LicenseAttributedData;
use Domain\Licenses\Models\License;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Domain\Licenses\States\ExpiredLicenseAttributedState;
use Domain\Licenses\States\PendingLicenseAttributedState;
use Domain\Licenses\States\WaitingApprovalLicenseAttributedState;
use Domain\OfficialDocuments\Actions\ValidateIndividualSportLicenseEligibilityAction;
use Domain\Users\Actions\SyncUserRolesBasedOnLicenseAction;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class BaseLicenseAttributedController extends Controller
{
    /**
     * Finds the default federation.
     */
    protected function findDefaultFederation()
    {
        return Federation::where('is_default_federation', 1)->firstOrFail();
    }

    public function store(
        LicenseAttributedRequest $request,
        CreateLicenseAttributedAction $create,
        CalculateLicensePriceAction $calculatePrice
    ): RedirectResponse {

        $licenseId = $request->input('license_id');
        $license = License::findOrFail($licenseId);
        $isSportLicense = $license->committee->code === 'SPORT';

        // Validate sport license requirements
        if ($isSportLicense && $request->input('license_type_name') === 'individual') {
            $individualIds = $request->input('individual', []);

            if (empty($individualIds)) {
                return redirect()->back()
                    ->with('error', __('No individuals selected'))
                    ->withInput();
            }

            foreach ($individualIds as $individualId) {
                $individual = Individual::find($individualId);

                if (! $individual) {
                    return redirect()->back()
                        ->with('error', __('Individual not found'))
                        ->withInput();
                }

                $validateEligibility = new ValidateIndividualSportLicenseEligibilityAction;
                $eligibilityResult = $validateEligibility($individual);

                if (! $eligibilityResult['is_valid']) {
                    return redirect()->back()
                        ->with('error', __('validation.license_eligibility_error') . ': ' .
                            implode(', ', array_column($eligibilityResult['errors'], 'message')))
                        ->withInput();
                }
            }
        }

        try {
            DB::beginTransaction();

            $type = $request->input('license_type_name');
            $modelType = $type === 'individual' ? 'individual' : 'entity';
            $modelClass = $type === 'individual' ? Individual::class : Entity::class;
            $modelIds = $type === 'individual' ? $request->get('individual') : [$request->get('entity_id')];

            $requesterType = $request->input('requester_model_type');

            $isSelfRequest = $requesterType === 'individual';
            $shouldEmitEvent = true;
            $licensesAttributed = [];
            $bulkValue = 0;

            foreach ($modelIds as $modelId) {
                $holder = $modelClass::findOrFail($modelId);

                // First determine the federation_id based on the requester type
                if ($isSelfRequest) {
                    $federationId = $this->findDefaultFederation()->id;
                } else {
                    $federationId = $request->federation_id;
                    // Verify the federation has an active relationship with the individual or entity
                    if ($type === 'individual') {
                        if (! $this->verifyFederationIndividualRelationship($federationId, $holder->id)) {
                            throw new Exception('The requesting federation does not have an active relationship with this individual.');
                        }
                    } else { // Entity case
                        if (! $this->verifyFederationEntityRelationship($federationId, $holder->id)) {
                            throw new Exception('The requesting federation does not have an active relationship with this entity.');
                        }
                    }
                }

                // Now check for duplicate active licenses with the determined federation_id
                if ($holder->licenses()
                    ->where('license_id', $licenseId)
                    ->where('federation_id', $federationId)
                    ->whereIn('status_class', [
                        ActiveLicenseAttributedState::class,
                        PendingLicenseAttributedState::class,
                        WaitingApprovalLicenseAttributedState::class,
                    ])
                    ->exists()
                ) {
                    throw ValidationException::withMessages([
                        'duplicate_license' => __('An active, pending or suspended license of this type already exists for ' . $holder->name),
                    ]);
                }

                // Gender validation for sport licenses
                if ($type === 'individual' && $license->committee->code === 'SPORT' && ! in_array($holder->gender, ['male', 'female'])) {
                    throw ValidationException::withMessages([
                        'gender' => __('The individual must have a gender (male or female) specified to request a sport license.'),
                    ]);
                }

                $holderName = $type === 'individual' ? $holder->name . ' ' . $holder->surname : $holder->name;

                $calculatedTotalValue = $calculatePrice($license, $requesterType);

                // Calculate validity dates
                $calculateValidityDates = new CalculateLicenseValidityDatesAction;
                $startDate = $request->input('current_term_starts_at') ?
                    \Carbon\Carbon::parse($request->input('current_term_starts_at')) :
                    \Carbon\Carbon::now();
                $validityDates = $calculateValidityDates->execute($license, $startDate);

                // Allow admin override of expiration date
                $endDate = $request->input('current_term_ends_at')
                    ? \Carbon\Carbon::parse($request->input('current_term_ends_at'))
                    : $validityDates['end_date'];

                $licenseAttributedData = new LicenseAttributedData(
                    id: '',
                    license_id: $licenseId,
                    federation_id: $federationId,
                    model_type: $modelType,
                    model_id: $modelId,
                    license_name: $license->name,
                    holder_name: $holderName,
                    total_value: $calculatedTotalValue,
                    current_term_starts_at: $validityDates['start_date']->format('Y-m-d'),
                    current_term_ends_at: $endDate?->format('Y-m-d'),
                    notes: $request->input('notes'),
                    owner_member_code: $holder->member_code ?? $holder->member_code,
                    requester_model_type: $requesterType,
                );

                // Determine the initial status
                if ($endDate && $endDate->endOfDay()->isPast()) {
                    $licenseAttributedData->status_class = ExpiredLicenseAttributedState::class;
                } elseif ($calculatedTotalValue <= 0) {
                    $licenseAttributedData->status_class = ActiveLicenseAttributedState::class;
                    $licenseAttributedData->activated_at = now();
                    $bulkValue += $calculatedTotalValue;
                } else {
                    $licenseAttributedData->status_class = PendingLicenseAttributedState::class;
                    $bulkValue += $calculatedTotalValue;
                }
                $createdLicense = $create($licenseAttributedData);
                $licensesAttributed[] = $createdLicense;

                if ($createdLicense->status_class === ActiveLicenseAttributedState::class) {
                    $syncUserRolesFromLicense = new SyncUserRolesBasedOnLicenseAction;
                    $syncUserRolesFromLicense($createdLicense);
                }

                // Log the license attribution
                activity('LicenseAttribution')
                    ->performedOn($createdLicense)
                    ->event('created')
                    ->withProperties([
                        'license_id' => $licenseId,
                        'holder_id' => $holder->id,
                        'holder_type' => $modelType,
                        'federation_id' => $federationId,
                        'status' => class_basename($licenseAttributedData->status_class),
                        'total_value' => $calculatedTotalValue,
                        'is_self_request' => $isSelfRequest,
                    ])
                    ->log('License was attributed');
            }

            DB::commit();

            if ($shouldEmitEvent && $bulkValue > 0) {
                event(new LicenseAttributedCreatedEvent($licensesAttributed, $isSelfRequest));
            }

            return redirect()->back()->with('success', 'Success! The license has been successfully created.');
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error('Error creating LicenseAttributed: ' . $ex->getMessage());

            return redirect()->back()->with('error', 'The following error occurred: ' . $ex->getMessage());
        }
    }

    private function verifyFederationIndividualRelationship($federationId, $individualId): bool
    {
        return Individual::where('id', $individualId)
            ->whereHas('individualFederations', function ($query) use ($federationId) {
                $query->where('federation_id', $federationId)
                    ->where('status_class', ActiveIndividualFederationState::class);
            })
            ->exists();
    }

    private function verifyFederationEntityRelationship($federationId, $entityId): bool
    {
        return Entity::where('id', $entityId)
            ->whereHas('entityFederations', function ($query) use ($federationId) {
                $query->where('federation_id', $federationId)
                    ->where('status_class', ActiveEntityFederationState::class);
            })
            ->exists();
    }
}
