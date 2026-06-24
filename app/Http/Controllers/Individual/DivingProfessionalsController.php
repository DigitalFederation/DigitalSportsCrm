<?php

namespace App\Http\Controllers\Individual;

use App\Http\Controllers\Controller;
use Domain\Entities\Models\EntityProfessionalRole;
use Domain\Entities\States\ActiveEntityProfessionalRoleState;
use Domain\Entities\States\CanceledEntityProfessionalRoleState;
use Domain\Entities\States\PendingEntityProfessionalRoleState;
use Domain\Entities\States\RejectedEntityProfessionalRoleState;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DivingProfessionalsController extends Controller
{
    /**
     * Display listing of diving professional invitations and relationships
     */
    public function index(): View
    {
        $individual = Auth::user()->individual;

        if (! $individual) {
            abort(403, 'Individual profile not found');
        }

        // Get diving professional relationships (pending, active, rejected - exclude canceled)
        // Filter by license committee DIVINGSERVICES (not professional_role committee)
        $professionalRoles = EntityProfessionalRole::where('individual_id', $individual->id)
            ->where('status_class', '!=', CanceledEntityProfessionalRoleState::class)
            ->whereHas('professionalRole', function ($query) {
                $query->where('role', 'DIVINGPROFESSIONAL')
                    ->whereHas('licenses', function ($q) {
                        $q->whereHas('committee', function ($q2) {
                            $q2->where('code', 'DIVINGSERVICES');
                        });
                    });
            })
            ->with(['entity.district', 'entity.media', 'professionalRole'])
            ->orderByRaw('FIELD(status_class, ?, ?, ?)', [
                PendingEntityProfessionalRoleState::class,
                ActiveEntityProfessionalRoleState::class,
                RejectedEntityProfessionalRoleState::class,
            ])
            ->orderByDesc('created_at')
            ->paginate(10);

        // Separate by status for easier display
        $pendingInvitations = $professionalRoles->filter(function ($role) {
            return $role->status_class === PendingEntityProfessionalRoleState::class;
        });

        $activeRelationships = $professionalRoles->filter(function ($role) {
            return $role->status_class === ActiveEntityProfessionalRoleState::class;
        });

        $rejectedInvitations = $professionalRoles->filter(function ($role) {
            return $role->status_class === RejectedEntityProfessionalRoleState::class;
        });

        return view('web.individual.diving_professionals.index', compact(
            'professionalRoles',
            'pendingInvitations',
            'activeRelationships',
            'rejectedInvitations'
        ));
    }

    /**
     * Accept a diving professional invitation
     */
    public function accept(EntityProfessionalRole $professionalRole): RedirectResponse
    {
        // Verify this invitation belongs to the authenticated individual
        if ($professionalRole->individual_id !== Auth::user()->individual->id) {
            abort(403, 'Unauthorized');
        }

        // Check if invitation is pending
        if ($professionalRole->status_class !== PendingEntityProfessionalRoleState::class) {
            return redirect()->route('individual.diving_professionals.index')
                ->with('error', __('diving.invitation_not_pending'));
        }

        try {
            DB::beginTransaction();

            // Use the accept method which creates IndividualEntity automatically
            $success = $professionalRole->accept();

            if (! $success) {
                throw new \Exception('Failed to accept invitation');
            }

            // Log the activity
            activity('diving_professional')
                ->performedOn($professionalRole)
                ->causedBy(Auth::user())
                ->withProperties([
                    'entity_id' => $professionalRole->entity_id,
                    'individual_id' => $professionalRole->individual_id,
                    'role' => 'DIVINGPROFESSIONAL',
                ])
                ->log('Diving professional invitation accepted');

            DB::commit();

            return redirect()->route('individual.diving_professionals.index')
                ->with('success', __('diving.invitation_accepted_successfully'));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to accept diving professional invitation: ' . $e->getMessage());

            return redirect()->route('individual.diving_professionals.index')
                ->with('error', __('diving.failed_to_accept_invitation'));
        }
    }

    /**
     * Reject a diving professional invitation
     */
    public function reject(Request $request, EntityProfessionalRole $professionalRole): RedirectResponse
    {
        // Verify this invitation belongs to the authenticated individual
        if ($professionalRole->individual_id !== Auth::user()->individual->id) {
            abort(403, 'Unauthorized');
        }

        // Check if invitation is pending
        if ($professionalRole->status_class !== PendingEntityProfessionalRoleState::class) {
            return redirect()->route('individual.diving_professionals.index')
                ->with('error', __('diving.invitation_not_pending'));
        }

        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $reason = $validated['reason'] ?? 'Invitation rejected by individual';
            $success = $professionalRole->reject($reason);

            if (! $success) {
                throw new \Exception('Failed to reject invitation');
            }

            // Log the activity
            activity('diving_professional')
                ->performedOn($professionalRole)
                ->causedBy(Auth::user())
                ->withProperties([
                    'entity_id' => $professionalRole->entity_id,
                    'individual_id' => $professionalRole->individual_id,
                    'reason' => $reason,
                ])
                ->log('Diving professional invitation rejected');

            return redirect()->route('individual.diving_professionals.index')
                ->with('success', __('diving.invitation_rejected_successfully'));

        } catch (\Exception $e) {
            Log::error('Failed to reject diving professional invitation: ' . $e->getMessage());

            return redirect()->route('individual.diving_professionals.index')
                ->with('error', __('diving.failed_to_reject_invitation'));
        }
    }

    /**
     * End/deactivate an active diving professional relationship
     */
    public function destroy(Request $request, EntityProfessionalRole $professionalRole): RedirectResponse
    {
        // Verify this relationship belongs to the authenticated individual
        if ($professionalRole->individual_id !== Auth::user()->individual->id) {
            abort(403, 'Unauthorized');
        }

        // Check if relationship is active
        if ($professionalRole->status_class !== ActiveEntityProfessionalRoleState::class) {
            return redirect()->route('individual.diving_professionals.index')
                ->with('error', __('diving.relationship_not_active'));
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $success = $professionalRole->deactivate($validated['reason'], 'individual');

            if (! $success) {
                throw new \Exception('Failed to deactivate professional relationship');
            }

            // Log the activity
            activity('diving_professional')
                ->performedOn($professionalRole)
                ->causedBy(Auth::user())
                ->withProperties([
                    'entity_id' => $professionalRole->entity_id,
                    'individual_id' => $professionalRole->individual_id,
                    'reason' => $validated['reason'],
                ])
                ->log('Diving professional relationship deactivated by individual');

            return redirect()->route('individual.diving_professionals.index')
                ->with('success', __('diving.professional_relationship_ended'));

        } catch (\Exception $e) {
            Log::error('Failed to deactivate diving professional relationship: ' . $e->getMessage());

            return redirect()->route('individual.diving_professionals.index')
                ->with('error', __('diving.failed_to_end_relationship'));
        }
    }
}
