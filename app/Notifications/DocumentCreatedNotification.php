<?php

namespace App\Notifications;

use Domain\Documents\Models\Document;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(protected Document $document)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = $this->getDocumentUrl();

        return (new MailMessage)
            ->subject(__('notifications.document_created.subject'))
            ->greeting(__('notifications.document_created.greeting'))
            ->line(__('notifications.document_created.line', ['invoice' => $this->document->invoiceExtended]))
            ->action(__('notifications.document_created.action'), url($url));
    }

    public function toDatabase(object $notifiable): array
    {
        $url = $this->getDocumentUrl();

        return [
            'document_id' => $this->document->id,
            'document_invoice' => $this->document->invoiceExtended,
            'message' => "A new invoice {$this->document->invoiceExtended} has been created.",
            'url' => $url,
        ];
    }

    protected function getDocumentUrl(): string
    {
        switch ($this->document->owner_type) {
            case Federation::class:
                return route('federation.document.show', $this->document);

            case Individual::class:
                return route('individual.document.show', $this->document);

            case Entity::class:
                return route('entity.document.show', $this->document);

            default:
                // Default route or error handling
                return route('federation.document.show', $this->document);
        }
    }
}
