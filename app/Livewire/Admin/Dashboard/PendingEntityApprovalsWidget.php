<?php

namespace App\Livewire\Admin\Dashboard;

use Domain\Entities\Models\EntityFederation;
use Domain\Entities\States\PendingEntityFederationState;
use Domain\Federations\Models\Federation;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class PendingEntityApprovalsWidget extends Component
{
    public function render(): \Illuminate\Contracts\View\View
    {
        $federation = Federation::where('is_default_federation', true)->first();
        $federationId = $federation?->id;

        $pendingCount = 0;
        $pendingEntities = collect();

        if ($federationId) {
            $cacheKey = "admin_pending_entity_approvals_{$federationId}";
            $ttl = 300; // 5 minutes

            $data = Cache::remember($cacheKey, $ttl, function () use ($federationId) {
                $query = EntityFederation::with('entity')
                    ->where('federation_id', $federationId)
                    ->where('status_class', PendingEntityFederationState::class);

                return [
                    'count' => $query->count(),
                    'items' => $query->latest()->take(5)->get(),
                ];
            });

            $pendingCount = $data['count'];
            $pendingEntities = $data['items'];
        }

        return view('livewire.admin.dashboard.pending-entity-approvals-widget', [
            'pendingCount' => $pendingCount,
            'pendingEntities' => $pendingEntities,
        ]);
    }
}
