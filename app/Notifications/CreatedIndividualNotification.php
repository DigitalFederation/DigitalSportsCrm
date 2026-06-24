<?php

namespace App\Notifications;

use Domain\Individuals\Models\Individual;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CreatedIndividualNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(protected Individual $individual, protected string $token) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $activationUrl = url('/activate-account');
        $individualName = $this->individual->name.' '.$this->individual->surname;

        return (new MailMessage)
            ->subject('Bem-vindo / Welcome - '.config('app.name'))
            ->markdown('emails.auth.welcome-individual', [
                'activationUrl' => $activationUrl,
                'individualName' => $individualName,
            ]);
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
}
