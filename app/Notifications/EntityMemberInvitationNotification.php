<?php

namespace App\Notifications;

use Domain\Entities\Models\Entity;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class EntityMemberInvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Entity $entity;

    /**
     * Create a new notification instance.
     *
     * @param  Entity  $entity  The entity sending the invitation
     */
    public function __construct(Entity $entity)
    {
        $this->entity = $entity;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable  The User model being notified
     * @return array<int, string>
     */
    public function via(mixed $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable  The User model being notified
     */
    public function toMail(mixed $notifiable): MailMessage
    {
        $entityName = $this->entity->name;
        $viewUrl = route('individual.entity.index');

        return (new MailMessage)
            ->subject(__('notifications.entity_member_invitation.subject', ['entity' => $entityName]))
            ->greeting(__('notifications.entity_member_invitation.greeting'))
            ->line(__('notifications.entity_member_invitation.line_invited', ['inviter' => $entityName]))
            ->line(__('notifications.entity_member_invitation.line_instructions'))
            ->action(__('notifications.entity_member_invitation.action'), $viewUrl)
            ->line(__('notifications.entity_member_invitation.line_ignore'))
            ->salutation(new HtmlString(__('notifications.entity_member_invitation.salutation', ['app' => config('app.name')])));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable  The User model being notified
     * @return array<string, mixed>
     */
    public function toArray(mixed $notifiable): array
    {
        return [
            'entity_id' => $this->entity->id,
            'entity_name' => $this->entity->name,
            'message' => __('notifications.entity_member_invitation.database', ['entity' => $this->entity->name]),
            'type' => 'entity_member_invitation',
        ];
    }
}
