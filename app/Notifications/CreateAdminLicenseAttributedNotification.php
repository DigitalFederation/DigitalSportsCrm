<?php

namespace App\Notifications;

use Domain\Licenses\Models\LicenseAttributed;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CreateAdminLicenseAttributedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $licenseAttributed;

    public function __construct(LicenseAttributed $licenseAttributed)
    {
        $this->licenseAttributed = $licenseAttributed;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->greeting(__('notifications.admin_license_attributed.greeting'))
            ->subject(__('notifications.admin_license_attributed.subject'))
            ->line(__('notifications.admin_license_attributed.line_intro'))
            ->line(__('notifications.admin_license_attributed.line_license', ['name' => $this->licenseAttributed->license_name]))
            ->line(__('notifications.admin_license_attributed.line_holder', ['holder' => $this->licenseAttributed->holder_name]))
            ->line(__('notifications.admin_license_attributed.line_federation', ['federation' => $this->licenseAttributed->federation_name]))
            ->action(__('notifications.admin_license_attributed.action'), url(route('admin.license-attributed.show', $this->licenseAttributed->id)));
    }

}
