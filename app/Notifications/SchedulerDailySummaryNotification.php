<?php

declare(strict_types=1);

namespace App\Notifications;

use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SchedulerDailySummaryNotification extends Notification
{
    use Queueable;

    /**
     * @param  array{items: array<int, array{label: string, count: int}>, notes: array<int, string>}  $summary
     */
    public function __construct(
        protected CarbonImmutable $from,
        protected CarbonImmutable $to,
        protected array $summary
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject(config('branding.primary.portal_name', 'Digital Sports CRM') . ' scheduler activity summary')
            ->line('Scheduler activity summary for ' . $this->from->format('Y-m-d H:i') . ' to ' . $this->to->format('Y-m-d H:i') . '.');

        foreach ($this->summary['items'] as $item) {
            $message->line($item['label'] . ': ' . $item['count']);
        }

        if ($this->summary['notes'] !== []) {
            foreach ($this->summary['notes'] as $note) {
                $message->line($note);
            }
        }

        return $message;
    }

    /**
     * @return array{from: string, to: string, items: array<int, array{label: string, count: int}>, notes: array<int, string>}
     */
    public function toArray(object $notifiable): array
    {
        return [
            'from' => $this->from->toIso8601String(),
            'to' => $this->to->toIso8601String(),
            'items' => $this->summary['items'],
            'notes' => $this->summary['notes'],
        ];
    }
}
