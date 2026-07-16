<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Domain\Payments\Models\PaymentMethod;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    public function index(): View
    {
        $paymentMethods = PaymentMethod::withoutGlobalScope('enabled')
            ->orderBy('name')
            ->get();

        $gatewayStatuses = $this->getGatewayStatuses();

        return view('web.admin.payment-methods.index', compact('paymentMethods', 'gatewayStatuses'));
    }

    public function edit(int $id): View
    {
        $paymentMethod = PaymentMethod::withoutGlobalScope('enabled')->findOrFail($id);
        $gatewayConfig = config("payment.gateways.{$paymentMethod->driver}", []);

        return view('web.admin.payment-methods.edit', compact('paymentMethod', 'gatewayConfig'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $paymentMethod = PaymentMethod::withoutGlobalScope('enabled')->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'instructions' => 'nullable|string',
            'is_enabled' => 'boolean',
        ]);

        $paymentMethod->update([
            'name' => $validated['name'],
            'instructions' => $validated['instructions'],
            'is_enabled' => $request->boolean('is_enabled'),
        ]);

        return redirect()
            ->route('admin.payment-methods.index')
            ->with('success', __('payment_admin.method_updated'));
    }

    public function toggle(int $id): RedirectResponse
    {
        $paymentMethod = PaymentMethod::withoutGlobalScope('enabled')->findOrFail($id);
        $isEnabled = ! $paymentMethod->is_enabled;

        $paymentMethod->update(['is_enabled' => $isEnabled]);

        $status = $isEnabled ? 'enabled' : 'disabled';

        return redirect()
            ->route('admin.payment-methods.index')
            ->with('success', __("payment_admin.method_{$status}", ['name' => $paymentMethod->name]));
    }

    private function getGatewayStatuses(): array
    {
        $easyPayConfig = config('payment.gateways.easypay', []);
        $manager = \Domain\Payments\Services\PaymentGatewayManager::createFromConfig();
        $currency = (string) config('app.currency', 'EUR');

        return [
            'easypay' => [
                'configured' => ! empty($easyPayConfig['account_id'] ?? null) && ! empty($easyPayConfig['api_key'] ?? null),
                'webhook_configured' => ! empty($easyPayConfig['webhook_secret'] ?? null) && $easyPayConfig['webhook_secret'] !== 'your-webhook-secret',
                'sandbox' => $easyPayConfig['sandbox'] ?? true,
                'webhook_url' => route('api.payment.webhook.easypay'),
                'currency_supported' => $manager->hasGateway('easypay')
                    ? $manager->supportsCurrency('easypay', $currency)
                    : false,
            ],
            'offline' => [
                'configured' => true,
                'webhook_configured' => false,
                'sandbox' => false,
                'webhook_url' => null,
                'currency_supported' => true,
            ],
        ];
    }
}
