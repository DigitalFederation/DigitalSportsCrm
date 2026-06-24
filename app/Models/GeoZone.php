<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static insert(array[] $zones)
 * @method static select(string ...$columns)
 */
class GeoZone extends Model
{
    use HasFactory;

    protected $table = 'geo_zones';

    protected $fillable = [
        'name',
    ];

    public function countries(): HasMany
    {
        return $this->hasMany(Country::class);
    }

    public function subRegions(): HasMany
    {
        return $this->hasMany(SubRegion::class);
    }
}
