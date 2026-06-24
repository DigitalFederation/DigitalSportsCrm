<?php

namespace App\Notifications\EventApplications;

use Domain\EventApplications\Models\EventApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected EventApplication $application) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('event_applications.notifications.approved.subject'))
            ->markdown('emails.event-applications.application-approved', [
                'application' => $this->application,
                'entity' => $this->application->entity,
                'url' => $this->getEntityUrl(),
            ]);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'application_id' => $this->application->id,
            'event_name' => $this->application->event_name,
            'decided_at' => $this->application->decided_at,
            'status' => $this->application->status_class,
            'message' => __('event_applications.notifications.approved.message'),
            'url' => $this->getEntityUrl(),
        ];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'application_id' => $this->application->id,
            'event_name' => $this->application->event_name,
            'decided_at' => $this->application->decided_at,
            'url' => $this->getEntityUrl(),
        ];
    }

    protected function getEntityUrl(): string
    {
        $entityType = strtolower(class_basename($this->application->entity_type));

        return route("{$entityType}.event-applications.show", $this->application->id);
    }
}
