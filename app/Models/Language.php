<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * @method static create(array $country)
 * @method static select(string ...$column)
 */
class Language extends Model
{
    use HasFactory;

    protected $table = 'languages';

    public $timestamps = false;

    protected $fillable = ['iso', 'name'];

    protected static function boot()
    {
        parent::boot();

        // Clear cache when a new language is created
        static::updated(function ($language) {
            Cache::forget('attachment_languages');
        });
        static::created(function ($language) {
            Cache::forget('attachment_languages');
        });
    }
}
