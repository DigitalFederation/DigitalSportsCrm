<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackupSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
    ];

    /** @var array<string, mixed> */
    protected static array $defaults = [
        'backup_enabled' => true,
        'backup_frequency' => 'daily',
        'backup_time' => '02:00',
        'retention_days' => 30,
        'max_storage_mb' => 5000,
    ];

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $fallback = $default ?? (self::$defaults[$key] ?? null);

        $setting = static::query()->where('key', $key)->first();

        if (! $setting) {
            return $fallback;
        }

        return self::castValue($setting->value, $setting->type);
    }

    public static function setValue(string $key, mixed $value): void
    {
        $type = match (true) {
            is_bool($value) => 'boolean',
            is_int($value) => 'integer',
            default => 'string',
        };

        $storedValue = match ($type) {
            'boolean' => $value ? '1' : '0',
            default => (string) $value,
        };

        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $storedValue, 'type' => $type],
        );
    }

    /** @return array<string, mixed> */
    public static function allValues(): array
    {
        $settings = static::all()->pluck('value', 'key')->toArray();
        $result = [];

        foreach (self::$defaults as $key => $default) {
            $setting = static::query()->where('key', $key)->first();

            if ($setting) {
                $result[$key] = self::castValue($setting->value, $setting->type);
            } else {
                $result[$key] = $default;
            }
        }

        return $result;
    }

    protected static function castValue(?string $value, string $type): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'boolean' => (bool) (int) $value,
            'integer' => (int) $value,
            default => $value,
        };
    }
}
