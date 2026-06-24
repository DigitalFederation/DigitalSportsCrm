<?php

namespace App\Support;

/**
 * Generates per-committee sidebar sections from config/committees.php so the
 * menu's committee entries are deployment-driven rather than hardcoded.
 *
 * Each committee may declare a `menu` block:
 *   - label:          section heading (translation key or literal)
 *   - icon:           section icon
 *   - order:          ordering hint (used by the database menu builder)
 *   - entities_label: label for the entity-holder license link (default "entities")
 *   - professionals:  [roleFilter => label] map for the individual-holder
 *                     license breakdown (athletes/coaches/instructors/…)
 *   - permission:     gate for the section (default "access {code} menu")
 *
 * Sections are emitted in the same nested array shape config/menu.php uses, so
 * they can be spread straight into a portal's menu definition.
 */
class CommitteeMenu
{
    /**
     * Committees that declare a sidebar section, in menu order.
     */
    private static function committees(): array
    {
        $committees = array_values(array_filter(
            Committees::all(),
            fn ($committee) => ! empty($committee['menu'])
        ));

        usort(
            $committees,
            fn ($a, $b) => ($a['menu']['order'] ?? 99) <=> ($b['menu']['order'] ?? 99)
        );

        return $committees;
    }

    /**
     * The committee filter value used in `filter[committee]` query params.
     */
    private static function filterValue(string $code): string
    {
        return strtolower($code);
    }

    /**
     * The per-committee top-level sections for a portal, as nested menu arrays.
     */
    public static function sections(string $portal): array
    {
        return array_map(
            fn ($committee) => self::section($portal, $committee),
            self::committees()
        );
    }

    /**
     * Per-committee attachment ("Files area") child links for a portal. The
     * committee attachment routes bind by committee id; the committee *code* is
     * emitted here and resolved to an id at seed time by MenuSeeder (config is
     * loaded at app boot, before committees are seeded, so the id is not yet
     * known here).
     */
    public static function attachmentChildren(string $portal): array
    {
        return array_map(function ($committee) use ($portal) {
            return [
                'name' => $committee['menu']['label'] ?? ($committee['name'] ?? $committee['code']),
                'route' => ["{$portal}.committee.attachments.index", ['committee' => $committee['code']]],
                'active' => ['attachments'],
            ];
        }, self::committees());
    }

    /**
     * Per-committee child links for a single filtered listing route, labelled by
     * each committee. Used where a portal groups committee items as children of a
     * function section (e.g. admin Certifications/Licenses, individual My Licenses)
     * rather than as top-level committee sections.
     *
     * @param  array<string, string>  $extraParams  additional query filters
     * @param  list<string>  $active  active patterns for each child
     */
    public static function committeeChildren(string $routeName, array $extraParams = [], array $active = []): array
    {
        return array_map(function ($committee) use ($routeName, $extraParams, $active) {
            return [
                'name' => $committee['menu']['label'] ?? ($committee['name'] ?? $committee['code']),
                'route' => [$routeName, ['filter[committee]' => self::filterValue($committee['code'])] + $extraParams],
                'active' => $active,
            ];
        }, self::committees());
    }

    /**
     * Build a single committee section for the given portal.
     */
    private static function section(string $portal, array $committee): array
    {
        $code = $committee['code'];
        $menu = $committee['menu'];
        $filter = self::filterValue($code);

        return [
            'name' => $menu['label'] ?? ($committee['name'] ?? $code),
            'committee' => $filter,
            'icon' => $menu['icon'] ?? 'circle-fill',
            'route' => '',
            'can' => $menu['permission'] ?? "access {$filter} menu",
            'active' => [
                'licenses-attributed', 'certifications-attributed',
                'license-attributed', 'certification-attributed', 'official-documents',
            ],
            'children' => self::children($portal, $code, $menu, $filter),
        ];
    }

    /**
     * The child links for a committee section.
     */
    private static function children(string $portal, string $code, array $menu, string $filter): array
    {
        $children = [];

        // Certifications
        $children[] = [
            'name' => 'menu.federation.certifications',
            'route' => ["{$portal}.certification-attributed.index", ['filter[committee]' => $filter]],
            'active' => ['certifications-attributed', 'certification-attributed'],
        ];

        // Entity-holder licenses (clubs / entities)
        $children[] = [
            'name' => $menu['entities_label'] ?? 'menu.federation.entities',
            'route' => ["{$portal}.license-attributed.index", [
                'filter[committee]' => $filter,
                'filter[filter_holder_type]' => 'entity',
            ]],
            'active' => ['license-attributed', 'licenses-attributed'],
        ];

        // Individual-holder licenses, broken down by professional role
        foreach ($menu['professionals'] ?? [] as $role => $label) {
            $children[] = [
                'name' => $label,
                'route' => ["{$portal}.license-attributed.index", [
                    'filter[committee]' => $filter,
                    'filter[filter_holder_type]' => 'individual',
                    'filter[filter_professional]' => $role,
                ]],
                'active' => ['license-attributed', 'licenses-attributed'],
            ];
        }

        // Official documents
        $children[] = [
            'name' => 'menu.federation.official_documents',
            'route' => ["{$portal}.official-documents.index", ['filter[committee]' => $filter]],
            'active' => ['official-documents'],
        ];

        return $children;
    }
}
