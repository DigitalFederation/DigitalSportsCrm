<?php

namespace App\Http\Controllers\Individual;

use App\Http\Controllers\Controller;
use App\Models\Committee;
use App\Models\User;
use Domain\Entities\Models\Entity;
use Domain\Entities\Models\EntityAthlete;
use Domain\Entities\Models\EntityProfessionalRole;
use Domain\Entities\Models\EntityProfessionalRoleInvitation;
use Domain\Entities\States\ActiveEntityProfessionalRoleState;
use Domain\Entities\States\CanceledEntityProfessionalRoleState;
use Domain\Entities\States\PendingEntityProfessionalRoleState;
use Domain\Entities\States\RejectedEntityProfessionalRoleState;
use Domain\Individuals\Actions\SyncIndividualLocalFederationsAction;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Licenses\Scopes\ExcludeInternationalScope;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class InstructorController extends Controller
{
    public function index(string $committee, ?string $code = null): View
    {
        if ($committee != 'diving' && $committee != 'scientific') {
            abort(404);
        }

        $userId = Auth::id();
        if (! $userId || ! Auth::user()->individual) {
            return view('web.individual.instructor.index', [
                'invites' => collect(),
                'pendingGenericInvites' => collect(),
                'committee' => $committee,
                'code' => $code,
                'professionalName' => null,
            ])->with('error', 'User not associated with an individual profile.');
        }
        $individualId = Auth::user()->individual->id;

        $invites = EntityProfessionalRole::where('individual_id', $individualId)
            ->with('entity.country')
            ->whereHas('professionalRole', function ($query) use ($committee, $code) {
                $query->select('id', 'committee_id', 'code')
                    ->whereHas('committee', function ($query) use ($committee) {
                        $query->select('id', 'code')->where('code', 'like', $committee);
                    })->when(! empty($code), function (Builder $query) use ($code) {
                        $query->where('code', 'like', strtoupper($code));
                    });
            })
            ->orderByRaw('FIELD(status_class, ?, ?, ?, ?)', [
                PendingEntityProfessionalRoleState::class,
                ActiveEntityProfessionalRoleState::class,
                CanceledEntityProfessionalRoleState::class,
                RejectedEntityProfessionalRoleState::class,
            ])
            ->orderByDesc('created_at')
            ->paginate();

        $pendingGenericInvites = EntityProfessionalRoleInvitation::where('invited_user_id', $userId)
            ->where('committee_code', strtoupper($committee))
            ->where('status', 'pending')
            ->with('entity:id,name')
            ->orderByDesc('created_at')
            ->get();

        $pendingGenericInvites->each(function ($invitation) use ($userId) {
            $params = [
                'entityId' => $invitation->inviting_entity_id,
                'userId' => $userId,
                'committeeCode' => $invitation->committee_code,
            ];
            $expiry = now()->addDays(7);

            $invitation->accept_url = URL::temporarySignedRoute('instructor-invitations.accept', $expiry, $params);
            $invitation->reject_url = URL::temporarySignedRoute('instructor-invitations.reject', $expiry, $params);
        });

        $professionalName = null;
        if (! empty($code)) {
            $professionalName = ProfessionalRole::select('id', 'name', 'code')->where('code', 'like', strtoupper($code))->value('name');
        }

        return view('web.individual.instructor.index', compact('invites', 'pendingGenericInvites', 'committee', 'code', 'professionalName'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        try {
            $invite = EntityProfessionalRole::findOrFail($id);

            if ($invite->individual_id !== Auth::user()?->individual?->id) {
                return redirect()->back()->with('error', 'Unauthorized action.');
            }

            $allowedStatuses = [
                ActiveEntityProfessionalRoleState::class,
                RejectedEntityProfessionalRoleState::class,
            ];
            if (! in_array($request->status_class, $allowedStatuses)) {
                return redirect()->back()->with('error', 'Invalid status provided.');
            }

            // Use the new accept/reject methods that handle IndividualEntity creation
            if ($request->status_class === ActiveEntityProfessionalRoleState::class) {
                $success = $invite->accept();
                $action = 'accepted';
            } else {
                $success = $invite->reject('Rejected by individual');
                $action = 'rejected';
            }

            if (! $success) {
                return redirect()->back()->with('error', 'Cannot update invitation in its current state.');
            }

            activity()
                ->performedOn($invite)
                ->event($action)
                ->log('Instructor/Leader invitation ' . $action);

            return redirect()->back()->with('success', 'Invitation status updated successfully.');
        } catch (Exception $e) {
            Log::error('Error updating invitation status: ' . $e->getMessage(), ['invite_id' => $id]);

            return redirect()->back()->with('error', 'An error occurred while updating the invitation status.');
        }
    }

    /**
     * Handles acceptance of a generic invitation link.
     */
    public function acceptInvitation(Request $request, int|string $entityId, int|string $userId, string $committeeCode): RedirectResponse
    {
        $entity = Entity::find($entityId);
        $user = User::find($userId);

        if (! $entity || ! $user) {
            Log::warning('Entity or User not found for invitation acceptance.', ['entity_id' => $entityId, 'user_id' => $userId]);

            return redirect()->back()->with('error', 'Invalid invitation link [Data].');
        }
        if (Auth::id() !== $user->id) {
            Log::warning('Generic invitation acceptance attempt mismatch.', ['auth_user' => Auth::id(), 'url_user' => $user->id]);

            return redirect()->back()->with('error', 'Invalid invitation link.');
        }
        $individual = $user->individual;
        if (! $individual) {
            Log::error('User has no associated individual profile for invitation acceptance.', ['user_id' => $user->id]);

            return redirect()->back()->with('error', 'Your user profile is not linked to an individual profile.');
        }

        DB::beginTransaction();
        try {
            // Optimization: Check if already accepted before locking anything
            $alreadyAccepted = EntityProfessionalRoleInvitation::where('inviting_entity_id', $entityId)
                ->where('invited_user_id', $userId)
                ->where('committee_code', $committeeCode)
                ->where('status', 'accepted')
                ->exists();

            if ($alreadyAccepted) {
                DB::rollBack(); // No transaction needed
                Log::info('Attempted to accept an already accepted generic invitation (pre-lock check).', ['entity' => $entityId, 'user' => $userId, 'committee' => $committeeCode]);

                return redirect()->back()->with('error', 'This invitation has already been accepted.');
            }

            // Find and lock the corresponding PENDING invitation record within the transaction
            $invitation = EntityProfessionalRoleInvitation::where('inviting_entity_id', $entityId)
                ->where('invited_user_id', $userId)
                ->where('committee_code', $committeeCode)
                ->where('status', 'pending')
                ->lockForUpdate() // Add pessimistic lock
                ->first();

            // Re-check if invitation exists (is pending) after locking
            if (! $invitation) {
                DB::rollBack();
                // Check again if it became accepted *while waiting for lock* or just before lock acquired
                $nowAccepted = EntityProfessionalRoleInvitation::where('inviting_entity_id', $entityId)
                    ->where('invited_user_id', $userId)
                    ->where('committee_code', $committeeCode)
                    ->where('status', 'accepted')
                    ->exists();

                if ($nowAccepted) {
                    Log::info('Generic invitation was accepted by another process while waiting for lock or just before lock.', ['entity' => $entityId, 'user' => $userId, 'committee' => $committeeCode]);

                    // Considered success or already done, inform user
                    return redirect()->back()->with('info', 'This invitation was just accepted by another process or has already been processed.');
                } else {
                    Log::warning('Pending generic invitation not found or status changed after acquiring lock (and not to accepted).', ['entity' => $entityId, 'user' => $userId, 'committee' => $committeeCode]);

                    return redirect()->back()->with('error', 'This invitation is no longer valid or has just been processed.');
                }
            }

            // Mark the invitation as accepted and update status_class
            $invitation->update([
                'status' => 'accepted',
                'status_class' => ActiveEntityProfessionalRoleState::class,
            ]);

            // Handle athlete invitations separately - they use EntityAthlete, not EntityProfessionalRole
            if ($committeeCode === 'athlete') {
                // Find and update the pending EntityAthlete record
                $entityAthlete = EntityAthlete::where('entity_id', $entity->id)
                    ->where('individual_id', $individual->id)
                    ->where('status_class', PendingEntityProfessionalRoleState::class)
                    ->first();

                if ($entityAthlete) {
                    $entityAthlete->update([
                        'status_class' => ActiveEntityProfessionalRoleState::class,
                    ]);

                    // Sync individual to entity's local federations
                    $syncAction = new SyncIndividualLocalFederationsAction;
                    $syncAction->execute($individual, $entity);

                    // Delete the invitation record (it has served its purpose)
                    $invitation->delete();

                    DB::commit();

                    activity()
                        ->performedOn($entityAthlete)
                        ->causedBy($user)
                        ->withProperties([
                            'entity_id' => $entity->id,
                            'individual_id' => $individual->id,
                            'sport_id' => $entityAthlete->sport_id,
                        ])
                        ->log('Accepted athlete invitation from entity ' . $entity->name . ' via email link');

                    return redirect()->route('individual.athlete.index', ['filter[status]' => 'active'])
                        ->with('success', __('Invitation accepted successfully! You are now associated with :entityName as an athlete.', ['entityName' => $entity->name]));
                } else {
                    DB::rollBack();
                    Log::warning('Pending EntityAthlete record not found for athlete invitation acceptance.', [
                        'entity_id' => $entity->id,
                        'individual_id' => $individual->id,
                    ]);

                    return redirect()->back()->with('error', 'Could not find the pending athlete association. Please contact support.');
                }
            }

            // Handle coach/instructor invitations
            // Always determine roles based on the individual's active licenses
            // The invitation's professional_role_id is for display/tracking purposes only
            $committee = Committee::where('code', $committeeCode)->firstOrFail();
            $relevantRoleIds = ProfessionalRole::where('committee_id', $committee->id)
                ->whereIn('role', ['INSTRUCTOR', 'LEADER'])
                ->pluck('id')
                ->toArray();

            $qualifiedRoleIds = [];
            if (! empty($relevantRoleIds)) {
                // Include international licenses (international diving instructors)
                $qualifiedRoleIds = $individual->licenses()
                    ->withoutGlobalScope(ExcludeInternationalScope::class)
                    ->where('status_class', ActiveLicenseAttributedState::class)
                    ->whereHas('license', function (Builder $query) use ($relevantRoleIds) {
                        $query->whereIn('professional_role_id', $relevantRoleIds);
                    })
                    ->with('license:id,professional_role_id')
                    ->get()
                    ->pluck('license.professional_role_id')
                    ->filter()
                    ->unique()
                    ->toArray();
            }

            $createdOrUpdatedCount = 0;
            if (! empty($qualifiedRoleIds)) {
                $professionalRoles = ProfessionalRole::whereIn('id', $qualifiedRoleIds)->pluck('name', 'id');
                foreach ($qualifiedRoleIds as $roleId) {
                    // First, try to find and update existing PENDING records
                    $pendingRole = EntityProfessionalRole::where('entity_id', $entity->id)
                        ->where('individual_id', $individual->id)
                        ->where('professional_role_id', $roleId)
                        ->where('status_class', PendingEntityProfessionalRoleState::class)
                        ->first();

                    if ($pendingRole) {
                        // Update the pending record to active
                        $pendingRole->update([
                            'status_class' => ActiveEntityProfessionalRoleState::class,
                            'entity_name' => $entity->name,
                            'individual_name' => $individual->name,
                            'role_name' => $professionalRoles->get($roleId, 'Unknown Role'),
                        ]);
                    } else {
                        // No pending record found - check if already active
                        $activeRole = EntityProfessionalRole::where('entity_id', $entity->id)
                            ->where('individual_id', $individual->id)
                            ->where('professional_role_id', $roleId)
                            ->where('status_class', ActiveEntityProfessionalRoleState::class)
                            ->exists();

                        if (! $activeRole) {
                            // Create a new active record (for cases where invitation exists but no EntityProfessionalRole)
                            EntityProfessionalRole::create([
                                'entity_id' => $entity->id,
                                'individual_id' => $individual->id,
                                'professional_role_id' => $roleId,
                                'status_class' => ActiveEntityProfessionalRoleState::class,
                                'entity_name' => $entity->name,
                                'individual_name' => $individual->name,
                                'role_name' => $professionalRoles->get($roleId, 'Unknown Role'),
                            ]);
                        }
                    }
                    $createdOrUpdatedCount++;
                }
            }

            DB::commit();

            if ($createdOrUpdatedCount > 0) {
                activity()
                    ->performedOn($individual)
                    ->causedBy($user)
                    ->withProperties(['entity_id' => $entity->id, 'committee_code' => $committeeCode, 'roles_activated' => $qualifiedRoleIds])
                    ->log('Accepted generic invitation from entity ' . $entity->name . ' for committee ' . $committeeCode . '. Activated ' . $createdOrUpdatedCount . ' role(s).');

                return redirect()->back()->with('success', __('Invitation accepted successfully! You are now associated with :entityName for :count role(s) based on your active licenses.', ['entityName' => $entity->name, 'count' => $createdOrUpdatedCount]));
            } else {
                activity()
                    ->performedOn($individual)
                    ->causedBy($user)
                    ->withProperties(['entity_id' => $entity->id, 'committee_code' => $committeeCode, 'relevant_roles' => $relevantRoleIds])
                    ->log('Accepted generic invitation from entity ' . $entity->name . ' for committee ' . $committeeCode . ', but no active qualifying licenses found.');

                return redirect()->back()->with('warning', __('Invitation accepted, but no currently active licenses were found for the relevant Instructor/Leader roles in the :committee committee. Please check your licenses.', ['committee' => $committeeCode]));
            }
        } catch (UniqueConstraintViolationException $e) {
            DB::rollBack();
            // Log specifically that this happened despite checks and locks
            Log::critical('Unique constraint violation on invitation status update despite locks and checks. Possible data inconsistency or unexpected condition.', [
                'entity_id' => $entityId,
                'user_id' => $userId,
                'committee_code' => $committeeCode,
                'exception_message' => $e->getMessage(),
                'exception_trace' => $e->getTraceAsString(), // Optional: for detailed debugging
            ]);

            return redirect()->back()->with('error', 'An unexpected conflict occurred while accepting the invitation. It might have already been accepted. Please verify the status.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error accepting generic instructor invitation: ' . $e->getMessage(), [
                'entity_id' => $entityId,
                'user_id' => $userId,
                'committee_code' => $committeeCode,
                'exception' => $e,
            ]);

            return redirect()->back()->with('error', 'An unexpected error occurred while accepting the invitation. Please try again later.');
        }
    }

    /**
     * Handles rejection of a generic invitation link.
     */
    public function rejectInvitation(Request $request, int|string $entityId, int|string $userId, string $committeeCode): RedirectResponse
    {
        $user = User::find($userId);
        if (! $user || Auth::id() !== $user->id) {
            Log::warning('Generic invitation rejection attempt mismatch.', ['auth_user' => Auth::id(), 'url_user' => $userId]);

            return redirect()->back()->with('error', 'Invalid invitation link.');
        }

        // Find the corresponding pending invitation record
        $invitation = EntityProfessionalRoleInvitation::where('inviting_entity_id', $entityId)
            ->where('invited_user_id', $userId)
            ->where('committee_code', $committeeCode)
            ->where('status', 'pending')
            ->first();

        if (! $invitation) {
            Log::warning('Pending generic invitation not found or already processed/expired on rejection attempt.', ['entity' => $entityId, 'user' => $userId, 'committee' => $committeeCode]);

            return redirect()->back()->with('info', 'This invitation is no longer valid or cannot be found.');
        }

        // Mark the invitation as rejected and update status_class
        $invitation->update([
            'status' => 'rejected',
            'status_class' => RejectedEntityProfessionalRoleState::class,
        ]);

        $entity = $invitation->entity;
        $entityName = $entity?->name ?? 'Unknown Entity (' . $entityId . ')';
        $individual = $user->individual;

        // Handle athlete invitation rejection - update EntityAthlete status
        if ($committeeCode === 'athlete' && $individual) {
            $entityAthlete = EntityAthlete::where('entity_id', $entityId)
                ->where('individual_id', $individual->id)
                ->where('status_class', PendingEntityProfessionalRoleState::class)
                ->first();

            if ($entityAthlete) {
                $entityAthlete->update([
                    'status_class' => RejectedEntityProfessionalRoleState::class,
                ]);

                // Delete the invitation record to allow future re-invitations
                $invitation->delete();

                activity()
                    ->performedOn($entityAthlete)
                    ->causedBy($user)
                    ->withProperties([
                        'entity_id' => $entityId,
                        'individual_id' => $individual->id,
                        'sport_id' => $entityAthlete->sport_id,
                    ])
                    ->log('Rejected athlete invitation from entity ' . $entityName . ' via email link');

                return redirect()->route('individual.athlete.index')
                    ->with('info', __('You have rejected the invitation from :entityName.', ['entityName' => $entityName]));
            }
        }

        // Log the rejection (for coach/instructor invitations)
        Log::info('Generic instructor invitation rejected.', [
            'entity_id' => $entityId,
            'user_id' => $userId,
            'committee_code' => $committeeCode,
            'invitation_id' => $invitation->id,
        ]);

        activity()
            ->performedOn($individual ?? $user)
            ->causedBy($user)
            ->withProperties(['entity_id' => $entityId, 'committee_code' => $committeeCode, 'invitation_id' => $invitation->id])
            ->log('Rejected generic invitation from entity ' . $entityName . ' for committee ' . $committeeCode);

        // Provide feedback
        return redirect()->back()->with('info', __('You have rejected the invitation from :entityName.', ['entityName' => $entityName]));
    }

    public function destroy(Request $request, int $entityProfessionalRoleId): RedirectResponse
    {
        try {
            $entityProfessionalRole = EntityProfessionalRole::findOrFail($entityProfessionalRoleId);

            if ($entityProfessionalRole->individual_id !== Auth::user()?->individual?->id) {
                return redirect()->back()->with('error', 'Unauthorized action.');
            }

            $validated = $request->validate([
                'reason' => 'nullable|string|max:500',
                'action' => 'required|in:deactivate,delete',
            ]);

            $entityName = $entityProfessionalRole->entity->name;
            $individualCode = $entityProfessionalRole->individual?->member_code ?? 'N/A';

            // Store necessary details before processing
            $entityId = $entityProfessionalRole->entity_id;
            $individualId = $entityProfessionalRole->individual_id;
            $userId = $entityProfessionalRole->individual?->user_id;
            $committeeCode = $entityProfessionalRole->professionalRole?->committee?->code;

            if ($validated['action'] === 'deactivate') {
                // Deactivate the relationship instead of deleting it
                $reason = $validated['reason'] ?? 'Individual chose to end the professional relationship';
                $entityProfessionalRole->deactivate($reason, 'individual');

                activity()
                    ->performedOn($entityProfessionalRole)
                    ->causedBy(Auth::user())
                    ->withProperties([
                        'entity_id' => $entityId,
                        'individual_id' => $individualId,
                        'professional_role_id' => $entityProfessionalRole->professional_role_id,
                        'member_code' => $individualCode,
                        'reason' => $reason,
                    ])
                    ->log("Instructor/Leader relationship ({$entityProfessionalRole->role_name}) with entity {$entityName} deactivated by individual {$individualCode}");

                return redirect()->back()->with('success', 'Professional relationship deactivated successfully.');
            } else {
                // Permanently delete the relationship
                activity()
                    ->performedOn($entityProfessionalRole)
                    ->causedBy(Auth::user())
                    ->withProperties([
                        'entity_id' => $entityId,
                        'individual_id' => $individualId,
                        'professional_role_id' => $entityProfessionalRole->professional_role_id,
                        'member_code' => $individualCode,
                    ])
                    ->log("Instructor/Leader relationship ({$entityProfessionalRole->role_name}) with entity {$entityName} deleted by individual {$individualCode}");

                $entityProfessionalRole->delete();

                // Clean up the corresponding generic invitation if it exists
                if ($userId && $committeeCode) {
                    EntityProfessionalRoleInvitation::where('inviting_entity_id', $entityId)
                        ->where('invited_user_id', $userId)
                        ->where('committee_code', $committeeCode)
                        ->delete();
                    Log::info('Cleaned up related EntityProfessionalRoleInvitation after role deletion by individual.', [
                        'entity_id' => $entityId,
                        'user_id' => $userId,
                        'committee_code' => $committeeCode,
                    ]);
                }

                return redirect()->back()->with('success', 'Instructor relationship removed successfully.');
            }
        } catch (Exception $e) {
            Log::error('Error removing/deactivating instructor relationship by individual: ' . $e->getMessage(), ['id' => $entityProfessionalRoleId]);

            return redirect()->back()->with('error', 'There was an issue processing the instructor relationship.');
        }
    }
}
