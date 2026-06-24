<?php

namespace App\Livewire\Concerns;

use Domain\Documents\States\PaidDocumentState;
use Domain\Entities\Models\Entity;
use Domain\Entities\States\ActiveEntityFederationState;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

trait HasEntityBillingQuery
{
    protected function getTopBilledEntities(?string $federationId): LengthAwarePaginator
    {
        if (! $federationId) {
            return Entity::query()->whereRaw('0 = 1')->paginate(30);
        }

        $paidStateClass = PaidDocumentState::class;
        $currentYear = now()->year;

        $totalPaidDocuments = "(
            SELECT COALESCE(SUM(d.total_value), 0)
            FROM document d
            WHERE d.owner_type = 'entity'
            AND d.owner_id = entity.id
            AND d.status_class = ?
            AND YEAR(d.created_at) = ?
            AND d.deleted_at IS NULL
        )";

        return Entity::query()
            ->select([
                'entity.id',
                'entity.name',
                'entity.district_id',
                DB::raw("({$totalPaidDocuments}) as total_affiliation_fee"),
            ])
            ->addBinding([$paidStateClass, $currentYear], 'select')
            ->whereHas('federations', function ($query) use ($federationId) {
                $query->where('federation_id', $federationId)
                    ->where('status_class', ActiveEntityFederationState::class);
            })
            ->with(['district:id,name', 'media'])
            ->having('total_affiliation_fee', '>', 0)
            ->orderByDesc('total_affiliation_fee')
            ->paginate(30);
    }
}
