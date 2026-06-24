<?php

namespace App\Notifications;

use Domain\Documents\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notification sent when a Moloni invoice fails to be created
 * after all retry attempts have been exhausted.
 *
 * This alerts administrators so they can manually investigate
 * and resolve the issue.
 */
class MoloniInvoiceFailedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Document $document,
        public string $errorMessage,
        public int $attempts = 3
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $documentUrl = route('admin.document.show', $this->document->id);
        $moloniSettingsUrl = route('admin.moloni-settings.index');

        return (new MailMessage)
            ->subject(__('moloni.notification_invoice_failed_subject'))
            ->error()
            ->greeting(__('moloni.notification_invoice_failed_greeting'))
            ->line(__('moloni.notification_invoice_failed_intro', [
                'document' => $this->document->number_extended ?? $this->document->id,
            ]))
            ->line(__('moloni.notification_invoice_failed_error', [
                'error' => $this->errorMessage,
            ]))
            ->line(__('moloni.notification_invoice_failed_attempts', [
                'attempts' => $this->attempts,
            ]))
            ->action(__('moloni.notification_invoice_failed_action'), $moloniSettingsUrl)
            ->line(__('moloni.notification_invoice_failed_document_link', [
                'url' => $documentUrl,
            ]));
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'moloni_invoice_failed',
            'document_id' => $this->document->id,
            'document_number' => $this->document->number_extended,
            'error_message' => $this->errorMessage,
            'attempts' => $this->attempts,
            'message' => __('moloni.notification_invoice_failed_database', [
                'document' => $this->document->number_extended ?? $this->document->id,
            ]),
            'url' => route('admin.moloni-settings.index'),
        ];
    }
}
