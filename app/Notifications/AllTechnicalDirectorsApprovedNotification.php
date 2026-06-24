<?php

namespace App\Notifications;

use Domain\Licenses\Models\LicenseAttributed;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AllTechnicalDirectorsApprovedNotification extends Notification
{
    use Queueable;

    protected LicenseAttributed $licenseAttributed;

    public function __construct(LicenseAttributed $licenseAttributed)
    {
        $this->licenseAttributed = $licenseAttributed;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(__('diving.all_technical_directors_approved_subject'))
            ->greeting(__('diving.all_technical_directors_approved_greeting'))
            ->line(__('diving.all_technical_directors_approved_message', [
                'license' => $this->licenseAttributed->license_name,
            ]))
            ->line(__('diving.license_now_pending_admin_validation'))
            ->action(__('common.view_license'), route('entity.diving_licenses.show', $this->licenseAttributed->id));
    }

    public function toArray($notifiable)
    {
        return [
            'title' => __('diving.all_technical_directors_approved'),
            'message' => __('diving.all_technical_directors_approved_message', [
                'license' => $this->licenseAttributed->license_name,
            ]),
            'license_id' => $this->licenseAttributed->id,
            'type' => 'license_approval',
        ];
    }
}
