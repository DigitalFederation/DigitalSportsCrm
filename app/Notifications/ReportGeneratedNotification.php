<?php

namespace App\Notifications;

use Domain\Reports\Models\GeneratedReport;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReportGeneratedNotification extends Notification
{
    use Queueable;

    protected $generatedReport;

    public function __construct(GeneratedReport $generatedReport)
    {
        $this->generatedReport = $generatedReport;
    }

    public function via($notifiable)
    {
        return ['mail', 'database']; // Sending via email and database
    }

    public function toMail($notifiable)
    {
        $downloadUrl = route('admin.reports.download', $this->generatedReport);

        return (new MailMessage)
            ->line(__('notifications.report_generated.line_ready'))
            ->action(__('notifications.report_generated.action'), $downloadUrl)
            ->line(__('notifications.report_generated.line_auth'));
    }

    public function toArray($notifiable)
    {
        $downloadUrl = route('admin.reports.download', $this->generatedReport);

        return [
            'report_id' => $this->generatedReport->id,
            'message' => __('notifications.report_generated.database'),
            'url' => $downloadUrl,
        ];
    }
}
