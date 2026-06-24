<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Domain\Documents\Actions\RegisterDocumentPaymentAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DocumentManualPaymentController extends Controller
{
    /**
     * Manually register a payment for a document
     * If $request->comment exists, it will be added into the receipt description
     * Only available for documents with type 'ord' and role 'cmas'
     */
    public function store(
        $id,
        Request $request,
        RegisterDocumentPaymentAction $doPayment
    ) {

        // Validate the input
        $validated = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01', // Adjust validation rules as necessary
            'comment' => 'nullable|string',
        ])->validate();

        // Ensure $amount is treated as a float
        $amount = (float) $validated['amount'];
        $comment = $validated['comment'] ?? null;
        $createMoloniInvoice = $request->boolean('create_moloni_invoice');

        try {
            $doPayment->execute($id, $amount, $comment, $createMoloniInvoice);

            return redirect(route('admin.document.show', $id))->with('success', __('Document payment was saved.'));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

    }
}
