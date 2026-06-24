<?php

namespace App\Policies;

use App\Models\User;
use Domain\Diving\Models\DivingTechnicalDirectorInvitation;
use Illuminate\Auth\Access\HandlesAuthorization;

class DivingTechnicalDirectorInvitationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Individuals can view their own invitations
        if ($user->hasGroup('individual')) {
            return true;
        }

        // Entities can view invitations they sent
        if ($user->hasGroup('entity')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, DivingTechnicalDirectorInvitation $invitation): bool
    {
        // Individual can view invitations sent to them
        if ($user->hasGroup('individual') && $user->userable_id === $invitation->individual_id) {
            return true;
        }

        // Entity can view invitations they sent
        if ($user->hasGroup('entity') && $user->userable_id === $invitation->entity_id) {
            return true;
        }

        // Federation and Admins can view all invitations
        if (($user->hasGroup('federation') || $user->hasGroup('admin')) &&
            $user->can('access diving licenses')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only entities can create invitations
        return $user->hasGroup('entity');
    }

    /**
     * Determine whether the user can accept the invitation.
     */
    public function accept(User $user, DivingTechnicalDirectorInvitation $invitation): bool
    {
        // Only the invited individual can accept
        return $user->hasGroup('individual') &&
               $user->userable_id === $invitation->individual_id &&
               $invitation->canBeAccepted();
    }

    /**
     * Determine whether the user can reject the invitation.
     */
    public function reject(User $user, DivingTechnicalDirectorInvitation $invitation): bool
    {
        // Only the invited individual can reject
        return $user->hasGroup('individual') &&
               $user->userable_id === $invitation->individual_id &&
               $invitation->canBeRejected();
    }

    /**
     * Determine whether the user can cancel the invitation.
     */
    public function cancel(User $user, DivingTechnicalDirectorInvitation $invitation): bool
    {
        // Only the entity that sent it can cancel
        return $user->hasGroup('entity') &&
               $user->userable_id === $invitation->entity_id &&
               $invitation->canBeCanceled();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, DivingTechnicalDirectorInvitation $invitation): bool
    {
        // Same as cancel for now
        return $this->cancel($user, $invitation);
    }
}
