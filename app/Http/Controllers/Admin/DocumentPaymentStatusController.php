<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Domain\Documents\Actions\ManuallyMarkDocumentAsPaidAction;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DocumentPaymentStatusController extends Controller
{
    /**
     * Update the payment status of a document
     */
    public function update(
        string $id,
        Request $request,
        ManuallyMarkDocumentAsPaidAction $markAsPaidAction): RedirectResponse
    {
        try {
            $createMoloniInvoice = $request->boolean('create_moloni_invoice');

            $markAsPaidAction->execute($id, $request->comment, $createMoloniInvoice);

            return redirect(route('admin.document.show', $id))->with('success', __('Document status was changed to Paid.'));
        } catch (Exception $exception) {
            return redirect()->back()->with('error', $exception->getMessage());
        }
    }
}
