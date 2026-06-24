<?php

namespace Domain\Licenses\Models;

use Database\Factories\LicenseTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property bool $is_individual
 *
 * @method static select(string ...$columns)
 */
class LicenseType extends Model
{
    use HasFactory;

    protected static function newFactory(): LicenseTypeFactory
    {
        return LicenseTypeFactory::new();
    }

    protected $table = 'license_type';

    protected $fillable = ['name', 'is_individual'];

    public $timestamps = false;

    public function licenses(): HasMany
    {
        return $this->hasMany(License::class);
    }
}
