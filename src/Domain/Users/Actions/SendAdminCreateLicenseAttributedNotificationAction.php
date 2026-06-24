<?php

namespace Domain\Users\Actions;

use App\Models\User;
use App\Notifications\CreateAdminLicenseAttributedNotification;

class SendAdminCreateLicenseAttributedNotificationAction
{
    /**
     * Execute the action.
     */
    public function execute(array $license_attributed_array): void
    {
        // Fetch users with 'cmas-notifications' role and 'receive email notifications' permission
        $notificationUsers = User::role('cmas-notifications')->permission('access email notifications')->get();

        // Check if there are users to notify
        if ($notificationUsers->isNotEmpty()) {
            foreach ($notificationUsers as $user) {
                // Send notification
                foreach ($license_attributed_array as $license_attributed) {
                    $user->notify(new CreateAdminLicenseAttributedNotification($license_attributed));
                }

            }
        }
    }
}
