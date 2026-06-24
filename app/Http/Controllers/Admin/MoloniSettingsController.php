<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Domain\Documents\Models\Document;
use Domain\Documents\States\PaidDocumentState;
use Domain\Invoicing\Actions\CreateMoloniInvoiceReceiptAction;
use Domain\Invoicing\Actions\SyncMoloniDataAction;
use Domain\Invoicing\Models\MoloniCustomer;
use Domain\Invoicing\Models\MoloniInvoice;
use Domain\Invoicing\Models\MoloniSyncLog;
use Domain\Invoicing\Services\MoloniAuthService;
use Domain\Invoicing\Services\MoloniClient;
use Domain\Invoicing\Services\MoloniCustomerService;
use Domain\Invoicing\Services\MoloniInvoiceService;
use Domain\Invoicing\Services\MoloniSettingsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MoloniSettingsController extends Controller
{
    public function __construct(
        private MoloniSettingsService $settingsService,
        private MoloniAuthService $authService
    ) {}

    public function index(): View
    {
        $token = $this->settingsService->getToken();
        $isConnected = $token && $token->isAccessTokenValid();
        $hasCredentials = $this->settingsService->hasCredentials();
        $isConfigured = $this->settingsService->isConfigured();
        $isEnabled = $this->settingsService->isEnabled();

        $companies = $this->settingsService->getCompaniesCache();
        $documentSets = $this->settingsService->getDocumentSetsCache();
        $taxes = $this->settingsService->getTaxesCache();
        $taxExemptions = $this->settingsService->getTaxExemptionsCache();
        $units = $this->settingsService->getUnitsCache();
        $categories = $this->settingsService->getCategoriesCache();
        $paymentMethods = $this->settingsService->getPaymentMethodsCache();
        $maturityDates = $this->settingsService->getMaturityDatesCache();

        $currentSettings = [
            'company_id' => $this->settingsService->getCompanyId(),
            'document_set_id' => $this->settingsService->getDocumentSetId(),
            'default_tax_id' => $this->settingsService->getDefaultTaxId(),
            'exempt_tax_id' => $this->settingsService->getExemptTaxId(),
            'default_exemption_reason' => $this->settingsService->getDefaultExemptionReason(),
            'default_unit_id' => $this->settingsService->getDefaultUnitId(),
            'default_category_id' => $this->settingsService->getDefaultCategoryId(),
            'payment_method_id' => $this->settingsService->getPaymentMethodId(),
            'default_maturity_date_id' => $this->settingsService->getDefaultMaturityDateId(),
            'document_set_mappings' => $this->settingsService->getDocumentSetMappings(),
            'use_invoice_receipts' => $this->settingsService->useInvoiceReceipts(),
            'create_as_draft' => $this->settingsService->createAsDraft(),
        ];

        $ownerTypeLabels = MoloniSettingsService::getOwnerTypeLabels();
        $committeeLabels = MoloniSettingsService::getCommitteeLabels();
        $committeeDocumentSetMappings = $this->settingsService->getCommitteeDocumentSetMappings();
        $invoiceGenerationRules = $this->settingsService->getInvoiceGenerationRulesForUI();
        $invoiceGenerationRequireAll = $this->settingsService->getInvoiceGenerationRules()['require_all_details_enabled'];

        $lastDataSync = MoloniSyncLog::getLastSuccessfulSync('data_sync');
        $recentLogs = MoloniSyncLog::latest()->limit(20)->get();

        $recentInvoices = MoloniInvoice::with('document')
            ->latest()
            ->limit(10)
            ->get();

        $failedInvoiceLogs = MoloniSyncLog::where('sync_type', 'invoice_create')
            ->where('status', 'failed')
            ->latest()
            ->limit(10)
            ->get();

        $syncedCustomers = MoloniCustomer::with('customerable')
            ->latest()
            ->limit(20)
            ->get();

        // Paid documents without Moloni invoice (missing invoices)
        $missingInvoices = Document::with('owner')
            ->where('status_class', PaidDocumentState::class)
            ->whereDoesntHave('moloniInvoice')
            ->whereHas('type', fn ($q) => $q->where('code', 'ORD'))
            ->latest('updated_at')
            ->limit(50)
            ->get();

        $missingInvoicesCount = Document::where('status_class', PaidDocumentState::class)
            ->whereDoesntHave('moloniInvoice')
            ->whereHas('type', fn ($q) => $q->where('code', 'ORD'))
            ->count();

        return view('web.admin.moloni-settings.index', compact(
            'token',
            'isConnected',
            'hasCredentials',
            'isConfigured',
            'isEnabled',
            'companies',
            'documentSets',
            'taxes',
            'taxExemptions',
            'units',
            'categories',
            'paymentMethods',
            'maturityDates',
            'currentSettings',
            'ownerTypeLabels',
            'committeeLabels',
            'committeeDocumentSetMappings',
            'invoiceGenerationRules',
            'invoiceGenerationRequireAll',
            'lastDataSync',
            'recentLogs',
            'recentInvoices',
            'failedInvoiceLogs',
            'syncedCustomers',
            'missingInvoices',
            'missingInvoicesCount'
        ));
    }

    public function redirectToAuthorize(): RedirectResponse
    {
        if (! $this->settingsService->hasCredentials()) {
            return redirect()
                ->route('admin.moloni-settings.index')
                ->with('error', __('moloni.missing_credentials'));
        }

        $url = $this->authService->getAuthorizationUrl();

        return redirect()->away($url);
    }

    public function callback(Request $request): RedirectResponse
    {
        $code = $request->get('code');
        $error = $request->get('error');

        if ($error) {
            return redirect()
                ->route('admin.moloni-settings.index')
                ->with('error', __('moloni.authorization_denied', ['error' => $error]));
        }

        if (! $code) {
            return redirect()
                ->route('admin.moloni-settings.index')
                ->with('error', __('moloni.no_authorization_code'));
        }

        try {
            $this->authService->exchangeCodeForToken($code);

            $syncAction = app(SyncMoloniDataAction::class);
            $syncAction();

            return redirect()
                ->route('admin.moloni-settings.index')
                ->with('success', __('moloni.connected_successfully'));

        } catch (\Exception $e) {
            return redirect()
                ->route('admin.moloni-settings.index')
                ->with('error', __('moloni.connection_failed', ['error' => $e->getMessage()]));
        }
    }

    public function disconnect(): RedirectResponse
    {
        $this->authService->disconnect();

        return redirect()
            ->route('admin.moloni-settings.index')
            ->with('success', __('moloni.disconnected_successfully'));
    }

    public function syncData(): RedirectResponse
    {
        try {
            $syncAction = app(SyncMoloniDataAction::class);
            $results = $syncAction();

            $totalItems = array_sum($results);

            return redirect()
                ->route('admin.moloni-settings.index')
                ->with('success', __('moloni.sync_completed', ['count' => $totalItems]));

        } catch (\Exception $e) {
            return redirect()
                ->route('admin.moloni-settings.index')
                ->with('error', __('moloni.sync_failed', ['error' => $e->getMessage()]));
        }
    }

    public function testConnection(): RedirectResponse
    {
        try {
            $client = app(MoloniClient::class);
            $success = $client->testConnection();

            if ($success) {
                return redirect()
                    ->route('admin.moloni-settings.index')
                    ->with('success', __('moloni.connection_successful'));
            }

            return redirect()
                ->route('admin.moloni-settings.index')
                ->with('error', __('moloni.connection_test_failed'));

        } catch (\Exception $e) {
            return redirect()
                ->route('admin.moloni-settings.index')
                ->with('error', __('moloni.connection_failed', ['error' => $e->getMessage()]));
        }
    }

    public function save(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_id' => 'nullable|integer',
            'document_set_id' => 'required|integer',
            'default_tax_id' => 'required|integer',
            'exempt_tax_id' => 'nullable|integer',
            'default_exemption_reason' => 'nullable|string|max:50',
            'default_unit_id' => 'nullable|integer',
            'default_category_id' => 'nullable|integer',
            'payment_method_id' => 'nullable|integer',
            'default_maturity_date_id' => 'nullable|integer',
            'document_set_mappings' => 'nullable|array',
            'document_set_mappings.*' => 'nullable|integer',
            'committee_document_set_mappings' => 'nullable|array',
            'committee_document_set_mappings.*' => 'nullable|integer',
            'use_invoice_receipts' => 'nullable|boolean',
            'create_as_draft' => 'nullable|boolean',
        ]);

        $this->settingsService->saveConfiguration($validated);

        return redirect()
            ->route('admin.moloni-settings.index')
            ->with('success', __('moloni.settings_saved'));
    }

    public function saveInvoiceGenerationRules(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'enabled_types' => 'nullable|array',
            'enabled_types.*' => 'boolean',
            'require_all' => 'nullable|boolean',
        ]);

        $enabledTypes = [];
        $defaults = $this->settingsService->getDefaultInvoiceGenerationRules();

        foreach (array_keys($defaults) as $typeKey) {
            $enabledTypes[$typeKey] = (bool) ($validated['enabled_types'][$typeKey] ?? false);
        }

        $requireAll = (bool) ($validated['require_all'] ?? false);

        $this->settingsService->saveInvoiceGenerationRules($enabledTypes, $requireAll);

        return redirect()
            ->route('admin.moloni-settings.index')
            ->with('success', __('moloni.invoice_generation_rules_saved'));
    }

    public function retryInvoice(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'document_id' => 'required|exists:document,id',
        ]);

        try {
            $document = \Domain\Documents\Models\Document::findOrFail($validated['document_id']);

            $action = app(CreateMoloniInvoiceReceiptAction::class);
            $invoice = $action($document);

            if ($invoice) {
                return redirect()
                    ->route('admin.moloni-settings.index')
                    ->with('success', __('moloni.invoice_created', ['number' => $invoice->moloni_number]));
            }

            return redirect()
                ->route('admin.moloni-settings.index')
                ->with('error', __('moloni.invoice_not_created'));

        } catch (\Exception $e) {
            return redirect()
                ->route('admin.moloni-settings.index')
                ->with('error', __('moloni.invoice_creation_failed', ['error' => $e->getMessage()]));
        }
    }

    public function syncCustomer(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'owner_type' => 'required|in:individual,entity',
            'owner_id' => 'required|integer',
        ]);

        try {
            $owner = $validated['owner_type'] === 'individual'
                ? \Domain\Individuals\Models\Individual::findOrFail($validated['owner_id'])
                : \Domain\Entities\Models\Entity::findOrFail($validated['owner_id']);

            $customerService = app(MoloniCustomerService::class);
            $customerId = $customerService->findOrCreate($owner);

            return redirect()
                ->route('admin.moloni-settings.index')
                ->with('success', __('moloni.customer_synced', ['id' => $customerId]));

        } catch (\Exception $e) {
            return redirect()
                ->route('admin.moloni-settings.index')
                ->with('error', __('moloni.customer_sync_failed', ['error' => $e->getMessage()]));
        }
    }

    public function downloadPdf(MoloniInvoice $moloniInvoice): RedirectResponse|StreamedResponse
    {
        try {
            $invoiceService = app(MoloniInvoiceService::class);
            $pdfUrl = $invoiceService->getPdfLink($moloniInvoice->moloni_document_id);

            if (! $pdfUrl) {
                return redirect()
                    ->route('admin.moloni-settings.index')
                    ->with('error', __('moloni.pdf_not_available'));
            }

            return redirect()->away($pdfUrl);

        } catch (\Exception $e) {
            return redirect()
                ->route('admin.moloni-settings.index')
                ->with('error', __('moloni.pdf_download_failed', ['error' => $e->getMessage()]));
        }
    }

    public function refreshStatus(MoloniInvoice $moloniInvoice): RedirectResponse
    {
        try {
            $invoiceService = app(MoloniInvoiceService::class);
            $updatedInvoice = $invoiceService->refreshInvoiceStatus($moloniInvoice);

            return redirect()
                ->route('admin.moloni-settings.index')
                ->with('success', __('moloni.status_refreshed', ['number' => $updatedInvoice->moloni_number]));

        } catch (\Exception $e) {
            return redirect()
                ->route('admin.moloni-settings.index')
                ->with('error', __('moloni.status_refresh_failed', ['error' => $e->getMessage()]));
        }
    }

    public function bulkRetryInvoices(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'document_ids' => 'required|array|min:1',
            'document_ids.*' => 'exists:document,id',
        ]);

        $action = app(CreateMoloniInvoiceReceiptAction::class);
        $successCount = 0;
        $failedCount = 0;

        foreach ($validated['document_ids'] as $documentId) {
            try {
                $document = \Domain\Documents\Models\Document::findOrFail($documentId);
                $invoice = $action($document);

                if ($invoice) {
                    $successCount++;
                } else {
                    $failedCount++;
                }
            } catch (\Exception $e) {
                $failedCount++;
            }
        }

        if ($successCount > 0 && $failedCount === 0) {
            return redirect()
                ->route('admin.moloni-settings.index')
                ->with('success', __('moloni.bulk_retry_success', ['count' => $successCount]));
        }

        if ($successCount > 0 && $failedCount > 0) {
            return redirect()
                ->route('admin.moloni-settings.index')
                ->with('warning', __('moloni.bulk_retry_partial', ['success' => $successCount, 'failed' => $failedCount]));
        }

        return redirect()
            ->route('admin.moloni-settings.index')
            ->with('error', __('moloni.bulk_retry_failed', ['count' => $failedCount]));
    }
}
