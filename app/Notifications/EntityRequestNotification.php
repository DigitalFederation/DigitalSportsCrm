<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class EntityRequestNotification extends Notification
{
    use Queueable;

    protected $individual;
    protected $entity;

    public function __construct($individual, $entity)
    {
        $this->individual = $individual;
        $this->entity = $entity;
    }

    public function via($notifiable)
    {
        return ['database']; // Using the database channel
    }

    public function toArray($notifiable)
    {
        return [
            'title' => __('notifications.entity_request.database_title'),
            'message' => __('notifications.entity_request.database_message', ['name' => $this->individual->name]),
            'url' => route('entity.individual-approve.index'),
            'individual_id' => $this->individual->id,
            'entity_id' => $this->entity->id,
        ];
    }
}
