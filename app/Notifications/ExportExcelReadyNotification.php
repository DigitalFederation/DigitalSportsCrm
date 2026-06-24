<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class ExportExcelReadyNotification extends Notification
{
    use Queueable;

    protected $filePath;
    protected $fileName;

    protected $expiration;
    protected $downloadLink;

    /**
     * Create a new notification instance.
     */
    public function __construct($filePath, $fileName)
    {
        $this->filePath = $filePath;
        $this->fileName = $fileName;

        $this->expiration = now()->addMinutes(30);
        $this->downloadLink = URL::temporarySignedRoute(
            'download.excel.export', $this->expiration, ['filePath' => $this->filePath, 'fileName' => $this->fileName]
        );
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        return ['mail', 'database']; // or any other channel you prefer
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->line(__('notifications.export_ready.line_intro'))
            ->action(__('notifications.export_ready.action'), $this->downloadLink);
    }

    public function toDatabase($notifiable)
    {
        return [
            'downloadLink' => $this->downloadLink,
            'message' => __('notifications.export_ready.database'),
        ];
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
