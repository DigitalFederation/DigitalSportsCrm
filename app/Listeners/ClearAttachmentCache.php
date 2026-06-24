<?php

namespace App\Listeners;

use App\Events\AttachmentFileCreatedEvent;
use Domain\Federations\Models\Federation;
use Illuminate\Support\Facades\Cache;

class ClearAttachmentCache
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(AttachmentFileCreatedEvent $event): void
    {
        $attachment = $event->attachment;
        // Check if the owner is a Federation
        if ($attachment->owner instanceof Federation) {
            $federationId = $attachment->owner->id;
            $committeeId = $attachment->committee_id;

            // Invalidate cache for the specific federation and committee
            $cacheKey = "attachments_for_federation_{$federationId}_committee_{$committeeId}";
            Cache::forget($cacheKey);
        }
    }
}
