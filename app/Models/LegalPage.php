<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * One row = one version of one legal page in one locale.
 *
 * Published versions are immutable: editing creates or updates a draft
 * (published_at = null), and publishing freezes it as the next version number.
 */
class LegalPage extends Model
{
    use HasFactory;

    public const TYPE_TERMS = 'terms-of-service';

    public const TYPE_PRIVACY = 'privacy-policy';

    public const TYPE_DATA_SHARING = 'data-sharing-policy';

    protected $fillable = [
        'type',
        'locale',
        'version',
        'title',
        'body',
        'effective_date',
        'published_at',
        'created_by',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'published_at' => 'datetime',
        'version' => 'integer',
    ];

    /** @return list<string> */
    public static function types(): array
    {
        return [self::TYPE_TERMS, self::TYPE_PRIVACY, self::TYPE_DATA_SHARING];
    }

    protected static function booted(): void
    {
        static::saved(fn () => self::flushCache());
        static::deleted(fn () => self::flushCache());
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function isDraft(): bool
    {
        return $this->published_at === null;
    }

    /**
     * The version served to the public for this type, honouring the locale
     * fallback chain: requested locale, then the app fallback locale, then any
     * locale that has a published version at all.
     *
     * Nothing here ever returns a draft.
     */
    public static function current(string $type, ?string $locale = null): ?self
    {
        $locale = $locale ?: app()->getLocale();
        $fallback = config('app.fallback_locale', 'en');

        foreach (array_unique([$locale, $fallback]) as $candidate) {
            if ($page = self::currentForLocale($type, $candidate)) {
                return $page;
            }
        }

        return self::anyPublished($type);
    }

    public static function currentForLocale(string $type, string $locale): ?self
    {
        return Cache::remember(
            self::cacheKey($type, $locale),
            3600,
            fn () => self::query()
                ->published()
                ->where('type', $type)
                ->where('locale', $locale)
                ->orderByDesc('version')
                ->first()
        );
    }

    /**
     * Last resort before the on-disk stub: any locale that has a published
     * version, in a deterministic order so two servers agree.
     */
    private static function anyPublished(string $type): ?self
    {
        $locales = self::query()
            ->published()
            ->where('type', $type)
            ->distinct()
            ->pluck('locale')
            ->all();

        if (! $locales) {
            return null;
        }

        $preferred = array_values(array_filter(
            [config('app.fallback_locale', 'en'), 'pt_PT'],
            fn ($locale) => in_array($locale, $locales, true)
        ));

        $rest = array_diff($locales, $preferred);
        sort($rest);

        foreach ([...$preferred, ...$rest] as $locale) {
            if ($page = self::currentForLocale($type, $locale)) {
                return $page;
            }
        }

        return null;
    }

    /** The draft for a type+locale, if one is open. */
    public static function draftFor(string $type, string $locale): ?self
    {
        return self::query()
            ->whereNull('published_at')
            ->where('type', $type)
            ->where('locale', $locale)
            ->first();
    }

    public static function nextVersion(string $type, string $locale): int
    {
        return (int) self::query()
            ->where('type', $type)
            ->where('locale', $locale)
            ->max('version') + 1;
    }

    public static function flushCache(): void
    {
        foreach (self::types() as $type) {
            foreach (array_keys(config('app.available_locales', [])) ?: [] as $locale) {
                Cache::forget(self::cacheKey($type, $locale));
            }

            foreach (self::query()->distinct()->pluck('locale') as $locale) {
                Cache::forget(self::cacheKey($type, $locale));
            }
        }
    }

    private static function cacheKey(string $type, string $locale): string
    {
        return "legal_page.current.{$type}.{$locale}";
    }
}
