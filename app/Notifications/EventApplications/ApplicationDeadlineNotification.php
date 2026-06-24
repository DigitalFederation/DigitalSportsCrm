<?php

namespace App\Notifications\EventApplications;

use Domain\EventApplications\Models\ApplicationTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationDeadlineNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected ApplicationTemplate $template,
        protected int $applicationsCount,
        protected int $daysRemaining
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('event_applications.notifications.admin.deadline.subject', ['template' => $this->template->name]))
            ->markdown('emails.event-applications.application-deadline', [
                'template' => $this->template,
                'applicationsCount' => $this->applicationsCount,
                'daysRemaining' => $this->daysRemaining,
                'url' => $this->getAdminUrl(),
            ]);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'template_id' => $this->template->id,
            'template_name' => $this->template->name,
            'submission_end_date' => $this->template->submission_end_date,
            'days_remaining' => $this->daysRemaining,
            'applications_count' => $this->applicationsCount,
            'message' => __('event_applications.notifications.admin.deadline.message', [
                'template' => $this->template->name,
                'days' => $this->daysRemaining,
            ]),
            'url' => $this->getAdminUrl(),
        ];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'template_id' => $this->template->id,
            'template_name' => $this->template->name,
            'submission_end_date' => $this->template->submission_end_date,
            'days_remaining' => $this->daysRemaining,
            'applications_count' => $this->applicationsCount,
            'url' => $this->getAdminUrl(),
        ];
    }

    protected function getAdminUrl(): string
    {
        return route('admin.event-application-templates.show', $this->template->id);
    }
}
