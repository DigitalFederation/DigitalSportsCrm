<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use Domain\Documents\Models\Document;
use Domain\Invoicing\Services\MoloniInvoiceService;
use Illuminate\Http\RedirectResponse;

class MoloniDocumentPdfController extends Controller
{
    public function __invoke(string $id): RedirectResponse
    {
        $document = Document::with('moloniInvoice')->where(compact('id'))->firstOrFail();

        $this->authorize('view', $document);

        $moloniInvoice = $document->moloniInvoice;

        if (! $moloniInvoice) {
            abort(404, __('moloni.invoice_not_found'));
        }

        $invoiceService = app(MoloniInvoiceService::class);
        $pdfUrl = $invoiceService->getPdfLink($moloniInvoice->moloni_document_id);

        if (! $pdfUrl) {
            return back()->with('error', __('moloni.pdf_not_available'));
        }

        return redirect()->away($pdfUrl);
    }
}
