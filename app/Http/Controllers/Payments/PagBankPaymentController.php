<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use Domain\Payments\Models\PaymentMethod;
use Domain\Payments\Models\PaymentTransaction;
use Illuminate\Contracts\View\View;

class PagBankPaymentController extends Controller
{
    public function __invoke(PaymentTransaction $transaction): View
    {
        $paymentMethod = PaymentMethod::withoutGlobalScopes()->find($transaction->payment_method_id);

        abort_unless($paymentMethod?->driver === 'pagbank', 404);

        $paymentData = json_decode((string) $transaction->payment_data, true) ?: [];
        $charge = data_get($paymentData, 'charges.0', []);

        if (! is_array($charge)) {
            $charge = [];
        }

        $qrCodeText = data_get($charge, 'qr_code.text');
        $qrCodeImageUrl = null;

        foreach (($charge['links'] ?? []) as $link) {
            if (($link['rel'] ?? null) === 'QRCODE.PNG') {
                $qrCodeImageUrl = $link['href'] ?? null;
                break;
            }
        }

        return view('payments.pagbank.show', [
            'transaction' => $transaction,
            'qrCodeText' => is_string($qrCodeText) ? $qrCodeText : null,
            'qrCodeImageUrl' => is_string($qrCodeImageUrl) ? $qrCodeImageUrl : null,
            'expirationDate' => data_get($charge, 'payment_method.pix.expiration_date'),
        ]);
    }
}
