<?php

namespace App\Policies;

use App\Models\User;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\States\IndividualEntity\ActiveIndividualEntityState;
use Domain\Insurance\Models\Insurance;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Insurance access policy.
 *
 * This policy class enforces access control for insurance-related actions,
 * such as viewing and downloading insurance documents. It ensures that users
 * can only access their own insurance documents.
 */
class InsurancePolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the user can view the insurance document.
     *
     * @param  User  $user  The user attempting to view the insurance document.
     * @param  Insurance  $insurance  The insurance being requested for viewing.
     * @return bool Returns true if the user is authorized to view the insurance document, false otherwise.
     */
    public function viewDocument(User $user, Insurance $insurance): bool
    {
        // Admins can view any insurance document
        if ($user->hasPermissionTo('access memberships')) {
            return true;
        }

        // Federation users can view insurance documents for their federation's members
        if ($user->hasGroup('FEDERATION') && $insurance->member && method_exists($insurance->member, 'federations')) {
            $federationIds = $user->federations->pluck('id')->toArray();
            $memberFederationIds = $insurance->member->federations->pluck('id')->toArray();

            if (! empty(array_intersect($federationIds, $memberFederationIds))) {
                return true;
            }
        }

        // Entity users can view insurance documents for their entity's members
        if ($this->isEntityMember($user, $insurance)) {
            return true;
        }

        // User can view insurance document if they are the member
        return $this->isInsuranceMember($user, $insurance);
    }

    /**
     * Determine if the user can download the insurance document.
     *
     * @param  User  $user  The user attempting to download the insurance document.
     * @param  Insurance  $insurance  The insurance being requested for download.
     * @return bool Returns true if the user is authorized to download the insurance document, false otherwise.
     */
    public function downloadDocument(User $user, Insurance $insurance): bool
    {
        // Admins can download any insurance document
        if ($user->hasPermissionTo('access memberships')) {
            return true;
        }

        // Federation users can download insurance documents for their federation's members
        if ($user->hasGroup('FEDERATION') && $insurance->member && method_exists($insurance->member, 'federations')) {
            $federationIds = $user->federations->pluck('id')->toArray();
            $memberFederationIds = $insurance->member->federations->pluck('id')->toArray();

            if (! empty(array_intersect($federationIds, $memberFederationIds))) {
                return true;
            }
        }

        // Entity users can download insurance documents for their entity's members
        if ($this->isEntityMember($user, $insurance)) {
            return true;
        }

        // User can download insurance document if they are the member
        return $this->isInsuranceMember($user, $insurance);
    }

    /**
     * Check if the user is the member of the insurance.
     * Handles morph alias comparison (e.g., 'individual' vs full class name).
     */
    private function isInsuranceMember(User $user, Insurance $insurance): bool
    {
        if (! $user->individual) {
            return false;
        }

        if ($insurance->member_id !== $user->individual->id) {
            return false;
        }

        // Resolve the morph alias to the actual class name for comparison
        $memberClass = Relation::getMorphedModel($insurance->member_type) ?? $insurance->member_type;

        return $memberClass === Individual::class || $insurance->member_type === 'individual';
    }

    /**
     * Check if the entity user's entity has the insurance member as an active individual.
     */
    private function isEntityMember(User $user, Insurance $insurance): bool
    {
        if (! $user->isEntity()) {
            return false;
        }

        $entity = $user->entities()->first();
        if (! $entity) {
            return false;
        }

        if (! $insurance->member instanceof Individual) {
            return false;
        }

        return $insurance->member->entities()
            ->where('entity.id', $entity->id)
            ->where('status_class', ActiveIndividualEntityState::class)
            ->exists();
    }
}
