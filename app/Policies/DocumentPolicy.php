<?php

namespace App\Policies;

use App\Models\User;
use Domain\Documents\Actions\DocumentDownloadAuthorizationAction;
use Domain\Documents\Models\Document;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Document access policy.
 *
 * This policy class uses the DocumentDownloadAuthorizationAction to enforce
 * access control for document-related actions, such as downloading a document.
 * It ensures that users are only able to perform actions on documents when they
 * have the appropriate permissions based on their user group and document ownership.
 */
class DocumentPolicy
{
    use HandlesAuthorization;
    /**
     * Create a new policy instance.
     */
    public function __construct() {}
    /**
     * Determine if the user can download the specified document.
     *
     * @param  User  $user  The user attempting to download the document.
     * @param  Document  $document  The document being requested for download.
     * @return bool Returns true if the user is authorized to download the document, false otherwise.
     */
    public function download(User $user, Document $document): bool
    {
        $authorizationAction = new DocumentDownloadAuthorizationAction;

        return $authorizationAction->execute($user, $document);
    }

    public function view(User $user, Document $document): bool
    {
        $authorizationAction = new DocumentDownloadAuthorizationAction;

        return $authorizationAction->execute($user, $document);
    }
}
