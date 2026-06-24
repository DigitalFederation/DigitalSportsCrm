<?php

namespace App\Notifications;

use Domain\Certifications\Models\CertificationAttributed;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InstructorNewCertificationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(protected CertificationAttributed $certification) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line(__('notifications.instructor_new_certification.line'))
            ->action(__('notifications.instructor_new_certification.action'), url("/certification-validate/{$this->certification->id}"));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'certification_name' => $this->certification->certification_name,
            'holder_name' => $this->certification->holder_name,
            'message' => __('notifications.instructor_new_certification.database'),
            'url' => url("/certification-validate/{$this->certification->id}"),
        ];
    }
}
