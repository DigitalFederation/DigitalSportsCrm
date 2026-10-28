<?php

namespace Domain\Documents\Actions;

use App\Models\User;
use Domain\Documents\Models\Document;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Memberships\Models\MemberSubscription;

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
        // Administrators may access every document.
        if ($user->isAdmin()) {
            return true;
        }

        // Federation users.
        if ($user->isFederation()) {
            $federation = $user->getFederation();

            if ($federation && $federation->isMainFederation()) {
                return true;
            }

            if ($this->isOwnerType($document, Federation::class)) {
                return $user->federations()
                    ->whereKey($document->owner_id)
                    ->exists();
            }

            if ($this->isOwnerType($document, Entity::class)) {
                $federationId = $user->getFederationId();

                if ($federationId) {
                    $entity = Entity::find($document->owner_id);

                    if (
                        $entity
                        && $entity->federations()
                            ->where('federation.id', $federationId)
                            ->exists()
                    ) {
                        return true;
                    }
                }
            }

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

        // Entity-owned documents.
        if (
            $this->isOwnerType($document, Entity::class)
            && $user->entities()
                ->whereKey($document->owner_id)
                ->exists()
        ) {
            return true;
        }

        /*
         * Individual documents.
         *
         * Do not depend on User::isIndividual() here. Portal permissions may
         * come from roles such as "individual-approved", while isIndividual()
         * checks the legacy/group classification (group_id).
         *
         * The actual relationship between User and Individual is sufficient
         * proof that this user owns that Individual record.
         */
        if ($this->canAccessAsLinkedIndividual($user, $document)) {
            return true;
        }

        return false;
    }

    private function canAccessAsLinkedIndividual(
        User $user,
        Document $document
    ): bool {
        $individualIds = $user->individuals()
            ->pluck('id')
            ->map(fn ($id) => (string) $id);

        if ($individualIds->isEmpty()) {
            return false;
        }

        // Normal case: the document itself belongs to the Individual.
        if (
            $this->isOwnerType($document, Individual::class)
            && $individualIds->contains((string) $document->owner_id)
        ) {
            return true;
        }

        /*
         * Subscription invoice fallback.
         *
         * A directly requested MemberSubscription belonging to one of this
         * user's Individuals is also evidence that the invoice belongs to
         * this Individual.
         *
         * entity_group subscriptions are deliberately excluded.
         */
        $individualTypes = array_values(array_unique([
            Individual::class,
            (new Individual)->getMorphClass(),
        ]));

        $subscriptionIds = MemberSubscription::query()
            ->whereIn('member_type', $individualTypes)
            ->whereIn('member_id', $individualIds->all())
            ->whereIn('requester_type', $individualTypes)
            ->whereIn('requester_id', $individualIds->all())
            ->where('request_type', 'direct')
            ->pluck('id');

        if ($subscriptionIds->isEmpty()) {
            return false;
        }

        $subscriptionTypes = array_values(array_unique([
            MemberSubscription::class,
            (new MemberSubscription)->getMorphClass(),
        ]));

        return $document->details()
            ->whereIn('owner_type', $subscriptionTypes)
            ->whereIn('owner_id', $subscriptionIds->all())
            ->exists();
    }

    private function isOwnerType(Document $document, string $class): bool
    {
        $morphClass = (new $class)->getMorphClass();

        return $document->owner_type === $class
            || $document->owner_type === $morphClass;
    }
}
