<?php

namespace Domain\Invoicing\Services;

use Domain\Documents\Models\Document;
use Domain\Invoicing\Exceptions\MoloniNotConfiguredException;
use Domain\Invoicing\Models\MoloniInvoice;
use Domain\Invoicing\Models\MoloniSyncLog;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MoloniInvoiceService
{
    public function __construct(
        private MoloniClient $client,
        private MoloniCustomerService $customerService,
        private MoloniSettingsService $settingsService
    ) {}

    public function createInvoiceReceipt(Document $document): MoloniInvoice
    {
        $documentCurrency = $document->currency ?? config('app.currency', 'EUR');
        if ($documentCurrency !== 'EUR') {
            Log::warning('MoloniInvoiceService: skipped — Moloni only supports EUR invoicing', [
                'document_id' => $document->id,
                'currency' => $documentCurrency,
            ]);

            throw new MoloniNotConfiguredException('Moloni invoicing only supports EUR.');
        }

        $existingInvoice = MoloniInvoice::findByDocument($document->id);
        if ($existingInvoice) {
            Log::info('MoloniInvoiceService: Invoice already exists for document', [
                'document_id' => $document->id,
                'moloni_invoice_id' => $existingInvoice->moloni_document_id,
            ]);

            return $existingInvoice;
        }

        if (! $this->settingsService->isConfigured()) {
            throw new MoloniNotConfiguredException;
        }

        $startTime = microtime(true);

        $document->loadMissing(['owner', 'details', 'method']);

        if ($document->details->isEmpty()) {
            throw new \RuntimeException(
                'Cannot create Moloni invoice: document has no details/line items. Document ID: ' . $document->id
            );
        }

        $customerId = $this->customerService->findOrCreate($document->owner);

        $invoiceData = $this->buildInvoiceData($document, $customerId);

        // Determine which document type to use based on settings
        $useInvoiceReceipts = $this->settingsService->useInvoiceReceipts();
        $endpoint = $useInvoiceReceipts ? 'invoiceReceipts/insert/' : 'invoices/insert/';
        $documentType = $useInvoiceReceipts ? 'invoice-receipt' : 'invoice';

        Log::info('MoloniInvoiceService: Creating ' . $documentType . ' in Moloni', [
            'document_id' => $document->id,
            'document_number' => $document->number_extended,
            'total' => $document->total_value,
            'document_type' => $documentType,
        ]);

        $response = $this->client->post($endpoint, $invoiceData);

        $durationMs = (int) ((microtime(true) - $startTime) * 1000);

        if (! isset($response['document_id'])) {
            Log::error('MoloniInvoiceService: Failed to create invoice', [
                'document_id' => $document->id,
                'response' => $response,
            ]);

            MoloniSyncLog::logFailure('invoice_create', 'No document_id in response', [
                'document_id' => $document->id,
                'response' => $response,
            ], $durationMs);

            throw new \RuntimeException('Failed to create Moloni invoice: no document_id returned');
        }

        try {
            $moloniInvoice = MoloniInvoice::create([
                'document_id' => $document->id,
                'moloni_document_id' => $response['document_id'],
                'moloni_document_set_id' => $invoiceData['document_set_id'],
                'moloni_number' => $response['document_number'] ?? $response['number'] ?? 'PENDING',
                'moloni_status' => ($response['status'] ?? 0) == 1 ? 'closed' : 'draft',
                'moloni_total' => $document->total_value,
                'currency' => $document->currency ?? config('app.currency', 'EUR'),
                'moloni_response' => $response,
                'synced_at' => now(),
            ]);
        } catch (UniqueConstraintViolationException $e) {
            $existingInvoice = MoloniInvoice::findByDocument($document->id);

            if ($existingInvoice) {
                Log::warning('MoloniInvoiceService: Invoice was created by another process', [
                    'document_id' => $document->id,
                    'moloni_invoice_id' => $existingInvoice->moloni_document_id,
                    'discarded_moloni_document_id' => $response['document_id'],
                ]);

                return $existingInvoice;
            }

            throw $e;
        }

        MoloniSyncLog::logSuccess('invoice_create', [
            'document_id' => $document->id,
            'moloni_document_id' => $response['document_id'],
            'moloni_number' => $moloniInvoice->moloni_number,
        ], $durationMs);

        Log::info('MoloniInvoiceService: Invoice-receipt created successfully', [
            'document_id' => $document->id,
            'moloni_document_id' => $response['document_id'],
            'moloni_number' => $moloniInvoice->moloni_number,
        ]);

        return $moloniInvoice;
    }

    private function buildInvoiceData(Document $document, int $customerId): array
    {
        // Get document set based on document details' owner type, or fall back to default
        $documentSetId = $this->settingsService->getDocumentSetIdForDocument($document);
        $defaultDocSetId = $this->settingsService->getDocumentSetId();

        // Get document set info from cache for validation and logging
        $documentSetsCache = $this->settingsService->getDocumentSetsCache();
        $selectedDocSetName = 'UNKNOWN';
        $selectedDocSetIsValid = true; // Default true for backwards compatibility
        $defaultDocSetName = 'UNKNOWN';

        foreach ($documentSetsCache as $docSet) {
            $docSetId = $docSet['document_set_id'] ?? $docSet['id'] ?? null;
            if ($docSetId == $documentSetId) {
                $selectedDocSetName = $docSet['name'] ?? 'UNKNOWN';
                $selectedDocSetIsValid = $docSet['is_valid_for_invoices'] ?? $docSet['has_at_codes'] ?? true;
            }
            if ($docSetId == $defaultDocSetId) {
                $defaultDocSetName = $docSet['name'] ?? 'UNKNOWN';
            }
        }

        Log::info('MoloniInvoiceService: FINAL DOCUMENT SET SELECTION', [
            'document_id' => $document->id,
            'document_number' => $document->number_extended ?? $document->id,
            'selected_document_set_id' => $documentSetId,
            'selected_document_set_name' => $selectedDocSetName,
            'selected_document_set_is_valid' => $selectedDocSetIsValid,
            'default_document_set_id' => $defaultDocSetId,
            'default_document_set_name' => $defaultDocSetName,
            'is_using_default' => $documentSetId === $defaultDocSetId,
            'company_id' => $this->settingsService->getCompanyId(),
            'customer_id' => $customerId,
            'available_document_sets' => array_map(fn ($s) => [
                'id' => $s['document_set_id'] ?? $s['id'] ?? 'N/A',
                'name' => $s['name'] ?? 'N/A',
                'valid' => $s['is_valid_for_invoices'] ?? $s['has_at_codes'] ?? 'unknown',
            ], $documentSetsCache),
        ]);

        // Warn if document set might not be valid
        if (! $selectedDocSetIsValid && $selectedDocSetName !== 'UNKNOWN') {
            Log::warning('MoloniInvoiceService: SELECTED DOCUMENT SET MAY NOT BE VALID FOR INVOICES', [
                'document_id' => $document->id,
                'document_set_id' => $documentSetId,
                'document_set_name' => $selectedDocSetName,
                'hint' => 'This document set may not have AT codes configured in Moloni. Check Moloni settings.',
            ]);
        }

        Log::debug('MoloniInvoiceService: Building invoice data', [
            'document_id' => $document->id,
            'company_id' => $this->settingsService->getCompanyId(),
            'document_set_id' => $documentSetId,
            'customer_id' => $customerId,
        ]);

        // Determine invoice status: 0 = draft, 1 = closed/finalized
        $invoiceStatus = $this->settingsService->createAsDraft() ? 0 : 1;

        $data = [
            'document_set_id' => $documentSetId,
            'date' => now()->format('Y-m-d'),
            'expiration_date' => now()->format('Y-m-d'),
            'customer_id' => $customerId,
            'our_reference' => Str::limit($document->number_extended ?? '', 50, ''),
            'your_reference' => Str::limit($document->number_extended ?? '', 50, ''),
            'products' => $this->buildProductLines($document),
            'status' => $invoiceStatus,
        ];

        // Only add payments for invoice receipts (Fatura-Recibo)
        // Regular invoices (Fatura) don't include payment data
        if ($this->settingsService->useInvoiceReceipts()) {
            $data['payments'] = [
                [
                    'payment_method_id' => $this->resolvePaymentMethod($document),
                    'date' => now()->format('Y-m-d'),
                    'value' => (float) $document->total_value,
                ],
            ];
        }

        return $data;
    }

    private function buildProductLines(Document $document): array
    {
        $lines = [];
        $defaultTaxId = $this->settingsService->getDefaultTaxId();
        $exemptTaxId = $this->settingsService->getExemptTaxId();
        $defaultExemptionReason = $this->settingsService->getDefaultExemptionReason();

        foreach ($document->details as $detail) {
            $quantity = $detail->quantity ?? 1;
            $unitValue = (float) ($detail->unit_value ?? $detail->total_value / max(1, $quantity));
            $taxPercentage = (float) ($detail->tax_percentage ?? 23);
            $isExempt = $taxPercentage == 0;

            $line = [
                'product_id' => $this->findOrCreateProduct($detail),
                'name' => Str::limit($detail->description ?? 'Service', 200, ''),
                'qty' => $quantity,
                'price' => $unitValue,
                'discount' => 0,
            ];

            if ($isExempt) {
                // For exempt products: use exempt tax ID if configured, otherwise no taxes
                if ($exemptTaxId) {
                    $line['taxes'] = [
                        [
                            'tax_id' => $exemptTaxId,
                            'value' => 0,
                        ],
                    ];
                }
                // Add exemption reason (required by Moloni for exempt products)
                if ($defaultExemptionReason) {
                    $line['exemption_reason'] = $defaultExemptionReason;
                }
            } else {
                // For taxed products: use the default tax ID with the tax percentage
                $line['taxes'] = [
                    [
                        'tax_id' => $defaultTaxId,
                        'value' => $taxPercentage,
                    ],
                ];
            }

            $lines[] = $line;
        }

        return $lines;
    }

    private function findOrCreateProduct($detail): int
    {
        $reference = Str::limit($detail->reference ?? 'SRV-' . Str::uuid()->toString(), 50, '');

        try {
            $existingProducts = $this->client->post('products/getByReference/', [
                'reference' => $reference,
            ]);

            // API returns an array of products, check first result
            if (! empty($existingProducts) && isset($existingProducts[0]['product_id'])) {
                Log::debug('MoloniInvoiceService: Found existing product in Moloni', [
                    'reference' => $reference,
                    'product_id' => $existingProducts[0]['product_id'],
                ]);

                return $existingProducts[0]['product_id'];
            }

            Log::debug('MoloniInvoiceService: Product not found by reference', [
                'reference' => $reference,
                'response' => $existingProducts,
            ]);
        } catch (\Exception $e) {
            Log::debug('MoloniInvoiceService: Error searching for product, will try to create', [
                'reference' => $reference,
                'error' => $e->getMessage(),
            ]);
        }

        $categoryId = $this->settingsService->getDefaultCategoryId();
        if (! $categoryId) {
            throw new \RuntimeException(
                'Cannot create product in Moloni: no default category configured. ' .
                'Either configure a default category in Moloni settings, or ensure the product ' .
                'already exists in Moloni with reference: ' . $reference
            );
        }

        $unitId = $this->settingsService->getDefaultUnitId();
        if (! $unitId) {
            throw new \RuntimeException(
                'Cannot create product in Moloni: no default unit configured. ' .
                'Either configure a default unit in Moloni settings, or ensure the product ' .
                'already exists in Moloni with reference: ' . $reference
            );
        }

        $taxPercentage = (float) ($detail->tax_percentage ?? 23);
        $isExempt = $taxPercentage == 0;

        $productData = [
            'category_id' => $categoryId,
            'type' => 2,
            'name' => Str::limit($detail->description ?? 'Service', 100, ''),
            'reference' => $reference,
            'price' => (float) ($detail->unit_value ?? $detail->total_value),
            'unit_id' => $unitId,
            'has_stock' => 0,
        ];

        if ($isExempt) {
            // For exempt products: use exempt tax ID if configured
            $exemptTaxId = $this->settingsService->getExemptTaxId();
            if ($exemptTaxId) {
                $productData['taxes'] = [
                    [
                        'tax_id' => $exemptTaxId,
                        'value' => 0,
                        'order' => 0,
                        'cumulative' => 0,
                    ],
                ];
            }
            // Add exemption reason (required by Moloni for exempt products)
            $exemptionReason = $this->settingsService->getDefaultExemptionReason();
            if ($exemptionReason) {
                $productData['exemption_reason'] = $exemptionReason;
            }
        } else {
            // For taxed products: use the default tax ID
            $productData['taxes'] = [
                [
                    'tax_id' => $this->settingsService->getDefaultTaxId(),
                    'value' => $taxPercentage,
                    'order' => 0,
                    'cumulative' => 0,
                ],
            ];
        }

        $response = $this->client->post('products/insert/', $productData);

        if (! isset($response['product_id'])) {
            throw new \RuntimeException('Failed to create Moloni product');
        }

        return $response['product_id'];
    }

    private function resolvePaymentMethod(Document $document): int
    {
        $paymentMethodId = $this->settingsService->resolvePaymentMethodForDocument($document);

        if (! $paymentMethodId) {
            throw new \RuntimeException(
                'Cannot create invoice in Moloni: no payment method could be resolved. ' .
                'Please configure a default payment method in Moloni settings.'
            );
        }

        return $paymentMethodId;
    }

    public function getInvoice(int $moloniDocumentId): array
    {
        $endpoint = $this->settingsService->useInvoiceReceipts()
            ? 'invoiceReceipts/getOne/'
            : 'invoices/getOne/';

        return $this->client->post($endpoint, [
            'document_id' => $moloniDocumentId,
        ]);
    }

    public function getPdfLink(int $moloniDocumentId): ?string
    {
        try {
            $response = $this->client->post('documents/getPDFLink/', [
                'document_id' => $moloniDocumentId,
            ]);

            return $response['url'] ?? null;
        } catch (\Exception $e) {
            Log::warning('MoloniInvoiceService: Failed to get PDF link', [
                'moloni_document_id' => $moloniDocumentId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function refreshInvoiceStatus(MoloniInvoice $moloniInvoice): MoloniInvoice
    {
        try {
            $response = $this->getInvoice($moloniInvoice->moloni_document_id);

            if (isset($response['status'])) {
                $moloniInvoice->update([
                    'moloni_status' => $response['status'] == 1 ? 'closed' : 'draft',
                    'moloni_number' => $response['document_number'] ?? $response['number'] ?? $moloniInvoice->moloni_number,
                    'moloni_response' => $response,
                ]);
            }

            return $moloniInvoice->fresh();
        } catch (\Exception $e) {
            Log::warning('MoloniInvoiceService: Failed to refresh invoice status', [
                'moloni_invoice_id' => $moloniInvoice->id,
                'error' => $e->getMessage(),
            ]);

            return $moloniInvoice;
        }
    }
}
