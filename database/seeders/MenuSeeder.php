<?php

namespace Database\Seeders;

use App\Models\Committee;
use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * Seeds the database-driven navigation menus (sidebar) for each user group from
 * config/menu.php.
 *
 * The menu lives in the database so it can be managed/edited per deployment (the
 * dynamic menu admin), but its content is *sourced* from config/menu.php — including
 * the per-committee sections generated from config/committees.php (see
 * App\Support\CommitteeMenu). Re-seeding rebuilds the menu from config; admin edits
 * made in the database persist until the next re-seed, exactly as before.
 *
 * Links to routes that do not exist in this install are skipped (route-validated).
 * Item names are translation keys; MenuItem::getDisplayText() runs them through __().
 */
class MenuSeeder extends Seeder
{
    /** @var array<string, int|null> committee code => id cache */
    private array $committeeIds = [];

    private int $seeded = 0;

    /** @var list<string> */
    private array $skipped = [];

    public function run(): void
    {
        foreach (['admin', 'federation', 'entity', 'individual'] as $group) {
            $menu = Menu::updateOrCreate(
                ['machine_name' => $group],
                [
                    'name' => Str::headline($group) . ' Menu',
                    'description' => Str::headline($group) . ' navigation menu',
                    'active' => true,
                ]
            );

            MenuItem::where('menu_id', $menu->id)->delete();

            $this->seedItems($menu, config("menu.{$group}", []), null);
        }

        $this->command?->info("Seeded {$this->seeded} menu items from config across 4 menus.");

        if ($this->skipped) {
            $this->command?->warn(
                'Skipped ' . count($this->skipped) . ' menu link(s) to unknown routes: '
                . implode(', ', array_unique($this->skipped))
            );
        }
    }

    /**
     * Recursively persist config menu items under the given parent.
     *
     * @param  array<int, array<string, mixed>>  $items
     */
    private function seedItems(Menu $menu, array $items, ?int $parentId): void
    {
        $order = 0;

        foreach ($items as $item) {
            [$routeName, $routeParameters] = $this->parseRoute($item['route'] ?? null);

            // Skip links whose route does not exist in this install. Parent items
            // without a route are kept as containers.
            if ($routeName && ! Route::has($routeName)) {
                $this->skipped[] = $routeName;

                continue;
            }

            // Committee attachment routes bind by committee id; resolve the
            // committee code (emitted by CommitteeMenu::attachmentChildren) to
            // its id now that committees are seeded.
            if (is_array($routeParameters) && isset($routeParameters['committee']) && ! is_numeric($routeParameters['committee'])) {
                $routeParameters['committee'] = $this->committeeId($routeParameters['committee']);
            }

            $menuItem = MenuItem::create([
                'menu_id' => $menu->id,
                'parent_id' => $parentId,
                'committee_id' => $this->committeeId($item['committee'] ?? null),
                'name' => $item['name'] ?? '',
                'icon' => $item['icon'] ?? null,
                'order' => $order++,
                'route_name' => $routeName,
                'route_parameters' => $routeParameters ?: null,
                'active_patterns' => $item['active'] ?? null,
                // Preserve today's behaviour: items are visible regardless of the
                // config `can` gate (the database menu can be permission-gated by
                // editing it directly).
                'permissions' => [],
                'visible' => true,
            ]);

            $this->seeded++;

            if (! empty($item['children'])) {
                $this->seedItems($menu, $item['children'], $menuItem->id);
            }
        }
    }

    /**
     * Normalize a config `route` (string, [name, params], or empty) to
     * [routeName, routeParameters].
     *
     * @return array{0: ?string, 1: ?array}
     */
    private function parseRoute($route): array
    {
        if (is_array($route)) {
            return [$route[0] ?? null, $route[1] ?? null];
        }

        return [$route ?: null, null];
    }

    /**
     * Resolve a committee filter value (e.g. "sport") to its committee id.
     */
    private function committeeId(?string $committee): ?int
    {
        if (! $committee) {
            return null;
        }

        $code = strtoupper($committee);

        if (! array_key_exists($code, $this->committeeIds)) {
            $this->committeeIds[$code] = Committee::where('code', $code)->value('id');
        }

        return $this->committeeIds[$code];
    }
}
