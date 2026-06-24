<?php

namespace App\Models;

use Database\Factories\CountryFactory;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Geographic\Models\District;
use Domain\Individuals\Models\Individual;
use Domain\OfficialDocuments\Models\OfficialDocument;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static create(array $country)
 * @method static select(string ...$column)
 *
 * @property int $id
 * @property string|null $ioc
 * @property string|null $name
 * @property string|null $region_name
 * @property string|null $sub_region_name
 * @property bool|null $supported
 * @property float|null $lat
 * @property float|null $lng
 */
class Country extends Model
{
    use HasFactory;

    protected $table = 'country';

    public $timestamps = false;

    protected $fillable = ['ioc', 'name', 'region_name', 'sub_region_name', 'supported', 'lat', 'lng'];

    protected static function newFactory(): CountryFactory
    {
        return CountryFactory::new();
    }

    public function entities(): HasMany
    {
        return $this->hasMany(Entity::class);
    }

    public function federations(): HasMany
    {
        return $this->hasMany(Federation::class);
    }

    public function individuals(): HasMany
    {
        return $this->hasMany(Individual::class);
    }

    public function geoZone(): BelongsTo
    {
        return $this->belongsTo(GeoZone::class);
    }

    public function subRegion(): BelongsTo
    {
        return $this->belongsTo(SubRegion::class);
    }

    public function officialDocuments(): HasMany
    {
        return $this->hasMany(OfficialDocument::class);
    }

    public function districts(): HasMany
    {
        return $this->hasMany(District::class);
    }
}
