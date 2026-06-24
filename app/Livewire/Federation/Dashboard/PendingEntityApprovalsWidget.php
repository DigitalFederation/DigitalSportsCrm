<?php

namespace App\Livewire\Federation\Dashboard;

use Domain\Entities\Models\EntityFederation;
use Domain\Entities\States\PendingEntityFederationState;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class PendingEntityApprovalsWidget extends Component
{
    public function render()
    {
        $user = Auth::user();
        $federationId = $user->federations()->first()->id ?? null;

        $pendingCount = 0;
        $pendingEntities = collect();

        if ($federationId) {
            $cacheKey = "pending_entity_approvals_{$federationId}";
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

        return view('livewire.federation.dashboard.pending-entity-approvals-widget', [
            'pendingCount' => $pendingCount,
            'pendingEntities' => $pendingEntities,
        ]);
    }
}
