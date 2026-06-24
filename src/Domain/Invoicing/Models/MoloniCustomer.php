<?php

namespace Domain\Invoicing\Models;

use Domain\Entities\Models\Entity;
use Domain\Individuals\Models\Individual;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MoloniCustomer extends Model
{
    protected $table = 'moloni_customers';

    protected $fillable = [
        'customerable_type',
        'customerable_id',
        'moloni_customer_id',
        'moloni_vat',
        'moloni_name',
    ];

    public function customerable(): MorphTo
    {
        return $this->morphTo();
    }

    public static function findByOwner(Individual|Entity $owner): ?self
    {
        return static::where('customerable_type', get_class($owner))
            ->where('customerable_id', $owner->id)
            ->first();
    }

    public static function findByMoloniId(int $moloniCustomerId): ?self
    {
        return static::where('moloni_customer_id', $moloniCustomerId)->first();
    }
}
