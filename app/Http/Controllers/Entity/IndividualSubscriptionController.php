<?php

namespace App\Http\Controllers\Entity;

use Domain\Individuals\Models\Individual;
use Domain\Memberships\Models\MemberSubscription;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndividualSubscriptionController
{
    public function index(): View
    {
        // Get the currently authenticated user's entity ID
        $entityId = Auth::user()->entities()->first()?->id;

        abort_if(! $entityId, 403, 'No entity associated with this user.');

        $subscriptions = QueryBuilder::for(MemberSubscription::class)
            ->allowedFilters([
                AllowedFilter::exact('status_class'),
                AllowedFilter::partial('member.name'),
                AllowedFilter::partial('member.surname'),
                AllowedFilter::partial('membershipPackage.name'),
            ])
            ->where('member_type', Individual::class)
            ->whereHasMorph(
                'member',
                [Individual::class],
                function ($query) use ($entityId) {
                    $query->whereHas('entities', function ($query) use ($entityId) {
                        $query->where('entity.id', $entityId);
                    });
                }
            )
            ->with([
                'member' => function ($query) {
                    $query->select('id', 'name', 'surname')
                        ->withDefault();
                },
                'membershipPackage' => function ($query) {
                    $query->select('id', 'name');
                },
            ])
            ->paginate()
            ->appends(request()->query());

        return view('web.entity.individual-subscriptions.index', compact('subscriptions'));
    }

    public function show(MemberSubscription $individual_subscription): View
    {
        // Ensure the entity can only view subscriptions of their members
        $entity = Auth::user()->getEntity();

        abort_unless($individual_subscription->member->entities()->where('entity.id', $entity->id)->exists(), 403);

        $individual_subscription->load(['member', 'membershipPackage', 'affiliations', 'insurances']);

        return view('web.entity.individual-subscriptions.show', compact('individual_subscription'));
    }
}
