<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\EventApplications\ApplicationDeadlineNotification;
use Domain\EventApplications\Models\ApplicationTemplate;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SendApplicationDeadlineNotifications extends Command
{
    protected $signature = 'event-applications:send-deadline-notifications';

    protected $description = 'Send notifications for application templates with approaching submission deadlines';

    public function handle()
    {
        $this->info('Checking for application templates with approaching deadlines...');

        // Get templates with submission_end_date within 7 days
        $templates = ApplicationTemplate::where('state', 'open')
            ->whereNotNull('submission_end_date')
            ->whereBetween('submission_end_date', [now(), now()->addDays(7)])
            ->get();

        if ($templates->isEmpty()) {
            $this->info('No templates with approaching deadlines found.');

            return Command::SUCCESS;
        }

        $this->info("Found {$templates->count()} template(s) with approaching deadlines.");

        $notificationsSent = 0;

        foreach ($templates as $template) {
            $daysRemaining = now()->diffInDays($template->submission_end_date, false);

            // Only send notifications for 7, 3, and 1 day(s) remaining
            if (! in_array(ceil($daysRemaining), [7, 3, 1])) {
                continue;
            }

            // Count applications for this template
            $applicationsCount = $template->applications()->count();

            // Get users with 'review event applications' permission
            $admins = User::permission('review event applications')->get();

            if ($admins->isEmpty()) {
                $this->warn("No users with 'review event applications' permission found for template: {$template->name}");

                continue;
            }

            // Send notification
            try {
                Notification::send(
                    $admins,
                    new ApplicationDeadlineNotification(
                        $template,
                        $applicationsCount,
                        (int) ceil($daysRemaining)
                    )
                );

                $notificationsSent++;

                $this->info("Sent deadline notification for template: {$template->name} ({$daysRemaining} days remaining)");

                // Log the notification
                activity('event_application_deadline')
                    ->performedOn($template)
                    ->withProperties([
                        'days_remaining' => (int) ceil($daysRemaining),
                        'applications_count' => $applicationsCount,
                        'submission_end_date' => $template->submission_end_date->toDateString(),
                        'recipients_count' => $admins->count(),
                    ])
                    ->log('Deadline notification sent');
            } catch (\Exception $e) {
                $this->error("Failed to send notification for template: {$template->name}");
                $this->error("Error: {$e->getMessage()}");

                // Log the error
                activity('event_application_deadline')
                    ->performedOn($template)
                    ->withProperties([
                        'error' => $e->getMessage(),
                        'days_remaining' => (int) ceil($daysRemaining),
                    ])
                    ->log('Failed to send deadline notification');
            }
        }

        $this->info("Sent {$notificationsSent} deadline notification(s).");

        return Command::SUCCESS;
    }
}
