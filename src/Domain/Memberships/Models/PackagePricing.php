<?php

namespace Domain\Memberships\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $package_id
 * @property float|string $price
 */
class PackagePricing extends Model
{
    use HasFactory;

    protected $table = 'package_pricings';

    protected $fillable = [
        'package_id',
        'price',
    ];

    protected $casts = [
        'price' => 'float',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(MembershipPackage::class, 'package_id');
    }
}
