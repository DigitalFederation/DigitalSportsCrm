<?php

namespace Domain\Invoicing\Models;

use Illuminate\Database\Eloquent\Model;

class MoloniSetting extends Model
{
    protected $table = 'moloni_settings';

    protected $fillable = [
        'key',
        'value',
        'type',
        'is_encrypted',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'is_encrypted' => 'boolean',
        ];
    }

    public function getTypedValue(): mixed
    {
        $value = $this->is_encrypted ? decrypt($this->value) : $this->value;

        return match ($this->type) {
            'json' => json_decode($value, true),
            'int', 'integer' => (int) $value,
            'bool', 'boolean' => (bool) $value,
            'float' => (float) $value,
            default => $value,
        };
    }

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();

        if (! $setting) {
            return $default;
        }

        return $setting->getTypedValue();
    }

    public static function setValue(string $key, mixed $value, string $type = 'string', bool $encrypted = false): void
    {
        $storeValue = match ($type) {
            'json' => json_encode($value),
            default => (string) $value,
        };

        if ($encrypted) {
            $storeValue = encrypt($storeValue);
        }

        static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $storeValue,
                'type' => $type,
                'is_encrypted' => $encrypted,
            ]
        );
    }
}
