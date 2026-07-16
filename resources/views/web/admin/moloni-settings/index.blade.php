<x-layout>
    @php
        // Get company VAT for building Moloni URLs
        $companyId = $currentSettings['company_id'] ?? null;
        $companyVat = null;
        if ($companyId && !empty($companies)) {
            foreach ($companies as $company) {
                if (($company['id'] ?? null) == $companyId) {
                    $companyVat = $company['vat'] ?? null;
                    break;
                }
            }
        }
    @endphp
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-4">
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('moloni.title') }}</h1>
            </div>
            <div>
                <a href="{{ route('admin.integration-issues.index') }}" class="btn btn-secondary">
                    {{ __('integration_issues.title') }}
                </a>
            </div>
        </div>

        @if(!$isEnabled)
            <x-information-box
                type="warning"
                title="{{ __('moloni.integration_disabled') }}"
                body="{{ __('moloni.enable_in_env') }}">
            </x-information-box>
        @endif

        @if(!$hasCredentials)
            <x-information-box
                type="error"
                title="{{ __('moloni.missing_credentials') }}"
                body="{{ __('moloni.add_credentials_to_env') }}">
            </x-information-box>
        @endif

        <!-- Connection Status Card -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-slate-800">{{ __('moloni.connection_status') }}</h2>
                @if($isConnected)
                    <span class="px-3 py-1 text-sm font-medium rounded-full bg-green-100 text-green-800">
                        {{ __('moloni.connected') }}
                    </span>
                @else
                    <span class="px-3 py-1 text-sm font-medium rounded-full bg-red-100 text-red-800">
                        {{ __('moloni.not_connected') }}
                    </span>
                @endif
            </div>

            @if($isConnected && $token)
                <div class="text-sm text-gray-600 mb-4">
                    <p>
                        <span class="font-medium">{{ __('moloni.token_expires') }}:</span>
                        {{ $token->access_token_expires_at->format('Y-m-d H:i') }}
                        ({{ $token->accessTokenExpiresInMinutes() }} {{ __('moloni.minutes_remaining') }})
                    </p>
                </div>
            @endif

            <div class="flex flex-wrap gap-2">
                @if($isConnected)
                    <form action="{{ route('admin.moloni-settings.test') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="btn btn-secondary">
                            {{ __('moloni.test_connection') }}
                        </button>
                    </form>
                    <form action="{{ route('admin.moloni-settings.disconnect') }}" method="POST" class="inline"
                          onsubmit="return confirm('{{ __('moloni.disconnect_confirm') }}')">
                        @csrf
                        <button type="submit" class="btn bg-red-500 hover:bg-red-600 text-white">
                            {{ __('moloni.disconnect') }}
                        </button>
                    </form>
                @else
                    @if($hasCredentials)
                        <a href="{{ route('admin.moloni-settings.authorize') }}" class="btn btn-primary">
                            {{ __('moloni.authorize') }}
                        </a>
                    @endif
                @endif
            </div>
        </div>

        @if($isConnected)
            <!-- Sync Data Card -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-slate-800">{{ __('moloni.sync_data') }}</h2>
                </div>

                @if($lastDataSync)
                    <p class="text-sm text-gray-600 mb-4">
                        <span class="font-medium">{{ __('moloni.last_sync') }}:</span>
                        {{ $lastDataSync->created_at->format('Y-m-d H:i') }}
                    </p>
                @else
                    <p class="text-sm text-gray-600 mb-4">
                        {{ __('moloni.no_sync_yet') }}
                    </p>
                @endif

                <form action="{{ route('admin.moloni-settings.sync-data') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-secondary">
                        {{ __('moloni.sync_now') }}
                    </button>
                </form>
            </div>

            <!-- Configuration Form -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold text-slate-800 mb-4">{{ __('moloni.configuration') }}</h2>

                @if(empty($documentSets) || empty($taxes) || empty($units) || empty($paymentMethods))
                    <x-information-box
                        type="info"
                        title="{{ __('moloni.sync_required') }}"
                        body="{{ __('moloni.sync_data_first') }}">
                    </x-information-box>
                @else
                    <form action="{{ route('admin.moloni-settings.save') }}" method="POST" class="space-y-4">
                        @csrf

                        @if(count($companies) > 1)
                        <!-- Company Selection -->
                        <div>
                            <label for="company_id" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('moloni.company') }}
                            </label>
                            <select name="company_id" id="company_id"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @foreach($companies as $company)
                                    <option value="{{ $company['id'] }}"
                                            {{ $currentSettings['company_id'] == $company['id'] ? 'selected' : '' }}>
                                        {{ $company['name'] }} {{ $company['vat'] ? '('.$company['vat'].')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <!-- Document Set -->
                        <div>
                            <label for="document_set_id" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('moloni.document_set') }} *
                            </label>
                            @php
                                $currentDocSetId = $currentSettings['document_set_id'] ?? null;
                                $docSetInCache = collect($documentSets)->contains(fn($s) => ($s['id'] ?? $s['document_set_id'] ?? null) == $currentDocSetId);
                            @endphp
                            @if($currentDocSetId && !$docSetInCache)
                                <div class="mb-2 p-2 bg-yellow-50 border border-yellow-200 rounded-md text-sm text-yellow-800">
                                    <strong>{{ __('moloni.warning') }}:</strong> {{ __('moloni.document_set_not_in_cache', ['id' => $currentDocSetId]) }}
                                    <br><span class="text-xs">{{ __('moloni.sync_to_refresh') }}</span>
                                </div>
                            @endif
                            <select name="document_set_id" id="document_set_id" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">{{ __('moloni.select_option') }}</option>
                                @if($currentDocSetId && !$docSetInCache)
                                    <option value="{{ $currentDocSetId }}" selected class="text-yellow-600">
                                        ID: {{ $currentDocSetId }} ({{ __('moloni.not_in_cache') }})
                                    </option>
                                @endif
                                @foreach($documentSets as $set)
                                    @php $setId = $set['id'] ?? $set['document_set_id'] ?? null; @endphp
                                    <option value="{{ $setId }}"
                                            {{ $currentDocSetId == $setId ? 'selected' : '' }}>
                                        {{ $set['name'] }} {{ $set['abbreviation'] ? '('.$set['abbreviation'].')' : '' }}
                                        @if(isset($set['is_valid_for_invoices']) && !$set['is_valid_for_invoices'])
                                            ({{ __('moloni.no_at_codes') }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('document_set_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Default Tax -->
                        <div>
                            <label for="default_tax_id" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('moloni.default_tax') }} *
                            </label>
                            <select name="default_tax_id" id="default_tax_id" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">{{ __('moloni.select_option') }}</option>
                                @foreach($taxes as $tax)
                                    <option value="{{ $tax['id'] }}"
                                            {{ $currentSettings['default_tax_id'] == $tax['id'] ? 'selected' : '' }}>
                                        {{ $tax['name'] }} ({{ $tax['value'] }}%)
                                    </option>
                                @endforeach
                            </select>
                            @error('default_tax_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Exempt Tax (0% IVA) -->
                        @php
                            $exemptTaxes = collect($taxes)->filter(fn($tax) => $tax['value'] == 0);
                        @endphp
                        <div>
                            <label for="exempt_tax_id" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('moloni.exempt_tax') }}
                                <span class="text-gray-400 font-normal">({{ __('moloni.for_exempt_products') }})</span>
                            </label>
                            @if($exemptTaxes->isEmpty())
                                <p class="text-sm text-amber-600 bg-amber-50 border border-amber-200 rounded-md px-3 py-2">
                                    {{ __('moloni.no_exempt_tax_available') }}
                                </p>
                            @else
                                <select name="exempt_tax_id" id="exempt_tax_id"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">{{ __('moloni.select_option') }}</option>
                                    @foreach($exemptTaxes as $tax)
                                        <option value="{{ $tax['id'] }}"
                                                {{ ($currentSettings['exempt_tax_id'] ?? null) == $tax['id'] ? 'selected' : '' }}>
                                            {{ $tax['name'] }} ({{ $tax['value'] }}%)
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-xs text-gray-500">{{ __('moloni.exempt_tax_help') }}</p>
                                @error('exempt_tax_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            @endif
                        </div>

                        <!-- Default Exemption Reason -->
                        <div>
                            <label for="default_exemption_reason" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('moloni.exemption_reason') }}
                                <span class="text-gray-400 font-normal">({{ __('moloni.required_for_exempt') }})</span>
                            </label>
                            <select name="default_exemption_reason" id="default_exemption_reason"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">{{ __('moloni.select_option') }}</option>
                                @foreach($taxExemptions as $exemption)
                                    <option value="{{ $exemption['code'] }}"
                                            {{ ($currentSettings['default_exemption_reason'] ?? null) == $exemption['code'] ? 'selected' : '' }}>
                                        {{ $exemption['code'] }} - {{ $exemption['name'] }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">{{ __('moloni.exemption_reason_help') }}</p>
                            @error('default_exemption_reason')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Default Unit (Optional) -->
                        <div>
                            <label for="default_unit_id" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('moloni.unit') }}
                                <span class="text-gray-400 font-normal">({{ __('moloni.optional') }})</span>
                            </label>
                            <select name="default_unit_id" id="default_unit_id"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">{{ __('moloni.select_option') }}</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit['id'] }}"
                                            {{ $currentSettings['default_unit_id'] == $unit['id'] ? 'selected' : '' }}>
                                        {{ $unit['name'] }} {{ $unit['abbreviation'] ? '('.$unit['abbreviation'].')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">{{ __('moloni.unit_help') }}</p>
                            @error('default_unit_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Default Category (Optional) -->
                        <div>
                            <label for="default_category_id" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('moloni.product_category') }}
                                <span class="text-gray-400 font-normal">({{ __('moloni.optional') }})</span>
                            </label>
                            <select name="default_category_id" id="default_category_id"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">{{ __('moloni.select_option') }}</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category['id'] }}"
                                            {{ $currentSettings['default_category_id'] == $category['id'] ? 'selected' : '' }}>
                                        {{ $category['name'] }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">{{ __('moloni.category_help') }}</p>
                            @error('default_category_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Payment Method (Optional) -->
                        <div>
                            <label for="payment_method_id" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('moloni.payment_method') }}
                                <span class="text-gray-400 font-normal">({{ __('moloni.optional') }})</span>
                            </label>
                            <select name="payment_method_id" id="payment_method_id"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">{{ __('moloni.auto_detect') }}</option>
                                @foreach($paymentMethods as $method)
                                    <option value="{{ $method['id'] }}"
                                            {{ $currentSettings['payment_method_id'] == $method['id'] ? 'selected' : '' }}>
                                        {{ $method['name'] }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">{{ __('moloni.payment_method_help') }}</p>
                            @error('payment_method_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        @if(!empty($maturityDates))
                        <!-- Maturity Date -->
                        <div>
                            <label for="default_maturity_date_id" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('moloni.maturity_date') }}
                            </label>
                            <select name="default_maturity_date_id" id="default_maturity_date_id"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">{{ __('moloni.select_option') }}</option>
                                @foreach($maturityDates as $date)
                                    <option value="{{ $date['id'] }}"
                                            {{ $currentSettings['default_maturity_date_id'] == $date['id'] ? 'selected' : '' }}>
                                        {{ $date['name'] }} ({{ $date['days'] }} {{ __('moloni.days') }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <!-- Document Type -->
                        <div>
                            <label for="use_invoice_receipts" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('moloni.document_type') }}
                            </label>
                            <select name="use_invoice_receipts" id="use_invoice_receipts"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="0" {{ !($currentSettings['use_invoice_receipts'] ?? false) ? 'selected' : '' }}>
                                    {{ __('moloni.invoice_fatura') }}
                                </option>
                                <option value="1" {{ ($currentSettings['use_invoice_receipts'] ?? false) ? 'selected' : '' }}>
                                    {{ __('moloni.invoice_receipt_fatura_recibo') }}
                                </option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">{{ __('moloni.document_type_help') }}</p>
                        </div>

                        <!-- Create as Draft -->
                        <div>
                            <label for="create_as_draft" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('moloni.document_status') }}
                            </label>
                            <select name="create_as_draft" id="create_as_draft"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="0" {{ !($currentSettings['create_as_draft'] ?? false) ? 'selected' : '' }}>
                                    {{ __('moloni.status_finalized') }}
                                </option>
                                <option value="1" {{ ($currentSettings['create_as_draft'] ?? false) ? 'selected' : '' }}>
                                    {{ __('moloni.status_draft') }}
                                </option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">{{ __('moloni.document_status_help') }}</p>
                        </div>

                        <!-- Committee-based Document Series -->
                        <div class="pt-6 border-t border-gray-200 mt-6">
                            <h3 class="text-md font-semibold text-slate-800 mb-2">{{ __('moloni.committee_document_series') }}</h3>
                            <p class="text-sm text-gray-500 mb-4">{{ __('moloni.committee_document_series_description') }}</p>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach($committeeLabels as $committeeCode => $labelKey)
                                    <div>
                                        <label for="committee_document_set_mappings_{{ $committeeCode }}" class="block text-sm font-medium text-gray-700 mb-1">
                                            {{ __($labelKey) }}
                                        </label>
                                        <select name="committee_document_set_mappings[{{ $committeeCode }}]" id="committee_document_set_mappings_{{ $committeeCode }}"
                                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                            <option value="">{{ __('moloni.use_default') }}</option>
                                            @foreach($documentSets as $set)
                                                <option value="{{ $set['id'] }}"
                                                        {{ ($committeeDocumentSetMappings[$committeeCode] ?? null) == $set['id'] ? 'selected' : '' }}>
                                                    {{ $set['name'] }} {{ $set['abbreviation'] ? '('.$set['abbreviation'].')' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Document Series by Type (Fallback) -->
                        <div class="pt-6 border-t border-gray-200 mt-6">
                            <h3 class="text-md font-semibold text-slate-800 mb-2">{{ __('moloni.document_series_by_type') }}</h3>
                            <p class="text-sm text-gray-500 mb-4">{{ __('moloni.document_series_by_type_description') }}</p>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach($ownerTypeLabels as $typeKey => $labelKey)
                                    <div>
                                        <label for="document_set_mappings_{{ $typeKey }}" class="block text-sm font-medium text-gray-700 mb-1">
                                            {{ __($labelKey) }}
                                        </label>
                                        <select name="document_set_mappings[{{ $typeKey }}]" id="document_set_mappings_{{ $typeKey }}"
                                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                            <option value="">{{ __('moloni.use_default') }}</option>
                                            @foreach($documentSets as $set)
                                                <option value="{{ $set['id'] }}"
                                                        {{ ($currentSettings['document_set_mappings'][$typeKey] ?? null) == $set['id'] ? 'selected' : '' }}>
                                                    {{ $set['name'] }} {{ $set['abbreviation'] ? '('.$set['abbreviation'].')' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="pt-4">
                            <button type="submit" class="btn btn-primary">
                                {{ __('moloni.save') }}
                            </button>
                        </div>
                    </form>
                @endif
            </div>

            <!-- Configuration Status -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold text-slate-800 mb-4">{{ __('moloni.status') }}</h2>

                <div class="flex items-center gap-2">
                    @if($isConfigured)
                        <span class="px-3 py-1 text-sm font-medium rounded-full bg-green-100 text-green-800">
                            {{ __('moloni.ready') }}
                        </span>
                        <span class="text-sm text-gray-600">{{ __('moloni.invoices_will_be_generated') }}</span>
                    @else
                        <span class="px-3 py-1 text-sm font-medium rounded-full bg-yellow-100 text-yellow-800">
                            {{ __('moloni.incomplete') }}
                        </span>
                        <span class="text-sm text-gray-600">{{ __('moloni.complete_configuration') }}</span>
                    @endif
                </div>
            </div>

            <!-- Invoice Generation Rules -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold text-slate-800 mb-2">{{ __('moloni.invoice_generation_rules') }}</h2>
                <p class="text-sm text-gray-500 mb-4">{{ __('moloni.invoice_generation_rules_description') }}</p>

                <form action="{{ route('admin.moloni-settings.invoice-generation-rules') }}" method="POST">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                        @foreach($invoiceGenerationRules as $typeKey => $config)
                            <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                                <input type="hidden" name="enabled_types[{{ $typeKey }}]" value="0">
                                <input type="checkbox"
                                       name="enabled_types[{{ $typeKey }}]"
                                       value="1"
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                       {{ $config['enabled'] ? 'checked' : '' }}>
                                <span class="text-sm font-medium text-gray-700">{{ __($config['label']) }}</span>
                            </label>
                        @endforeach
                    </div>

                    <div class="border-t border-gray-200 pt-4 mt-4">
                        <label class="flex items-center gap-3">
                            <input type="hidden" name="require_all" value="0">
                            <input type="checkbox"
                                   name="require_all"
                                   value="1"
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                   {{ $invoiceGenerationRequireAll ? 'checked' : '' }}>
                            <div>
                                <span class="text-sm font-medium text-gray-700">{{ __('moloni.require_all_details_enabled') }}</span>
                                <p class="text-xs text-gray-500">{{ __('moloni.require_all_details_enabled_help') }}</p>
                            </div>
                        </label>
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="btn btn-primary">
                            {{ __('moloni.save_invoice_rules') }}
                        </button>
                    </div>
                </form>
            </div>
        @endif

        @if($isConnected)
        <!-- Missing Invoices (Paid documents without Moloni invoice) -->
        @if($missingInvoices->isNotEmpty())
        <div class="bg-white rounded-lg shadow mb-6" x-data="{ selectedMissing: [] }">
            <div class="px-4 py-3 border-b bg-amber-50 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <h2 class="font-semibold text-amber-800">{{ __('moloni.missing_invoices') }}</h2>
                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-amber-200 text-amber-800">
                        {{ $missingInvoicesCount }} {{ __('moloni.documents') }}
                    </span>
                </div>
                <form action="{{ route('admin.moloni-settings.bulk-retry-invoices') }}" method="POST" class="inline"
                      x-show="selectedMissing.length > 0">
                    @csrf
                    <template x-for="docId in selectedMissing" :key="docId">
                        <input type="hidden" name="document_ids[]" :value="docId">
                    </template>
                    <button type="submit" class="btn bg-amber-600 hover:bg-amber-700 text-white text-sm px-3 py-1">
                        {{ __('moloni.create_invoices') }} (<span x-text="selectedMissing.length"></span>)
                    </button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-10">
                                <input type="checkbox"
                                       class="rounded border-gray-300 text-amber-600 focus:ring-amber-500"
                                       @change="selectedMissing = $event.target.checked
                                           ? [...document.querySelectorAll('.missing-checkbox')].map(cb => cb.value)
                                           : []">
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('moloni.document') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('moloni.owner') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('moloni.total') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('moloni.paid_date') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('moloni.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($missingInvoices as $doc)
                            <tr>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <input type="checkbox"
                                           class="missing-checkbox rounded border-gray-300 text-amber-600 focus:ring-amber-500"
                                           value="{{ $doc->id }}"
                                           @change="$event.target.checked
                                               ? (!selectedMissing.includes('{{ $doc->id }}') && selectedMissing.push('{{ $doc->id }}'))
                                               : selectedMissing = selectedMissing.filter(id => id !== '{{ $doc->id }}')">
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <a href="{{ route('admin.document.show', $doc->id) }}" class="text-blue-600 hover:underline">
                                        {{ $doc->number_extended ?? $doc->id }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                                    @if($doc->owner)
                                        {{ $doc->owner->name ?? '-' }}
                                    @else
                                        <span class="text-gray-400">{{ __('moloni.no_owner') }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                                    {{ money($doc->total_value, $doc->currency) }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                                    {{ $doc->updated_at->format('Y-m-d H:i') }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <form action="{{ route('admin.moloni-settings.retry-invoice') }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="document_id" value="{{ $doc->id }}">
                                        <button type="submit" class="text-amber-600 hover:text-amber-800 text-sm font-medium">
                                            {{ __('moloni.create_invoice') }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($missingInvoicesCount > 50)
                <div class="px-4 py-3 border-t bg-amber-50 text-sm text-amber-700">
                    {{ __('moloni.showing_first_50', ['count' => $missingInvoicesCount]) }}
                </div>
            @endif
        </div>
        @endif

        <!-- Recent Invoices -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-4 py-3 border-b">
                <h2 class="font-semibold text-slate-800">{{ __('moloni.recent_invoices') }}</h2>
            </div>

            @if($recentInvoices->isEmpty())
                <div class="p-4 text-gray-500 text-center">
                    {{ __('moloni.no_invoices') }}
                </div>
            @else
                <x-dynamic-table :headers="[
                    __('moloni.document'),
                    __('moloni.moloni_number'),
                    __('moloni.moloni_status'),
                    __('moloni.total'),
                    __('moloni.date'),
                    __('moloni.actions')
                ]">
                    @foreach($recentInvoices as $invoice)
                        <tr>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <a href="{{ route('admin.document.show', $invoice->document_id) }}" class="text-blue-600 hover:underline">
                                    {{ $invoice->document?->number_extended ?? $invoice->document_id }}
                                </a>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <code class="bg-gray-100 px-2 py-1 rounded text-sm">{{ $invoice->moloni_number }}</code>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-{{ $invoice->moloni_status === 'closed' ? 'green' : 'yellow' }}-100 text-{{ $invoice->moloni_status === 'closed' ? 'green' : 'yellow' }}-800">
                                    {{ ucfirst($invoice->moloni_status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                                {{ money($invoice->moloni_total, $invoice->currency) }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                                {{ $invoice->synced_at?->format('Y-m-d H:i') }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    @if($companyVat)
                                        <a href="https://www.moloni.pt/{{ $companyVat }}/Faturas/showDetail/{{ $invoice->moloni_document_id }}"
                                           target="_blank"
                                           class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                                           title="{{ __('moloni.view_in_moloni') }}">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                            </svg>
                                        </a>
                                    @endif
                                    @if($invoice->moloni_status === 'closed')
                                        <a href="{{ route('admin.moloni-settings.invoice.pdf', $invoice) }}"
                                           target="_blank"
                                           class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                                           title="{{ __('moloni.download_pdf') }}">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </a>
                                    @endif
                                    <form action="{{ route('admin.moloni-settings.invoice.refresh', $invoice) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit"
                                                class="text-gray-600 hover:text-gray-800"
                                                title="{{ __('moloni.refresh_status') }}">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-dynamic-table>
            @endif
        </div>

        <!-- Failed Invoices -->
        @if($failedInvoiceLogs->isNotEmpty())
        <div class="bg-white rounded-lg shadow mb-6" x-data="{ selectedDocuments: [] }">
            <div class="px-4 py-3 border-b bg-red-50 flex items-center justify-between">
                <h2 class="font-semibold text-red-800">{{ __('moloni.failed_invoices') }}</h2>
                <form action="{{ route('admin.moloni-settings.bulk-retry-invoices') }}" method="POST" class="inline"
                      x-show="selectedDocuments.length > 0">
                    @csrf
                    <template x-for="docId in selectedDocuments" :key="docId">
                        <input type="hidden" name="document_ids[]" :value="docId">
                    </template>
                    <button type="submit" class="btn bg-red-600 hover:bg-red-700 text-white text-sm px-3 py-1">
                        {{ __('moloni.retry_selected') }} (<span x-text="selectedDocuments.length"></span>)
                    </button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-10">
                                <input type="checkbox"
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                       @change="selectedDocuments = $event.target.checked
                                           ? [...document.querySelectorAll('.doc-checkbox')].map(cb => cb.value)
                                           : []">
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('moloni.document') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('moloni.error') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('moloni.date') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('moloni.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($failedInvoiceLogs as $log)
                            @php
                                $documentId = $log->data['document_id'] ?? null;
                                $documentNumber = $log->data['document_number'] ?? null;
                            @endphp
                            <tr>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if($documentId)
                                        <input type="checkbox"
                                               class="doc-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                               value="{{ $documentId }}"
                                               @change="$event.target.checked
                                                   ? selectedDocuments.push('{{ $documentId }}')
                                                   : selectedDocuments = selectedDocuments.filter(id => id !== '{{ $documentId }}')">
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if($documentId)
                                        <a href="{{ route('admin.document.show', $documentId) }}" class="text-blue-600 hover:underline">
                                            {{ $documentNumber ?? Str::limit($documentId, 12) }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-red-600">
                                    <div class="max-w-md" title="{{ $log->error_message }}">
                                        <span class="font-medium">{{ $log->error_message }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                                    {{ $log->created_at->format('Y-m-d H:i') }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if($documentId)
                                        <form action="{{ route('admin.moloni-settings.retry-invoice') }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="document_id" value="{{ $documentId }}">
                                            <button type="submit" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                                {{ __('moloni.retry') }}
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Synced Customers -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-4 py-3 border-b">
                <h2 class="font-semibold text-slate-800">{{ __('moloni.synced_customers') }}</h2>
            </div>

            @if($syncedCustomers->isEmpty())
                <div class="p-4 text-gray-500 text-center">
                    {{ __('moloni.no_customers') }}
                </div>
            @else
                <x-dynamic-table :headers="[
                    __('moloni.customer_name'),
                    __('moloni.customer_vat'),
                    __('moloni.customer_type'),
                    __('moloni.moloni_id'),
                    __('moloni.date')
                ]">
                    @foreach($syncedCustomers as $customer)
                        <tr>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($customer->customerable)
                                    @php
                                        $isIndividual = $customer->customerable_type === \Domain\Individuals\Models\Individual::class;
                                        $route = $isIndividual
                                            ? route('admin.individual.show', $customer->customerable_id)
                                            : route('admin.entity.show', $customer->customerable_id);
                                    @endphp
                                    <a href="{{ $route }}" class="text-blue-600 hover:underline">
                                        {{ $customer->moloni_name ?? $customer->customerable->name ?? '-' }}
                                    </a>
                                @else
                                    {{ $customer->moloni_name ?? '-' }}
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                                <code class="bg-gray-100 px-2 py-1 rounded">{{ $customer->moloni_vat ?? '-' }}</code>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @php
                                    $isIndividual = $customer->customerable_type === \Domain\Individuals\Models\Individual::class;
                                @endphp
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-{{ $isIndividual ? 'blue' : 'purple' }}-100 text-{{ $isIndividual ? 'blue' : 'purple' }}-800">
                                    {{ $isIndividual ? __('moloni.individual') : __('moloni.entity') }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                                <code class="bg-gray-100 px-2 py-1 rounded">{{ $customer->moloni_customer_id }}</code>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                                {{ $customer->created_at->format('Y-m-d H:i') }}
                            </td>
                        </tr>
                    @endforeach
                </x-dynamic-table>
            @endif
        </div>
        @endif

        <!-- Activity Log - User Friendly Design -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h2 class="font-semibold text-slate-800 text-lg">{{ __('moloni.recent_logs') }}</h2>
                    <p class="text-sm text-gray-500 mt-0.5">{{ __('moloni.activity_log_description') }}</p>
                </div>
                <div class="flex items-center gap-2 text-xs text-gray-500">
                    <span class="inline-flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-green-500"></span> {{ __('moloni.success') }}
                    </span>
                    <span class="inline-flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-red-500"></span> {{ __('moloni.failed') }}
                    </span>
                </div>
            </div>

            @if($recentLogs->isEmpty())
                <div class="p-8 text-center">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <p class="text-gray-500">{{ __('moloni.no_logs') }}</p>
                </div>
            @else
                <div class="divide-y divide-gray-100">
                    @foreach($recentLogs as $log)
                        @php
                            $logData = is_array($log->data) ? $log->data : json_decode($log->data, true);
                            $isInvoice = $log->sync_type === 'invoice_create';
                            $isSync = $log->sync_type === 'data_sync';
                            $isSuccess = $log->status === 'success';
                            $moloniDocId = $logData['moloni_document_id'] ?? null;
                            $moloniNumber = $logData['moloni_number'] ?? null;
                            $documentId = $logData['document_id'] ?? null;
                        @endphp

                        <div class="px-5 py-4 hover:bg-gray-50 transition-colors duration-150">
                            <div class="flex items-start gap-4">
                                {{-- Status Icon --}}
                                <div class="flex-shrink-0 mt-0.5">
                                    @if($isInvoice)
                                        <div class="w-10 h-10 rounded-full {{ $isSuccess ? 'bg-green-100' : 'bg-red-100' }} flex items-center justify-center">
                                            @if($isSuccess)
                                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                            @else
                                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                </svg>
                                            @endif
                                        </div>
                                    @elseif($isSync)
                                        <div class="w-10 h-10 rounded-full {{ $isSuccess ? 'bg-blue-100' : 'bg-red-100' }} flex items-center justify-center">
                                            <svg class="w-5 h-5 {{ $isSuccess ? 'text-blue-600' : 'text-red-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                            </svg>
                                        </div>
                                    @else
                                        <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center">
                                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>

                                {{-- Main Content --}}
                                <div class="flex-1 min-w-0">
                                    {{-- Title Row --}}
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-medium text-gray-900">
                                            @if($isInvoice)
                                                {{ $isSuccess ? __('moloni.invoice_created_title') : __('moloni.invoice_failed_title') }}
                                            @elseif($isSync)
                                                {{ $isSuccess ? __('moloni.sync_completed_title') : __('moloni.sync_failed_title') }}
                                            @else
                                                {{ ucfirst(str_replace('_', ' ', $log->sync_type)) }}
                                            @endif
                                        </span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $isSuccess ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $isSuccess ? __('moloni.success') : __('moloni.failed') }}
                                        </span>
                                    </div>

                                    {{-- Details based on type --}}
                                    @if($isInvoice && $isSuccess)
                                        <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm">
                                            @if($moloniNumber && $moloniNumber !== 'PENDING')
                                                <div class="flex items-center gap-1.5 text-gray-700">
                                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                                                    </svg>
                                                    <span class="font-mono bg-gray-100 px-2 py-0.5 rounded">{{ $moloniNumber }}</span>
                                                </div>
                                            @endif
                                            @if($moloniDocId && $companyVat)
                                                <a href="https://www.moloni.pt/{{ $companyVat }}/Faturas/showDetail/{{ $moloniDocId }}"
                                                   target="_blank"
                                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-md transition-colors duration-150 font-medium">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                    </svg>
                                                    {{ __('moloni.view_in_moloni') }}
                                                </a>
                                            @endif
                                        </div>
                                    @elseif($isInvoice && !$isSuccess)
                                        <div class="mt-2">
                                            @if($log->error_message)
                                                <div class="p-3 bg-red-50 border border-red-100 rounded-md">
                                                    <div class="flex items-start gap-2">
                                                        <svg class="w-4 h-4 text-red-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        <p class="text-sm text-red-700">{{ $log->error_message }}</p>
                                                    </div>
                                                </div>
                                            @endif
                                            @if($documentId)
                                                <div class="mt-2 flex items-center gap-2">
                                                    <a href="{{ route('admin.document.show', $documentId) }}"
                                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md transition-colors duration-150 text-sm">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                        </svg>
                                                        {{ __('moloni.view_document') }}
                                                    </a>
                                                    <form action="{{ route('admin.moloni-settings.retry-invoice') }}" method="POST" class="inline">
                                                        @csrf
                                                        <input type="hidden" name="document_id" value="{{ $documentId }}">
                                                        <button type="submit"
                                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-100 hover:bg-amber-200 text-amber-800 rounded-md transition-colors duration-150 text-sm font-medium">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                            </svg>
                                                            {{ __('moloni.retry') }}
                                                        </button>
                                                    </form>
                                                </div>
                                            @endif
                                        </div>
                                    @elseif($isSync && $isSuccess && $logData)
                                        <div class="mt-2 flex flex-wrap gap-2">
                                            @if(isset($logData['companies']))
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-gray-100 text-gray-700 rounded-md text-sm">
                                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                                    </svg>
                                                    {{ $logData['companies'] }} {{ __('moloni.companies_synced') }}
                                                </span>
                                            @endif
                                            @if(isset($logData['document_sets']))
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-gray-100 text-gray-700 rounded-md text-sm">
                                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                                    </svg>
                                                    {{ $logData['document_sets'] }} {{ __('moloni.series_synced') }}
                                                </span>
                                            @endif
                                            @if(isset($logData['taxes']))
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-gray-100 text-gray-700 rounded-md text-sm">
                                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                    </svg>
                                                    {{ $logData['taxes'] }} {{ __('moloni.taxes_synced') }}
                                                </span>
                                            @endif
                                            @if(isset($logData['categories']))
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-gray-100 text-gray-700 rounded-md text-sm">
                                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                                    </svg>
                                                    {{ $logData['categories'] }} {{ __('moloni.categories_synced') }}
                                                </span>
                                            @endif
                                        </div>
                                    @elseif($isSync && !$isSuccess)
                                        @if($log->error_message)
                                            <div class="mt-2 p-3 bg-red-50 border border-red-100 rounded-md">
                                                <p class="text-sm text-red-700">{{ $log->error_message }}</p>
                                            </div>
                                        @endif
                                    @endif

                                    {{-- Timestamp --}}
                                    <div class="mt-2 flex items-center gap-3 text-xs text-gray-500">
                                        <span class="flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            {{ $log->created_at->format('d/m/Y H:i') }}
                                        </span>
                                        @if($log->duration_ms)
                                            <span class="flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                                </svg>
                                                {{ $log->getDurationFormatted() }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
</x-layout>
