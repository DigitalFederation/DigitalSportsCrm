<?php

namespace App\Support;

/**
 * Committee-driven license-purchase routing helpers.
 *
 * Reads the `purchase` wiring from config/committees.php so that purchase
 * routes, redirects, and links are derived from the configured committee set
 * rather than hardcoded per committee. See config/committees.php for the
 * shape of each committee's `purchase` block.
 */
class Committees
{
    /**
     * The configured committee list.
     */
    public static function all(): array
    {
        return config('committees.list', []);
    }

    /**
     * Find a committee config entry by code.
     */
    public static function find(string $code): ?array
    {
        foreach (self::all() as $committee) {
            if (($committee['code'] ?? null) === $code) {
                return $committee;
            }
        }

        return null;
    }

    /**
     * Whether a committee's content is international, per config.
     */
    public static function isInternational(string $code): bool
    {
        return (bool) (self::find($code)['is_international'] ?? false);
    }

    /**
     * The purchase page config for a committee + type ('entity' | 'members'),
     * or null when that committee declares no such purchase page.
     */
    public static function purchasePage(string $code, string $type): ?array
    {
        $page = self::find($code)['purchase'][$type] ?? null;

        return is_array($page) ? $page : null;
    }

    /**
     * The route-name prefix for a purchase page type. Entity and member pages
     * live under the entity portal; individual pages under the individual one.
     */
    public static function routePrefix(string $type): string
    {
        return $type === 'individual' ? 'individual' : 'entity';
    }

    /**
     * The generated route name for a committee's purchase page, or null.
     *
     * For 'entity', a committee may instead point at a custom flow via
     * `purchase.entity_route` (e.g. a wizard) when it has no generated page.
     */
    public static function purchaseRouteName(string $code, string $type): ?string
    {
        if ($slug = (self::purchasePage($code, $type)['slug'] ?? null)) {
            return self::routePrefix($type) . ".{$slug}.index";
        }

        if ($type === 'entity') {
            return self::find($code)['purchase']['entity_route'] ?? null;
        }

        return null;
    }

    /**
     * The entity-purchase route a member purchase should redirect to when the
     * entity has no active entity license. Resolves `entity_license_via` (the
     * committee whose entity license gates this one), defaulting to the
     * committee itself.
     */
    public static function memberEntityRedirectRouteName(string $code): ?string
    {
        $via = self::purchasePage($code, 'members')['entity_license_via'] ?? $code;

        return self::purchaseRouteName($via, 'entity')
            ?? self::defaultEntityPurchaseRouteName();
    }

    /**
     * The first committee that exposes a purchase page of the given type, used
     * as the generic landing/fallback for that portal's bare purchase route.
     */
    public static function defaultPurchaseRouteName(string $type): ?string
    {
        foreach (self::all() as $committee) {
            if ($name = self::purchaseRouteName($committee['code'] ?? '', $type)) {
                return $name;
            }
        }

        return null;
    }

    /**
     * The first committee that exposes an entity purchase page, used as the
     * generic landing/fallback for the bare entity license-purchase route.
     */
    public static function defaultEntityPurchaseRouteName(): ?string
    {
        return self::defaultPurchaseRouteName('entity');
    }

    /**
     * Every committee purchase page (of the given types) that should be
     * registered as a route, yielded as ['code', 'type', 'slug'].
     */
    public static function registrablePurchaseRoutes(array $types = ['entity', 'members']): array
    {
        $routes = [];

        foreach (self::all() as $committee) {
            $code = $committee['code'] ?? null;
            if (! $code) {
                continue;
            }

            foreach ($types as $type) {
                if ($slug = (self::purchasePage($code, $type)['slug'] ?? null)) {
                    $routes[] = ['code' => $code, 'type' => $type, 'slug' => $slug];
                }
            }
        }

        return $routes;
    }

    // ----------------------------------------------------------------------
    // Licenses-attributed screens
    // ----------------------------------------------------------------------

    /**
     * The URL/route-name base for a committee's licenses-attributed screens.
     */
    public static function slug(string $code): ?string
    {
        return self::find($code)['slug'] ?? null;
    }

    /**
     * The translation-key base for a committee's licenses-attributed titles.
     */
    public static function titleSlug(string $code): ?string
    {
        return self::find($code)['title_slug'] ?? null;
    }

    /**
     * The licenses-attributed route name for a portal + committee + holder.
     * Holder is 'entity'|'members' for the entity portal, 'entity'|'individual'
     * for federation/admin, and ignored for the individual portal.
     */
    public static function attributedRouteName(string $portal, string $code, string $holder = 'entity'): ?string
    {
        $slug = self::slug($code);
        if (! $slug) {
            return null;
        }

        return match ($portal) {
            'entity' => $holder === 'members'
                ? "entity.{$slug}-member-licenses-attributed.index"
                : "entity.{$slug}-licenses-attributed.index",
            'federation' => "federation.{$slug}-{$holder}-licenses-attributed.index",
            'admin' => "admin.{$slug}-{$holder}-licenses-attributed.index",
            'individual' => "individual.{$slug}-licenses-attributed.index",
            default => null,
        };
    }

    /**
     * The page-title translation key for a licenses-attributed screen.
     */
    public static function attributedTitle(string $portal, string $code, string $holder = 'entity'): ?string
    {
        if ($portal === 'entity') {
            return self::find($code)['attributed']['entity_portal'][$holder] ?? null;
        }

        $titleSlug = self::titleSlug($code);

        return match ($portal) {
            'federation' => "licenses.federation_{$titleSlug}_{$holder}_licenses_title",
            'admin' => "licenses.admin_{$titleSlug}_{$holder}_licenses_title",
            'individual' => "licenses.individual_{$titleSlug}_licenses_title",
            default => null,
        };
    }

    /**
     * The page-subtitle translation key for a licenses-attributed screen, or
     * null where the portal/screen has no subtitle.
     */
    public static function attributedSubtitle(string $portal, string $code, string $holder = 'entity'): ?string
    {
        $titleSlug = self::titleSlug($code);

        return match ($portal) {
            'federation' => "licenses.federation_{$titleSlug}_{$holder}_licenses_subtitle",
            'admin' => "licenses.admin_{$titleSlug}_{$holder}_licenses_subtitle",
            'individual' => "licenses.individual_{$titleSlug}_licenses_subtitle",
            default => null,
        };
    }

    /**
     * The resolved page title for a licenses-attributed screen.
     */
    public static function attributedTitleText(string $portal, string $code, string $holder = 'entity'): string
    {
        $key = self::attributedTitle($portal, $code, $holder);

        if ($key) {
            $text = __($key);
            // Use the configured/convention title when it actually resolves.
            if ($text !== $key && $text !== '') {
                return $text;
            }
        }

        // Fallback: a generic, committee-label-driven title so a committee that
        // declares no explicit title (and has no convention lang key) still reads
        // sensibly instead of showing a raw translation key.
        $label = self::find($code)['name'] ?? $code;
        $genericKey = $holder === 'entity'
            ? 'licenses.:committee Entity Licenses'
            : 'licenses.:committee Professional Licenses';

        return __($genericKey, ['committee' => $label]);
    }

    /**
     * The resolved page subtitle for a licenses-attributed screen, or null when
     * the screen has no (or an empty) subtitle.
     */
    public static function attributedSubtitleText(string $portal, string $code, string $holder = 'entity'): ?string
    {
        $key = self::attributedSubtitle($portal, $code, $holder);
        if (! $key) {
            return null;
        }

        $text = __($key);

        return ($text === '' || $text === $key) ? null : $text;
    }

    /**
     * Entity-portal licenses-attributed routes to register, yielded as
     * ['code', 'holder', 'slug'] where holder is 'entity'|'members'.
     */
    public static function entityAttributedRoutes(): array
    {
        $routes = [];

        foreach (self::all() as $committee) {
            $code = $committee['code'] ?? null;
            $slug = $committee['slug'] ?? null;
            if (! $code || ! $slug) {
                continue;
            }

            foreach (array_keys($committee['attributed']['entity_portal'] ?? []) as $holder) {
                $routes[] = ['code' => $code, 'holder' => $holder, 'slug' => $slug];
            }
        }

        return $routes;
    }

    /**
     * Federation/admin licenses-attributed routes to register, yielded as
     * ['code', 'holder', 'slug'] for both entity and individual holders.
     */
    public static function holderAttributedRoutes(): array
    {
        $routes = [];

        foreach (self::all() as $committee) {
            $code = $committee['code'] ?? null;
            $slug = $committee['slug'] ?? null;
            if (! $code || ! $slug) {
                continue;
            }

            foreach (['entity', 'individual'] as $holder) {
                $routes[] = ['code' => $code, 'holder' => $holder, 'slug' => $slug];
            }
        }

        return $routes;
    }

    /**
     * Individual-portal licenses-attributed routes to register, yielded as
     * ['code', 'slug'].
     */
    public static function individualAttributedRoutes(): array
    {
        $routes = [];

        foreach (self::all() as $committee) {
            $code = $committee['code'] ?? null;
            $slug = $committee['slug'] ?? null;
            if ($code && $slug) {
                $routes[] = ['code' => $code, 'slug' => $slug];
            }
        }

        return $routes;
    }
}
