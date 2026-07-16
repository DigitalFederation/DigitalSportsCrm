<?php

namespace Domain\Payments\Services;

use Domain\Payments\Contracts\PaymentGatewayInterface;
use Domain\Payments\Gateways\OfflineGateway;
use InvalidArgumentException;

class PaymentGatewayManager
{
    private array $gateways = [];
    private array $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->registerDefaultGateways();
    }

    /**
     * Register a payment gateway
     */
    public function register(string $name, string $gatewayClass): void
    {
        if (! class_exists($gatewayClass)) {
            throw new InvalidArgumentException("Gateway class {$gatewayClass} does not exist");
        }

        if (! in_array(PaymentGatewayInterface::class, class_implements($gatewayClass))) {
            throw new InvalidArgumentException("Gateway class {$gatewayClass} must implement PaymentGatewayInterface");
        }

        $this->gateways[$name] = $gatewayClass;
    }

    /**
     * Get a configured gateway instance
     */
    public function gateway(string $name): PaymentGatewayInterface
    {
        if (! isset($this->gateways[$name])) {
            throw new InvalidArgumentException("Gateway {$name} is not registered");
        }

        $gatewayClass = $this->gateways[$name];
        $gateway = new $gatewayClass;

        // Configure the gateway with its config
        $gatewayConfig = $this->config['gateways'][$name] ?? [];
        $gateway->configure($gatewayConfig);

        return $gateway;
    }

    /**
     * Get all registered gateway names
     */
    public function getAvailableGateways(): array
    {
        return array_keys($this->gateways);
    }

    /**
     * Check if a gateway is registered
     */
    public function hasGateway(string $name): bool
    {
        return isset($this->gateways[$name]);
    }

    /**
     * Register default gateways
     */
    private function registerDefaultGateways(): void
    {
        // The offline gateway is always available as the built-in default.
        $this->register('offline', OfflineGateway::class);

        // Register any additional gateways declared in config/payment.php. This keeps
        // country/provider-specific gateways (e.g. the bundled EasyPay example) out of
        // the core service: a deployment adds its own by implementing
        // PaymentGatewayInterface and pointing the gateway's `gateway` key at it.
        // See docs/guides/building-integrations.md.
        foreach ($this->config['gateways'] ?? [] as $name => $gatewayConfig) {
            if ($name === 'offline') {
                continue;
            }

            // Skip gateways explicitly disabled via their `enabled` flag (e.g. EASYPAY_ENABLED=false).
            if (($gatewayConfig['enabled'] ?? true) === false) {
                continue;
            }

            $gatewayClass = $gatewayConfig['gateway'] ?? null;

            if ($gatewayClass) {
                try {
                    $this->register($name, $gatewayClass);
                } catch (InvalidArgumentException $e) {
                    // A misconfigured/removed plugin gateway must not take
                    // down every page that inspects payment methods.
                    \Illuminate\Support\Facades\Log::warning("Skipping payment gateway {$name}: {$e->getMessage()}");
                }
            }
        }
    }

    /**
     * Whether the named gateway can charge in the given currency.
     * A gateway that cannot be instantiated (broken config, removed
     * plugin) is reported as unsupported instead of taking the page down.
     */
    public function supportsCurrency(string $name, string $currency): bool
    {
        if (! $this->hasGateway($name)) {
            return false;
        }

        try {
            $supported = $this->gateway($name)->supportedCurrencies();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Payment gateway {$name} could not be inspected for currency support", [
                'error' => $e->getMessage(),
            ]);

            return false;
        }

        return in_array('*', $supported, true) || in_array($currency, $supported, true);
    }

    /**
     * Registered gateway names that can charge in the given currency.
     *
     * @return string[]
     */
    public function gatewaysSupporting(?string $currency = null): array
    {
        $currency ??= (string) config('app.currency', 'EUR');

        return array_values(array_filter(
            $this->getAvailableGateways(),
            fn (string $name): bool => $this->supportsCurrency($name, $currency)
        ));
    }

    /**
     * Create manager instance from Laravel config
     */
    public static function createFromConfig(): self
    {
        return new self(config('payment', []));
    }
}
