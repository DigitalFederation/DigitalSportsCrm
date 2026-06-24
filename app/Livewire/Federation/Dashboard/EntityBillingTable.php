<?php

namespace App\Livewire\Federation\Dashboard;

use Domain\Documents\States\PaidDocumentState;
use Domain\Entities\Models\Entity;
use Domain\Entities\States\ActiveEntityFederationState;
use Domain\Memberships\Models\MemberSubscription;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class EntityBillingTable extends Component
{
    use WithPagination;

    public function render()
    {
        $federationId = Auth::user()->federations()->first()->id ?? null;

        $entities = $this->getTopBilledEntitiesByAffiliationPlans($federationId);

        return view('livewire.federation.dashboard.entity-billing-table', [
            'entities' => $entities,
        ]);
    }

    protected function getTopBilledEntitiesByAffiliationPlans(?string $federationId): LengthAwarePaginator
    {
        if (! $federationId) {
            return Entity::query()->whereRaw('0 = 1')->paginate(10);
        }

        $paidStateClass = PaidDocumentState::class;
        $memberSubscriptionClass = MemberSubscription::class;
        $currentYear = now()->year;

        // Subquery to get total from paid documents related to member_subscriptions
        // where the subscription was requested by the entity and has an affiliation
        // to the current federation. Only counts document_details whose value matches
        // the affiliation fee (individual_fee or entity_fee) for this federation.
        $totalAffiliationDocuments = "(
            SELECT COALESCE(SUM(dd.total_value), 0)
            FROM document_detail dd
            INNER JOIN document d ON d.id = dd.document_id
            INNER JOIN member_subscriptions ms ON ms.id = dd.owner_id
                AND dd.owner_type = ?
            INNER JOIN affiliations a ON a.member_subscription_id = ms.id
                AND a.federation_id = ?
                AND (dd.total_value = a.individual_fee OR dd.total_value = a.entity_fee)
            WHERE ms.requester_type = 'entity'
            AND ms.requester_id = entity.id
            AND d.status_class = ?
            AND YEAR(d.created_at) = ?
            AND d.deleted_at IS NULL
            AND dd.deleted_at IS NULL
        )";

        return Entity::query()
            ->select([
                'entity.id',
                'entity.name',
                'entity.district_id',
                DB::raw("({$totalAffiliationDocuments}) as total_affiliation_fee"),
            ])
            ->addBinding([$memberSubscriptionClass, $federationId, $paidStateClass, $currentYear], 'select')
            ->whereHas('federations', function ($query) use ($federationId) {
                $query->where('federation_id', $federationId)
                    ->where('status_class', ActiveEntityFederationState::class);
            })
            ->with(['district:id,name', 'media'])
            ->having('total_affiliation_fee', '>', 0)
            ->orderByDesc('total_affiliation_fee')
            ->paginate(10);
    }
}
