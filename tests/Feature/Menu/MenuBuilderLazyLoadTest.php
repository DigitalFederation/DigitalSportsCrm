<?php

namespace Tests\Feature\Menu;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Services\MenuBuilderService;
use Illuminate\Support\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuBuilderLazyLoadTest extends TestCase
{
    use RefreshDatabase;

    public function test_attach_children_materializes_children_on_every_node(): void
    {
        // The federation dashboard crash was a LazyLoadingViolationException on
        // MenuItem::children: leaf nodes reached filterMenuItemsForUser()/the views
        // without `children` materialized, so accessing it lazy-loaded the relation.
        // The fix makes attachChildren() set `children` (even when empty) on every
        // node as a plain attribute, so it is never lazy-loaded.
        $menu = Menu::create(['name' => 'Federation', 'machine_name' => 'federation-test']);
        $root = MenuItem::create([
            'menu_id' => $menu->id, 'name' => 'Section', 'visible' => true, 'order' => 1,
        ]);
        $child = MenuItem::create([
            'menu_id' => $menu->id, 'parent_id' => $root->id, 'name' => 'Dashboard',
            'route_name' => 'federation.dashboard', 'visible' => true, 'order' => 1,
        ]);

        // buildFresh() skips the cache and user filtering — it exercises
        // attachChildren() directly without touching `children` afterwards.
        $structure = app(MenuBuilderService::class)->buildFresh('federation-test');

        $rootNode = $structure->firstWhere('id', $root->id);
        $this->assertNotNull($rootNode, 'root menu item should be present');

        $childNode = collect($rootNode->getAttributes()['children'] ?? [])
            ->firstWhere('id', $child->id);
        $this->assertNotNull($childNode, 'child should be attached under the root');

        // The regression guard: a LEAF node must carry `children` as a plain
        // attribute (not merely a not-yet-loaded relation), so nothing downstream
        // lazy-loads it. Without the fix attachChildren skips leaves and this key
        // is absent.
        $this->assertArrayHasKey('children', $childNode->getAttributes());
        $this->assertInstanceOf(Collection::class, $childNode->getAttributes()['children']);
        $this->assertTrue($childNode->getAttributes()['children']->isEmpty());
    }
}
