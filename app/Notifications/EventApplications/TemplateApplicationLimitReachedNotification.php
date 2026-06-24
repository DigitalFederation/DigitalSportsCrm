<?php

namespace App\Notifications\EventApplications;

use Domain\EventApplications\Models\ApplicationTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TemplateApplicationLimitReachedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected ApplicationTemplate $template,
        protected int $totalApplications
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('event_applications.notifications.admin.limit_reached.subject', ['template' => $this->template->name]))
            ->markdown('emails.event-applications.template-limit-reached', [
                'template' => $this->template,
                'totalApplications' => $this->totalApplications,
                'maxApplications' => $this->template->max_applications,
                'url' => $this->getAdminUrl(),
            ]);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'template_id' => $this->template->id,
            'template_name' => $this->template->name,
            'max_applications' => $this->template->max_applications,
            'total_applications' => $this->totalApplications,
            'message' => __('event_applications.notifications.admin.limit_reached.message', [
                'template' => $this->template->name,
                'limit' => $this->template->max_applications,
            ]),
            'url' => $this->getAdminUrl(),
        ];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'template_id' => $this->template->id,
            'template_name' => $this->template->name,
            'max_applications' => $this->template->max_applications,
            'total_applications' => $this->totalApplications,
            'url' => $this->getAdminUrl(),
        ];
    }

    protected function getAdminUrl(): string
    {
        return route('admin.event-application-templates.show', $this->template->id);
    }
}
