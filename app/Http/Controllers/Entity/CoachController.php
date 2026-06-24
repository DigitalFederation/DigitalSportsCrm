<?php

namespace App\Http\Controllers\Entity;

use App\Http\Controllers\Controller;
use App\Models\Sport;
use Domain\Entities\Models\EntityProfessionalRole;
use Domain\Entities\Models\EntityProfessionalRoleInvitation;
use Domain\Entities\States\PendingEntityProfessionalRoleState;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Domain\Licenses\States\PendingLicenseAttributedState;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CoachController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $entity = Auth::user()->entities()->first();
        if (! $entity) {
            // Handle case where user has no associated entity
            abort(403, 'User not associated with an entity.');
        }

        // Get sports where entity has active licenses
        $sportsWithLicenses = $this->getEntitySportsWithActiveLicenses($entity);

        // Get sports where entity has pending licenses (for warning message)
        $sportsWithPendingLicenses = $this->getEntitySportsWithPendingLicenses($entity);

        // Get coach professional roles
        $coachRoles = ProfessionalRole::where('role', 'COACH')->get();
        $coachRoleIds = $coachRoles->pluck('id')->toArray();

        // Fetch ALL coach records from EntityProfessionalRole (source of truth)
        // This ensures consistency between badge counts and table data
        $allCoaches = EntityProfessionalRole::where('entity_id', $entity->id)
            ->whereIn('professional_role_id', $coachRoleIds)
            ->with(['individual.country', 'professionalRole', 'sport'])
            ->orderByDesc('created_at')
            ->get();

        // Filter into active, pending, and history from the same collection
        $coaches = $allCoaches->filter(fn ($c) => $c->isActive());
        $pendingInvitations = $allCoaches->filter(
            fn ($c) => $c->status_class === PendingEntityProfessionalRoleState::class
        );
        // History: rejected and canceled invitations
        $historyInvitations = $allCoaches->filter(
            fn ($c) => in_array($c->status_class, [
                \Domain\Entities\States\RejectedEntityProfessionalRoleState::class,
                \Domain\Entities\States\CanceledEntityProfessionalRoleState::class,
            ])
        );

        // Pass all to the view
        return view('web.entity.coach.index', compact('coaches', 'pendingInvitations', 'historyInvitations', 'sportsWithLicenses', 'sportsWithPendingLicenses', 'entity', 'coachRoles'));
    }

    public function destroy(int $entityProfessionalRoleId): RedirectResponse
    {
        try {

            $entityProfessionalRole = EntityProfessionalRole::findOrFail($entityProfessionalRoleId);
            // Check if the entity matches the logged in user's entity
            if ($entityProfessionalRole->entity_id !== Auth::user()->entities()->first()->id) {
                return redirect()->back()->with('error', 'Unauthorized action.');
            }
            $entityProfessionalRole->delete();

            // Log the action before deleting
            activity()
                ->performedOn($entityProfessionalRole)
                ->withProperties([
                    'entity_id' => $entityProfessionalRole->entity_id,
                    'individual_id' => $entityProfessionalRole->individual_id,
                    'professional_role_id' => $entityProfessionalRole->professional_role_id,
                    'member_code' => $entityProfessionalRole->individual->member_code,
                ])
                ->log("Coach relationship with CMAS code {$entityProfessionalRole->individual->member_code} removed");

            return redirect()->back()->with('success', __('coaches.coach_removed_successfully'));
        } catch (Exception $exception) {
            // Log the exception and return with an error message
            Log::error($exception->getMessage());

            return redirect()->back()->with('error', 'There was an issue removing the coach relationship.');
        }
    }

    public function cancelInvitation(int $invitationId): RedirectResponse
    {
        try {
            $entity = Auth::user()->entities()->first();
            if (! $entity) {
                return redirect()->back()->with('error', __('coaches.cancel_failed'));
            }

            $invitation = EntityProfessionalRoleInvitation::where('id', $invitationId)
                ->where('entity_id', $entity->id)
                ->where('status_class', PendingEntityProfessionalRoleState::class)
                ->first();

            if (! $invitation) {
                return redirect()->back()->with('error', __('coaches.invitation_not_found'));
            }

            DB::beginTransaction();

            $individualId = $invitation->individual_id;
            $entityName = $entity->name;
            $individualName = $invitation->individual?->name . ' ' . $invitation->individual?->surname;

            // Delete the pending EntityProfessionalRole record (so coach can be re-invited)
            if ($individualId) {
                EntityProfessionalRole::where('entity_id', $entity->id)
                    ->where('individual_id', $individualId)
                    ->where('status_class', PendingEntityProfessionalRoleState::class)
                    ->delete();
            }

            // Delete the invitation record
            $invitation->delete();

            DB::commit();

            // Log to activity log for audit trail
            activity('coach-invitation')
                ->causedBy(Auth::user())
                ->withProperties([
                    'invitation_id' => $invitationId,
                    'entity_id' => $entity->id,
                    'entity_name' => $entityName,
                    'individual_id' => $individualId,
                    'individual_name' => $individualName,
                    'action' => 'canceled_and_deleted',
                ])
                ->log(__('coaches.activity_canceled_invitation', ['entity' => $entityName]));

            Log::info('Coach invitation canceled and deleted', [
                'invitation_id' => $invitationId,
                'entity_id' => $entity->id,
                'individual_id' => $individualId,
            ]);

            return redirect()->back()->with('success', __('coaches.invitation_canceled'));
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error('Failed to cancel coach invitation: ' . $exception->getMessage());

            return redirect()->back()->with('error', __('coaches.cancel_failed'));
        }
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
}
