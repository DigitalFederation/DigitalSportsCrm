<?php

namespace App\Http\Controllers\Individual;

use App\Http\Controllers\Controller;
use Domain\Documents\Models\Document;
use Domain\Payments\Actions\InitiatePaymentAction;
use Domain\Payments\Models\PaymentMethod;
use Illuminate\Http\Request;

class DocumentPaymentController extends Controller
{
    public function store(Request $request, string $documentId)
    {
        // Validate the method_id input
        $request->validate([
            'method_id' => 'required|exists:payment_method,id',
        ]);

        $methodId = $request->input('method_id');
        $document = Document::where('id', $documentId)->firstOrFail();

        // Initialize your payment action or handler here, passing in the selected method
        $paymentAction = new InitiatePaymentAction;
        $response = $paymentAction->execute($document, $methodId);

        $method = PaymentMethod::findOrFail($methodId);

        // Handle different response types from the new payment system

        // Check if response is a Laravel redirect response (from EasyPay)
        if ($response instanceof \Illuminate\Http\RedirectResponse) {
            // For AJAX requests, return JSON with the URL to open in new window
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'type' => 'redirect',
                    'url' => $response->getTargetUrl(),
                ]);
            }

            return $response;
        }

        // Check if response is boolean true (offline payments)
        if ($response === true) {
            $instruction = $method->instructions ?? __('payments.offline_payment_instructions');

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'type' => 'message',
                    'message' => $instruction,
                    'redirect' => route('individual.document.show', $documentId),
                ]);
            }

            return redirect()->route('individual.document.show', $documentId)
                ->with('information', $instruction);
        }

        // Handle any other unexpected response types
        if (is_object($response)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'type' => 'error',
                    'message' => __('payments.payment_failed'),
                ], 422);
            }

            return redirect()->route('individual.document.show', $documentId)
                ->with('error', __('payments.payment_failed'));
        }

        // If response is not handled above, consider it a failure
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'type' => 'error',
                'message' => __('payments.payment_failed'),
            ], 422);
        }

        return redirect()->route('individual.document.show', $documentId)
            ->with('error', __('payments.payment_failed'));
    }
}
