<?php

namespace Domain\Licenses\Actions;

use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Domain\Licenses\States\SuspendedLicenseAttributedState;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BulkManageLicensesAction
{
    private ManageLicenseAction $manageLicenseAction;

    public function __construct(ManageLicenseAction $manageLicenseAction)
    {
        $this->manageLicenseAction = $manageLicenseAction;
    }

    /**
     * Bulk suspend multiple licenses.
     */
    public function bulkSuspend(Collection $licenseIds, string $reason = ''): array
    {
        $results = ['successful' => [], 'failed' => []];

        DB::beginTransaction();

        try {
            $licenses = LicenseAttributed::whereIn('id', $licenseIds)
                ->where('status_class', ActiveLicenseAttributedState::class)
                ->get();

            foreach ($licenses as $license) {
                try {
                    $this->manageLicenseAction->suspend($license, $reason);
                    $results['successful'][] = $license->id;
                } catch (Exception $e) {
                    $results['failed'][] = [
                        'license_id' => $license->id,
                        'error' => $e->getMessage(),
                    ];
                }
            }

            DB::commit();

            // Log bulk action
            activity('License')
                ->event('bulk_suspended')
                ->withProperties([
                    'successful_count' => count($results['successful']),
                    'failed_count' => count($results['failed']),
                    'reason' => $reason,
                    'license_ids' => $licenseIds->toArray(),
                ])
                ->log('Bulk suspended ' . count($results['successful']) . ' licenses');

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $results;
    }

    /**
     * Bulk reactivate multiple licenses.
     */
    public function bulkReactivate(Collection $licenseIds): array
    {
        $results = ['successful' => [], 'failed' => []];

        DB::beginTransaction();

        try {
            $licenses = LicenseAttributed::whereIn('id', $licenseIds)
                ->where('status_class', SuspendedLicenseAttributedState::class)
                ->get();

            foreach ($licenses as $license) {
                try {
                    $this->manageLicenseAction->reactivate($license);
                    $results['successful'][] = $license->id;
                } catch (Exception $e) {
                    $results['failed'][] = [
                        'license_id' => $license->id,
                        'error' => $e->getMessage(),
                    ];
                }
            }

            DB::commit();

            // Log bulk action
            activity('License')
                ->event('bulk_reactivated')
                ->withProperties([
                    'successful_count' => count($results['successful']),
                    'failed_count' => count($results['failed']),
                    'license_ids' => $licenseIds->toArray(),
                ])
                ->log('Bulk reactivated ' . count($results['successful']) . ' licenses');

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $results;
    }

    /**
     * Bulk delete multiple licenses.
     */
    public function bulkDelete(Collection $licenseIds, string $reason = ''): array
    {
        $results = ['successful' => [], 'failed' => []];

        DB::beginTransaction();

        try {
            $licenses = LicenseAttributed::whereIn('id', $licenseIds)->get();

            foreach ($licenses as $license) {
                try {
                    $this->manageLicenseAction->deleteLicense($license, $reason);
                    $results['successful'][] = $license->id;
                } catch (Exception $e) {
                    $results['failed'][] = [
                        'license_id' => $license->id,
                        'error' => $e->getMessage(),
                    ];
                }
            }

            DB::commit();

            // Log bulk action
            activity('License')
                ->event('bulk_deleted')
                ->withProperties([
                    'successful_count' => count($results['successful']),
                    'failed_count' => count($results['failed']),
                    'reason' => $reason,
                    'license_ids' => $licenseIds->toArray(),
                ])
                ->log('Bulk deleted ' . count($results['successful']) . ' licenses');

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $results;
    }

    /**
     * Bulk update expiration dates.
     */
    public function bulkUpdateExpiration(Collection $licenseIds, string $newExpirationDate): array
    {
        $results = ['successful' => [], 'failed' => []];

        DB::beginTransaction();

        try {
            $licenses = LicenseAttributed::whereIn('id', $licenseIds)->get();

            foreach ($licenses as $license) {
                try {
                    $this->manageLicenseAction->updateExpiration($license, $newExpirationDate);
                    $results['successful'][] = $license->id;
                } catch (Exception $e) {
                    $results['failed'][] = [
                        'license_id' => $license->id,
                        'error' => $e->getMessage(),
                    ];
                }
            }

            DB::commit();

            // Log bulk action
            activity('License')
                ->event('bulk_expiration_updated')
                ->withProperties([
                    'successful_count' => count($results['successful']),
                    'failed_count' => count($results['failed']),
                    'new_expiration' => $newExpirationDate,
                    'license_ids' => $licenseIds->toArray(),
                ])
                ->log('Bulk updated expiration for ' . count($results['successful']) . ' licenses');

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $results;
    }

    /**
     * Send bulk notifications to license holders.
     */
    public function sendBulkNotifications(Collection $licenseIds, string $message, string $subject = 'License Notification'): array
    {
        $results = ['successful' => [], 'failed' => []];

        $licenses = LicenseAttributed::whereIn('id', $licenseIds)
            ->with('owner')
            ->get();

        foreach ($licenses as $license) {
            try {
                // This would integrate with your notification system
                // For now, we'll just log the notification
                activity('License')
                    ->performedOn($license)
                    ->event('notification_sent')
                    ->withProperties([
                        'subject' => $subject,
                        'message' => $message,
                        'recipient' => $license->holder_name,
                    ])
                    ->log('Notification sent to license holder');

                $results['successful'][] = $license->id;
            } catch (Exception $e) {
                $results['failed'][] = [
                    'license_id' => $license->id,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }
}
