<?php

use App\Http\Controllers\Api\PaymentWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Payment webhooks — each gateway's webhook is registered only while that gateway is enabled,
// so disabling a gateway (e.g. EASYPAY_ENABLED=false) also removes its public webhook endpoint.
// Rate limited to 60 requests per minute per IP to prevent abuse while allowing legitimate retries.
Route::prefix('payment/webhook')->middleware('throttle:60,1')->group(function () {
    if (config('payment.gateways.easypay.enabled')) {
        Route::post('easypay', [PaymentWebhookController::class, 'easypay'])->name('api.payment.webhook.easypay');
    }
});
