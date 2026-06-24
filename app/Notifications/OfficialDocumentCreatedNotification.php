<?php

namespace App\Notifications;

use App\Enums\OfficialDocumentTypeEnum;
use Domain\OfficialDocuments\Models\OfficialDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class OfficialDocumentCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(protected OfficialDocument $officialDocument) {}

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
        return [
            'officialDocument_id' => $this->officialDocument->id,
            'officialDocument_name' => $this->officialDocument->name,
            'message' => __('notifications.official_document_created.database', ['name' => OfficialDocumentTypeEnum::toString($this->officialDocument->type)]),
            'url' => route('federation.official-documents.index'),
        ];
    }
}
