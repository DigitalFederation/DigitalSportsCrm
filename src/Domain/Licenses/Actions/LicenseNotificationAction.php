<?php

namespace Domain\Licenses\Actions;

use Carbon\Carbon;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Domain\Licenses\States\ExpiredLicenseAttributedState;
use Domain\Licenses\States\PendingLicenseAttributedState;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

class LicenseNotificationAction
{
    /**
     * Send expiration reminder notifications.
     *
     * @param  int  $daysBefore  Number of days before expiration to send reminder
     */
    public function sendExpirationReminders(int $daysBefore = 30): array
    {
        $expirationDate = Carbon::now()->addDays($daysBefore);

        $licenses = LicenseAttributed::where('status_class', ActiveLicenseAttributedState::class)
            ->whereDate('date_expire', $expirationDate->toDateString())
            ->with(['owner', 'license', 'federation'])
            ->get();

        $sent = 0;
        $failed = 0;

        foreach ($licenses as $license) {
            try {
                $this->sendExpirationReminderEmail($license, $daysBefore);
                $sent++;

                // Log notification
                activity('License')
                    ->performedOn($license)
                    ->event('expiration_reminder_sent')
                    ->withProperties([
                        'days_before' => $daysBefore,
                        'expiration_date' => $license->date_expire,
                        'recipient' => $license->holder_name,
                    ])
                    ->log("Expiration reminder sent ({$daysBefore} days before)");

            } catch (\Exception $e) {
                $failed++;

                activity('License')
                    ->performedOn($license)
                    ->event('expiration_reminder_failed')
                    ->withProperties([
                        'error' => $e->getMessage(),
                        'days_before' => $daysBefore,
                    ])
                    ->log('Failed to send expiration reminder');
            }
        }

        return [
            'total_licenses' => $licenses->count(),
            'sent' => $sent,
            'failed' => $failed,
        ];
    }

    /**
     * Send renewal reminders for expired licenses.
     */
    public function sendRenewalReminders(int $daysAfterExpiration = 7): array
    {
        $cutoffDate = Carbon::now()->subDays($daysAfterExpiration);

        $licenses = LicenseAttributed::where('status_class', ExpiredLicenseAttributedState::class)
            ->whereDate('date_expire', $cutoffDate->toDateString())
            ->with(['owner', 'license', 'federation'])
            ->get();

        $sent = 0;
        $failed = 0;

        foreach ($licenses as $license) {
            try {
                $this->sendRenewalReminderEmail($license, $daysAfterExpiration);
                $sent++;

                activity('License')
                    ->performedOn($license)
                    ->event('renewal_reminder_sent')
                    ->withProperties([
                        'days_after_expiration' => $daysAfterExpiration,
                        'expiration_date' => $license->date_expire,
                        'recipient' => $license->holder_name,
                    ])
                    ->log("Renewal reminder sent ({$daysAfterExpiration} days after expiration)");

            } catch (\Exception $e) {
                $failed++;

                activity('License')
                    ->performedOn($license)
                    ->event('renewal_reminder_failed')
                    ->withProperties([
                        'error' => $e->getMessage(),
                        'days_after_expiration' => $daysAfterExpiration,
                    ])
                    ->log('Failed to send renewal reminder');
            }
        }

        return [
            'total_licenses' => $licenses->count(),
            'sent' => $sent,
            'failed' => $failed,
        ];
    }

    /**
     * Send payment pending notifications.
     */
    public function sendPaymentPendingReminders(int $daysAfterCreation = 3): array
    {
        $cutoffDate = Carbon::now()->subDays($daysAfterCreation);

        $licenses = LicenseAttributed::where('status_class', PendingLicenseAttributedState::class)
            ->whereDate('created_at', $cutoffDate->toDateString())
            ->whereNull('purchased_at')
            ->with(['owner', 'license', 'federation'])
            ->get();

        $sent = 0;
        $failed = 0;

        foreach ($licenses as $license) {
            try {
                $this->sendPaymentPendingEmail($license, $daysAfterCreation);
                $sent++;

                activity('License')
                    ->performedOn($license)
                    ->event('payment_reminder_sent')
                    ->withProperties([
                        'days_after_creation' => $daysAfterCreation,
                        'total_value' => $license->total_value,
                        'recipient' => $license->holder_name,
                    ])
                    ->log("Payment reminder sent ({$daysAfterCreation} days after creation)");

            } catch (\Exception $e) {
                $failed++;

                activity('License')
                    ->performedOn($license)
                    ->event('payment_reminder_failed')
                    ->withProperties([
                        'error' => $e->getMessage(),
                        'days_after_creation' => $daysAfterCreation,
                    ])
                    ->log('Failed to send payment reminder');
            }
        }

        return [
            'total_licenses' => $licenses->count(),
            'sent' => $sent,
            'failed' => $failed,
        ];
    }

    /**
     * Send custom notification to license holders.
     */
    public function sendCustomNotification(Collection $licenseIds, string $subject, string $message, array $additionalData = []): array
    {
        $licenses = LicenseAttributed::whereIn('id', $licenseIds)
            ->with(['owner', 'license', 'federation'])
            ->get();

        $sent = 0;
        $failed = 0;

        foreach ($licenses as $license) {
            try {
                $this->sendCustomEmail($license, $subject, $message, $additionalData);
                $sent++;

                activity('License')
                    ->performedOn($license)
                    ->event('custom_notification_sent')
                    ->withProperties([
                        'subject' => $subject,
                        'recipient' => $license->holder_name,
                        'additional_data' => $additionalData,
                    ])
                    ->log('Custom notification sent');

            } catch (\Exception $e) {
                $failed++;

                activity('License')
                    ->performedOn($license)
                    ->event('custom_notification_failed')
                    ->withProperties([
                        'subject' => $subject,
                        'error' => $e->getMessage(),
                    ])
                    ->log('Failed to send custom notification');
            }
        }

        return [
            'total_licenses' => $licenses->count(),
            'sent' => $sent,
            'failed' => $failed,
        ];
    }

    /**
     * Get license holders needing notifications.
     */
    public function getNotificationSummary(): array
    {
        $now = Carbon::now();

        return [
            'expiring_soon' => [
                'in_7_days' => LicenseAttributed::where('status_class', ActiveLicenseAttributedState::class)
                    ->whereBetween('date_expire', [$now, $now->copy()->addDays(7)])
                    ->count(),
                'in_30_days' => LicenseAttributed::where('status_class', ActiveLicenseAttributedState::class)
                    ->whereBetween('date_expire', [$now, $now->copy()->addDays(30)])
                    ->count(),
            ],
            'expired_recently' => [
                'last_7_days' => LicenseAttributed::where('status_class', ExpiredLicenseAttributedState::class)
                    ->whereBetween('date_expire', [$now->copy()->subDays(7), $now])
                    ->count(),
                'last_30_days' => LicenseAttributed::where('status_class', ExpiredLicenseAttributedState::class)
                    ->whereBetween('date_expire', [$now->copy()->subDays(30), $now])
                    ->count(),
            ],
            'payment_pending' => [
                'older_than_3_days' => LicenseAttributed::where('status_class', PendingLicenseAttributedState::class)
                    ->where('created_at', '<', $now->copy()->subDays(3))
                    ->whereNull('purchased_at')
                    ->count(),
                'older_than_7_days' => LicenseAttributed::where('status_class', PendingLicenseAttributedState::class)
                    ->where('created_at', '<', $now->copy()->subDays(7))
                    ->whereNull('purchased_at')
                    ->count(),
            ],
        ];
    }

    /**
     * Send expiration reminder email.
     */
    private function sendExpirationReminderEmail(LicenseAttributed $license, int $daysBefore): void
    {
        // This would integrate with your email system
        // For now, we'll simulate the email sending
        $emailData = [
            'recipient' => $license->holder_name,
            'license_name' => $license->license_name,
            'expiration_date' => $license->date_expire,
            'days_remaining' => $daysBefore,
            'federation' => $license->federation_name,
        ];

        // In a real implementation, you would use:
        // Mail::to($license->owner->email)->send(new LicenseExpirationReminder($emailData));
    }

    /**
     * Send renewal reminder email.
     */
    private function sendRenewalReminderEmail(LicenseAttributed $license, int $daysAfterExpiration): void
    {
        $emailData = [
            'recipient' => $license->holder_name,
            'license_name' => $license->license_name,
            'expiration_date' => $license->date_expire,
            'days_expired' => $daysAfterExpiration,
            'federation' => $license->federation_name,
        ];

        // Mail::to($license->owner->email)->send(new LicenseRenewalReminder($emailData));
    }

    /**
     * Send payment pending email.
     */
    private function sendPaymentPendingEmail(LicenseAttributed $license, int $daysAfterCreation): void
    {
        $emailData = [
            'recipient' => $license->holder_name,
            'license_name' => $license->license_name,
            'total_value' => $license->total_value,
            'days_pending' => $daysAfterCreation,
            'federation' => $license->federation_name,
        ];

        // Mail::to($license->owner->email)->send(new LicensePaymentPending($emailData));
    }

    /**
     * Send custom email.
     */
    private function sendCustomEmail(LicenseAttributed $license, string $subject, string $message, array $additionalData): void
    {
        $emailData = array_merge([
            'recipient' => $license->holder_name,
            'license_name' => $license->license_name,
            'subject' => $subject,
            'message' => $message,
            'federation' => $license->federation_name,
        ], $additionalData);

        // Mail::to($license->owner->email)->send(new CustomLicenseNotification($emailData));
    }
}
