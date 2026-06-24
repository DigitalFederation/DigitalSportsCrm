<?php

namespace App\Notifications;

use Domain\Entities\Models\Entity;
use Domain\Individuals\Models\Individual;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class EntityMemberAcceptedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Individual $individual;

    public Entity $entity;

    /**
     * Create a new notification instance.
     *
     * @param  Individual  $individual  The individual who accepted
     * @param  Entity  $entity  The entity they joined
     */
    public function __construct(Individual $individual, Entity $entity)
    {
        $this->individual = $individual;
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
        $individualName = $this->individual->full_name;
        $entityName = $this->entity->name;
        $viewUrl = route('entity.individual.index');

        return (new MailMessage)
            ->subject(__('notifications.entity_member_accepted.subject', ['name' => $individualName]))
            ->greeting(__('notifications.entity_member_accepted.greeting'))
            ->line(__('notifications.entity_member_accepted.line_accepted', ['name' => $individualName, 'entity' => $entityName]))
            ->line(__('notifications.entity_member_accepted.line_active'))
            ->action(__('notifications.entity_member_accepted.action'), $viewUrl)
            ->salutation(new HtmlString(__('notifications.entity_member_accepted.salutation', ['app' => config('app.name')])));
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
            'individual_id' => $this->individual->id,
            'individual_name' => $this->individual->full_name,
            'entity_id' => $this->entity->id,
            'entity_name' => $this->entity->name,
            'message' => __('notifications.entity_member_accepted.database', ['name' => $this->individual->full_name]),
            'type' => 'entity_member_accepted',
        ];
    }
}
