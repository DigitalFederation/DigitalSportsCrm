<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class OfficialDocumentDeletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private string $sender;

    /**
     * Create a new notification instance.
     */
    public function __construct(protected array $documentData, string $sender)
    {
        $this->sender = $sender;
    }

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
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        if ($this->sender == 'federation') {
            $url = route('federation.official-documents.index');
        } elseif ($this->sender == 'individual') {
            $url = route('individual.official-documents.index');
        } elseif ($this->sender == 'cmas' || $this->sender == 'admin') {
            $url = route('admin.official-documents.index');
        } else {
            $url = route('individual.official-documents.index');
        }

        return [
            'officialDocument_id' => $this->documentData['id'],
            'officialDocument_type' => $this->documentData['type'],
            'message' => __('notifications.official_document_deleted.database', ['name' => $this->documentData['type']]),
            'url' => $url,
        ];
    }
}
