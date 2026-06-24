<?php

namespace Domain\Invoicing\Services;

use Domain\Certifications\Models\CertificationAttributed;
use Domain\EvtEvents\Models\AthleteEnrollment;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\IndividualEnrollment;
use Domain\Insurance\Models\Insurance;
use Domain\Invoicing\Models\MoloniSetting;
use Domain\Invoicing\Models\MoloniToken;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Memberships\Models\Membership;
use Domain\Memberships\Models\MemberSubscription;

class MoloniSettingsService
{
    /**
     * Supported document detail owner types for Moloni document series mapping.
     * Maps class name to a human-readable key for settings storage.
     */
    public const DOCUMENT_OWNER_TYPES = [
        LicenseAttributed::class => 'license',
        Membership::class => 'membership',
        MemberSubscription::class => 'member_subscription',
        CertificationAttributed::class => 'certification',
        Enrollment::class => 'enrollment',
        IndividualEnrollment::class => 'individual_enrollment',
        AthleteEnrollment::class => 'athlete_enrollment',
        Insurance::class => 'insurance',
    ];
    public function get(string $key, mixed $default = null): mixed
    {
        return MoloniSetting::getValue($key, $default);
    }

    public function set(string $key, mixed $value, string $type = 'string', bool $encrypted = false): void
    {
        MoloniSetting::setValue($key, $value, $type, $encrypted);
    }

    public function getDocumentSetId(): ?int
    {
        $value = $this->get('document_set_id');

        return $value ? (int) $value : null;
    }

    public function getDefaultTaxId(): ?int
    {
        $value = $this->get('default_tax_id');

        return $value ? (int) $value : null;
    }

    public function getExemptTaxId(): ?int
    {
        $value = $this->get('exempt_tax_id');

        return $value ? (int) $value : null;
    }

    public function getDefaultExemptionReason(): ?string
    {
        return $this->get('default_exemption_reason');
    }

    public function getDefaultUnitId(): ?int
    {
        $value = $this->get('default_unit_id');

        return $value ? (int) $value : null;
    }

    public function getDefaultCategoryId(): ?int
    {
        $value = $this->get('default_category_id');

        return $value ? (int) $value : null;
    }

    public function getPaymentMethodId(): ?int
    {
        $value = $this->get('payment_method_id');

        return $value ? (int) $value : null;
    }

    public function getDefaultMaturityDateId(): ?int
    {
        $value = $this->get('default_maturity_date_id');

        return $value ? (int) $value : null;
    }

    public function getCompanyId(): ?int
    {
        $value = $this->get('company_id');

        return $value ? (int) $value : null;
    }

    public function getDocumentSetsCache(): array
    {
        return $this->get('document_sets_cache') ?? [];
    }

    public function getTaxesCache(): array
    {
        return $this->get('taxes_cache') ?? [];
    }

    public function getUnitsCache(): array
    {
        return $this->get('units_cache') ?? [];
    }

    public function getCategoriesCache(): array
    {
        return $this->get('categories_cache') ?? [];
    }

    public function getPaymentMethodsCache(): array
    {
        return $this->get('payment_methods_cache') ?? [];
    }

    public function getMaturityDatesCache(): array
    {
        return $this->get('maturity_dates_cache') ?? [];
    }

    public function getTaxExemptionsCache(): array
    {
        return $this->get('tax_exemptions_cache') ?? [];
    }

    public function getCompaniesCache(): array
    {
        return $this->get('companies_cache') ?? [];
    }

    public function isConfigured(): bool
    {
        // Minimum requirements for Moloni invoice creation:
        // - Valid token (for API authentication)
        // - Document set ID (required by Moloni)
        // - Default tax ID (required for product lines)
        //
        // Optional (only needed when creating NEW products in Moloni):
        // - default_unit_id: will throw error if needed but not set
        // - default_category_id: will throw error if needed but not set
        // - payment_method_id: auto-resolves from document or finds "Bank Transfer"
        return $this->hasValidToken()
            && $this->getDocumentSetId()
            && $this->getDefaultTaxId();
    }

    /**
     * Find a payment method ID from Moloni's cache by name pattern.
     * Searches for partial matches (case-insensitive).
     */
    public function findPaymentMethodByName(string $pattern): ?int
    {
        $methods = $this->getPaymentMethodsCache();
        $pattern = mb_strtolower($pattern);

        foreach ($methods as $method) {
            if (str_contains(mb_strtolower($method['name'] ?? ''), $pattern)) {
                return (int) $method['id'];
            }
        }

        return null;
    }

    /**
     * Get the appropriate Moloni payment method ID for a document.
     * Tries to match based on the document's payment method, falls back to configured default,
     * then falls back to finding "Bank Transfer" in Moloni.
     */
    public function resolvePaymentMethodForDocument($document): ?int
    {
        // 1. If a default is configured, use it
        $defaultMethodId = $this->getPaymentMethodId();
        if ($defaultMethodId) {
            return $defaultMethodId;
        }

        // 2. Try to find a matching method based on the document's payment
        if ($document->method_id) {
            $paymentMethod = $document->method;
            if ($paymentMethod) {
                // Map known drivers to Moloni payment method names
                $moloniMethodId = match ($paymentMethod->driver) {
                    'offline' => $this->findPaymentMethodByName('transfer') ?? $this->findPaymentMethodByName('bancária'),
                    'easypay' => $this->findPaymentMethodByName('multibanco') ?? $this->findPaymentMethodByName('online'),
                    default => null,
                };

                if ($moloniMethodId) {
                    return $moloniMethodId;
                }
            }
        }

        // 3. Final fallback: try to find Bank Transfer
        return $this->findPaymentMethodByName('transfer')
            ?? $this->findPaymentMethodByName('bancária')
            ?? $this->findPaymentMethodByName('Bank');
    }

    public function hasValidToken(): bool
    {
        $token = MoloniToken::first();

        if (! $token) {
            return false;
        }

        // Token is valid if access token is current OR if we can refresh it
        return $token->isAccessTokenValid() || $token->isRefreshTokenValid();
    }

    public function isEnabled(): bool
    {
        return (bool) config('invoicing.providers.moloni.enabled', false);
    }

    /**
     * Check if we should use Invoice Receipts (Fatura-Recibo) instead of regular Invoices (Fatura).
     * Invoice Receipts require the document set to have 'for_invoice_receipt' enabled in Moloni.
     * Defaults to false (use regular Invoices) which works with standard document set configurations.
     */
    public function useInvoiceReceipts(): bool
    {
        return (bool) $this->get('use_invoice_receipts', false);
    }

    /**
     * Check if invoices should be created as draft (status=0) instead of closed/finalized (status=1).
     * Draft invoices require manual finalization in Moloni before they become valid fiscal documents.
     * Defaults to false (create finalized invoices) for production use.
     */
    public function createAsDraft(): bool
    {
        return (bool) $this->get('create_as_draft', false);
    }

    public function getToken(): ?MoloniToken
    {
        return MoloniToken::first();
    }

    public function hasCredentials(): bool
    {
        return config('invoicing.providers.moloni.client_id')
            && config('invoicing.providers.moloni.client_secret');
    }

    public function saveConfiguration(array $data): void
    {
        if (isset($data['document_set_id'])) {
            $this->set('document_set_id', $data['document_set_id'], 'int');
        }

        if (isset($data['default_tax_id'])) {
            $this->set('default_tax_id', $data['default_tax_id'], 'int');
        }

        if (isset($data['default_unit_id'])) {
            $this->set('default_unit_id', $data['default_unit_id'], 'int');
        }

        if (isset($data['default_category_id'])) {
            $this->set('default_category_id', $data['default_category_id'], 'int');
        }

        if (isset($data['payment_method_id'])) {
            $this->set('payment_method_id', $data['payment_method_id'], 'int');
        }

        if (isset($data['company_id'])) {
            $currentCompanyId = $this->getCompanyId();
            $newCompanyId = (int) $data['company_id'];

            // If company changed, clear all company-specific cached data
            // These IDs are company-specific and will be invalid for the new company
            if ($currentCompanyId && $currentCompanyId !== $newCompanyId) {
                $this->clearCompanySpecificSettings();
            }

            $this->set('company_id', $newCompanyId, 'int');
        }

        if (isset($data['default_maturity_date_id'])) {
            $this->set('default_maturity_date_id', $data['default_maturity_date_id'], 'int');
        }

        if (isset($data['exempt_tax_id'])) {
            $this->set('exempt_tax_id', $data['exempt_tax_id'], 'int');
        }

        if (isset($data['default_exemption_reason'])) {
            $this->set('default_exemption_reason', $data['default_exemption_reason'], 'string');
        }

        if (isset($data['document_set_mappings'])) {
            $this->setDocumentSetMappings($data['document_set_mappings']);
        }

        if (isset($data['committee_document_set_mappings'])) {
            $this->setCommitteeDocumentSetMappings($data['committee_document_set_mappings']);
        }

        if (array_key_exists('use_invoice_receipts', $data)) {
            $this->set('use_invoice_receipts', $data['use_invoice_receipts'] ? '1' : '0', 'bool');
        }

        if (array_key_exists('create_as_draft', $data)) {
            $this->set('create_as_draft', $data['create_as_draft'] ? '1' : '0', 'bool');
        }
    }

    /**
     * Get the document set mappings (owner_type key => document_set_id).
     */
    public function getDocumentSetMappings(): array
    {
        return $this->get('document_set_mappings') ?? [];
    }

    /**
     * Set the document set mappings.
     *
     * @param  array<string, int|null>  $mappings  Key is the owner type key (e.g., 'license'), value is document_set_id
     */
    public function setDocumentSetMappings(array $mappings): void
    {
        // Filter out empty/null values
        $filtered = array_filter($mappings, fn ($value) => ! empty($value));
        $this->set('document_set_mappings', $filtered, 'json');
    }

    /**
     * Get the document set ID for a specific owner type class.
     * Falls back to the default document_set_id if no specific mapping exists.
     *
     * @param  string  $ownerTypeClass  The fully qualified class name (e.g., LicenseAttributed::class)
     */
    public function getDocumentSetIdForOwnerType(string $ownerTypeClass): ?int
    {
        $mappings = $this->getDocumentSetMappings();
        $key = self::DOCUMENT_OWNER_TYPES[$ownerTypeClass] ?? null;

        if ($key && isset($mappings[$key])) {
            return (int) $mappings[$key];
        }

        // Fallback to default document set
        return $this->getDocumentSetId();
    }

    /**
     * Get the document set ID for a Document based on its details' owner types and committees.
     * For licenses and certifications, checks committee-based mapping first.
     * Falls back to owner type mapping, then default if no specific mapping exists.
     *
     * @param  \Domain\Documents\Models\Document  $document
     */
    public function getDocumentSetIdForDocument($document): ?int
    {
        $document->loadMissing('details');

        $defaultDocSetId = $this->getDocumentSetId();
        $committeeMappings = $this->getCommitteeDocumentSetMappings();
        $ownerTypeMappings = $this->getDocumentSetMappings();

        \Illuminate\Support\Facades\Log::info('MoloniSettingsService: getDocumentSetIdForDocument START', [
            'document_id' => $document->id,
            'document_number' => $document->number_extended ?? $document->id,
            'details_count' => $document->details->count(),
            'default_document_set_id' => $defaultDocSetId,
            'committee_mappings' => $committeeMappings,
            'owner_type_mappings' => $ownerTypeMappings,
        ]);

        if ($document->details->isEmpty()) {
            \Illuminate\Support\Facades\Log::info('MoloniSettingsService: No details, using default', [
                'document_id' => $document->id,
                'selected_document_set_id' => $defaultDocSetId,
                'reason' => 'document_has_no_details',
            ]);

            return $defaultDocSetId;
        }

        // Use the first detail's owner type to determine document set
        $firstDetail = $document->details->first();
        $ownerType = $firstDetail->owner_type;

        \Illuminate\Support\Facades\Log::info('MoloniSettingsService: First detail info', [
            'document_id' => $document->id,
            'detail_id' => $firstDetail->id,
            'detail_owner_type' => $ownerType,
            'detail_owner_id' => $firstDetail->owner_id,
            'detail_description' => $firstDetail->description ?? 'N/A',
        ]);

        // For licenses and certifications, check committee-based mapping first
        if ($ownerType === \Domain\Licenses\Models\LicenseAttributed::class) {
            \Illuminate\Support\Facades\Log::info('MoloniSettingsService: Owner is LicenseAttributed, checking committee mapping', [
                'document_id' => $document->id,
                'license_attributed_id' => $firstDetail->owner_id,
            ]);

            $committeeDocSetId = $this->getDocumentSetIdForLicenseDetail($firstDetail);
            if ($committeeDocSetId) {
                \Illuminate\Support\Facades\Log::info('MoloniSettingsService: Using committee-based document set for license', [
                    'document_id' => $document->id,
                    'selected_document_set_id' => $committeeDocSetId,
                    'reason' => 'committee_mapping_found',
                ]);

                return $committeeDocSetId;
            }

            \Illuminate\Support\Facades\Log::info('MoloniSettingsService: No committee mapping found for license, will check owner type mapping', [
                'document_id' => $document->id,
            ]);
        }

        if ($ownerType === \Domain\Certifications\Models\CertificationAttributed::class) {
            \Illuminate\Support\Facades\Log::info('MoloniSettingsService: Owner is CertificationAttributed, checking committee mapping', [
                'document_id' => $document->id,
                'certification_attributed_id' => $firstDetail->owner_id,
            ]);

            $committeeDocSetId = $this->getDocumentSetIdForCertificationDetail($firstDetail);
            if ($committeeDocSetId) {
                \Illuminate\Support\Facades\Log::info('MoloniSettingsService: Using committee-based document set for certification', [
                    'document_id' => $document->id,
                    'selected_document_set_id' => $committeeDocSetId,
                    'reason' => 'committee_mapping_found',
                ]);

                return $committeeDocSetId;
            }

            \Illuminate\Support\Facades\Log::info('MoloniSettingsService: No committee mapping found for certification, will check owner type mapping', [
                'document_id' => $document->id,
            ]);
        }

        if ($ownerType && isset(self::DOCUMENT_OWNER_TYPES[$ownerType])) {
            $ownerTypeKey = self::DOCUMENT_OWNER_TYPES[$ownerType];
            $ownerTypeDocSetId = $this->getDocumentSetIdForOwnerType($ownerType);

            \Illuminate\Support\Facades\Log::info('MoloniSettingsService: Using owner type mapping', [
                'document_id' => $document->id,
                'owner_type' => $ownerType,
                'owner_type_key' => $ownerTypeKey,
                'owner_type_mapping_value' => $ownerTypeMappings[$ownerTypeKey] ?? null,
                'selected_document_set_id' => $ownerTypeDocSetId,
                'reason' => isset($ownerTypeMappings[$ownerTypeKey]) ? 'owner_type_mapping_found' : 'owner_type_fallback_to_default',
            ]);

            return $ownerTypeDocSetId;
        }

        \Illuminate\Support\Facades\Log::info('MoloniSettingsService: Using default document set', [
            'document_id' => $document->id,
            'owner_type' => $ownerType,
            'selected_document_set_id' => $defaultDocSetId,
            'reason' => 'no_specific_mapping_found',
        ]);

        return $defaultDocSetId;
    }

    /**
     * Get the document set ID for a license detail based on its committee.
     *
     * @param  \Domain\Documents\Models\DocumentDetail  $detail
     */
    private function getDocumentSetIdForLicenseDetail($detail): ?int
    {
        \Illuminate\Support\Facades\Log::info('MoloniSettingsService: getDocumentSetIdForLicenseDetail START', [
            'detail_id' => $detail->id,
            'owner_id' => $detail->owner_id,
        ]);

        try {
            $licenseAttributed = \Domain\Licenses\Models\LicenseAttributed::withoutGlobalScopes()
                ->with('license.committee')
                ->find($detail->owner_id);

            \Illuminate\Support\Facades\Log::info('MoloniSettingsService: LicenseAttributed lookup result', [
                'detail_id' => $detail->id,
                'owner_id' => $detail->owner_id,
                'license_attributed_found' => $licenseAttributed !== null,
                'license_attributed_id' => $licenseAttributed?->id,
                'license_id' => $licenseAttributed?->license_id,
                'license_found' => $licenseAttributed?->license !== null,
                'license_name' => $licenseAttributed?->license?->name ?? 'N/A',
                'committee_found' => $licenseAttributed?->license?->committee !== null,
                'committee_id' => $licenseAttributed?->license?->committee?->id,
                'committee_code' => $licenseAttributed?->license?->committee?->code ?? 'N/A',
                'committee_name' => $licenseAttributed?->license?->committee?->name ?? 'N/A',
            ]);

            if ($licenseAttributed?->license?->committee) {
                $committeeCode = $licenseAttributed->license->committee->code;
                $docSetId = $this->getDocumentSetIdForCommittee($committeeCode);

                \Illuminate\Support\Facades\Log::info('MoloniSettingsService: Committee document set lookup', [
                    'detail_id' => $detail->id,
                    'committee_code' => $committeeCode,
                    'document_set_id_for_committee' => $docSetId,
                    'all_committee_mappings' => $this->getCommitteeDocumentSetMappings(),
                ]);

                return $docSetId;
            }

            \Illuminate\Support\Facades\Log::warning('MoloniSettingsService: No committee found for license', [
                'detail_id' => $detail->id,
                'owner_id' => $detail->owner_id,
                'license_attributed_exists' => $licenseAttributed !== null,
                'license_exists' => $licenseAttributed?->license !== null,
                'committee_exists' => $licenseAttributed?->license?->committee !== null,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('MoloniSettingsService: Exception getting committee for license', [
                'detail_id' => $detail->id,
                'owner_id' => $detail->owner_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return null;
    }

    /**
     * Get the document set ID for a certification detail based on its committee.
     *
     * @param  \Domain\Documents\Models\DocumentDetail  $detail
     */
    private function getDocumentSetIdForCertificationDetail($detail): ?int
    {
        try {
            $certificationAttributed = \Domain\Certifications\Models\CertificationAttributed::with('certification.committee')
                ->find($detail->owner_id);

            if ($certificationAttributed?->certification?->committee) {
                $committeeCode = $certificationAttributed->certification->committee->code;

                return $this->getDocumentSetIdForCommittee($committeeCode);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('MoloniSettingsService: Failed to get committee for certification', [
                'detail_id' => $detail->id,
                'owner_id' => $detail->owner_id,
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    /**
     * Get the document set ID for a specific committee code.
     * Returns null if no specific mapping exists (will fall back to owner type or default).
     */
    public function getDocumentSetIdForCommittee(string $committeeCode): ?int
    {
        $mappings = $this->getCommitteeDocumentSetMappings();

        \Illuminate\Support\Facades\Log::info('MoloniSettingsService: getDocumentSetIdForCommittee', [
            'requested_committee_code' => $committeeCode,
            'all_committee_mappings' => $mappings,
            'mapping_exists' => isset($mappings[$committeeCode]),
            'mapped_value' => $mappings[$committeeCode] ?? null,
        ]);

        if (isset($mappings[$committeeCode])) {
            $docSetId = (int) $mappings[$committeeCode];

            // Validate that the document set ID exists in the cache
            $documentSetsCache = $this->getDocumentSetsCache();
            $docSetExists = false;
            $docSetName = 'UNKNOWN';
            foreach ($documentSetsCache as $docSet) {
                if (($docSet['document_set_id'] ?? null) == $docSetId) {
                    $docSetExists = true;
                    $docSetName = $docSet['name'] ?? 'UNKNOWN';
                    break;
                }
            }

            \Illuminate\Support\Facades\Log::info('MoloniSettingsService: Committee mapping FOUND', [
                'committee_code' => $committeeCode,
                'document_set_id' => $docSetId,
                'document_set_name' => $docSetName,
                'document_set_exists_in_cache' => $docSetExists,
                'cached_document_sets_count' => count($documentSetsCache),
            ]);

            if (! $docSetExists) {
                \Illuminate\Support\Facades\Log::warning('MoloniSettingsService: CONFIGURED DOCUMENT SET ID DOES NOT EXIST IN MOLONI CACHE', [
                    'committee_code' => $committeeCode,
                    'configured_document_set_id' => $docSetId,
                    'available_document_sets' => array_map(fn ($s) => [
                        'id' => $s['document_set_id'] ?? 'N/A',
                        'name' => $s['name'] ?? 'N/A',
                    ], $documentSetsCache),
                ]);
            }

            return $docSetId;
        }

        \Illuminate\Support\Facades\Log::warning('MoloniSettingsService: Committee mapping NOT FOUND - will fallback', [
            'committee_code' => $committeeCode,
            'available_mappings' => array_keys($mappings),
        ]);

        return null;
    }

    /**
     * Get the committee-based document set mappings (committee_code => document_set_id).
     */
    public function getCommitteeDocumentSetMappings(): array
    {
        // Direct database query for debugging
        $dbRecord = MoloniSetting::where('key', 'committee_document_set_mappings')->first();

        \Illuminate\Support\Facades\Log::info('MoloniSettingsService: getCommitteeDocumentSetMappings RAW DB', [
            'db_record_exists' => $dbRecord !== null,
            'db_record_id' => $dbRecord?->id,
            'db_record_value' => $dbRecord?->value,
            'db_record_type' => $dbRecord?->type,
            'db_record_is_encrypted' => $dbRecord?->is_encrypted,
        ]);

        $rawValue = $this->get('committee_document_set_mappings');
        $mappings = $rawValue ?? [];

        \Illuminate\Support\Facades\Log::info('MoloniSettingsService: getCommitteeDocumentSetMappings PARSED', [
            'raw_value_from_service' => $rawValue,
            'raw_value_type' => gettype($rawValue),
            'parsed_mappings' => $mappings,
            'mapping_keys' => is_array($mappings) ? array_keys($mappings) : 'N/A',
        ]);

        return $mappings;
    }

    /**
     * Set the committee-based document set mappings.
     *
     * @param  array<string, int|null>  $mappings  Key is committee code (e.g., 'DIVING'), value is document_set_id
     */
    public function setCommitteeDocumentSetMappings(array $mappings): void
    {
        // Filter out empty/null values
        $filtered = array_filter($mappings, fn ($value) => ! empty($value));
        $this->set('committee_document_set_mappings', $filtered, 'json');
    }

    /**
     * Get the list of committee codes with their labels for the UI.
     *
     * @return array<string, string>
     */
    public static function getCommitteeLabels(): array
    {
        return [
            'DIVING' => 'moloni.committee_diving',
            'SCIENTIFIC' => 'moloni.committee_scientific',
            'SPORT' => 'moloni.committee_sport',
            'DIVINGSERVICES' => 'moloni.committee_divingservices',
        ];
    }

    /**
     * Get the list of supported owner types with their labels for the UI.
     *
     * @return array<string, string> Key is the settings key, value is the translation key
     */
    public static function getOwnerTypeLabels(): array
    {
        return [
            'license' => 'moloni.owner_type_license',
            'membership' => 'moloni.owner_type_membership',
            'member_subscription' => 'moloni.owner_type_member_subscription',
            'certification' => 'moloni.owner_type_certification',
            'enrollment' => 'moloni.owner_type_enrollment',
            'individual_enrollment' => 'moloni.owner_type_individual_enrollment',
            'athlete_enrollment' => 'moloni.owner_type_athlete_enrollment',
            'insurance' => 'moloni.owner_type_insurance',
        ];
    }

    /**
     * Clear all company-specific settings when switching companies.
     * This is necessary because document sets, taxes, units, categories, etc.
     * are all company-specific in Moloni and IDs from one company are invalid for another.
     */
    public function clearCompanySpecificSettings(): void
    {
        $keysToDelete = [
            'document_sets_cache',
            'taxes_cache',
            'tax_exemptions_cache',
            'units_cache',
            'categories_cache',
            'payment_methods_cache',
            'maturity_dates_cache',
            'document_set_id',
            'default_tax_id',
            'exempt_tax_id',
            'default_exemption_reason',
            'default_unit_id',
            'default_category_id',
            'payment_method_id',
            'default_maturity_date_id',
            'document_set_mappings',
        ];

        MoloniSetting::whereIn('key', $keysToDelete)->delete();
    }

    /**
     * Get the default invoice generation rules.
     * By default, only member_subscription (Filiacao) and insurance (Seguros) create invoices.
     *
     * @return array<string, bool>
     */
    public function getDefaultInvoiceGenerationRules(): array
    {
        return [
            'license' => false,
            'membership' => false,
            'member_subscription' => true,
            'certification' => false,
            'enrollment' => false,
            'individual_enrollment' => false,
            'athlete_enrollment' => false,
            'insurance' => true,
        ];
    }

    /**
     * Get the invoice generation rules configuration.
     *
     * @return array{enabled_detail_types: array<string, bool>, require_all_details_enabled: bool}
     */
    public function getInvoiceGenerationRules(): array
    {
        $stored = $this->get('invoice_generation_rules');

        if (! $stored || ! is_array($stored)) {
            return [
                'enabled_detail_types' => $this->getDefaultInvoiceGenerationRules(),
                'require_all_details_enabled' => false,
            ];
        }

        // Merge with defaults to ensure all keys exist
        $enabledTypes = array_merge(
            $this->getDefaultInvoiceGenerationRules(),
            $stored['enabled_detail_types'] ?? []
        );

        return [
            'enabled_detail_types' => $enabledTypes,
            'require_all_details_enabled' => $stored['require_all_details_enabled'] ?? false,
        ];
    }

    /**
     * Save invoice generation rules to the database.
     *
     * @param  array<string, bool>  $enabledTypes  Map of type key to enabled status
     * @param  bool  $requireAll  If true, ALL detail types must be enabled to generate invoice
     */
    public function saveInvoiceGenerationRules(array $enabledTypes, bool $requireAll = false): void
    {
        $rules = [
            'enabled_detail_types' => $enabledTypes,
            'require_all_details_enabled' => $requireAll,
        ];

        $this->set('invoice_generation_rules', $rules, 'json');
    }

    /**
     * Check if a document should have a Moloni invoice generated based on its details.
     *
     * Checks the document's detail owner types against the configured rules.
     * By default, returns true if ANY detail type is enabled. If require_all_details_enabled
     * is true, ALL detail types must be enabled.
     *
     * @param  \Domain\Documents\Models\Document  $document
     */
    public function shouldGenerateInvoiceForDocument($document): bool
    {
        $document->loadMissing('details');

        if ($document->details->isEmpty()) {
            // No details means nothing to check - allow invoice generation
            return true;
        }

        $rules = $this->getInvoiceGenerationRules();
        $enabledTypes = $rules['enabled_detail_types'];
        $requireAll = $rules['require_all_details_enabled'];

        $hasEnabledType = false;
        $hasDisabledType = false;

        foreach ($document->details as $detail) {
            $ownerType = $detail->owner_type;

            if (! $ownerType) {
                // Manual/null owner type - treat as enabled
                $hasEnabledType = true;

                continue;
            }

            // Map class name to settings key
            $typeKey = self::DOCUMENT_OWNER_TYPES[$ownerType] ?? null;

            if (! $typeKey) {
                // Unknown type - treat as enabled by default
                $hasEnabledType = true;

                continue;
            }

            if ($enabledTypes[$typeKey] ?? false) {
                $hasEnabledType = true;
            } else {
                $hasDisabledType = true;
            }
        }

        if ($requireAll) {
            // All detail types must be enabled
            return $hasEnabledType && ! $hasDisabledType;
        }

        // Any enabled detail type triggers invoice generation
        return $hasEnabledType;
    }

    /**
     * Get the list of detail types with their enabled status for the UI.
     *
     * @return array<string, array{label: string, enabled: bool}>
     */
    public function getInvoiceGenerationRulesForUI(): array
    {
        $rules = $this->getInvoiceGenerationRules();
        $enabledTypes = $rules['enabled_detail_types'];
        $labels = self::getOwnerTypeLabels();

        $result = [];
        foreach ($labels as $key => $labelKey) {
            $result[$key] = [
                'label' => $labelKey,
                'enabled' => $enabledTypes[$key] ?? false,
            ];
        }

        return $result;
    }
}
