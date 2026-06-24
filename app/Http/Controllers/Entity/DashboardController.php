<?php

namespace App\Http\Controllers\Entity;

use App\Http\Controllers\Controller;
use Domain\Entities\Models\Entity;
use Domain\Individuals\Models\IndividualEntity;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Domain\Memberships\States\ActiveAffiliationState;
use Domain\Memberships\States\PendingPaymentAffiliationState;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DashboardController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function index(): View
    {
        $entityId = auth()->user()->getEntityId();

        if ($entityId) {
            try {
                $entity = Entity::with(['federations', 'affiliations', 'licenses.license.committee'])
                    ->findOrFail($entityId);

                // Get entity affiliations (active and pending)
                $affiliations = $entity->affiliations()
                    ->with(['federation', 'memberSubscription.membershipPackage.affiliationPlans'])
                    ->whereIn('status_class', [
                        ActiveAffiliationState::class,
                        PendingPaymentAffiliationState::class,
                    ])
                    ->latest()
                    ->take(5)
                    ->get();

                // Get sport licenses (entity licenses with SPORT committee)
                $sportLicenses = $entity->licenses()
                    ->with('license.committee')
                    ->whereHas('license.committee', function ($q) {
                        $q->where('code', 'SPORT');
                    })
                    ->where('status_class', ActiveLicenseAttributedState::class)
                    ->latest()
                    ->take(5)
                    ->get();

                // Get diving licenses (entity licenses with DIVING committee)
                $divingLicenses = $entity->licenses()
                    ->with('license.committee')
                    ->whereHas('license.committee', function ($q) {
                        $q->where('code', 'DIVING');
                    })
                    ->where('status_class', ActiveLicenseAttributedState::class)
                    ->latest()
                    ->take(5)
                    ->get();

                // Get pending members to approve
                $pendingMembers = IndividualEntity::with('individual')
                    ->where('entity_id', $entityId)
                    ->whereIn('status_class', [
                        \Domain\Individuals\States\PendingFromIndividualEntityState::class,
                        \Domain\Individuals\States\PendingFromEntityIndividualEntityState::class,
                    ])
                    ->latest()
                    ->take(5)
                    ->get();

                // Count totals for badges
                $sportLicensesCount = $entity->licenses()
                    ->whereHas('license.committee', function ($q) {
                        $q->where('code', 'SPORT');
                    })
                    ->where('status_class', ActiveLicenseAttributedState::class)
                    ->count();

                $divingLicensesCount = $entity->licenses()
                    ->whereHas('license.committee', function ($q) {
                        $q->where('code', 'DIVING');
                    })
                    ->where('status_class', ActiveLicenseAttributedState::class)
                    ->count();

                $affiliationsCount = $entity->affiliations()
                    ->whereIn('status_class', [
                        ActiveAffiliationState::class,
                        PendingPaymentAffiliationState::class,
                    ])
                    ->count();

                $pendingMembersCount = IndividualEntity::where('entity_id', $entityId)
                    ->whereIn('status_class', [
                        \Domain\Individuals\States\PendingFromIndividualEntityState::class,
                        \Domain\Individuals\States\PendingFromEntityIndividualEntityState::class,
                    ])
                    ->count();

                return view('web.entity.dashboard', compact(
                    'entity',
                    'affiliations',
                    'sportLicenses',
                    'divingLicenses',
                    'pendingMembers',
                    'sportLicensesCount',
                    'divingLicensesCount',
                    'affiliationsCount',
                    'pendingMembersCount'
                ));
            } catch (ModelNotFoundException $e) {
                \Log::error('Entity not found for ID: '.$entityId);

                return view('web.entity.dashboard')->withErrors(['error' => 'Entity not found.']);
            }
        } else {
            \Log::error('No entity associated with user: '.auth()->id());

            return view('web.entity.dashboard')->withErrors(['error' => 'No entity associated with this account.']);
        }
    }
}
