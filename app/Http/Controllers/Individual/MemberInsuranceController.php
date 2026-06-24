<?php

namespace App\Http\Controllers\Individual;

use App\Enums\MembershipTargetType;
use App\Http\Controllers\Controller;
use Domain\Insurance\Models\Insurance;
use Domain\Insurance\States\ActiveInsuranceState;
use Domain\Insurance\States\PendingPaymentInsuranceState;
use Domain\Memberships\Models\MembershipPackage;
use Domain\Memberships\Models\MemberSubscription;
use Domain\Memberships\States\PendingPaymentMemberSubscriptionState;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class MemberInsuranceController extends Controller
{
    public function index(): View
    {
        $individual = auth()->user()->individuals()->firstOrFail();

        $currentInsurances = Insurance::where('member_type', 'individual')
            ->where('member_id', $individual->id)
            ->where('end_date', '>=', Carbon::now())
            ->where('status_class', ActiveInsuranceState::class)
            ->with('insurancePlan')
            ->get();

        // Get pending insurances (those with pending payment status)
        $pendingInsurances = Insurance::where('member_type', 'individual')
            ->where('member_id', $individual->id)
            ->where('status_class', PendingPaymentInsuranceState::class)
            ->with(['insurancePlan', 'memberSubscription'])
            ->get();

        $insuranceHistory = Insurance::where('member_type', 'individual')
            ->where('member_id', $individual->id)
            ->where('end_date', '<', Carbon::now())
            ->with('insurancePlan')
            ->orderBy('end_date', 'desc')
            ->get();

        // Get package IDs from pending subscriptions to exclude them from available packages
        $pendingPackageIds = MemberSubscription::where('member_type', 'individual')
            ->where('member_id', $individual->id)
            ->where('status_class', PendingPaymentMemberSubscriptionState::class)
            ->whereHas('membershipPackage', function ($query) {
                $query->whereHas('insurancePlans')
                    ->whereDoesntHave('affiliationPlans');
            })
            ->pluck('membership_package_id')
            ->toArray();

        // Get insurance-only membership packages for individuals
        $availableInsurancePackages = MembershipPackage::where('is_active', true)
            ->whereIn('target_type', [MembershipTargetType::INDIVIDUAL, MembershipTargetType::BOTH])
            ->whereNotIn('id', $pendingPackageIds) // Exclude pending packages
            ->with(['insurancePlans', 'affiliationPlans'])
            ->get()
            ->filter(function ($package) {
                // Only packages that have insurance plans and no affiliation plans (insurance-only)
                return $package->insurancePlans->isNotEmpty() && $package->affiliationPlans->isEmpty();
            })
            ->map(function ($package) {
                $package->calculated_price = $package->calculatePriceForType('individual');

                return $package;
            });

        return view('web.individual.insurances.index', compact(
            'individual',
            'currentInsurances',
            'pendingInsurances',
            'insuranceHistory',
            'availableInsurancePackages'
        ));
    }
}
