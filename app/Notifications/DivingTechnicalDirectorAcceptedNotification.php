<?php

namespace App\Notifications;

use Domain\Diving\Models\DivingTechnicalDirectorInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DivingTechnicalDirectorAcceptedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public DivingTechnicalDirectorInvitation $invitation
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Technical Director Invitation Accepted'))
            ->greeting(__('Great news!'))
            ->line(__(':individual has accepted your invitation to serve as Technical Director for your :license license.', [
                'individual' => $this->invitation->individual->name,
                'license' => $this->invitation->license->name,
            ]))
            ->line(__('Certification System: :system', [
                'system' => $this->invitation->certification_system,
            ]))
            ->line(__('The technical director is now assigned to your license and can oversee your diving operations.'))
            ->action(__('View License Details'), route('entity.diving-licenses.show', $this->invitation->license_attributed_id))
            ->line(__('Thank you for using our platform!'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'diving_technical_director_accepted',
            'invitation_id' => $this->invitation->id,
            'individual_id' => $this->invitation->individual_id,
            'individual_name' => $this->invitation->individual->name,
            'license_id' => $this->invitation->license_id,
            'license_name' => $this->invitation->license->name,
            'certification_system' => $this->invitation->certification_system,
            'message' => __(':individual accepted as Technical Director for :license', [
                'individual' => $this->invitation->individual->name,
                'license' => $this->invitation->license->name,
            ]),
        ];
    }
}
