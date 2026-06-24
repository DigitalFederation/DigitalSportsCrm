<?php

namespace App\Notifications\EventApplications;

use Domain\EventApplications\Models\EventApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewApplicationSubmittedNotification extends Notification implements ShouldQueue
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
            ->subject(__('event_applications.notifications.admin.new_submitted.subject'))
            ->markdown('emails.event-applications.new-application-submitted', [
                'application' => $this->application,
                'entity' => $this->application->entity,
                'template' => $this->application->template,
                'url' => $this->getAdminUrl(),
            ]);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'application_id' => $this->application->id,
            'event_name' => $this->application->event_name,
            'entity_name' => $this->application->entity->name ?? 'Unknown',
            'entity_type' => class_basename($this->application->entity_type),
            'submitted_at' => $this->application->submitted_at,
            'template_name' => $this->application->template->name ?? 'N/A',
            'message' => __('event_applications.notifications.admin.new_submitted.message'),
            'url' => $this->getAdminUrl(),
        ];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'application_id' => $this->application->id,
            'event_name' => $this->application->event_name,
            'entity_name' => $this->application->entity->name ?? 'Unknown',
            'submitted_at' => $this->application->submitted_at,
            'url' => $this->getAdminUrl(),
        ];
    }

    protected function getAdminUrl(): string
    {
        return route('admin.event-applications.show', $this->application->id);
    }
}
