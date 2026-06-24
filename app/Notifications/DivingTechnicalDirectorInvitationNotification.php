<?php

namespace App\Notifications;

use Domain\Diving\Models\DivingTechnicalDirectorInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DivingTechnicalDirectorInvitationNotification extends Notification implements ShouldQueue
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
            ->subject(__('Technical Director Invitation'))
            ->greeting(__('Hello :name!', ['name' => $notifiable->name]))
            ->line(__(':entity has invited you to serve as their Technical Director for their :license license.', [
                'entity' => $this->invitation->entity->name,
                'license' => $this->invitation->license->name,
            ]))
            ->line(__('Certification System: :system', [
                'system' => $this->invitation->certification_system,
            ]))
            ->when($this->invitation->message, function ($mail) {
                return $mail->line(__('Message from the entity:'))
                    ->line('"' . $this->invitation->message . '"');
            })
            ->line(__('As a Technical Director, you will be responsible for overseeing the diving operations and ensuring compliance with certification standards.'))
            ->action(__('View Invitation'), route('individual.technical-director-invitations.index'))
            ->line(__('You can accept or reject this invitation through your dashboard.'))
            ->line(__('Thank you for your consideration!'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'diving_technical_director_invitation',
            'invitation_id' => $this->invitation->id,
            'entity_id' => $this->invitation->entity_id,
            'entity_name' => $this->invitation->entity->name,
            'license_id' => $this->invitation->license_id,
            'license_name' => $this->invitation->license->name,
            'certification_system' => $this->invitation->certification_system,
            'message' => __(':entity invited you as Technical Director for :license', [
                'entity' => $this->invitation->entity->name,
                'license' => $this->invitation->license->name,
            ]),
        ];
    }
}
