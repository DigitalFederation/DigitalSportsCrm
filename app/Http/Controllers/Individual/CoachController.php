<?php

namespace App\Http\Controllers\Individual;

use App\Http\Controllers\Controller;
use Domain\Entities\Models\EntityProfessionalRole;
use Domain\Entities\Models\EntityProfessionalRoleInvitation;
use Domain\Entities\States\ActiveEntityProfessionalRoleState;
use Domain\Entities\States\CanceledEntityProfessionalRoleState;
use Domain\Entities\States\PendingEntityProfessionalRoleState;
use Domain\Entities\States\RejectedEntityProfessionalRoleState;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CoachController extends Controller
{
    public function index(): View
    {
        $individualId = auth()->user()->individuals()->first()->id;

        // Always fetch ALL coach records - the view will filter them into active/pending tabs
        // This ensures consistency between badge counts and table data
        $invites = EntityProfessionalRole::where('individual_id', $individualId)
            ->with(['entity.district', 'sport'])
            ->whereHas('professionalRole', function ($query) {
                $query->where('role', 'COACH');
            })
            ->orderByRaw('FIELD(status_class, ?, ?, ?, ?)', [
                PendingEntityProfessionalRoleState::class,
                ActiveEntityProfessionalRoleState::class,
                CanceledEntityProfessionalRoleState::class,
                RejectedEntityProfessionalRoleState::class,
            ])
            ->orderByDesc('created_at')
            ->get();

        // Calculate counts from the same collection to ensure consistency
        $activeCount = $invites->filter(fn ($invite) => $invite->isActive())->count();
        // Only count truly pending invitations (not rejected/canceled)
        $pendingCount = $invites->filter(
            fn ($invite) => $invite->status_class === PendingEntityProfessionalRoleState::class
        )->count();
        // History: rejected and canceled invitations
        $historyCount = $invites->filter(
            fn ($invite) => in_array($invite->status_class, [
                RejectedEntityProfessionalRoleState::class,
                CanceledEntityProfessionalRoleState::class,
            ])
        )->count();

        return view('web.individual.coach.index', compact('invites', 'activeCount', 'pendingCount', 'historyCount'));
    }

    public function response(Request $request, $id): RedirectResponse
    {
        $invite = EntityProfessionalRole::where(compact('id'))
            ->where('status_class', PendingEntityProfessionalRoleState::class)
            ->firstOrFail();

        $previousStatus = $invite->status_class;
        $invite->update([
            'status_class' => $request->status_class,
        ]);

        // Clean up EntityProfessionalRoleInvitation records
        // Delete all invitation records for this entity/user/sport combination to avoid unique constraint violations
        // The main EntityProfessionalRole is the source of truth, invitations are just for tracking
        EntityProfessionalRoleInvitation::where('inviting_entity_id', $invite->entity_id)
            ->where('invited_user_id', $invite->individual?->user_id)
            ->where('committee_code', 'SPORT')
            ->where('sport_id', $invite->sport_id)
            ->delete();

        // Log the response activity
        $action = $request->status_class === ActiveEntityProfessionalRoleState::class ? 'accepted' : 'rejected';
        $logMessage = $action === 'accepted'
            ? __('coaches.activity_accepted_invitation', ['entity' => $invite->entity->name])
            : __('coaches.activity_rejected_invitation', ['entity' => $invite->entity->name]);

        activity('coach-invitation')
            ->performedOn($invite)
            ->causedBy(auth()->user())
            ->withProperties([
                'entity_id' => $invite->entity_id,
                'entity_name' => $invite->entity->name,
                'individual_id' => $invite->individual_id,
                'previous_status' => $previousStatus,
                'new_status' => $request->status_class,
                'action' => $action,
            ])
            ->log($logMessage);

        // Redirect to active tab after accepting, stay on pending tab after rejecting
        if ($request->status_class === ActiveEntityProfessionalRoleState::class) {
            return redirect()->route('individual.coach.index', ['filter[status]' => 'active'])
                ->with('success', __('coaches.response_sent_successfully'));
        }

        return redirect()->back()->with('success', __('coaches.response_sent_successfully'));
    }

    public function destroy(int $entityProfessionalRoleId): RedirectResponse
    {
        try {
            $entityProfessionalRole = EntityProfessionalRole::findOrFail($entityProfessionalRoleId);

            // Check if the entity matches the logged in user's entity
            if ($entityProfessionalRole->individual_id !== auth()->user()->individuals()->first()->id) {
                return redirect()->back()->with('error', 'Unauthorized action.');
            }

            // Store values before delete for cleanup and logging
            $entityId = $entityProfessionalRole->entity_id;
            $individualId = $entityProfessionalRole->individual_id;
            $userId = $entityProfessionalRole->individual?->user_id;
            $entityName = $entityProfessionalRole->entity->name;
            $memberCode = $entityProfessionalRole->individual->member_code;
            $sportId = $entityProfessionalRole->sport_id;

            // Log the action before deleting
            activity('coach-entity')
                ->performedOn($entityProfessionalRole)
                ->causedBy(auth()->user())
                ->withProperties([
                    'entity_id' => $entityId,
                    'individual_id' => $individualId,
                    'professional_role_id' => $entityProfessionalRole->professional_role_id,
                    'member_code' => $memberCode,
                ])
                ->log(__('coaches.activity_left_entity', ['entity' => $entityName]));

            $entityProfessionalRole->delete();

            // Clean up the corresponding invitation if it exists (uses old structure)
            if ($userId) {
                EntityProfessionalRoleInvitation::where('inviting_entity_id', $entityId)
                    ->where('invited_user_id', $userId)
                    ->where('committee_code', 'SPORT')
                    ->where('sport_id', $sportId)
                    ->delete();
            }

            return redirect()->back()->with('success', __('coaches.entity_relationship_removed'));

        } catch (Exception $exception) {
            // Log the exception and return with an error message
            Log::error($exception->getMessage());

            return redirect()->back()->with('error', 'There was an issue removing the coach relationship.');
        }
    }
}
