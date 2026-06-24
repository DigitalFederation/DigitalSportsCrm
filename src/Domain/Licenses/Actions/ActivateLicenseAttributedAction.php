<?php

namespace Domain\Licenses\Actions;

use App\Services\DashboardCacheService;
use Domain\Documents\Models\DocumentDetail;
use Domain\Documents\States\PaidDocumentState;
use Domain\Entities\Models\Entity;
use Domain\Individuals\Actions\AssociateIndividualToProfessionalRoleAction;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\IndividualFederation;
use Domain\Individuals\States\ActiveIndividualFederationState;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Domain\Users\Actions\SyncUserEntityCommitteeAction;
use Domain\Users\Actions\SyncUserRolesAction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ActivateLicenseAttributedAction
{
    private CalculateLicenseValidityDatesAction $calculateValidityDatesAction;

    public function __construct(CalculateLicenseValidityDatesAction $calculateValidityDatesAction)
    {
        $this->calculateValidityDatesAction = $calculateValidityDatesAction;
    }

    public function __invoke(LicenseAttributed $license, ?string $current_term_ends_at, bool $bypassPaymentCheck = false): void
    {
        DB::beginTransaction();
        try {
            // Check if payment is required and complete (unless bypassed by payment listener)
            if (! $bypassPaymentCheck && ! $this->canActivate($license)) {
                throw new \Exception(__('licenses.cannot_activate_unpaid_license'));
            }
            $license->status_class = ActiveLicenseAttributedState::class;
            $license->activated_at = now();

            // Load the license relationship to check interval configuration
            $license->load('license');

            // Calculate validity dates based on license configuration
            if ($license->license && ! $license->current_term_starts_at) {
                $dates = $this->calculateValidityDatesAction->execute($license->license, $license->activated_at);
                $license->current_term_starts_at = $dates['start_date'];
                $license->current_term_ends_at = $dates['end_date'];
            }

            $license->save();

            /*
             * TODO: validate if creating a document after a License is activated makes sense.
            if (! DocumentDetail::where(['owner_type' => LicenseAttributed::class, 'owner_id' => $license->id])->exists()) {
                event(new LicenseAttributedCreatedEvent([$license], false));
            }
            */

            // User-related actions
            $this->handleUserRelatedActions($license);

            // Logging activity
            $this->logLicenseActivity($license);

            DB::commit();

            app(DashboardCacheService::class)->invalidateActivationCaches();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());

            throw $e;
        }
    }

    private function handleUserRelatedActions($license)
    {
        $license->load('license', 'owner');

        if ($license->model_type == 'entity') {
            // Per PM requirement: Entity licenses should NOT trigger role changes
            // Only sync committee information, but do not modify user roles
            $user = $license->owner?->users()->first();
            if ($user) {
                $syncUserEntityCommitteeAction = new SyncUserEntityCommitteeAction;
                $syncUserEntityCommitteeAction->execute($user);
            }
        } else {
            // Individual licenses continue to trigger role changes as before
            $individual = $license->owner; // Use the relationship instead of finding by ID
            if ($individual && $license->license->professional_role_id) {
                $professionalRoleAction = new AssociateIndividualToProfessionalRoleAction;
                $professionalRoleAction($individual, $license->license->professional_role_id);
            }

            if ($individual && $individual->user()->first()) {
                $syncUserRolesAction = new SyncUserRolesAction;
                $syncUserRolesAction->execute($individual->user()->first());
            }

            // Sync individual to ALL modalidade federations linked to this license
            // This is the trigger for joining sport-specific associations
            // Only sync to federations where is_local = false (modalidade federations)
            if ($individual && $license->license) {
                $modalidadeFederations = $license->license->federations()
                    ->where('is_local', false)
                    ->get();

                foreach ($modalidadeFederations as $federation) {
                    $this->syncIndividualToLicenseFederation($individual, $federation->id);
                }
            }
        }
    }

    /**
     * Sync individual to a modalidade federation.
     * When a sport license is activated, the individual becomes a member of
     * all modalidade federations (is_local = false) linked to that license.
     */
    private function syncIndividualToLicenseFederation(Individual $individual, int $federationId): void
    {
        $existing = IndividualFederation::where('individual_id', $individual->id)
            ->where('federation_id', $federationId)
            ->first();

        if ($existing) {
            // Activate if not already active
            if ($existing->status_class !== ActiveIndividualFederationState::class) {
                $existing->update([
                    'status_class' => ActiveIndividualFederationState::class,
                    'active' => 1,
                ]);

                activity('License Federation Sync')
                    ->performedOn($individual)
                    ->event('activated')
                    ->withProperties([
                        'federation_id' => $federationId,
                        'trigger' => 'license_activation',
                        'action' => 'activated_existing',
                    ])
                    ->log('Individual federation membership activated via license activation');
            }
        } else {
            // Create new federation membership
            IndividualFederation::create([
                'individual_id' => $individual->id,
                'federation_id' => $federationId,
                'status_class' => ActiveIndividualFederationState::class,
                'active' => 1,
            ]);

            activity('License Federation Sync')
                ->performedOn($individual)
                ->event('created')
                ->withProperties([
                    'federation_id' => $federationId,
                    'trigger' => 'license_activation',
                    'action' => 'created_new',
                ])
                ->log('Individual synced to federation via license activation');
        }
    }

    private function logLicenseActivity($license)
    {
        activity('License')
            ->performedOn($license)
            ->event('activated')
            ->withProperties((array) $license)
            ->log('License changed to Active state.');
    }

    /**
     * Check if the license can be activated.
     * A license can be activated if:
     * - It's free (total_value <= 0), OR
     * - It has a paid document associated with it
     */
    private function canActivate(LicenseAttributed $license): bool
    {
        // Free licenses can always be activated
        if ($license->total_value <= 0) {
            return true;
        }

        // Check if there's a paid document for this license
        $hasPaidDocument = DocumentDetail::where('owner_type', LicenseAttributed::class)
            ->where('owner_id', $license->id)
            ->whereHas('document', function ($query) {
                $query->where('status_class', PaidDocumentState::class);
            })
            ->exists();

        return $hasPaidDocument;
    }
}
