<?php

namespace App\Http\Controllers\Individual;

use App\Http\Controllers\Controller;
use Domain\Entities\Models\EntityAthlete;
use Domain\Entities\Models\EntityProfessionalRoleInvitation;
use Domain\Entities\States\ActiveEntityProfessionalRoleState;
use Domain\Entities\States\CanceledEntityProfessionalRoleState;
use Domain\Entities\States\PendingEntityProfessionalRoleState;
use Domain\Entities\States\RejectedEntityProfessionalRoleState;
use Domain\Individuals\Actions\SyncIndividualLocalFederationsAction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AthleteController extends Controller
{
    public function index(): View
    {
        $individualId = auth()->user()->individuals()->first()->id;

        // Get all invites - view will filter by tab
        $invites = EntityAthlete::where('individual_id', $individualId)
            ->with(['entity.district', 'sport'])
            ->orderByRaw('FIELD(status_class, ?, ?, ?, ?)', [
                PendingEntityProfessionalRoleState::class,
                ActiveEntityProfessionalRoleState::class,
                CanceledEntityProfessionalRoleState::class,
                RejectedEntityProfessionalRoleState::class,
            ])
            ->orderByDesc('created_at')
            ->get();

        // Get counts for tabs
        $activeCount = $invites->filter(fn ($invite) => $invite->isActive())->count();
        $pendingCount = $invites->filter(
            fn ($invite) => $invite->status_class === PendingEntityProfessionalRoleState::class
        )->count();
        $historyCount = $invites->filter(
            fn ($invite) => in_array($invite->status_class, [
                RejectedEntityProfessionalRoleState::class,
                CanceledEntityProfessionalRoleState::class,
            ])
        )->count();

        return view('web.individual.athlete.index', compact('invites', 'activeCount', 'pendingCount', 'historyCount'));
    }

    public function response(Request $request, $id): RedirectResponse
    {
        $invite = EntityAthlete::where(compact('id'))->where('status_class', PendingEntityProfessionalRoleState::class)->firstOrFail();
        $previousStatus = $invite->status_class;
        $invite->update([
            'status_class' => $request->status_class,
        ]);

        // Delete the corresponding invitation record (it has served its purpose)
        // Using delete instead of update to avoid unique constraint violations
        EntityProfessionalRoleInvitation::where('entity_id', $invite->entity_id)
            ->where('individual_id', $invite->individual_id)
            ->where('status_class', PendingEntityProfessionalRoleState::class)
            ->delete();

        // Sync local federation memberships if accepting
        if ($request->status_class === ActiveEntityProfessionalRoleState::class) {
            $syncAction = new SyncIndividualLocalFederationsAction;
            $syncAction->execute($invite->individual, $invite->entity);
        }

        // Log the response activity
        $action = $request->status_class === ActiveEntityProfessionalRoleState::class ? 'accepted' : 'rejected';
        $logMessage = $action === 'accepted'
            ? __('athletes.activity_accepted_invitation', ['entity' => $invite->entity->name])
            : __('athletes.activity_rejected_invitation', ['entity' => $invite->entity->name]);

        activity('athlete-invitation')
            ->performedOn($invite)
            ->causedBy(auth()->user())
            ->withProperties([
                'entity_id' => $invite->entity_id,
                'entity_name' => $invite->entity->name,
                'individual_id' => $invite->individual_id,
                'sport_id' => $invite->sport_id,
                'previous_status' => $previousStatus,
                'new_status' => $request->status_class,
                'action' => $action,
            ])
            ->log($logMessage);

        // Redirect to active tab after accepting, stay on pending tab after rejecting
        if ($request->status_class === ActiveEntityProfessionalRoleState::class) {
            return redirect()->route('individual.athlete.index', ['filter[status]' => 'active'])
                ->with('success', __('athletes.response_sent_successfully'));
        }

        return redirect()->back()->with('success', __('athletes.response_sent_successfully'));
    }

    public function destroy(EntityAthlete $entityAthlete): RedirectResponse
    {
        $entityName = $entityAthlete->entity->name;
        $individual = auth()->user()->individuals()->first();
        $entityId = $entityAthlete->entity_id;
        $individualId = $entityAthlete->individual_id;

        // Remove individual from entity's local federations before deleting
        $syncAction = new SyncIndividualLocalFederationsAction;
        $syncAction->removeOnDeactivation($individual, $entityAthlete->entity);

        // Perform soft delete
        $entityAthlete->delete();

        // Clean up the corresponding invitation if it exists (uses new structure)
        EntityProfessionalRoleInvitation::where('entity_id', $entityId)
            ->where('individual_id', $individualId)
            ->delete();

        // Log activity
        activity('athlete-entity')
            ->performedOn($entityAthlete)
            ->causedBy(auth()->user())
            ->withProperties(['entity_id' => $entityId, 'individual_id' => $individualId])
            ->log(__('athletes.activity_left_entity', ['entity' => $entityName]));

        return redirect()->route('individual.athlete.index')->with('success', __('athletes.entity_relationship_removed'));
    }
}
