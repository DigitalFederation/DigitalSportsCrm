<?php

namespace Domain\Documents\Actions;

use Domain\Documents\Models\Document;

class GenerateDocumentInvoiceNumberAction
{
    public function __invoke(Document $document): Document
    {
        $currentYear = date('Y');

        $lastInvoice = Document::whereNotNull('invoice_number')
            ->where('invoice_year', $currentYear) // Only look at current year invoices
            ->orderBy('invoice_number', 'desc')
            ->first();

        $nextNumber = $lastInvoice ? $lastInvoice->invoice_number + 1 : 1;

        $document->update([
            'invoice_number' => $nextNumber,
            'invoice_year' => $currentYear,
        ]);

        return $document;
    }
}
