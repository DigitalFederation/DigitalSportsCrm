<?php

namespace Domain\Payments\Models;

use Database\Factories\PaymentMethodFactory;
use Domain\Documents\Models\Document;
use Domain\Payments\Handlers\BasePaymentHandler;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $driver
 * @property bool $is_enabled
 * @property string|null $name
 */
class PaymentMethod extends Model
{
    use HasFactory;

    protected $table = 'payment_method';

    protected $fillable = [
        'name',
        'instructions',
        'handler',
        'driver',
        'is_enabled',
    ];

    protected static function boot()
    {
        parent::boot();

        // Only return enabled payment methods
        static::addGlobalScope('enabled', function (Builder $builder) {
            $builder->where('is_enabled', true);
        });
    }

    protected static function newFactory(): PaymentMethodFactory
    {
        return PaymentMethodFactory::new();
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function getHandlerInstance(?Document $document = null): BasePaymentHandler
    {
        $handlerClass = config("payment.gateways.{$this->driver}.handler");

        if ($document) {
            return new $handlerClass($document);
        }

        // For backward compatibility, create a dummy document if none provided
        $dummyDocument = new Document;

        return new $handlerClass($dummyDocument);
    }
}
