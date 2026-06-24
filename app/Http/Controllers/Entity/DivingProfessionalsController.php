<?php

namespace App\Http\Controllers\Entity;

use App\Http\Controllers\Controller;
use Domain\Entities\Models\EntityProfessionalRole;
use Domain\Entities\States\ActiveEntityProfessionalRoleState;
use Domain\Entities\States\PendingEntityProfessionalRoleState;
use Domain\Entities\States\RejectedEntityProfessionalRoleState;
use Domain\Individuals\Models\ProfessionalRole;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DivingProfessionalsController extends Controller
{
    public function index(): View
    {
        $entity = auth()->user()->entities()->first();

        if (! $entity) {
            abort(403, __('diving.entity_not_found'));
        }

        // Get diving professional roles (DIVINGPROFESSIONAL role with license from DIVINGSERVICES committee)
        $professionalRoles = ProfessionalRole::where('role', 'DIVINGPROFESSIONAL')
            ->whereHas('licenses', function ($q) {
                $q->whereHas('committee', function ($q2) {
                    $q2->where('code', 'DIVINGSERVICES');
                });
            })
            ->get();

        // Get diving professionals for this entity (only active and deactivated, not pending)
        $instructors = $entity->entityProfessionals()
            ->with(['individual.media', 'professionalRole'])
            ->whereIn('status_class', [
                ActiveEntityProfessionalRoleState::class,
                RejectedEntityProfessionalRoleState::class,
            ])
            ->whereHas('professionalRole', function ($query) {
                $query->where('role', 'DIVINGPROFESSIONAL')
                    ->whereHas('licenses', function ($q) {
                        $q->whereHas('committee', function ($q2) {
                            $q2->where('code', 'DIVINGSERVICES');
                        });
                    });
            })
            ->paginate(10);

        // Get pending invitations from EntityProfessionalRole
        $pendingInvitations = $entity->entityProfessionals()
            ->where('status_class', PendingEntityProfessionalRoleState::class)
            ->whereHas('professionalRole', function ($query) {
                $query->where('role', 'DIVINGPROFESSIONAL')
                    ->whereHas('licenses', function ($q) {
                        $q->whereHas('committee', function ($q2) {
                            $q2->where('code', 'DIVINGSERVICES');
                        });
                    });
            })
            ->with(['individual', 'professionalRole'])
            ->get();

        return view('web.entity.diving_professionals.index', compact(
            'entity',
            'instructors',
            'pendingInvitations',
            'professionalRoles'
        ));
    }

    public function cancelInvitation(int $invitationId): RedirectResponse
    {
        $entity = auth()->user()->entities()->first();

        if (! $entity) {
            abort(403, __('diving.entity_not_found'));
        }

        $invitation = EntityProfessionalRole::find($invitationId);

        if (! $invitation || $invitation->entity_id !== $entity->id) {
            abort(403);
        }

        if ($invitation->status_class !== PendingEntityProfessionalRoleState::class) {
            return back()->with('error', __('diving.cannot_cancel_invitation'));
        }

        try {
            // Cancel the invitation
            $invitation->update([
                'status_class' => \Domain\Entities\States\CanceledEntityProfessionalRoleState::class,
            ]);

            return redirect()->route('entity.diving_professionals.index')
                ->with('success', __('diving.invitation_canceled_successfully'));

        } catch (\Exception $e) {
            Log::error('Failed to cancel invitation: ' . $e->getMessage());

            return back()->with('error', __('diving.failed_to_cancel_invitation'));
        }
    }

    public function remove(Request $request, int $professionalId): RedirectResponse
    {
        $entity = auth()->user()->entities()->first();

        if (! $entity) {
            abort(403, __('diving.entity_not_found'));
        }

        $professional = $entity->entityProfessionals()->find($professionalId);

        if (! $professional) {
            abort(404);
        }

        $validated = $request->validate([
            'reason' => 'required_if:action,deactivate|nullable|string|max:500',
            'action' => 'required|in:deactivate,delete',
        ]);

        try {
            if ($validated['action'] === 'deactivate') {
                // Deactivate the relationship instead of deleting it
                $professional->deactivate($validated['reason'], 'entity');

                activity('entity_professional_role')
                    ->performedOn($professional)
                    ->causedBy(auth()->user())
                    ->withProperties([
                        'entity_id' => $entity->id,
                        'individual_id' => $professional->individual_id,
                        'reason' => $validated['reason'],
                    ])
                    ->log('Professional relationship deactivated by entity');

                return redirect()->route('entity.diving_professionals.index')
                    ->with('success', __('diving.professional_deactivated_successfully'));
            } else {
                // Permanently delete the relationship
                $professional->delete();

                return redirect()->route('entity.diving_professionals.index')
                    ->with('success', __('diving.professional_removed_successfully'));
            }

        } catch (\Exception $e) {
            Log::error('Failed to remove/deactivate professional: ' . $e->getMessage());

            return back()->with('error', __('diving.failed_to_remove_professional'));
        }
    }
}
