<?php

namespace App\Http\Controllers\Federation;

use App\Http\Controllers\Controller;
use Domain\Documents\Actions\RegisterDocumentPaymentAction;
use Domain\Documents\Models\Document;
use Domain\Federations\Models\Federation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DocumentManualPaymentController extends Controller
{
    /**
     * Manually register a payment for a document
     */
    public function store(
        $id,
        Request $request,
        RegisterDocumentPaymentAction $doPayment
    ) {
        $document = Document::findOrFail($id);
        $federation = auth()->user()->getFederation();
        $isMainFederation = $federation && $federation->isMainFederation();
        $federationOwnerTypes = Document::ownerTypeValuesFor(Federation::class);
        $federationIds = auth()->user()->federations()->pluck('federation.id')->toArray();
        $isOwnedByUserFederation = in_array($document->owner_type, $federationOwnerTypes, true)
            && in_array($document->owner_id, $federationIds, true);

        if (! $isMainFederation && ! $isOwnedByUserFederation) {
            abort(403, __('You are not authorized to register payments for this document.'));
        }

        $validated = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'comment' => 'nullable|string',
        ])->validate();

        $amount = (float) $validated['amount'];
        $comment = $validated['comment'] ?? null;
        $createMoloniInvoice = $request->boolean('create_moloni_invoice');

        try {
            $doPayment->execute($id, $amount, $comment, $createMoloniInvoice);

            return redirect(route('federation.document.show', $id))->with('success', __('Document payment was saved.'));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
