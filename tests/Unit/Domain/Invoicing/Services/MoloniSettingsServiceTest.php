<?php

use Domain\Documents\Models\Document;
use Domain\Documents\Models\DocumentDetail;
use Domain\Documents\Models\DocumentType;
use Domain\Invoicing\Models\MoloniToken;
use Domain\Invoicing\Services\MoloniSettingsService;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Memberships\Models\Membership;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(MoloniSettingsService::class);
});

test('can get and set a string setting', function () {
    $this->service->set('test_key', 'test_value');

    expect($this->service->get('test_key'))->toBe('test_value');
});

test('can get and set an integer setting', function () {
    $this->service->set('document_set_id', 123, 'int');

    expect($this->service->getDocumentSetId())->toBe(123);
});

test('returns null for non-existent setting', function () {
    expect($this->service->get('non_existent_key'))->toBeNull();
    expect($this->service->getDocumentSetId())->toBeNull();
});

test('returns default value for non-existent setting', function () {
    expect($this->service->get('non_existent_key', 'default'))->toBe('default');
});

test('can save full configuration', function () {
    $this->service->saveConfiguration([
        'document_set_id' => 1,
        'default_tax_id' => 2,
        'default_unit_id' => 3,
        'default_category_id' => 4,
        'payment_method_id' => 5,
        'company_id' => 100,
    ]);

    expect($this->service->getDocumentSetId())->toBe(1);
    expect($this->service->getDefaultTaxId())->toBe(2);
    expect($this->service->getDefaultUnitId())->toBe(3);
    expect($this->service->getDefaultCategoryId())->toBe(4);
    expect($this->service->getPaymentMethodId())->toBe(5);
    expect($this->service->getCompanyId())->toBe(100);
});

test('isConfigured returns false when no settings are set', function () {
    expect($this->service->isConfigured())->toBeFalse();
});

test('isConfigured returns false when only partial settings are set', function () {
    $this->service->saveConfiguration([
        'document_set_id' => 1,
        'default_tax_id' => 2,
    ]);

    expect($this->service->isConfigured())->toBeFalse();
});

test('isConfigured returns false when no valid token exists', function () {
    $this->service->saveConfiguration([
        'document_set_id' => 1,
        'default_tax_id' => 2,
        'default_unit_id' => 3,
        'default_category_id' => 4,
        'payment_method_id' => 5,
    ]);

    expect($this->service->isConfigured())->toBeFalse();
});

test('isConfigured returns true when all settings and valid token exist', function () {
    $this->service->saveConfiguration([
        'document_set_id' => 1,
        'default_tax_id' => 2,
        'default_unit_id' => 3,
        'default_category_id' => 4,
        'payment_method_id' => 5,
    ]);

    MoloniToken::create([
        'access_token' => 'test_token',
        'refresh_token' => 'test_refresh',
        'access_token_expires_at' => now()->addHour(),
        'refresh_token_expires_at' => now()->addDays(14),
    ]);

    expect($this->service->isConfigured())->toBeTrue();
});

test('hasValidToken returns false when no token exists', function () {
    expect($this->service->hasValidToken())->toBeFalse();
});

test('hasValidToken returns false when token is expired', function () {
    MoloniToken::create([
        'access_token' => 'test_token',
        'refresh_token' => 'test_refresh',
        'access_token_expires_at' => now()->subHour(),
        'refresh_token_expires_at' => now()->subHour(),
    ]);

    expect($this->service->hasValidToken())->toBeFalse();
});

test('hasValidToken returns true when token is valid', function () {
    MoloniToken::create([
        'access_token' => 'test_token',
        'refresh_token' => 'test_refresh',
        'access_token_expires_at' => now()->addHour(),
        'refresh_token_expires_at' => now()->addDays(14),
    ]);

    expect($this->service->hasValidToken())->toBeTrue();
});

test('isEnabled returns false by default', function () {
    config(['invoicing.providers.moloni.enabled' => false]);

    expect($this->service->isEnabled())->toBeFalse();
});

test('isEnabled returns true when configured', function () {
    config(['invoicing.providers.moloni.enabled' => true]);

    expect($this->service->isEnabled())->toBeTrue();
});

test('hasCredentials returns false when credentials not set', function () {
    config(['invoicing.providers.moloni.client_id' => null]);
    config(['invoicing.providers.moloni.client_secret' => null]);

    expect($this->service->hasCredentials())->toBeFalse();
});

test('hasCredentials returns true when credentials are set', function () {
    config(['invoicing.providers.moloni.client_id' => 'test_id']);
    config(['invoicing.providers.moloni.client_secret' => 'test_secret']);

    expect($this->service->hasCredentials())->toBeTrue();
});

test('can store and retrieve JSON cache values', function () {
    $documentSets = [
        ['id' => 1, 'name' => 'FR 2024'],
        ['id' => 2, 'name' => 'FT 2024'],
    ];

    $this->service->set('document_sets_cache', $documentSets, 'json');

    expect($this->service->getDocumentSetsCache())->toBe($documentSets);
});

test('returns empty array for non-existent cache', function () {
    expect($this->service->getDocumentSetsCache())->toBe([]);
    expect($this->service->getTaxesCache())->toBe([]);
    expect($this->service->getUnitsCache())->toBe([]);
    expect($this->service->getCategoriesCache())->toBe([]);
    expect($this->service->getPaymentMethodsCache())->toBe([]);
});

test('getToken returns null when no token exists', function () {
    expect($this->service->getToken())->toBeNull();
});

test('getToken returns token when it exists', function () {
    $token = MoloniToken::create([
        'access_token' => 'test_token',
        'refresh_token' => 'test_refresh',
        'access_token_expires_at' => now()->addHour(),
        'refresh_token_expires_at' => now()->addDays(14),
    ]);

    expect($this->service->getToken())->not->toBeNull();
    expect($this->service->getToken()->access_token)->toBe('test_token');
});

// Document Set Mappings Tests

test('can set and get document set mappings', function () {
    $mappings = [
        'license' => 10,
        'membership' => 20,
        'certification' => 30,
    ];

    $this->service->setDocumentSetMappings($mappings);

    expect($this->service->getDocumentSetMappings())->toBe($mappings);
});

test('setDocumentSetMappings filters out empty values', function () {
    $mappings = [
        'license' => 10,
        'membership' => '',
        'certification' => null,
        'enrollment' => 0,
    ];

    $this->service->setDocumentSetMappings($mappings);

    expect($this->service->getDocumentSetMappings())->toBe(['license' => 10]);
});

test('getDocumentSetMappings returns empty array when not set', function () {
    expect($this->service->getDocumentSetMappings())->toBe([]);
});

test('getDocumentSetIdForOwnerType returns mapped value when set', function () {
    $this->service->set('document_set_id', 1, 'int');
    $this->service->setDocumentSetMappings([
        'license' => 100,
        'membership' => 200,
    ]);

    expect($this->service->getDocumentSetIdForOwnerType(LicenseAttributed::class))->toBe(100);
    expect($this->service->getDocumentSetIdForOwnerType(Membership::class))->toBe(200);
});

test('getDocumentSetIdForOwnerType falls back to default when no mapping exists', function () {
    $this->service->set('document_set_id', 1, 'int');
    $this->service->setDocumentSetMappings([
        'license' => 100,
    ]);

    // Membership has no specific mapping, should use default
    expect($this->service->getDocumentSetIdForOwnerType(Membership::class))->toBe(1);
});

test('getDocumentSetIdForOwnerType returns default for unknown owner type', function () {
    $this->service->set('document_set_id', 1, 'int');
    $this->service->setDocumentSetMappings([
        'license' => 100,
    ]);

    // Unknown class should fall back to default
    expect($this->service->getDocumentSetIdForOwnerType('Unknown\\Class'))->toBe(1);
});

test('getDocumentSetIdForDocument returns mapped value based on first detail', function () {
    $this->service->set('document_set_id', 1, 'int');
    $this->service->setDocumentSetMappings([
        'license' => 100,
        'membership' => 200,
    ]);

    // Create a document type first
    $documentType = DocumentType::create([
        'code' => 'ORD',
        'name' => 'Order',
        'prefix' => 'ORD',
    ]);

    // Create a document with a license detail
    $document = Document::factory()->create([
        'type_id' => $documentType->id,
    ]);

    DocumentDetail::create([
        'document_id' => $document->id,
        'owner_type' => LicenseAttributed::class,
        'owner_id' => 1,
        'description' => 'Test License',
        'quantity' => 1,
        'unit_value' => 100,
        'total_value' => 100,
    ]);

    expect($this->service->getDocumentSetIdForDocument($document))->toBe(100);
});

test('getDocumentSetIdForDocument returns default when no details exist', function () {
    $this->service->set('document_set_id', 1, 'int');
    $this->service->setDocumentSetMappings([
        'license' => 100,
    ]);

    // Create a document type first
    $documentType = DocumentType::create([
        'code' => 'ORD',
        'name' => 'Order',
        'prefix' => 'ORD',
    ]);

    // Create a document without details
    $document = Document::factory()->create([
        'type_id' => $documentType->id,
    ]);

    expect($this->service->getDocumentSetIdForDocument($document))->toBe(1);
});

test('getDocumentSetIdForDocument returns default when detail has unmapped owner type', function () {
    $this->service->set('document_set_id', 1, 'int');
    $this->service->setDocumentSetMappings([
        'license' => 100,
    ]);

    // Create a document type first
    $documentType = DocumentType::create([
        'code' => 'ORD',
        'name' => 'Order',
        'prefix' => 'ORD',
    ]);

    // Create a document with an unmapped owner type detail
    $document = Document::factory()->create([
        'type_id' => $documentType->id,
    ]);

    DocumentDetail::create([
        'document_id' => $document->id,
        'owner_type' => 'Unknown\\OwnerType',
        'owner_id' => 1,
        'description' => 'Test Item',
        'quantity' => 1,
        'unit_value' => 100,
        'total_value' => 100,
    ]);

    expect($this->service->getDocumentSetIdForDocument($document))->toBe(1);
});

test('saveConfiguration saves document set mappings', function () {
    $this->service->saveConfiguration([
        'document_set_id' => 1,
        'document_set_mappings' => [
            'license' => 100,
            'membership' => 200,
        ],
    ]);

    expect($this->service->getDocumentSetMappings())->toBe([
        'license' => 100,
        'membership' => 200,
    ]);
});

test('getOwnerTypeLabels returns all supported types', function () {
    $labels = MoloniSettingsService::getOwnerTypeLabels();

    expect($labels)->toHaveKey('license');
    expect($labels)->toHaveKey('membership');
    expect($labels)->toHaveKey('member_subscription');
    expect($labels)->toHaveKey('certification');
    expect($labels)->toHaveKey('enrollment');
    expect($labels)->toHaveKey('individual_enrollment');
    expect($labels)->toHaveKey('athlete_enrollment');
    expect($labels)->toHaveKey('insurance');
});

test('DOCUMENT_OWNER_TYPES constant maps class names to keys', function () {
    $types = MoloniSettingsService::DOCUMENT_OWNER_TYPES;

    expect($types)->toHaveKey(LicenseAttributed::class);
    expect($types[LicenseAttributed::class])->toBe('license');
    expect($types)->toHaveKey(Membership::class);
    expect($types[Membership::class])->toBe('membership');
});

// Invoice Generation Rules Tests

test('getDefaultInvoiceGenerationRules returns expected defaults', function () {
    $defaults = $this->service->getDefaultInvoiceGenerationRules();

    // By default, only member_subscription and insurance are enabled
    expect($defaults['member_subscription'])->toBeTrue();
    expect($defaults['insurance'])->toBeTrue();
    expect($defaults['license'])->toBeFalse();
    expect($defaults['certification'])->toBeFalse();
    expect($defaults['enrollment'])->toBeFalse();
});

test('getInvoiceGenerationRules returns defaults when not configured', function () {
    $rules = $this->service->getInvoiceGenerationRules();

    expect($rules)->toHaveKey('enabled_detail_types');
    expect($rules)->toHaveKey('require_all_details_enabled');
    expect($rules['require_all_details_enabled'])->toBeFalse();
    expect($rules['enabled_detail_types']['member_subscription'])->toBeTrue();
    expect($rules['enabled_detail_types']['insurance'])->toBeTrue();
    expect($rules['enabled_detail_types']['license'])->toBeFalse();
});

test('can save and retrieve invoice generation rules', function () {
    $enabledTypes = [
        'license' => true,
        'membership' => false,
        'member_subscription' => true,
        'certification' => false,
        'enrollment' => false,
        'individual_enrollment' => false,
        'athlete_enrollment' => false,
        'insurance' => false,
    ];

    $this->service->saveInvoiceGenerationRules($enabledTypes, true);

    $rules = $this->service->getInvoiceGenerationRules();

    expect($rules['enabled_detail_types']['license'])->toBeTrue();
    expect($rules['enabled_detail_types']['insurance'])->toBeFalse();
    expect($rules['require_all_details_enabled'])->toBeTrue();
});

test('shouldGenerateInvoiceForDocument returns true when document has no details', function () {
    $documentType = DocumentType::create([
        'code' => 'ORD',
        'name' => 'Order',
        'prefix' => 'ORD',
    ]);

    $document = Document::factory()->create([
        'type_id' => $documentType->id,
    ]);

    expect($this->service->shouldGenerateInvoiceForDocument($document))->toBeTrue();
});

test('shouldGenerateInvoiceForDocument returns true when any enabled type is present', function () {
    // Configure: only member_subscription enabled
    $this->service->saveInvoiceGenerationRules([
        'license' => false,
        'membership' => false,
        'member_subscription' => true,
        'certification' => false,
        'enrollment' => false,
        'individual_enrollment' => false,
        'athlete_enrollment' => false,
        'insurance' => false,
    ]);

    $documentType = DocumentType::create([
        'code' => 'ORD',
        'name' => 'Order',
        'prefix' => 'ORD',
    ]);

    $document = Document::factory()->create([
        'type_id' => $documentType->id,
    ]);

    // Add a member_subscription detail (enabled type)
    DocumentDetail::create([
        'document_id' => $document->id,
        'owner_type' => \Domain\Memberships\Models\MemberSubscription::class,
        'owner_id' => 1,
        'description' => 'Test Subscription',
        'quantity' => 1,
        'unit_value' => 100,
        'total_value' => 100,
    ]);

    expect($this->service->shouldGenerateInvoiceForDocument($document))->toBeTrue();
});

test('shouldGenerateInvoiceForDocument returns false when only disabled types are present', function () {
    // Configure: only member_subscription and insurance enabled (licenses disabled)
    $this->service->saveInvoiceGenerationRules([
        'license' => false,
        'membership' => false,
        'member_subscription' => true,
        'certification' => false,
        'enrollment' => false,
        'individual_enrollment' => false,
        'athlete_enrollment' => false,
        'insurance' => true,
    ]);

    $documentType = DocumentType::create([
        'code' => 'ORD',
        'name' => 'Order',
        'prefix' => 'ORD',
    ]);

    $document = Document::factory()->create([
        'type_id' => $documentType->id,
    ]);

    // Add a license detail (disabled type)
    DocumentDetail::create([
        'document_id' => $document->id,
        'owner_type' => LicenseAttributed::class,
        'owner_id' => 1,
        'description' => 'Test License',
        'quantity' => 1,
        'unit_value' => 100,
        'total_value' => 100,
    ]);

    expect($this->service->shouldGenerateInvoiceForDocument($document))->toBeFalse();
});

test('shouldGenerateInvoiceForDocument with mixed types returns true by default', function () {
    // Configure: only insurance enabled
    $this->service->saveInvoiceGenerationRules([
        'license' => false,
        'membership' => false,
        'member_subscription' => false,
        'certification' => false,
        'enrollment' => false,
        'individual_enrollment' => false,
        'athlete_enrollment' => false,
        'insurance' => true,
    ]);

    $documentType = DocumentType::create([
        'code' => 'ORD',
        'name' => 'Order',
        'prefix' => 'ORD',
    ]);

    $document = Document::factory()->create([
        'type_id' => $documentType->id,
    ]);

    // Add a license detail (disabled) and insurance detail (enabled)
    DocumentDetail::create([
        'document_id' => $document->id,
        'owner_type' => LicenseAttributed::class,
        'owner_id' => 1,
        'description' => 'Test License',
        'quantity' => 1,
        'unit_value' => 100,
        'total_value' => 100,
    ]);

    DocumentDetail::create([
        'document_id' => $document->id,
        'owner_type' => \Domain\Insurance\Models\Insurance::class,
        'owner_id' => 1,
        'description' => 'Test Insurance',
        'quantity' => 1,
        'unit_value' => 50,
        'total_value' => 50,
    ]);

    // By default (require_all = false), any enabled type triggers invoice
    expect($this->service->shouldGenerateInvoiceForDocument($document))->toBeTrue();
});

test('shouldGenerateInvoiceForDocument with require_all returns false for mixed types', function () {
    // Configure: only insurance enabled, require all
    $this->service->saveInvoiceGenerationRules([
        'license' => false,
        'membership' => false,
        'member_subscription' => false,
        'certification' => false,
        'enrollment' => false,
        'individual_enrollment' => false,
        'athlete_enrollment' => false,
        'insurance' => true,
    ], true); // require_all = true

    $documentType = DocumentType::create([
        'code' => 'ORD',
        'name' => 'Order',
        'prefix' => 'ORD',
    ]);

    $document = Document::factory()->create([
        'type_id' => $documentType->id,
    ]);

    // Add a license detail (disabled) and insurance detail (enabled)
    DocumentDetail::create([
        'document_id' => $document->id,
        'owner_type' => LicenseAttributed::class,
        'owner_id' => 1,
        'description' => 'Test License',
        'quantity' => 1,
        'unit_value' => 100,
        'total_value' => 100,
    ]);

    DocumentDetail::create([
        'document_id' => $document->id,
        'owner_type' => \Domain\Insurance\Models\Insurance::class,
        'owner_id' => 1,
        'description' => 'Test Insurance',
        'quantity' => 1,
        'unit_value' => 50,
        'total_value' => 50,
    ]);

    // With require_all = true, having any disabled type prevents invoice
    expect($this->service->shouldGenerateInvoiceForDocument($document))->toBeFalse();
});

test('shouldGenerateInvoiceForDocument treats null owner type as enabled', function () {
    // Configure: all types disabled
    $this->service->saveInvoiceGenerationRules([
        'license' => false,
        'membership' => false,
        'member_subscription' => false,
        'certification' => false,
        'enrollment' => false,
        'individual_enrollment' => false,
        'athlete_enrollment' => false,
        'insurance' => false,
    ]);

    $documentType = DocumentType::create([
        'code' => 'ORD',
        'name' => 'Order',
        'prefix' => 'ORD',
    ]);

    $document = Document::factory()->create([
        'type_id' => $documentType->id,
    ]);

    // Add a manual detail (null owner_type)
    DocumentDetail::create([
        'document_id' => $document->id,
        'owner_type' => null,
        'owner_id' => null,
        'description' => 'Manual Item',
        'quantity' => 1,
        'unit_value' => 100,
        'total_value' => 100,
    ]);

    // Manual/null owner types are treated as enabled
    expect($this->service->shouldGenerateInvoiceForDocument($document))->toBeTrue();
});

test('getInvoiceGenerationRulesForUI returns formatted data for UI', function () {
    $uiRules = $this->service->getInvoiceGenerationRulesForUI();

    expect($uiRules)->toHaveKey('license');
    expect($uiRules['license'])->toHaveKey('label');
    expect($uiRules['license'])->toHaveKey('enabled');
    expect($uiRules['license']['label'])->toBe('moloni.owner_type_license');
});

// Committee-based Document Series Tests

test('getCommitteeLabels returns all committee codes', function () {
    $labels = MoloniSettingsService::getCommitteeLabels();

    expect($labels)->toHaveKey('DIVING');
    expect($labels)->toHaveKey('SCIENTIFIC');
    expect($labels)->toHaveKey('SPORT');
    expect($labels)->toHaveKey('DIVINGSERVICES');
});

test('getCommitteeDocumentSetMappings returns empty array when not configured', function () {
    expect($this->service->getCommitteeDocumentSetMappings())->toBe([]);
});

test('can save and retrieve committee document set mappings', function () {
    $mappings = [
        'DIVING' => 100,
        'SCIENTIFIC' => 100,
        'SPORT' => 200,
    ];

    $this->service->setCommitteeDocumentSetMappings($mappings);

    $retrieved = $this->service->getCommitteeDocumentSetMappings();

    expect($retrieved['DIVING'])->toBe(100);
    expect($retrieved['SCIENTIFIC'])->toBe(100);
    expect($retrieved['SPORT'])->toBe(200);
});

test('setCommitteeDocumentSetMappings filters out empty values', function () {
    $mappings = [
        'DIVING' => 100,
        'SCIENTIFIC' => '',
        'SPORT' => null,
        'DIVINGSERVICES' => 0,
    ];

    $this->service->setCommitteeDocumentSetMappings($mappings);

    $retrieved = $this->service->getCommitteeDocumentSetMappings();

    expect($retrieved)->toBe(['DIVING' => 100]);
});

test('getDocumentSetIdForCommittee returns mapped value', function () {
    $this->service->setCommitteeDocumentSetMappings([
        'DIVING' => 100,
        'SCIENTIFIC' => 100,
    ]);

    expect($this->service->getDocumentSetIdForCommittee('DIVING'))->toBe(100);
    expect($this->service->getDocumentSetIdForCommittee('SCIENTIFIC'))->toBe(100);
});

test('getDocumentSetIdForCommittee returns null when no mapping exists', function () {
    $this->service->setCommitteeDocumentSetMappings([
        'DIVING' => 100,
    ]);

    expect($this->service->getDocumentSetIdForCommittee('SPORT'))->toBeNull();
});

test('saveConfiguration saves committee document set mappings', function () {
    $this->service->saveConfiguration([
        'document_set_id' => 1,
        'committee_document_set_mappings' => [
            'DIVING' => 100,
            'SCIENTIFIC' => 100,
        ],
    ]);

    expect($this->service->getCommitteeDocumentSetMappings())->toBe([
        'DIVING' => 100,
        'SCIENTIFIC' => 100,
    ]);
});
