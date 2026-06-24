<?php

namespace Domain\Documents\Actions;

use App\Notifications\DocumentCreatedNotification;
use Domain\Documents\Models\Document;

class ResendInvoiceNotificationAction
{
    public function execute(Document $invoice): void
    {
        $user = $invoice->owner->users()->first() ?? $invoice->owner->user()->first();

        $user->notify(new DocumentCreatedNotification($invoice));
    }
}
