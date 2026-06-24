<?php

return [
    // Page title
    'title' => 'Moloni Integration',

    // Connection status
    'connection_status' => 'Connection Status',
    'connected' => 'Connected',
    'not_connected' => 'Not Connected',
    'token_expires' => 'Token expires',
    'minutes_remaining' => 'minutes remaining',

    // Buttons
    'authorize' => 'Authorize with Moloni',
    'disconnect' => 'Disconnect',
    'test_connection' => 'Test Connection',
    'sync_now' => 'Sync Now',
    'save' => 'Save Configuration',

    // Sync
    'sync_data' => 'Sync Data from Moloni',
    'last_sync' => 'Last sync',
    'no_sync_yet' => 'No data has been synchronized yet. Click "Sync Now" to fetch data from Moloni.',
    'sync_required' => 'Sync Required',
    'sync_data_first' => 'Please sync data from Moloni first to populate the configuration options.',

    // Configuration
    'configuration' => 'Invoice Configuration',
    'document_set' => 'Document Set',
    'default_tax' => 'Default Tax',
    'exempt_tax' => 'Exempt Tax (0% IVA)',
    'for_exempt_products' => 'for exempt products',
    'exempt_tax_help' => 'Select the 0% tax to use for VAT-exempt products. Required when plans have 0% VAT rate.',
    'no_exempt_tax_available' => 'No 0% tax rate configured in Moloni. Create a 0% tax in your Moloni account and sync data to enable this option.',
    'exemption_reason' => 'Exemption Reason',
    'required_for_exempt' => 'required for exempt products',
    'exemption_reason_help' => 'Legal exemption reason code (e.g., M07 for Article 9 CIVA). Required by Moloni for products without VAT.',
    'product_category' => 'Product Category',
    'payment_method' => 'Payment Method',
    'unit' => 'Unit of Measure',
    'select_option' => 'Select an option...',
    'optional' => 'optional',
    'category_help' => 'Only required if creating new products in Moloni. Not needed if products already exist (matched by reference).',
    'auto_detect' => 'Auto-detect from payment',
    'payment_method_help' => 'Leave empty to auto-detect based on document payment method (Bank Transfer, Multibanco, etc.).',
    'unit_help' => 'Only required if creating new products in Moloni. Not needed if products already exist (matched by reference).',

    // Status
    'status' => 'Integration Status',
    'ready' => 'Ready',
    'incomplete' => 'Configuration Incomplete',
    'invoices_will_be_generated' => 'Invoices will be automatically generated for paid documents.',
    'complete_configuration' => 'Please complete the configuration to enable automatic invoice generation.',

    // Logs
    'recent_logs' => 'Recent Sync Logs',
    'no_logs' => 'No sync logs available.',
    'type' => 'Type',
    'date' => 'Date',
    'duration' => 'Duration',
    'details' => 'Details',

    // Messages
    'connected_successfully' => 'Successfully connected to Moloni!',
    'disconnected_successfully' => 'Disconnected from Moloni.',
    'connection_successful' => 'Connection test successful!',
    'connection_test_failed' => 'Connection test failed. Please check your credentials.',
    'connection_failed' => 'Connection failed: :error',
    'sync_completed' => 'Data synchronized successfully. :count items fetched.',
    'sync_failed' => 'Sync failed: :error',
    'settings_saved' => 'Settings saved successfully.',
    'authorization_denied' => 'Authorization denied: :error',
    'no_authorization_code' => 'No authorization code received from Moloni.',
    'disconnect_confirm' => 'Are you sure you want to disconnect from Moloni? This will remove the stored tokens.',

    // Warnings
    'integration_disabled' => 'Integration Disabled',
    'enable_in_env' => 'Moloni integration is currently disabled. Set MOLONI_ENABLED=true in your .env file to enable it.',
    'missing_credentials' => 'Missing Credentials',
    'add_credentials_to_env' => 'Please add MOLONI_CLIENT_ID and MOLONI_CLIENT_SECRET to your .env file.',

    // New fields
    'company' => 'Company',
    'maturity_date' => 'Payment Terms',
    'days' => 'days',

    // Invoices
    'recent_invoices' => 'Recent Invoices',
    'no_invoices' => 'No invoices generated yet.',
    'failed_invoices' => 'Failed Invoices',
    'document' => 'Document',
    'moloni_number' => 'Moloni Number',
    'moloni_status' => 'Status',
    'total' => 'Total',
    'error' => 'Error',
    'actions' => 'Actions',
    'retry' => 'Retry',

    // Manual operations
    'invoice_created' => 'Invoice :number created successfully.',
    'invoice_not_created' => 'Invoice could not be created (Moloni not configured or document not eligible).',
    'invoice_creation_failed' => 'Invoice creation failed: :error',
    'customer_synced' => 'Customer synced successfully. Moloni ID: :id',
    'customer_sync_failed' => 'Customer sync failed: :error',

    // PDF and status
    'download_pdf' => 'Download PDF',
    'refresh_status' => 'Refresh',
    'pdf_not_available' => 'PDF is not available for this invoice.',
    'invoice_not_found' => 'No Moloni invoice found for this document.',
    'pdf_download_failed' => 'PDF download failed: :error',
    'status_refreshed' => 'Invoice :number status refreshed successfully.',
    'status_refresh_failed' => 'Status refresh failed: :error',
    'view_in_moloni' => 'View in Moloni',

    // Customer management
    'synced_customers' => 'Synced Customers',
    'no_customers' => 'No customers synced yet.',
    'customer_name' => 'Name',
    'customer_vat' => 'VAT Number',
    'customer_type' => 'Type',
    'moloni_id' => 'Moloni ID',
    'individual' => 'Individual',
    'entity' => 'Entity',
    'sync_customer_button' => 'Sync Customer',

    // Bulk operations
    'retry_selected' => 'Retry Selected',
    'select_all' => 'Select All',
    'bulk_retry_success' => ':count invoices successfully retried.',
    'bulk_retry_partial' => ':success invoices succeeded, :failed invoices failed.',
    'bulk_retry_failed' => ':count invoices failed to retry.',
    'no_invoices_selected' => 'Please select at least one invoice to retry.',

    // Product reference
    'product_reference' => 'Moloni Reference',
    'product_reference_help' => 'Unique reference code for matching this plan to a Moloni product. If set, the same product will be reused across invoices.',

    // Document series per type
    'document_series_by_type' => 'Document Series by Type',
    'document_series_by_type_description' => 'Configure different document series for each type of document. Leave empty to use the default series above.',
    'owner_type_license' => 'Licenses',
    'owner_type_membership' => 'Entity Quotas',
    'owner_type_member_subscription' => 'Individual Affiliations',
    'owner_type_certification' => 'Certifications',
    'owner_type_enrollment' => 'Entity Enrollments (Events)',
    'owner_type_individual_enrollment' => 'Staff/Officials Enrollments',
    'owner_type_athlete_enrollment' => 'Athlete Enrollments (Competitions)',
    'owner_type_insurance' => 'Insurance',
    'use_default' => 'Use Default',

    // Document type
    'document_type' => 'Document Type',
    'invoice_fatura' => 'Invoice (Fatura)',
    'invoice_receipt_fatura_recibo' => 'Invoice Receipt (Fatura-Recibo)',
    'document_type_help' => 'Invoice Receipt combines invoice + payment. Requires the document set to have Fatura-Recibo enabled in Moloni.',

    // Document status (draft vs finalized)
    'document_status' => 'Document Status',
    'status_finalized' => 'Finalized (Closed)',
    'status_draft' => 'Draft (Rascunho)',
    'document_status_help' => 'Draft invoices require manual finalization in Moloni before becoming valid fiscal documents. Use this for review before closing.',

    // Missing invoices
    'missing_invoices' => 'Documents Without Invoice',
    'documents' => 'documents',
    'create_invoices' => 'Create Invoices',
    'create_invoice' => 'Create Invoice',
    'owner' => 'Owner',
    'paid_date' => 'Paid Date',
    'no_owner' => 'No owner',
    'showing_first_50' => 'Showing first 50 of :count documents. The remaining will be shown after these are processed.',
    'no_missing_invoices' => 'All paid documents have their Moloni invoices created.',

    // Failure notification
    'notification_invoice_failed_subject' => 'Moloni Invoice Creation Failed',
    'notification_invoice_failed_greeting' => 'Invoice Generation Alert',
    'notification_invoice_failed_intro' => 'The system was unable to create a Moloni invoice for document :document after multiple attempts.',
    'notification_invoice_failed_error' => 'Error: :error',
    'notification_invoice_failed_attempts' => 'The system attempted :attempts times before giving up.',
    'notification_invoice_failed_action' => 'View Moloni Settings',
    'notification_invoice_failed_document_link' => 'You can view the document at: :url',
    'notification_invoice_failed_database' => 'Failed to create Moloni invoice for document :document',

    // Invoice generation rules
    'invoice_generation_rules' => 'Invoice Generation Rules',
    'invoice_generation_rules_description' => 'Select which document detail types should trigger Moloni invoice generation. Unchecked types will skip invoice creation.',
    'invoice_generation_rules_saved' => 'Invoice generation rules saved successfully.',
    'save_invoice_rules' => 'Save Invoice Rules',
    'require_all_details_enabled' => 'Require all detail types to be enabled',
    'require_all_details_enabled_help' => 'If checked, invoices will only be created when ALL detail types in the document are enabled. If unchecked, invoices are created when ANY enabled type is present.',

    // Committee-based document series
    'committee_document_series' => 'Committee-based Document Series',
    'committee_document_series_description' => 'Select the document series for licenses and certifications based on their committee. This takes priority over the type-based mapping below.',
    'committee_diving' => 'Diving Committee',
    'committee_scientific' => 'Scientific Committee',
    'committee_sport' => 'Sport Committee',
    'committee_divingservices' => 'Diving Services Committee',

    // Warnings and validation
    'warning' => 'Warning',
    'document_set_not_in_cache' => 'The configured document set (ID: :id) is not in the synced data.',
    'sync_to_refresh' => 'Click "Sync Data" to refresh the available document sets from Moloni.',
    'not_in_cache' => 'Not in synced data',
    'no_at_codes' => 'No AT codes - invalid for invoices',

    // Activity log
    'activity_log_description' => 'Recent invoice and sync activity',
    'invoice_created_title' => 'Invoice Created',
    'invoice_failed_title' => 'Invoice Failed',
    'sync_completed_title' => 'Data Sync Completed',
    'sync_failed_title' => 'Data Sync Failed',
    'success' => 'Success',
    'failed' => 'Failed',
    'view_document' => 'View Document',
    'companies_synced' => 'companies',
    'series_synced' => 'series',
    'taxes_synced' => 'taxes',
    'categories_synced' => 'categories',
];
