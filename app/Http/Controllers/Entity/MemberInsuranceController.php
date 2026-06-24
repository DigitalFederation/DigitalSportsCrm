<?php

namespace App\Http\Controllers\Entity;

use App\Enums\MembershipTargetType;
use App\Http\Controllers\Controller;
use Domain\Entities\Models\Entity;
use Domain\Insurance\Models\Insurance;
use Domain\Memberships\Models\MembershipPackage;
use Domain\Memberships\Models\MemberSubscription;
use Domain\Memberships\States\ActiveMemberSubscriptionState;
use Domain\Memberships\States\PendingPaymentMemberSubscriptionState;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MemberInsuranceController extends Controller
{
    public function index(): View
    {
        $entity = Auth::user()->getEntity();

        // Get insurance-only subscriptions (packages that contain only insurance plans)
        $insuranceOnlySubscriptions = MemberSubscription::with([
            'membershipPackage.insurancePlans',
            'membershipPackage.affiliationPlans',
            'insurances.insurancePlan',
        ])
            ->where('member_type', 'entity')
            ->where('member_id', $entity->id)
            ->where('end_date', '>=', Carbon::now())
            ->whereIn('status_class', [
                ActiveMemberSubscriptionState::class,
                PendingPaymentMemberSubscriptionState::class,
            ])
            ->whereHas('membershipPackage', function ($query) {
                $query->whereHas('insurancePlans')
                    ->whereDoesntHave('affiliationPlans');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Get direct insurance records (if any exist outside of subscriptions)
        $directInsurances = Insurance::where('member_type', Entity::class)
            ->where('member_id', $entity->id)
            ->where('end_date', '>=', Carbon::now())
            ->whereIn('status_class', [
                \Domain\Insurance\States\ActiveInsuranceState::class,
                \Domain\Insurance\States\PendingPaymentInsuranceState::class,
            ])
            ->with('insurancePlan')
            ->get();

        // Combine both collections for display
        $currentInsurances = collect()
            ->merge($insuranceOnlySubscriptions)
            ->merge($directInsurances);

        // Get already subscribed package IDs (including pending ones)
        $subscribedPackageIds = $entity->memberSubscriptions()
            ->where('end_date', '>=', Carbon::now())
            ->whereIn('status_class', [
                ActiveMemberSubscriptionState::class,
                PendingPaymentMemberSubscriptionState::class,
            ])
            ->pluck('membership_package_id')
            ->toArray();

        // Get insurance-only membership packages for entities
        $availableInsurancePackages = MembershipPackage::where('is_active', true)
            ->whereIn('target_type', [MembershipTargetType::ENTITY, MembershipTargetType::BOTH])
            ->whereNotIn('id', $subscribedPackageIds)
            ->with(['insurancePlans', 'affiliationPlans'])
            ->get()
            ->filter(function ($package) {
                // Only packages that have insurance plans and no affiliation plans (insurance-only)
                return $package->insurancePlans->isNotEmpty() && $package->affiliationPlans->isEmpty();
            })
            ->map(function ($package) {
                $package->calculated_price = $package->calculatePriceForType('entity');

                return $package;
            });

        return view('web.entity.insurances.index', compact('currentInsurances', 'availableInsurancePackages'));
    }
}
