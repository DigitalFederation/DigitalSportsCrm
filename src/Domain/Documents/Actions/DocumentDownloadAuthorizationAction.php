<?php

namespace Domain\Documents\Actions;

use App\Models\User;
use Domain\Documents\Models\Document;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;

/**
 * Handles the authorization logic for document download requests.
 *
 * This action class is responsible for determining whether a user has the
 * necessary permissions to download a specific document based on their group
 * membership and the ownership of the document.
 *
 * @mixin \Domain\Documents\Actions\DocumentDownloadAuthorizationAction
 */
class DocumentDownloadAuthorizationAction
{
    /**
     * Execute the authorization check.
     *
     * @param  User  $user  The user attempting to download the document.
     * @param  Document  $document  The document being requested for download.
     * @return bool Returns true if the user is authorized to download the document, false otherwise.
     */
    public function execute(User $user, Document $document): bool
    {
        // admin users can download any document
        if ($user->isAdmin()) {
            return true;
        }

        // Federation users
        if ($user->isFederation()) {
            // Main federation has access to all documents, like platform administrators.
            $federation = $user->getFederation();
            if ($federation && $federation->isMainFederation()) {
                return true;
            }

            // Documents owned by the federation itself
            if ($this->isOwnerType($document, Federation::class)) {
                return $user->federations->contains($document->owner_id);
            }

            // Documents owned by entities that belong to the federation
            if ($this->isOwnerType($document, Entity::class)) {
                $federationId = $user->getFederationId();
                if ($federationId) {
                    $entity = Entity::find($document->owner_id);
                    if ($entity && $entity->federations()->where('federation.id', $federationId)->exists()) {
                        return true;
                    }
                }
            }

            // View access to documents related to DIVING/SCIENTIFIC certifications or licenses
            if (
                $federation
                && Document::query()
                    ->whereKey($document->id)
                    ->hasDivingOrScientificCertOrLicenseForFederation($federation)
                    ->exists()
            ) {
                return true;
            }
        }

        // Entity users can download documents owned by their entity
        if ($user->isEntity() && $this->isOwnerType($document, Entity::class)) {
            return $user->entities->contains($document->owner_id);
        }

        // Individual users can download documents owned by their individuals
        if ($user->isIndividual() && $this->isOwnerType($document, Individual::class)) {
            return $user->individuals->contains($document->owner_id);
        }

        return false;
    }

    /**
     * Check if the document owner type matches the given class.
     * Handles both morph alias (e.g., 'entity') and full class name (e.g., 'Domain\Entities\Models\Entity').
     */
    private function isOwnerType(Document $document, string $class): bool
    {
        $morphClass = (new $class)->getMorphClass();

        return $document->owner_type === $class || $document->owner_type === $morphClass;
    }
}
