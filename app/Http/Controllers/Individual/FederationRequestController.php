<?php

namespace App\Http\Controllers\Individual;

use App\Http\Controllers\Controller;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\States\PendingIndividualFederationState;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FederationRequestController extends Controller
{
    /**
     * Show the form to request federation association.
     */
    public function create(): View
    {
        $user = Auth::user();
        if (! $user) {
            abort(403, 'Unauthorized action.');
        }

        // Get the authenticated individual
        $individual = Individual::where('user_id', $user->id)->first();
        if (! $individual) {
            abort(403, 'Individual profile not found.');
        }

        // Get available federations for request
        // Only Sport/Class Associations that are not manual
        $availableFederations = Federation::availableForIndividualRequest()
            ->orderBy('name')
            ->get();

        // Get existing federation relationships to exclude them
        $existingFederations = $individual->individualFederations()
            ->pluck('federation_id')
            ->toArray();

        // Filter out federations where individual already has a relationship
        $availableFederations = $availableFederations->filter(function ($federation) use ($existingFederations) {
            return ! in_array($federation->id, $existingFederations);
        });

        return view('web.individual.federation-request.create', compact('availableFederations'));
    }

    /**
     * Store the federation association request.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (! $user) {
            abort(403, 'Unauthorized action.');
        }

        // Get the authenticated individual
        $individual = Individual::where('user_id', $user->id)->first();
        if (! $individual) {
            abort(403, 'Individual profile not found.');
        }

        // Validate the request
        $request->validate([
            'federation_id' => 'required|exists:federation,id',
        ]);

        try {
            DB::beginTransaction();

            // Check if federation is eligible
            $federation = Federation::availableForIndividualRequest()
                ->where('id', $request->federation_id)
                ->first();

            if (! $federation) {
                throw new \Exception('Federation is not available for individual requests.');
            }

            // Check for existing relationship
            $existingRelationship = $individual->individualFederations()
                ->where('federation_id', $federation->id)
                ->first();

            if ($existingRelationship) {
                throw new \Exception('You already have a relationship with this federation.');
            }

            // Create individual_federation relationship
            $federationList = [$federation->id];

            // Add parent federation if exists
            if ($federation->parent_id) {
                $federationList[] = $federation->parent_id;
            }

            // Attach federations with pending status
            foreach ($federationList as $federationId) {
                $individual->federations()->attach($federationId, [
                    'active' => 0,
                    'status_class' => PendingIndividualFederationState::class,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            return redirect()->route('individual.federation-request.success')
                ->with('success', __('Your federation association request has been submitted successfully.'));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Federation association request error: ' . $e->getMessage());

            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Show success page after request submission.
     */
    public function success(): View
    {
        return view('web.individual.federation-request.success');
    }
}
