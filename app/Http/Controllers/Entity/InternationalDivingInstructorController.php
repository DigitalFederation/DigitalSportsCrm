<?php

namespace App\Http\Controllers\Entity;

use App\Http\Controllers\Controller;
use Domain\Entities\Models\EntityProfessionalRole;
use Domain\Entities\Models\EntityProfessionalRoleInvitation;
use Domain\Individuals\Models\ProfessionalRole;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class InternationalDivingInstructorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $entity = Auth::user()->entities()->first();
        if (! $entity) {
            abort(403, 'User not associated with an entity.');
        }

        // Fetch Professional Roles for Livewire component (international Diving - INSTRUCTOR and LEADER roles)
        $professionalRoles = ProfessionalRole::select('id', 'name')
            ->whereIn('role', ['INSTRUCTOR', 'LEADER'])
            ->whereHas('committee', function (Builder $query) {
                return $query->where('code', 'DIVING');
            })
            ->get();

        $professionalRoleIds = $professionalRoles->pluck('id')->toArray();

        // Fetch associated instructors (active ones)
        $instructors = EntityProfessionalRole::where('entity_id', $entity->id)
            ->whereIn('professional_role_id', $professionalRoleIds)
            ->with(['individual.country', 'professionalRole'])
            ->orderByDesc('created_at')
            ->paginate(15);

        // Fetch Pending Invitations sent by this entity for DIVING committee (INSTRUCTOR/LEADER)
        $hasNewStructure = \Schema::hasColumn('entity_professional_role_invitations', 'entity_id') &&
                          \Schema::hasColumn('entity_professional_role_invitations', 'professional_role_id');

        if ($hasNewStructure) {
            $pendingInvitations = EntityProfessionalRoleInvitation::where('entity_id', $entity->id)
                ->whereHas('professionalRole', function ($query) {
                    $query->whereIn('role', ['INSTRUCTOR', 'LEADER'])
                        ->whereHas('committee', function ($q) {
                            $q->where('code', 'DIVING');
                        });
                })
                ->where('status_class', \Domain\Entities\States\PendingEntityProfessionalRoleState::class)
                ->with(['individual', 'professionalRole'])
                ->orderByDesc('created_at')
                ->get();
        } else {
            $pendingInvitations = EntityProfessionalRoleInvitation::where('inviting_entity_id', $entity->id)
                ->where('committee_code', 'DIVING')
                ->where('status', 'pending')
                ->with('user')
                ->orderByDesc('created_at')
                ->get();
        }

        return view('web.entity.international_diving_instructor.index', compact(
            'entity',
            'instructors',
            'professionalRoles',
            'pendingInvitations'
        ));
    }

    public function cancelInvitation($invitationId): RedirectResponse
    {
        $entity = Auth::user()->entities()->first();
        if (! $entity) {
            abort(403, 'User not associated with an entity.');
        }

        $invitation = EntityProfessionalRole::find($invitationId);

        if (! $invitation || $invitation->entity_id !== $entity->id) {
            abort(403);
        }

        if ($invitation->status_class !== \Domain\Entities\States\PendingEntityProfessionalRoleState::class) {
            return back()->with('error', __('international_diving.cannot_cancel_invitation'));
        }

        try {
            $invitation->update([
                'status_class' => \Domain\Entities\States\CanceledEntityProfessionalRoleState::class,
            ]);

            return redirect()->route('entity.international-diving-instructor.index')
                ->with('success', __('international_diving.invitation_canceled_successfully'));

        } catch (\Exception $e) {
            Log::error('Failed to cancel invitation: ' . $e->getMessage());

            return back()->with('error', __('international_diving.failed_to_cancel_invitation'));
        }
    }

    public function remove(Request $request, $professionalId): RedirectResponse
    {
        $entity = Auth::user()->entities()->first();
        if (! $entity) {
            abort(403, 'User not associated with an entity.');
        }

        $professional = $entity->entityProfessionals()->find($professionalId);

        if (! $professional) {
            abort(404);
        }

        // Verify it's a DIVING committee INSTRUCTOR/LEADER role
        if ($professional->professionalRole->committee->code !== 'DIVING' ||
            ! in_array($professional->professionalRole->role, ['INSTRUCTOR', 'LEADER'])) {
            abort(403);
        }

        $validated = $request->validate([
            'reason' => 'required_if:action,deactivate|nullable|string|max:500',
            'action' => 'required|in:deactivate,delete',
        ]);

        try {
            if ($validated['action'] === 'deactivate') {
                $professional->deactivate($validated['reason'], 'entity');

                activity('entity_professional_role')
                    ->performedOn($professional)
                    ->causedBy(auth()->user())
                    ->withProperties([
                        'entity_id' => $entity->id,
                        'individual_id' => $professional->individual_id,
                        'reason' => $validated['reason'],
                    ])
                    ->log('CMAS diving instructor relationship deactivated by entity');

                return redirect()->route('entity.international-diving-instructor.index')
                    ->with('success', __('international_diving.professional_deactivated_successfully'));
            } else {
                $professional->delete();

                return redirect()->route('entity.international-diving-instructor.index')
                    ->with('success', __('international_diving.professional_removed_successfully'));
            }

        } catch (\Exception $e) {
            Log::error('Failed to remove/deactivate professional: ' . $e->getMessage());

            return back()->with('error', __('international_diving.failed_to_remove_professional'));
        }
    }

    public function destroy(int $entityProfessionalRoleId): RedirectResponse
    {
        try {
            $entityProfessionalRole = EntityProfessionalRole::findOrFail($entityProfessionalRoleId);
            if ($entityProfessionalRole->entity_id !== Auth::user()->entities()->first()->id) {
                return redirect()->back()->with('error', 'Unauthorized action.');
            }
            $entityProfessionalRole->delete();

            activity()
                ->performedOn($entityProfessionalRole)
                ->withProperties([
                    'entity_id' => $entityProfessionalRole->entity_id,
                    'individual_id' => $entityProfessionalRole->individual_id,
                    'professional_role_id' => $entityProfessionalRole->professional_role_id,
                    'member_code' => $entityProfessionalRole->individual->member_code,
                ])
                ->log("CMAS diving instructor relationship with CMAS code {$entityProfessionalRole->individual->member_code} removed");

            return redirect()->back()->with('success', 'CMAS diving instructor relationship removed successfully.');
        } catch (Exception $exception) {
            Log::error($exception->getMessage());

            return redirect()->back()->with('error', 'There was an issue removing the CMAS diving instructor relationship.');
        }
    }
}
