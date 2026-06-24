<?php

$primaryName = config('branding.primary.name', 'Example Federation');
$primaryShortName = config('branding.primary.short_name', 'DF');

return [
    // Page titles
    'payment_documents' => 'Payment Documents',
    'payment_documents_disclaimer' => 'These documents are for informational purposes only, they have no legal validity. For each document, an invoice - receipt must be issued in a certified accounting program.',
    'invoices' => 'Invoices',
    'create_manual_order' => 'Create Manual Order',
    'latest_documents' => 'Latest Documents',
    'filtered_results' => 'Filtered Results',
    'entities' => 'Entities',
    'member' => 'Member',

    // Table headers
    'number' => '# Number',
    'type' => 'Type',
    'document_name' => 'Document Name',
    'status' => 'Status',
    'issue_date' => 'Issue Date',
    'expiration_date' => 'Expiration Date',
    'total' => 'Total',
    'id' => 'ID',
    'download' => 'Download',
    'category' => 'Category',

    // Document detail page
    'document_detail' => 'Document Detail',
    'payment' => 'Payment',
    'select_method' => 'Select a method',
    'proceed_to_payment' => 'Proceed to Payment',

    // Document info labels
    'number_label' => 'Number',
    'type_label' => 'Type',
    'date_label' => 'Date',
    'recipient' => 'Recipient',
    'vat_number' => 'VAT number',
    'city' => 'City',
    'address' => 'Address',
    'postal_code' => 'Postal Code',
    'country' => 'Country',

    // Table columns
    'product' => 'Product',
    'qty' => 'Qty',
    'unit_price' => 'Unit Price',
    'amount' => 'Amount',
    'subtotal' => 'Subtotal',
    'amount_paid' => 'Amount Paid',
    'remaining_balance' => 'Remaining Balance',

    // Payment status
    'document_is_paid' => 'This document is already paid',
    'find_details_below' => 'Find details below',
    'view_moloni_invoice' => 'View Invoice/Receipt',
    'document_type' => 'Document type',
    'created_at' => 'Created at',
    'transactions' => 'Transactions',
    'transaction_status' => 'Status',
    'transaction_date' => 'Date',
    'transaction_info' => 'Info',
    'associated_documents' => 'Associated documents',

    // Filters
    'year' => 'Year',
    'document_number' => 'Document Number',
    'filter_cmas_code_help' => 'Search by the international code of the owner',
    'filter_member_placeholder' => 'Member name',
    'organization' => 'Organization',
    'national_organization' => 'National Organization',
    'date_from' => 'Date From',
    'date_to' => 'Date To',
    'payment_date' => 'Payment Date',

    // Index page filters
    'filters' => [
        'category' => 'Category',
        'status' => 'Status',
        'type' => 'Type',
    ],

    // Index page table
    'table' => [
        'number' => '# Number',
        'date' => 'Date',
        'type' => 'Type',
        'status' => 'Status',
        'total' => 'Total',
    ],

    // Document manual create
    'attention' => 'Attention',
    'document_no' => 'Document No.',
    'due_date' => 'Due Date',
    'federation' => 'Federation',
    'entity' => 'Entity',
    'individual' => 'Individual',
    'manual_entry' => 'Manual Entry',
    'select_federation' => 'Select Federation',
    'select_federation_option' => '-- Select Federation --',
    'select_entity' => 'Select Entity',
    'select_entity_option' => '-- Select Entity --',
    'search_individual' => 'Search Individual',
    'search_individual_placeholder' => 'Enter Filiation No., name or email',
    'active_member' => 'Active Member',
    'birth_date' => 'Birth Date',
    'manual_customer_entry' => 'Manual Customer Entry',
    'customer_name' => 'Customer Name',
    'document_state' => 'Document State',
    'description' => 'Description',
    'delete' => 'Delete',
    'add_invoice_items' => 'Add Invoice Items',
    'document_line' => 'Document Line',
    'products' => 'Products',
    'select_product' => '-- Select Product --',
    'or' => 'OR',
    'product_service' => 'Product/Service',
    'vat_percentage' => 'VAT %',
    'add_item' => 'Add Item',
    'notes' => 'Notes',
    'save_document' => 'Save Document',

    // Moloni invoice
    'create_moloni_invoice' => 'Create Moloni Invoice',
    'create_moloni_invoice_description' => 'Check this option to automatically create an invoice in Moloni for this payment.',

    // Owner type categories (for document filters)
    'categories' => [
        'License' => 'License',
        'Membership' => 'Subscription',
        'Document' => 'Document',
        'Certification' => 'Certification',
        'Registration' => 'Registration',
        'Manual Order' => 'Manual Order',
        'Insurance' => 'Insurance',
    ],

    // Document states
    'states' => [
        'paid' => 'Paid',
        'draft' => 'Draft',
        'pending' => 'Pending',
        'canceled' => 'Canceled',
        'partially_paid' => 'Partially Paid',
        'void' => 'Void',
    ],

    // Action messages
    'edit_draft_only' => 'Editing is only allowed for documents in Draft state.',
    'notification_sent' => 'Notification sent.',
    'document_canceled_successfully' => 'Document canceled successfully.',
    'not_cancellable_state' => 'Document is not in a cancellable state.',
    'has_associated_payments' => 'Document cannot be deleted as it has associated payments.',
    'no_invoices_found' => 'No invoices found matching the specified criteria.',
    'export_failed' => 'Failed to generate export. Please try again or contact support.',

    // Confirmations
    'confirm_delete_warning' => 'Are you sure you want to delete this document? This action is irreversible and will delete all associated data.',
    'confirm_cancel_warning' => 'Are you sure you want to cancel this document?',
    'document_deleted_successfully' => 'Document deleted successfully.',

    // Buttons
    'resend_notification' => 'Resend notification',
    'delete_document' => 'Delete Document',

    // Filter labels
    'document_period' => 'Document Period',

    // Invoice/Order PDF labels
    'pdf' => [
        'name' => 'Name',
        'city' => 'City',
        'address' => 'Address',
        'date' => 'Date',
        'vat_number' => 'VAT Number',
        'postal_code' => 'Postal Code',
        'member_number' => 'Member No.',
        'country' => 'Country',
        'notes' => 'Notes',
        'description' => 'DESCRIPTION',
        'qty' => 'QTY',
        'unit_price' => 'UNIT PRICE',
        'total' => 'TOTAL',
        'subtotal' => 'Subtotal',
        'tax' => 'Tax',
        'order_disclaimer' => 'This document does not constitute an invoice or a receipt. The valid tax document will be issued after payment confirmation, through a certified invoicing program in accordance with current legislation.',
    ],

    // Invoice PDF compliance text
    'invoice_compliance_en' => "Entities and individuals hereby undertake to comply with and strictly enforce {$primaryShortName} rules, as well as to urge their members to adopt an underwater environmental friendly attitude.",
    'invoice_compliance_pt' => "As entidades e individuos comprometem-se por este documento a aplicar e fazer aplicar rigorosamente as regras de {$primaryShortName} e a incentivar os seus membros a adotar uma atitude respeitosa pelo ambiente subaquatico.",
];
