<?php

namespace App\Notifications;

use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EntityApprovalNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $entity;
    protected $federation;

    public function __construct(Entity $entity, Federation $federation)
    {
        $this->entity = $entity;
        $this->federation = $federation;
    }

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $url = route('federation.entity-request.index');

        return (new MailMessage)
            ->subject(__('notifications.entity_approval.subject'))
            ->greeting(__('notifications.entity_approval.greeting', ['name' => $notifiable->name]))
            ->line(__('notifications.entity_approval.line_intro'))
            ->line(__('notifications.entity_approval.line_entity', ['entity' => $this->entity->name]))
            ->action(__('notifications.entity_approval.action'), $url)
            ->line(__('notifications.entity_approval.line_review'))
            ->salutation(__('notifications.entity_approval.salutation_regards'))
            ->salutation(__('notifications.entity_approval.salutation_team', ['app' => config('app.name')]));
    }

    public function toDatabase($notifiable): array
    {
        return [
            'entity_id' => $this->entity->id,
            'entity_name' => $this->entity->name,
            'federation_id' => $this->federation->id,
            'federation_name' => $this->federation->name,
            'message' => __('notifications.entity_approval.database'),
            'url' => route('federation.entity-request.index'),
        ];
    }

    public function toArray($notifiable): array
    {
        return [
            'entity_id' => $this->entity->id,
            'federation_id' => $this->federation->id,
        ];
    }
}
