<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MemberSubscriptionRequest;
use App\QueryFilters\FilterMemberNameByWordStart;
use Domain\Entities\Models\Entity;
use Domain\Individuals\Models\Individual;
use Domain\Memberships\Actions\CreateMemberSubscriptionAction;
use Domain\Memberships\Actions\RenewMemberSubscriptionAction;
use Domain\Memberships\DataTransferObject\MemberSubscriptionData;
use Domain\Memberships\Models\MembershipPackage;
use Domain\Memberships\Models\MemberSubscription;
use Domain\Memberships\Queries\RequesterEntitiesQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class MemberSubscriptionController extends Controller
{
    public function index(RequesterEntitiesQuery $requesterEntitiesQuery): View
    {
        $subscriptions = QueryBuilder::for(MemberSubscription::class)
            ->allowedFilters([
                AllowedFilter::exact('member_type'),
                AllowedFilter::exact('status_class'),
                AllowedFilter::custom('member.name', new FilterMemberNameByWordStart),
                AllowedFilter::exact('membership_package_id'),
                AllowedFilter::exact('requester_id'),
            ])
            ->with([
                'member', // Load the polymorphic member
                'membershipPackage',
                'requester',
            ])
            ->withCount(['affiliations', 'insurances'])
            ->orderByDesc('start_date')
            ->paginate()
            ->appends(request()->query());

        // Load entities relationship only for individuals
        $subscriptions->getCollection()->transform(function (MemberSubscription $subscription) {
            if ($subscription->member_type === 'individual' && $subscription->member) {
                $subscription->member->load('entities');
            }

            return $subscription;
        });

        $membershipPackages = MembershipPackage::orderBy('name')->pluck('name', 'id');
        $requesterEntities = $requesterEntitiesQuery->execute();

        return view('web.admin.member-subscriptions.index', compact('subscriptions', 'membershipPackages', 'requesterEntities'));
    }

    public function show(MemberSubscription $subscription): View
    {
        $subscription->load([
            'member',
            'membershipPackage',
            'affiliations.federation',
            'insurances.insurancePlan',
        ]);

        return view('web.admin.member-subscriptions.show', compact('subscription'));
    }

    public function create(): View
    {

        $individuals = Individual::get(['id', 'name', 'surname']);
        $entities = Entity::get(['id', 'name']);

        // Only show packages that have affiliation plans (not insurance-only packages)
        $membershipPackages = MembershipPackage::whereHas('affiliationPlans')
            ->where('is_active', true)
            ->get(['id', 'name']);

        return view('web.admin.member-subscriptions.create', compact('individuals', 'entities', 'membershipPackages'));
    }

    public function store(MemberSubscriptionRequest $request, CreateMemberSubscriptionAction $action): RedirectResponse
    {
        $subscriptionData = MemberSubscriptionData::fromArray($request->validated());

        $subscription = $action($subscriptionData);

        return redirect()->route('admin.member-subscriptions.show', $subscription)
            ->with('success', __('memberships.member_subscriptions.created_successfully'));
    }

    public function edit(MemberSubscription $subscription): View
    {
        $individuals = Individual::get(['id', 'name', 'surname']);
        $entities = Entity::get(['id', 'name']);

        // Only show packages that have affiliation plans (not insurance-only packages)
        $membershipPackages = MembershipPackage::whereHas('affiliationPlans')
            ->where('is_active', true)
            ->get(['id', 'name']);

        return view('web.admin.member-subscriptions.edit', compact('subscription', 'individuals', 'entities', 'membershipPackages'));
    }

    public function renew(MemberSubscription $subscription, RenewMemberSubscriptionAction $action): RedirectResponse
    {
        $renewedSubscription = $action($subscription);

        return redirect()->route('admin.member-subscriptions.show', $renewedSubscription)
            ->with('success', __('memberships.member_subscriptions.renewed_successfully'));
    }

    public function destroy(MemberSubscription $subscription): RedirectResponse
    {
        $subscription->load(['member', 'membershipPackage']);

        try {
            DB::transaction(function () use ($subscription) {
                // Count related records before deletion
                $affiliationsCount = $subscription->affiliations()->count();
                $insurancesCount = $subscription->insurances()->count();

                // Log activity before deletion with complete details
                activity()
                    ->performedOn($subscription)
                    ->causedBy(auth()->user())
                    ->withProperties([
                        'subscription_id' => $subscription->id,
                        'member_type' => $subscription->member_type,
                        'member_id' => $subscription->member_id,
                        'member_name' => $subscription->member?->name ?? __('common.not_available'),
                        'package_name' => $subscription->membershipPackage?->name ?? __('common.not_available'),
                        'start_date' => $subscription->start_date->format('Y-m-d'),
                        'end_date' => $subscription->end_date->format('Y-m-d'),
                        'status_class' => $subscription->status_class,
                        'affiliations_deleted_count' => $affiliationsCount,
                        'insurances_deleted_count' => $insurancesCount,
                    ])
                    ->log('Member subscription deleted with ' . $affiliationsCount . ' affiliation(s) and ' . $insurancesCount . ' insurance(s)');

                // Delete related records first
                $subscription->affiliations()->delete();
                $subscription->insurances()->delete();

                // Delete the subscription
                $subscription->delete();
            });

            return redirect()->route('admin.member-subscriptions.index')
                ->with('success', __('memberships.member_subscriptions.deleted_successfully'));

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', __('memberships.member_subscriptions.delete_failed'));
        }
    }

    public function updateStatus(Request $request, MemberSubscription $subscription): RedirectResponse
    {
        $subscription->load(['member', 'membershipPackage']);

        $request->validate([
            'status_class' => 'required|string|in:' . implode(',', [
                'Domain\\Memberships\\States\\ActiveMemberSubscriptionState',
                'Domain\\Memberships\\States\\PendingMemberSubscriptionState',
                'Domain\\Memberships\\States\\ExpiredMemberSubscriptionState',
                'Domain\\Memberships\\States\\PendingPaymentMemberSubscriptionState',
            ]),
        ]);

        try {
            $oldStatus = $subscription->status_class;
            $newStatus = $request->status_class;

            // Update the status
            $subscription->update(['status_class' => $newStatus]);

            // Log the status change
            activity()
                ->performedOn($subscription)
                ->withProperties([
                    'old_status_class' => $oldStatus,
                    'new_status_class' => $newStatus,
                    'old_status_name' => $oldStatus ? (new $oldStatus($subscription))->name() : __('common.not_available'),
                    'new_status_name' => (new $newStatus($subscription))->name(),
                    'member_type' => $subscription->member_type,
                    'member_id' => $subscription->member_id,
                    'member_name' => $subscription->member?->name ?? __('common.not_available'),
                    'package_name' => $subscription->membershipPackage?->name ?? __('common.not_available'),
                ])
                ->log('Member subscription status changed');

            return redirect()->back()
                ->with('success', __('memberships.member_subscriptions.status_updated_successfully'));

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', __('memberships.member_subscriptions.status_update_failed'));
        }
    }
}
