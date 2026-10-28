<?php

namespace App\Http\Controllers\Individual;

use App\Http\Controllers\Controller;
use Domain\Documents\Models\Document;
use Domain\Payments\Actions\InitiatePaymentAction;
use Domain\Payments\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class DocumentPaymentController extends Controller
{
    public function store(Request $request, string $documentId)
    {
        $request->validate([
            'method_id' => 'required|exists:payment_method,id',
        ]);

        $methodId = (int) $request->input('method_id');

        $document = Document::where('id', $documentId)
            ->firstOrFail();

        // Never initiate payment for a document the user cannot access.
        $this->authorize('view', $document);

        $method = PaymentMethod::findOrFail($methodId);

        Log::info('Payment initiation requested', [
            'document_id' => $document->id,
            'method_id' => $methodId,
            'driver' => $method->driver,
            'amount' => $document->total_value,
        ]);

        try {
            $paymentAction = new InitiatePaymentAction;

            $response = $paymentAction->execute(
                $document,
                $methodId
            );
        } catch (Throwable $e) {
            Log::error('Payment initiation failed', [
                'document_id' => $document->id,
                'method_id' => $methodId,
                'driver' => $method->driver,
                'exception' => $e::class,
                'error' => $e->getMessage(),
            ]);

            $message = config('app.debug')
                ? $e->getMessage()
                : __('payments.payment_failed');

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'type' => 'error',
                    'message' => $message,
                ], 502);
            }

            return redirect()
                ->route('individual.document.show', $documentId)
                ->with('error', $message);
        }

        if ($response instanceof \Illuminate\Http\RedirectResponse) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'type' => 'redirect',
                    'url' => $response->getTargetUrl(),
                ]);
            }

            return $response;
        }

        if ($response === true) {
            $instruction = $method->instructions
                ?? __('payments.offline_payment_instructions');

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'type' => 'message',
                    'message' => $instruction,
                    'redirect' => route(
                        'individual.document.show',
                        $documentId
                    ),
                ]);
            }

            return redirect()
                ->route('individual.document.show', $documentId)
                ->with('information', $instruction);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'type' => 'error',
                'message' => __('payments.payment_failed'),
            ], 422);
        }

        return redirect()
            ->route('individual.document.show', $documentId)
            ->with('error', __('payments.payment_failed'));
    }
}
