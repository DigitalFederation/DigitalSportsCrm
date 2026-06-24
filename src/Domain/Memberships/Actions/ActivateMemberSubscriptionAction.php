<?php

namespace Domain\Memberships\Actions;

use App\Notifications\UserAlert;
use App\Services\DashboardCacheService;
use Domain\Entities\Models\Entity;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\IndividualFederation;
use Domain\Individuals\States\ActiveIndividualFederationState;
use Domain\Insurance\States\ActiveInsuranceState;
use Domain\Memberships\Models\MemberSubscription;
use Domain\Memberships\Services\MemberNumberService;
use Domain\Memberships\States\ActiveMemberSubscriptionState;
use Domain\Memberships\States\PendingPaymentMemberSubscriptionState;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ActivateMemberSubscriptionAction
{
    /**
     * Activate a member subscription after payment is confirmed
     *
     * @throws Exception
     */
    public function __invoke(string $id): void
    {
        $memberSubscription = MemberSubscription::with(['member', 'membershipPackage', 'affiliations', 'insurances'])->findOrFail($id);

        if ($memberSubscription->status_class !== PendingPaymentMemberSubscriptionState::class) {
            Log::warning(sprintf('MemberSubscription: %s is not in Pending Payment state to be activated.', $memberSubscription->id));

            return;
        }

        try {
            DB::beginTransaction();

            // Update subscription status to active
            $memberSubscription->status_class = ActiveMemberSubscriptionState::class;
            $memberSubscription->save();

            // Activate related licenses if the relationship exists
            if ($memberSubscription->relationLoaded('licenses')) {
                foreach ($memberSubscription->licenses as $license) {
                    if ($license->pivot->status !== 'active') {
                        $memberSubscription->licenses()->updateExistingPivot($license->id, [
                            'status' => 'active',
                            'start_date' => $memberSubscription->start_date,
                            'end_date' => $memberSubscription->end_date,
                        ]);
                    }
                }
            }

            // Activate related affiliations if they exist
            if ($memberSubscription->relationLoaded('affiliations')) {
                foreach ($memberSubscription->affiliations as $affiliation) {
                    if ($affiliation->status_class !== 'Domain\Memberships\States\ActiveAffiliationState') {
                        $affiliation->status_class = 'Domain\Memberships\States\ActiveAffiliationState';
                        $affiliation->save();
                    }

                    // Sync individual to affiliation's federation
                    if ($affiliation->federation_id &&
                        ($memberSubscription->member_type === 'individual' ||
                         $memberSubscription->member_type === Individual::class)) {
                        $this->syncIndividualToAffiliationFederation(
                            $memberSubscription->member,
                            $affiliation->federation_id
                        );
                    }
                }
            }

            // Assign member number if individual doesn't have one yet
            if ($memberSubscription->member_type === 'individual' ||
                $memberSubscription->member_type === Individual::class) {
                $memberNumberService = new MemberNumberService;
                $memberNumberService->assignIndividualMemberNumber($memberSubscription->member);
            }

            // Activate related insurances if they exist
            if ($memberSubscription->relationLoaded('insurances')) {
                foreach ($memberSubscription->insurances as $insurance) {
                    if ($insurance->status_class !== ActiveInsuranceState::class) {
                        $insurance->status_class = ActiveInsuranceState::class;
                        $insurance->save();
                    }
                }
            }

            DB::commit();

            app(DashboardCacheService::class)->invalidateActivationCaches();

            // Send notifications to the member
            $this->notifyMember($memberSubscription);

            Log::info('MemberSubscription activated after payment', [
                'subscription_id' => $memberSubscription->id,
                'member_type' => $memberSubscription->member_type,
                'member_id' => $memberSubscription->member_id,
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error activating member subscription: ' . $e->getMessage(), [
                'subscription_id' => $memberSubscription->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Send notification to the member about subscription activation
     */
    private function notifyMember(MemberSubscription $memberSubscription): void
    {
        $users = collect();
        $message = __('memberships.subscription_activated_notification', [
            'package' => $memberSubscription->membershipPackage->name,
            'date' => $memberSubscription->end_date->format('d/m/Y'),
        ]);

        // Get users based on member type
        switch ($memberSubscription->member_type) {
            case 'individual':
            case 'Domain\Individuals\Models\Individual':
                if ($memberSubscription->member instanceof Individual && $memberSubscription->member->user) {
                    $users->push($memberSubscription->member->user);
                }
                break;
            case 'entity':
            case 'Domain\Entities\Models\Entity':
                if ($memberSubscription->member instanceof Entity) {
                    $users = $memberSubscription->member->users;
                }
                break;
        }

        // Send notifications
        if (! $users->isEmpty()) {
            $notification = new UserAlert($message);
            $users->each(function ($user) use ($notification) {
                if ($user) {
                    $user->notify($notification);
                }
            });
        } else {
            Log::warning("MemberSubscription with id {$memberSubscription->id} has no associated users for notification.");
        }
    }

    /**
     * Sync individual to an affiliation's federation.
     * When an affiliation is activated, the individual becomes a member of
     * that affiliation's federation.
     */
    private function syncIndividualToAffiliationFederation(Individual $individual, int $federationId): void
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

                Log::info('Individual federation membership activated via affiliation', [
                    'individual_id' => $individual->id,
                    'federation_id' => $federationId,
                    'action' => 'activated_existing',
                ]);
            }
        } else {
            // Create new federation membership
            IndividualFederation::create([
                'individual_id' => $individual->id,
                'federation_id' => $federationId,
                'status_class' => ActiveIndividualFederationState::class,
                'active' => 1,
            ]);

            Log::info('Individual synced to federation via affiliation', [
                'individual_id' => $individual->id,
                'federation_id' => $federationId,
                'action' => 'created_new',
            ]);
        }
    }
}
