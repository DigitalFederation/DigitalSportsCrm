<?php

namespace Domain\Licenses\Actions;

use Carbon\Carbon;
use Domain\Federations\Models\Federation;
use Domain\Licenses\Models\LicenseAttributed;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class ExportLicensesAction
{
    private ViewLicenseRequestsAction $viewLicenseRequestsAction;

    public function __construct(ViewLicenseRequestsAction $viewLicenseRequestsAction)
    {
        $this->viewLicenseRequestsAction = $viewLicenseRequestsAction;
    }

    /**
     * Export licenses to CSV format.
     *
     * @param  mixed  $viewer  The viewer (Federation, Entity, or Individual)
     * @return string File path of the exported CSV
     */
    public function exportToCsv($viewer, array $filters = [], array $columns = []): string
    {
        // Get all licenses (not paginated)
        $licenses = $this->getAllLicenses($viewer, $filters);

        // Define default columns if none specified
        if (empty($columns)) {
            $columns = $this->getDefaultColumns();
        }

        // Generate CSV content
        $csvContent = $this->generateCsvContent($licenses, $columns);

        // Save to storage
        $filename = $this->generateFilename('csv');
        $filePath = "exports/licenses/{$filename}";

        Storage::disk('local')->put($filePath, $csvContent);

        // Log export activity
        activity('License')
            ->event('exported')
            ->withProperties([
                'format' => 'csv',
                'filename' => $filename,
                'record_count' => $licenses->count(),
                'filters' => $filters,
            ])
            ->log('Licenses exported to CSV');

        return Storage::disk('local')->path($filePath);
    }

    /**
     * Export licenses to Excel format.
     *
     * @param  mixed  $viewer
     * @return string File path of the exported Excel file
     */
    public function exportToExcel($viewer, array $filters = [], array $columns = []): string
    {
        // For now, we'll generate CSV format
        // In a real implementation, you'd use a library like PhpSpreadsheet
        $licenses = $this->getAllLicenses($viewer, $filters);

        if (empty($columns)) {
            $columns = $this->getDefaultColumns();
        }

        $csvContent = $this->generateCsvContent($licenses, $columns);

        $filename = $this->generateFilename('xlsx');
        $filePath = "exports/licenses/{$filename}";

        Storage::disk('local')->put($filePath, $csvContent);

        activity('License')
            ->event('exported')
            ->withProperties([
                'format' => 'excel',
                'filename' => $filename,
                'record_count' => $licenses->count(),
                'filters' => $filters,
            ])
            ->log('Licenses exported to Excel');

        return Storage::disk('local')->path($filePath);
    }

    /**
     * Export licenses to JSON format.
     *
     * @param  mixed  $viewer
     * @return string File path of the exported JSON file
     */
    public function exportToJson($viewer, array $filters = [], bool $includeRelations = false): string
    {
        $licenses = $this->getAllLicenses($viewer, $filters, $includeRelations);

        $jsonData = [
            'export_info' => [
                'generated_at' => now()->toISOString(),
                'viewer_type' => get_class($viewer),
                'record_count' => $licenses->count(),
                'filters' => $filters,
            ],
            'licenses' => $licenses->toArray(),
        ];

        $jsonContent = json_encode($jsonData, JSON_PRETTY_PRINT);

        $filename = $this->generateFilename('json');
        $filePath = "exports/licenses/{$filename}";

        Storage::disk('local')->put($filePath, $jsonContent);

        activity('License')
            ->event('exported')
            ->withProperties([
                'format' => 'json',
                'filename' => $filename,
                'record_count' => $licenses->count(),
                'filters' => $filters,
            ])
            ->log('Licenses exported to JSON');

        return Storage::disk('local')->path($filePath);
    }

    /**
     * Generate a summary report with analytics.
     *
     * @param  mixed  $viewer
     * @return string File path of the generated report
     */
    public function generateSummaryReport($viewer, array $filters = []): string
    {
        $licenses = $this->getAllLicenses($viewer, $filters);

        // Get analytics data
        $analyticsAction = new LicenseAnalyticsAction;
        $federation = $viewer instanceof Federation ? $viewer : null;
        $analytics = $analyticsAction->getLicenseStatistics($federation, $filters);

        // Generate report content
        $reportContent = $this->generateReportContent($licenses, $analytics, $filters);

        $filename = $this->generateFilename('txt', 'summary_report');
        $filePath = "exports/licenses/{$filename}";

        Storage::disk('local')->put($filePath, $reportContent);

        activity('License')
            ->event('report_generated')
            ->withProperties([
                'filename' => $filename,
                'record_count' => $licenses->count(),
                'filters' => $filters,
            ])
            ->log('License summary report generated');

        return Storage::disk('local')->path($filePath);
    }

    /**
     * Get all licenses without pagination.
     */
    private function getAllLicenses($viewer, array $filters = [], bool $includeRelations = false): Collection
    {
        $query = LicenseAttributed::query();

        // Apply role-based filtering (same as ViewLicenseRequestsAction)
        if ($viewer instanceof Federation) {
            $query->where('federation_id', $viewer->id);
        } elseif ($viewer instanceof \Domain\Entities\Models\Entity) {
            $query->where(function ($q) use ($viewer) {
                $q->where(function ($sub) use ($viewer) {
                    $sub->where('model_type', 'entity')
                        ->where('model_id', $viewer->id);
                })
                    ->orWhere('requested_by_id', $viewer->id);
            });
        } elseif ($viewer instanceof \Domain\Individuals\Models\Individual) {
            $query->where('model_type', 'individual')
                ->where('model_id', $viewer->id);
        }

        // Apply filters (same logic as ViewLicenseRequestsAction)
        $this->applyFilters($query, $filters);

        // Include relations if requested
        if ($includeRelations) {
            $query->with(['license', 'license.committee', 'federation', 'owner', 'requestedBy']);
        }

        return $query->get();
    }

    /**
     * Apply filters to query.
     */
    private function applyFilters($query, array $filters): void
    {
        if (! empty($filters['status'])) {
            $query->licenseAttributedStatus($filters['status']);
        }

        if (! empty($filters['license_type_id'])) {
            $query->whereHas('license', function ($q) use ($filters) {
                $q->where('type_id', $filters['license_type_id']);
            });
        }

        if (! empty($filters['holder_type'])) {
            $query->holderType($filters['holder_type']);
        }

        if (! empty($filters['request_type'])) {
            if ($filters['request_type'] === 'direct') {
                $query->directRequests();
            } elseif ($filters['request_type'] === 'entity_group') {
                $query->entityGroupRequests();
            }
        }

        if (! empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->holderName($search)
                    ->orWhere('license_name', 'like', "%{$search}%")
                    ->orWhere('license_number', 'like', "%{$search}%");
            });
        }
    }

    /**
     * Get default columns for export.
     */
    private function getDefaultColumns(): array
    {
        return [
            'id' => 'License ID',
            'license_name' => 'License Name',
            'holder_name' => 'Holder Name',
            'federation_name' => 'Federation',
            'status_class' => 'Status',
            'request_type' => 'Request Type',
            'total_value' => 'Value',
            'date_begin' => 'Start Date',
            'date_expire' => 'Expiration Date',
            'activated_at' => 'Activated At',
            'purchased_at' => 'Purchased At',
            'created_at' => 'Created At',
        ];
    }

    /**
     * Generate CSV content.
     */
    private function generateCsvContent(Collection $licenses, array $columns): string
    {
        $csv = [];

        // Add header row
        $csv[] = implode(',', array_values($columns));

        // Add data rows
        foreach ($licenses as $license) {
            $row = [];
            foreach (array_keys($columns) as $field) {
                $value = $license->{$field} ?? '';

                // Format special fields
                if (in_array($field, ['date_begin', 'date_expire', 'activated_at', 'purchased_at', 'created_at']) && $value) {
                    $value = Carbon::parse($value)->format('Y-m-d H:i:s');
                }

                if ($field === 'status_class') {
                    $value = class_basename($value);
                }

                // Escape CSV values
                $value = str_replace('"', '""', $value);
                if (strpos($value, ',') !== false || strpos($value, '"') !== false) {
                    $value = '"' . $value . '"';
                }

                $row[] = $value;
            }
            $csv[] = implode(',', $row);
        }

        return implode("\n", $csv);
    }

    /**
     * Generate summary report content.
     */
    private function generateReportContent(Collection $licenses, array $analytics, array $filters): string
    {
        $content = [];
        $content[] = 'LICENSE SUMMARY REPORT';
        $content[] = 'Generated: ' . now()->format('Y-m-d H:i:s');
        $content[] = '=' . str_repeat('=', 50);
        $content[] = '';

        // Overview statistics
        $content[] = 'OVERVIEW STATISTICS';
        $content[] = '-' . str_repeat('-', 20);
        foreach ($analytics['overview'] as $key => $value) {
            $content[] = ucwords(str_replace('_', ' ', $key)) . ': ' . $value;
        }
        $content[] = '';

        // Status breakdown
        $content[] = 'STATUS BREAKDOWN';
        $content[] = '-' . str_repeat('-', 20);
        foreach ($analytics['by_status'] as $status) {
            $content[] = $status['status'] . ': ' . $status['count'] . ' (' . $status['percentage'] . '%)';
        }
        $content[] = '';

        // Revenue statistics
        $content[] = 'REVENUE STATISTICS';
        $content[] = '-' . str_repeat('-', 20);
        foreach ($analytics['revenue'] as $key => $value) {
            $content[] = ucwords(str_replace('_', ' ', $key)) . ': $' . number_format($value, 2);
        }
        $content[] = '';

        // Applied filters
        if (! empty($filters)) {
            $content[] = 'APPLIED FILTERS';
            $content[] = '-' . str_repeat('-', 20);
            foreach ($filters as $key => $value) {
                $content[] = ucwords(str_replace('_', ' ', $key)) . ': ' . $value;
            }
            $content[] = '';
        }

        $content[] = 'Total Records: ' . $licenses->count();

        return implode("\n", $content);
    }

    /**
     * Generate filename for export.
     */
    private function generateFilename(string $extension, string $prefix = 'licenses'): string
    {
        $timestamp = now()->format('Y-m-d_H-i-s');

        return "{$prefix}_export_{$timestamp}.{$extension}";
    }
}
