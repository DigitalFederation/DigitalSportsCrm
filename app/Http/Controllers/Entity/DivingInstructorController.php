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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DivingInstructorController extends Controller
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

        // Fetch Professional Roles for Livewire component
        $professionalRoles = ProfessionalRole::select('id', 'name')
            ->whereIn('role', ['INSTRUCTOR', 'LEADER'])
            ->whereHas('committee', function (Builder $query) {
                return $query->where('code', 'DIVING');
            })
            ->get();

        // Fetch Pending Invitations sent by this entity for DIVING committee
        $pendingInvitations = EntityProfessionalRoleInvitation::where('inviting_entity_id', $entity->id)
            ->where('committee_code', 'DIVING')
            ->where('status', 'pending')
            ->with('individual:individual.id,individual.name,individual.surname,individual.member_code')
            ->orderByDesc('created_at')
            ->get();

        // Pass both to the view
        return view('web.entity.diving_instructor.index', compact('professionalRoles', 'pendingInvitations'));
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
                ->log("Instructor relationship with CMAS code {$entityProfessionalRole->individual->member_code} removed");

            return redirect()->back()->with('success', 'Instructor relationship removed successfully.');
        } catch (Exception $exception) {
            // Log the exception and return with an error message
            Log::error($exception->getMessage());

            return redirect()->back()->with('error', 'There was an issue removing the instructor relationship.');
        }
    }
}
