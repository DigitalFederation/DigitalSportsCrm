<?php

use App\Models\Group;
use App\Models\LegalPage;
use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed --class=RoleAndPermissionSeeder');
    $this->artisan('db:seed --class=UserGroupSeeder');

    $this->admin = User::factory()->create([
        'email' => 'legal-admin@example.test',
        'group_id' => Group::where('code', 'ADMIN')->value('id'),
        'active' => true,
    ]);
    $this->admin->assignRole('admin');

    $this->member = User::factory()->create([
        'email' => 'legal-member@example.test',
        'active' => true,
    ]);

    LegalPage::query()->delete();
    LegalPage::flushCache();
});

function publishAdminFixture(array $attributes = []): LegalPage
{
    return LegalPage::create(array_merge([
        'type' => LegalPage::TYPE_PRIVACY,
        'locale' => 'en',
        'version' => 1,
        'title' => 'Privacy Policy',
        'body' => '<p>Published body</p>',
        'effective_date' => now()->toDateString(),
        'published_at' => now(),
    ], $attributes));
}

/* -------------------------------------------------------------------------- */
/*  Access control                                                            */
/* -------------------------------------------------------------------------- */

test('guests cannot reach the admin screen', function () {
    $this->get(route('admin.legal-pages.index'))->assertRedirect();
});

test('a user without access settings is forbidden', function () {
    $this->actingAs($this->member)
        ->get(route('admin.legal-pages.index'))
        ->assertForbidden();
});

test('an admin can view the index and the editor', function () {
    publishAdminFixture();
    app()->setLocale('en');

    $this->actingAs($this->admin)
        ->get(route('admin.legal-pages.index'))
        ->assertOk()
        ->assertSee('Privacy Policy', false);

    $this->actingAs($this->admin)
        ->get(route('admin.legal-pages.edit', [LegalPage::TYPE_PRIVACY, 'en']))
        ->assertOk();
});

test('unknown types and locales are rejected', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.legal-pages.edit', ['not-a-type', 'en']))
        ->assertNotFound();

    $this->actingAs($this->admin)
        ->get(route('admin.legal-pages.edit', [LegalPage::TYPE_PRIVACY, 'xx']))
        ->assertNotFound();
});

/* -------------------------------------------------------------------------- */
/*  Draft / publish flow                                                      */
/* -------------------------------------------------------------------------- */

test('saving a draft does not change the public page', function () {
    publishAdminFixture(['body' => '<p>Currently live</p>']);

    $this->actingAs($this->admin)->put(route('admin.legal-pages.update'), [
        'type' => LegalPage::TYPE_PRIVACY,
        'locale' => 'en',
        'title' => 'New title',
        'body' => '<p>Draft in progress</p>',
        'effective_date' => now()->toDateString(),
    ])->assertRedirect();

    expect(LegalPage::draftFor(LegalPage::TYPE_PRIVACY, 'en'))->not->toBeNull();

    $this->get(route('privacy-policy'))
        ->assertSee('Currently live', false)
        ->assertDontSee('Draft in progress', false);
});

test('publishing creates a new version and preserves history', function () {
    publishAdminFixture(['body' => '<p>Version one</p>']);

    $this->actingAs($this->admin)->put(route('admin.legal-pages.publish'), [
        'type' => LegalPage::TYPE_PRIVACY,
        'locale' => 'en',
        'title' => 'Privacy Policy',
        'body' => '<p>Version two</p>',
        'effective_date' => now()->toDateString(),
    ])->assertRedirect();

    $versions = LegalPage::where('type', LegalPage::TYPE_PRIVACY)->where('locale', 'en')->get();

    expect($versions)->toHaveCount(2)
        ->and($versions->max('version'))->toBe(2);

    $this->get(route('privacy-policy'))
        ->assertSee('Version two', false)
        ->assertDontSee('Version one', false);
});

test('the form rejects an empty body or title', function () {
    $this->actingAs($this->admin)->put(route('admin.legal-pages.update'), [
        'type' => LegalPage::TYPE_PRIVACY,
        'locale' => 'en',
        'title' => '',
        'body' => '',
    ])->assertSessionHasErrors(['title', 'body']);
});

/* -------------------------------------------------------------------------- */
/*  The security path: hostile input through the real endpoint                */
/* -------------------------------------------------------------------------- */

test('a script payload submitted through the admin form never reaches the public page', function () {
    publishAdminFixture();

    $payload = '<p>Legitimate clause.</p>'
        . '<script>window.__pwned = true;</script>'
        . '<img src=x onerror="window.__pwned=true">'
        . '<iframe src="https://evil.test"></iframe>'
        . '<a href="javascript:window.__pwned=true">click me</a>'
        . '<p onclick="window.__pwned=true">and this</p>';

    $this->actingAs($this->admin)->put(route('admin.legal-pages.publish'), [
        'type' => LegalPage::TYPE_PRIVACY,
        'locale' => 'en',
        'title' => 'Privacy Policy',
        'body' => $payload,
        'effective_date' => now()->toDateString(),
    ])->assertRedirect();

    $stored = LegalPage::where('type', LegalPage::TYPE_PRIVACY)
        ->where('locale', 'en')
        ->orderByDesc('version')
        ->first();

    // Nothing executable is persisted...
    expect(strtolower($stored->body))
        ->not->toContain('<script')
        ->not->toContain('<iframe')
        ->not->toContain('onerror')
        ->not->toContain('onclick')
        ->not->toContain('javascript:')
        ->and($stored->body)->toContain('Legitimate clause.');

    // ...and nothing executable is served.
    $html = $this->get(route('privacy-policy'))->assertOk()->getContent();

    expect(strtolower($html))
        ->not->toContain('window.__pwned')
        ->not->toContain('javascript:');
});

/* -------------------------------------------------------------------------- */
/*  Menu delivery                                                             */
/* -------------------------------------------------------------------------- */

test('the sidebar entry is created without disturbing existing menu items', function () {
    $this->artisan('db:seed --class=MenuSeeder');

    MenuItem::where('route_name', 'admin.legal-pages.index')->delete();

    $before = MenuItem::orderBy('id')->get()
        ->map->only(['id', 'menu_id', 'parent_id', 'name', 'route_name', 'order'])->toArray();

    $migration = require database_path('migrations/2026_08_13_100002_add_legal_pages_menu_item.php');
    $migration->up();

    $item = MenuItem::where('route_name', 'admin.legal-pages.index')->first();

    expect($item)->not->toBeNull()
        ->and($item->permissions)->toBe(['access settings']);

    $after = MenuItem::whereKeyNot($item->id)->orderBy('id')->get()
        ->map->only(['id', 'menu_id', 'parent_id', 'name', 'route_name', 'order'])->toArray();

    // Every pre-existing row survives untouched: the migration only inserts.
    expect($after)->toEqual($before);
});

test('re-running the menu migration does not duplicate the entry', function () {
    $this->artisan('db:seed --class=MenuSeeder');

    $migration = require database_path('migrations/2026_08_13_100002_add_legal_pages_menu_item.php');
    $migration->up();
    $migration->up();

    expect(MenuItem::where('route_name', 'admin.legal-pages.index')->count())->toBe(1);
});

test('the menu seeder honours an explicit permissions list', function () {
    $this->artisan('db:seed --class=MenuSeeder');

    $item = MenuItem::where('route_name', 'admin.legal-pages.index')->first();

    expect($item)->not->toBeNull()
        ->and($item->permissions)->toBe(['access settings']);
});
