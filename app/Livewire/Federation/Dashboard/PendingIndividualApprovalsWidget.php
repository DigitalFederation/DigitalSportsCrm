<?php

namespace App\Livewire\Federation\Dashboard;

use Domain\Individuals\Models\IndividualFederation;
use Domain\Individuals\States\PendingIndividualFederationState;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class PendingIndividualApprovalsWidget extends Component
{
    public function render()
    {
        $user = Auth::user();
        $federationId = $user->federations()->first()->id ?? null;

        $pendingCount = 0;
        $pendingIndividuals = collect();

        if ($federationId) {
            $cacheKey = "pending_individual_approvals_{$federationId}";
            $ttl = 300; // 5 minutes

            $data = Cache::remember($cacheKey, $ttl, function () use ($federationId) {
                $query = IndividualFederation::with('individual')
                    ->where('federation_id', $federationId)
                    ->where('status_class', PendingIndividualFederationState::class);

                return [
                    'count' => $query->count(),
                    'items' => $query->latest()->take(5)->get(),
                ];
            });

            $pendingCount = $data['count'];
            $pendingIndividuals = $data['items'];
        }

        return view('livewire.federation.dashboard.pending-individual-approvals-widget', [
            'pendingCount' => $pendingCount,
            'pendingIndividuals' => $pendingIndividuals,
        ]);
    }
}
