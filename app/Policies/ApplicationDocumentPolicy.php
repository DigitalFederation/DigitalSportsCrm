<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Domain\EventApplications\Models\ApplicationDocument;
use Domain\EventApplications\Models\EventApplication;
use Domain\EventApplications\States\DraftApplicationState;
use Domain\EventApplications\States\ReturnedForCorrectionApplicationState;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Application Document Policy
 *
 * Controls access to application documents based on:
 * - User role (admin vs entity)
 * - Document ownership (entity owns the application)
 * - Application state (only editable in Draft or Returned states)
 *
 * Business Rules:
 * - Admins can view/manage all documents
 * - Entities can only manage documents for their own applications
 * - Entities can only create/update/delete documents when application is in mutable state
 * - Viewing is allowed in any state (for own applications)
 */
class ApplicationDocumentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the document.
     */
    public function view(User $user, ApplicationDocument $document): bool
    {
        // Admins can view all documents
        if ($this->isAdmin($user)) {
            return true;
        }

        // Entities can view their own application documents
        if ($document->application) {
            return $this->ownsApplication($user, $document->application);
        }

        // Template documents are viewable by all authenticated users
        if ($document->template_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create documents.
     */
    public function create(User $user, EventApplication $application): bool
    {
        // Admins can always create documents
        if ($this->isAdmin($user)) {
            return true;
        }

        // Entities can create documents for their own applications
        // only when application is in a mutable state
        return $this->ownsApplication($user, $application) &&
               $this->isApplicationMutable($application);
    }

    /**
     * Determine whether the user can update the document.
     */
    public function update(User $user, ApplicationDocument $document): bool
    {
        // Admins can always update documents
        if ($this->isAdmin($user)) {
            return true;
        }

        // Entities can update their own documents only in mutable states
        if ($document->application) {
            return $this->ownsApplication($user, $document->application) &&
                   $this->isApplicationMutable($document->application);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the document.
     */
    public function delete(User $user, ApplicationDocument $document): bool
    {
        // Admins can always delete documents
        if ($this->isAdmin($user)) {
            return true;
        }

        // Entities can delete their own application documents
        if ($document->application) {
            return $this->ownsApplication($user, $document->application);
        }

        return false;
    }

    /**
     * Determine whether the user can download the document.
     */
    public function download(User $user, ApplicationDocument $document): bool
    {
        // Same logic as view
        return $this->view($user, $document);
    }

    /**
     * Check if user has admin permissions for event applications.
     */
    private function isAdmin(User $user): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->can('manage application templates') ||
               $user->can('review event applications');
    }

    /**
     * Check if user owns the application (via their entity).
     */
    private function ownsApplication(User $user, EventApplication $application): bool
    {
        // Check if user has entity relationship
        if (! $user->entities || $user->entities->isEmpty()) {
            return false;
        }

        // Check if any of the user's entities owns this application
        return $user->entities->contains(function ($entity) use ($application) {
            return $application->entity_id === $entity->id &&
                   $application->entity_type === get_class($entity);
        });
    }

    /**
     * Check if application is in a mutable state (Draft or Returned for Correction).
     */
    private function isApplicationMutable(EventApplication $application): bool
    {
        // Get the current state class
        $stateClass = $application->status_class;

        // Check if state is Draft or ReturnedForCorrection
        return $stateClass === DraftApplicationState::class ||
               $stateClass === ReturnedForCorrectionApplicationState::class;
    }
}
