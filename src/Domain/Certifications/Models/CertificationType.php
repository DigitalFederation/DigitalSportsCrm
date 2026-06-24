<?php

namespace Domain\Certifications\Models;

use Database\Factories\CertificationTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static insert(array[] $types)
 * @method static select(string ...$columns)
 */
class CertificationType extends Model
{
    use HasFactory;
    use HasFactory;

    protected static function newFactory(): CertificationTypeFactory
    {
        return CertificationTypeFactory::new();
    }

    protected $table = 'certification_type';

    protected $fillable = ['name'];

    public $timestamps = false;

    public function certification(): HasMany
    {
        return $this->hasMany(Certification::class);
    }
}
