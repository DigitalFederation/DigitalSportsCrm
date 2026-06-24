<?php

namespace App\Listeners;

use App\Events\LicenseAttributedCreatedEvent;

class AdminNotificationForLicenseAttributedListener
{
    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(LicenseAttributedCreatedEvent $event)
    {
        /* DISABLED AS REQUESTED BY THE CLIENT
        $sendNotificationAction = new SendAdminCreateLicenseAttributedNotificationAction();
        $sendNotificationAction->execute($event->licenseAttributed);
        */
    }
}
