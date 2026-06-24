<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Notifications\GenericCoachInvitationNotification;
use Carbon\Carbon;
use Domain\Entities\Models\EntityAthlete;
use Domain\Entities\Models\EntityProfessionalRoleInvitation;
use Domain\Entities\States\PendingEntityProfessionalRoleState;
use Domain\Individuals\Models\ProfessionalRole;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Component;

class PendingAthleteInvitations extends Component
{
    /**
     * Get the pending invitations for the current entity.
     */
    #[Computed]
    public function pendingInvitations(): Collection
    {
        $entity = Auth::user()?->entities()->first();

        if (! $entity) {
            return collect();
        }

        $athleteRole = ProfessionalRole::where('role', 'ATHLETE')->first();

        if (! $athleteRole) {
            return collect();
        }

        $invitations = EntityProfessionalRoleInvitation::where('entity_id', $entity->id)
            ->where('professional_role_id', $athleteRole->id)
            ->where('status_class', PendingEntityProfessionalRoleState::class)
            ->with(['individual:id,name,surname,member_code', 'sport:id,name'])
            ->orderByDesc('created_at')
            ->get();

        // For invitations without sport_id, try to get sport from EntityAthlete
        $invitations->each(function ($invitation) use ($entity) {
            if (! $invitation->sport_id && $invitation->individual_id) {
                $entityAthlete = EntityAthlete::where('entity_id', $entity->id)
                    ->where('individual_id', $invitation->individual_id)
                    ->where('status_class', PendingEntityProfessionalRoleState::class)
                    ->with('sport:id,name')
                    ->first();

                if ($entityAthlete) {
                    $invitation->setRelation('sport', $entityAthlete->sport);
                }
            }
        });

        // Dispatch event to update the count in the UI
        $this->dispatch('pending-invitations-updated', count: $invitations->count());

        return $invitations;
    }

    /**
     * Get the count of pending invitations.
     */
    #[Computed]
    public function pendingCount(): int
    {
        return $this->pendingInvitations->count();
    }

    /**
     * Resend an invitation notification to the athlete.
     */
    public function resendInvitation(int $invitationId): void
    {
        $entity = Auth::user()?->entities()->first();

        if (! $entity) {
            session()->flash('error', __('athletes.resend_failed'));

            return;
        }

        $invitation = EntityProfessionalRoleInvitation::where('id', $invitationId)
            ->where('entity_id', $entity->id)
            ->where('status_class', PendingEntityProfessionalRoleState::class)
            ->with(['individual.user', 'entity'])
            ->first();

        if (! $invitation) {
            session()->flash('error', __('athletes.invitation_not_found'));

            return;
        }

        $individual = $invitation->individual;

        if (! $individual?->user) {
            session()->flash('error', __('athletes.athlete_has_no_user'));

            return;
        }

        // Find the sport from the pending EntityAthlete record
        $entityAthlete = EntityAthlete::where('entity_id', $entity->id)
            ->where('individual_id', $individual->id)
            ->where('status_class', PendingEntityProfessionalRoleState::class)
            ->with('sport')
            ->first();

        $sportName = $entityAthlete?->sport?->name;

        try {
            DB::beginTransaction();

            // Update the invitation expiry date
            $invitation->update([
                'expires_at' => Carbon::now()->addDays(7),
            ]);

            // Resend the notification
            $individual->user->notify(new GenericCoachInvitationNotification(
                $invitation->entity,
                'athlete',
                $sportName
            ));

            DB::commit();

            session()->flash('success', __('athletes.invitation_resent'));

            // Log to activity log for audit trail
            $individualFullName = $individual->name . ' ' . $individual->surname;
            activity('athlete-invitation')
                ->performedOn($invitation)
                ->causedBy(Auth::user())
                ->withProperties([
                    'invitation_id' => $invitationId,
                    'entity_id' => $entity->id,
                    'entity_name' => $entity->name,
                    'individual_id' => $individual->id,
                    'individual_name' => $individualFullName,
                    'sport_name' => $sportName,
                    'new_expires_at' => $invitation->expires_at,
                    'action' => 'resent',
                ])
                ->log(__('athletes.activity_resent_invitation', ['entity' => $entity->name, 'individual' => $individualFullName]));

            Log::info('Athlete invitation resent', [
                'invitation_id' => $invitationId,
                'entity_id' => $entity->id,
                'individual_id' => $individual->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to resend athlete invitation: ' . $e->getMessage());
            session()->flash('error', __('athletes.resend_failed'));
        }
    }

    /**
     * Cancel a pending invitation - deletes both the invitation and EntityAthlete records
     * so the athlete can be re-invited in the future.
     */
    public function cancelInvitation(int $invitationId): void
    {
        $entity = Auth::user()?->entities()->first();

        if (! $entity) {
            session()->flash('error', __('athletes.cancel_failed'));

            return;
        }

        $invitation = EntityProfessionalRoleInvitation::where('id', $invitationId)
            ->where('entity_id', $entity->id)
            ->where('status_class', PendingEntityProfessionalRoleState::class)
            ->first();

        if (! $invitation) {
            session()->flash('error', __('athletes.invitation_not_found'));

            return;
        }

        try {
            DB::beginTransaction();

            // Store data for logging before deleting
            $individualId = $invitation->individual_id;
            $entityName = $entity->name;

            // Delete the pending EntityAthlete record if it exists (so athlete can be re-invited)
            if ($individualId) {
                EntityAthlete::where('entity_id', $entity->id)
                    ->where('individual_id', $individualId)
                    ->where('status_class', PendingEntityProfessionalRoleState::class)
                    ->delete();
            }

            // Delete the invitation record (so athlete can be re-invited)
            $invitation->delete();

            DB::commit();

            session()->flash('success', __('athletes.invitation_canceled'));

            // Log to activity log for audit trail
            activity('athlete-invitation')
                ->causedBy(Auth::user())
                ->withProperties([
                    'invitation_id' => $invitationId,
                    'entity_id' => $entity->id,
                    'entity_name' => $entityName,
                    'individual_id' => $individualId,
                    'action' => 'canceled_and_deleted',
                ])
                ->log(__('athletes.activity_canceled_invitation', ['entity' => $entityName]));

            Log::info('Athlete invitation canceled and deleted', [
                'invitation_id' => $invitationId,
                'entity_id' => $entity->id,
                'individual_id' => $individualId,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to cancel athlete invitation: ' . $e->getMessage());
            session()->flash('error', __('athletes.cancel_failed'));
        }
    }

    public function render()
    {
        return view('livewire.pending-athlete-invitations');
    }
}
