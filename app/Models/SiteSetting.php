<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    private const CACHE_KEY = 'site_settings.all';

    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * All settings as a cached key => value map.
     *
     * @return array<string, string|null>
     */
    public static function allSettings(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function (): array {
            return self::query()->pluck('value', 'key')->all();
        });
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        $value = self::allSettings()[$key] ?? null;

        return ($value === null || $value === '') ? $default : $value;
    }

    public static function set(string $key, ?string $value): void
    {
        self::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget(self::CACHE_KEY);
    }

    public static function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
