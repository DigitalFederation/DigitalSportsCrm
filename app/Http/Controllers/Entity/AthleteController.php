<?php

namespace App\Http\Controllers\Entity;

use App\Http\Controllers\Controller;
use App\Models\Sport;
use Domain\Entities\Models\EntityAthlete;
use Domain\Entities\Models\EntityProfessionalRoleInvitation;
use Domain\Entities\States\ActiveEntityProfessionalRoleState;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Domain\Licenses\States\PendingLicenseAttributedState;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class AthleteController extends Controller
{
    public function index(): View
    {
        $entity = Auth::user()->entities()->first();

        if (! $entity) {
            abort(403, 'User is not associated with an entity.');
        }

        // Get sports where entity has active licenses
        $sportsWithLicenses = $this->getEntitySportsWithActiveLicenses($entity);

        // Get sports where entity has pending licenses (for warning message)
        $sportsWithPendingLicenses = $this->getEntitySportsWithPendingLicenses($entity);

        // Get athlete professional role
        $athleteRole = ProfessionalRole::where('role', 'ATHLETE')->first();

        // Fetch Pending Invitations sent by this entity for athletes
        $pendingInvitations = EntityProfessionalRoleInvitation::where('entity_id', $entity->id)
            ->where('professional_role_id', $athleteRole?->id)
            ->where('status_class', 'Domain\Entities\States\PendingEntityProfessionalRoleState')
            ->with('individual:id,name,surname,member_code')
            ->orderByDesc('created_at')
            ->get();

        // Get existing athletes (only active ones - pending are shown in invitations table)
        // Note: We filter by entity_id directly on EntityAthlete, not via IndividualsFromEntity scope
        // which requires a separate IndividualEntity relationship
        $athletes = QueryBuilder::for(EntityAthlete::class)
            ->allowedFilters([
                AllowedFilter::scope('filter_sport', 'filterSport'),
            ])
            ->where('entity_id', $entity->id)
            ->where('status_class', ActiveEntityProfessionalRoleState::class)
            ->with('individual.country', 'sport')
            ->whereHas('individual')
            ->paginate();

        $sports_filter = Sport::query()->pluck('name', 'id')->toArray();

        return view('web.entity.athlete.index', compact(
            'entity',
            'athletes',
            'sports_filter',
            'sportsWithLicenses',
            'sportsWithPendingLicenses',
            'athleteRole',
            'pendingInvitations'
        ));
    }

    /**
     * Get sports where the entity has active licenses
     */
    private function getEntitySportsWithActiveLicenses($entity)
    {
        // Get entity's active licenses
        $activeLicenses = LicenseAttributed::where('model_type', $entity->getMorphClass())
            ->where('model_id', $entity->id)
            ->where('status_class', ActiveLicenseAttributedState::class)
            ->with('license')
            ->get();

        // Extract unique sport IDs from licenses
        $sportIds = $activeLicenses->pluck('license.sport_id')->filter()->unique();

        // Get sport details
        return Sport::whereIn('id', $sportIds)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get sports where the entity has pending licenses
     */
    private function getEntitySportsWithPendingLicenses($entity)
    {
        // Get entity's pending licenses
        $pendingLicenses = LicenseAttributed::where('model_type', $entity->getMorphClass())
            ->where('model_id', $entity->id)
            ->where('status_class', PendingLicenseAttributedState::class)
            ->with('license')
            ->get();

        // Extract unique sport IDs from licenses
        $sportIds = $pendingLicenses->pluck('license.sport_id')->filter()->unique();

        // Get sport details
        return Sport::whereIn('id', $sportIds)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
    }

    /**
     * Remove the athlete association from the entity.
     */
    public function destroy(int $entityAthleteId): RedirectResponse
    {
        try {
            $entityAthlete = EntityAthlete::findOrFail($entityAthleteId);

            // Check if the entity matches the logged in user's entity
            if ($entityAthlete->entity_id !== Auth::user()->entities()->first()->id) {
                return redirect()->back()->with('error', 'Unauthorized action.');
            }

            // Log the action before deleting
            activity()
                ->performedOn($entityAthlete)
                ->withProperties([
                    'entity_id' => $entityAthlete->entity_id,
                    'individual_id' => $entityAthlete->individual_id,
                    'sport_id' => $entityAthlete->sport_id,
                    'member_code' => $entityAthlete->individual?->member_code,
                ])
                ->log(__('athletes.activity_athlete_removed', ['member_code' => $entityAthlete->individual?->member_code]));

            $entityAthlete->delete();

            return redirect()->back()->with('success', __('athletes.disassociate_success'));
        } catch (Exception $exception) {
            Log::error($exception->getMessage());

            return redirect()->back()->with('error', __('athletes.disassociate_error'));
        }
    }
}
