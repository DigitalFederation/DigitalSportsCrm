<?php

namespace Domain\Invoicing\Actions;

use Domain\Invoicing\Models\MoloniSetting;
use Domain\Invoicing\Models\MoloniSyncLog;
use Domain\Invoicing\Services\MoloniClient;
use Domain\Invoicing\Services\MoloniSettingsService;
use Illuminate\Support\Facades\Log;

class SyncMoloniDataAction
{
    public function __construct(
        private MoloniClient $client,
        private MoloniSettingsService $settingsService
    ) {}

    public function __invoke(): array
    {
        $results = [];
        $startTime = microtime(true);

        Log::info('SyncMoloniDataAction: Starting Moloni data sync');

        try {
            $results['companies'] = $this->syncCompanies();
            $results['document_sets'] = $this->syncDocumentSets();
            $results['taxes'] = $this->syncTaxes();
            $results['tax_exemptions'] = $this->syncTaxExemptions();
            $results['units'] = $this->syncUnits();
            $results['categories'] = $this->syncCategories();
            $results['payment_methods'] = $this->syncPaymentMethods();
            $results['maturity_dates'] = $this->syncMaturityDates();

            $durationMs = (int) ((microtime(true) - $startTime) * 1000);

            MoloniSyncLog::logSuccess('data_sync', $results, $durationMs);

            Log::info('SyncMoloniDataAction: Sync completed successfully', $results);

            return $results;

        } catch (\Exception $e) {
            $durationMs = (int) ((microtime(true) - $startTime) * 1000);

            Log::error('SyncMoloniDataAction: Sync failed', [
                'error' => $e->getMessage(),
                'partial_results' => $results,
            ]);

            MoloniSyncLog::logFailure('data_sync', $e->getMessage(), $results, $durationMs);

            throw $e;
        }
    }

    private function syncCompanies(): int
    {
        $companies = $this->client->post('companies/getAll/');

        if (! empty($companies) && isset($companies[0]['company_id'])) {
            $formattedCompanies = array_map(function ($company) {
                return [
                    'id' => $company['company_id'],
                    'name' => $company['name'] ?? 'Unknown',
                    'vat' => $company['vat'] ?? null,
                ];
            }, $companies);

            MoloniSetting::setValue('companies_cache', $formattedCompanies, 'json');

            if (! $this->settingsService->getCompanyId() && isset($companies[0]['company_id'])) {
                MoloniSetting::setValue('company_id', $companies[0]['company_id'], 'int');
            }
        }

        return count($companies);
    }

    private function syncDocumentSets(): int
    {
        $documentSets = $this->client->post('documentSets/getAll/');

        Log::debug('SyncMoloniDataAction: Document sets API response', [
            'company_id' => $this->settingsService->getCompanyId(),
            'count' => is_array($documentSets) ? count($documentSets) : 0,
            'raw_response' => $documentSets,
        ]);

        if (! empty($documentSets)) {
            $formatted = array_values(array_map(function ($set) {
                // Check if this document set has AT codes (required for certified invoices in Portugal)
                $hasAtCodes = ! empty($set['document_set_at_codes']);

                return [
                    'document_set_id' => $set['document_set_id'],
                    'id' => $set['document_set_id'], // Keep for backwards compatibility
                    'name' => $set['name'] ?? 'Unknown',
                    'abbreviation' => $set['abbreviation'] ?? null,
                    'for_invoice_receipt' => (bool) ($set['for_invoice_receipt'] ?? false),
                    'has_at_codes' => $hasAtCodes,
                    'is_valid_for_invoices' => $hasAtCodes, // Document sets need AT codes to create certified invoices
                ];
            }, array_filter($documentSets, fn ($set) => isset($set['document_set_id']))));

            MoloniSetting::setValue('document_sets_cache', $formatted, 'json');

            // Log which sets are valid for invoices
            $validSets = array_filter($formatted, fn ($set) => $set['is_valid_for_invoices']);
            $invalidSets = array_filter($formatted, fn ($set) => ! $set['is_valid_for_invoices']);

            Log::info('SyncMoloniDataAction: Document sets synced', [
                'count' => count($formatted),
                'sets' => array_column($formatted, 'name'),
                'valid_for_invoices' => array_map(fn ($s) => $s['name'] . ' (' . $s['document_set_id'] . ')', $validSets),
                'not_valid_for_invoices' => array_map(fn ($s) => $s['name'] . ' (' . $s['document_set_id'] . ')', $invalidSets),
            ]);
        } else {
            Log::warning('SyncMoloniDataAction: No document sets returned from Moloni API', [
                'company_id' => $this->settingsService->getCompanyId(),
            ]);
        }

        return is_array($documentSets) ? count($documentSets) : 0;
    }

    private function syncTaxes(): int
    {
        $taxes = $this->client->post('taxes/getAll/');

        if (! empty($taxes)) {
            $formatted = array_map(function ($tax) {
                return [
                    'id' => $tax['tax_id'],
                    'name' => $tax['name'] ?? 'Unknown',
                    'value' => $tax['value'] ?? 0,
                    'type' => $tax['type'] ?? null,
                ];
            }, $taxes);

            MoloniSetting::setValue('taxes_cache', $formatted, 'json');
        }

        return count($taxes);
    }

    private function syncUnits(): int
    {
        $units = $this->client->post('measurementUnits/getAll/');

        if (! empty($units)) {
            $formatted = array_map(function ($unit) {
                return [
                    'id' => $unit['unit_id'],
                    'name' => $unit['name'] ?? 'Unknown',
                    'abbreviation' => $unit['abbreviation'] ?? null,
                ];
            }, $units);

            MoloniSetting::setValue('units_cache', $formatted, 'json');
        }

        return count($units);
    }

    private function syncCategories(): int
    {
        // Moloni API requires parent_id parameter - start with root categories (parent_id = 0)
        $categories = $this->client->post('productCategories/getAll/', [
            'parent_id' => 0,
        ]);

        Log::debug('SyncMoloniDataAction: Categories API response', [
            'company_id' => $this->settingsService->getCompanyId(),
            'count' => is_array($categories) ? count($categories) : 0,
            'raw_response' => $categories,
        ]);

        if (! empty($categories)) {
            $formatted = $this->flattenCategories($categories);
            MoloniSetting::setValue('categories_cache', $formatted, 'json');

            Log::info('SyncMoloniDataAction: Categories synced successfully', [
                'count' => count($formatted),
                'categories' => array_column($formatted, 'name'),
            ]);
        } else {
            Log::warning('SyncMoloniDataAction: No categories returned from Moloni API', [
                'company_id' => $this->settingsService->getCompanyId(),
            ]);
        }

        return is_array($categories) ? count($categories) : 0;
    }

    private function flattenCategories(array $categories, int $level = 0, string $prefix = ''): array
    {
        $result = [];

        foreach ($categories as $category) {
            $name = $prefix . ($category['name'] ?? 'Unknown');

            $result[] = [
                'id' => $category['category_id'],
                'name' => $name,
                'level' => $level,
            ];

            if (! empty($category['child_categories'])) {
                $children = $this->flattenCategories(
                    $category['child_categories'],
                    $level + 1,
                    $name . ' > '
                );
                $result = array_merge($result, $children);
            }
        }

        return $result;
    }

    private function syncPaymentMethods(): int
    {
        $paymentMethods = $this->client->post('paymentMethods/getAll/');

        if (! empty($paymentMethods)) {
            $formatted = array_map(function ($method) {
                return [
                    'id' => $method['payment_method_id'],
                    'name' => $method['name'] ?? 'Unknown',
                ];
            }, $paymentMethods);

            MoloniSetting::setValue('payment_methods_cache', $formatted, 'json');
        }

        return count($paymentMethods);
    }

    private function syncMaturityDates(): int
    {
        $maturityDates = $this->client->post('maturityDates/getAll/');

        if (! empty($maturityDates)) {
            $formatted = array_map(function ($date) {
                return [
                    'id' => $date['maturity_date_id'],
                    'name' => $date['name'] ?? 'Unknown',
                    'days' => $date['days'] ?? 0,
                ];
            }, $maturityDates);

            MoloniSetting::setValue('maturity_dates_cache', $formatted, 'json');

            if (! $this->settingsService->getDefaultMaturityDateId() && isset($maturityDates[0]['maturity_date_id'])) {
                MoloniSetting::setValue('default_maturity_date_id', $maturityDates[0]['maturity_date_id'], 'int');
            }
        }

        return count($maturityDates);
    }

    private function syncTaxExemptions(): int
    {
        $exemptions = $this->client->post('taxExemptions/getAll/');

        if (! empty($exemptions)) {
            $formatted = array_map(function ($exemption) {
                return [
                    'code' => $exemption['code'] ?? '',
                    'name' => $exemption['name'] ?? 'Unknown',
                    'description' => $exemption['description'] ?? '',
                ];
            }, $exemptions);

            MoloniSetting::setValue('tax_exemptions_cache', $formatted, 'json');

            Log::info('SyncMoloniDataAction: Tax exemptions synced', [
                'count' => count($formatted),
            ]);
        }

        return is_array($exemptions) ? count($exemptions) : 0;
    }
}
