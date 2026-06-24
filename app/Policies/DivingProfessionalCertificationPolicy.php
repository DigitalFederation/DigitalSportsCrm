<?php

namespace App\Policies;

use App\Models\User;
use Domain\Diving\Models\DivingProfessionalCertification;
use Illuminate\Auth\Access\HandlesAuthorization;

class DivingProfessionalCertificationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Only individuals can view their own certifications
        return $user->hasGroup('individual');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, DivingProfessionalCertification $certification): bool
    {
        // User can view their own certifications
        if ($user->hasGroup('individual') && $user->individual?->id === $certification->individual_id) {
            return true;
        }

        // Federation admins can view all certifications
        if ($user->hasGroup('federation') && $user->can('access diving certifications')) {
            return true;
        }

        // Admins can view all certifications
        if ($user->hasGroup('admin') && $user->can('access diving certifications')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only individuals can create their own certifications
        return $user->hasGroup('individual');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, DivingProfessionalCertification $certification): bool
    {
        // User can update their own certifications if pending
        if ($user->hasGroup('individual') &&
            $user->individual?->id === $certification->individual_id &&
            $certification->state instanceof \Domain\Diving\States\PendingValidationDivingCertificationState) {
            return true;
        }

        // Federation admins can update certifications (for validation)
        if ($user->hasGroup('federation') && $user->can('validate diving certifications')) {
            return true;
        }

        // Admins can update certifications
        if ($user->hasGroup('admin') && $user->can('validate diving certifications')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, DivingProfessionalCertification $certification): bool
    {
        // User can delete their own certifications if pending
        if ($user->hasGroup('individual') &&
            $user->individual?->id === $certification->individual_id &&
            $certification->state instanceof \Domain\Diving\States\PendingValidationDivingCertificationState) {
            return true;
        }

        // Admins can delete certifications
        if ($user->hasGroup('admin') && $user->can('delete diving certifications')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can validate the model.
     */
    public function validate(User $user, DivingProfessionalCertification $certification): bool
    {
        // Federation admins can validate certifications
        if ($user->hasGroup('federation') && $user->can('validate diving certifications')) {
            return true;
        }

        // Admins can validate certifications
        if ($user->hasGroup('admin') && $user->can('validate diving certifications')) {
            return true;
        }

        return false;
    }
}
