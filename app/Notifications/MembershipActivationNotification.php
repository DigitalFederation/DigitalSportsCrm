<?php

namespace App\Notifications;

use Domain\Memberships\Models\Membership;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MembershipActivationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(protected Membership $membership) {}

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
            ->line(__('notifications.membership_activation.line_activated', ['name' => $this->membership->name]))
            ->action(__('notifications.membership_activation.action'), route('federation.membership.index'))
            ->line(__('notifications.membership_activation.salutation'));
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
            'membership_id' => $this->membership->id,
            'membership_name' => $this->membership->name,
            'message' => __('notifications.membership_activation.database', ['name' => $this->membership->name]),
            'url' => route('federation.membership.index'),
        ];
    }
}
