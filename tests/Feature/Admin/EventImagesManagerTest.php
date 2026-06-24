<?php

use App\Enums\UserGroupEnum;
use App\Livewire\Admin\EvtEvents\EventImagesManager;
use App\Models\Country;
use App\Models\Group;
use App\Models\User;
use Domain\EvtEvents\Models\Sport;
use Domain\Federations\Models\Federation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    Group::query()->delete();
    Group::insert([
        ['id' => 1, 'name' => 'Individual', 'code' => 'INDIVIDUAL'],
        ['id' => 2, 'name' => 'Entity', 'code' => 'ENTITY'],
        ['id' => 3, 'name' => 'Federation', 'code' => 'FEDERATION'],
        ['id' => 5, 'name' => 'Admin', 'code' => 'ADMIN'],
    ]);

    $permission = Permission::firstOrCreate(['name' => 'access events', 'guard_name' => 'web']);
    $role = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $role->givePermissionTo($permission);

    $this->adminUser = User::factory()->create([
        'group_id' => UserGroupEnum::ADMIN->value,
    ]);
    $this->adminUser->assignRole('admin');

    $this->sport = Sport::factory()->create(['name' => 'Finswimming']);

    $country = Country::factory()->create();
    $this->federation = Federation::create([
        'country_id' => $country->id,
        'name' => 'Test Federation',
        'legal_name' => 'Test Federation',
        'is_default_federation' => true,
        'member_code' => 'TEST01',
        'email' => 'test@federation.test',
    ]);
});

test('event images page can be rendered', function () {
    actingAs($this->adminUser)
        ->get(route('admin.evt-events.event-images.index'))
        ->assertSuccessful()
        ->assertSeeLivewire(EventImagesManager::class);
});

test('it displays all sports', function () {
    Livewire::actingAs($this->adminUser)
        ->test(EventImagesManager::class)
        ->assertSee($this->sport->translatedName)
        ->assertSuccessful();
});

test('admin can upload a sport hero image', function () {
    Storage::fake('public');
    $file = UploadedFile::fake()->image('hero.jpg', 1920, 600);

    Livewire::actingAs($this->adminUser)
        ->test(EventImagesManager::class)
        ->set("sportImages.{$this->sport->id}", $file)
        ->call('uploadSportImage', $this->sport->id)
        ->assertHasNoErrors();

    expect($this->sport->fresh()->getFirstMediaUrl('hero-image'))->not->toBeEmpty();
});

test('admin can remove a sport hero image', function () {
    Storage::fake('public');
    $this->sport->addMedia(UploadedFile::fake()->image('hero.jpg'))
        ->toMediaCollection('hero-image');

    expect($this->sport->getFirstMediaUrl('hero-image'))->not->toBeEmpty();

    Livewire::actingAs($this->adminUser)
        ->test(EventImagesManager::class)
        ->call('removeSportImage', $this->sport->id)
        ->assertHasNoErrors();

    expect($this->sport->fresh()->getFirstMediaUrl('hero-image'))->toBeEmpty();
});

test('admin can upload an organization event hero image', function () {
    Storage::fake('public');
    $file = UploadedFile::fake()->image('org-hero.jpg', 1920, 600);

    Livewire::actingAs($this->adminUser)
        ->test(EventImagesManager::class)
        ->set('organizationImage', $file)
        ->call('uploadOrganizationImage')
        ->assertHasNoErrors();

    expect($this->federation->fresh()->getFirstMediaUrl('organization-event-hero'))->not->toBeEmpty();
});

test('admin can remove an organization event hero image', function () {
    Storage::fake('public');
    $this->federation->addMedia(UploadedFile::fake()->image('org-hero.jpg'))
        ->toMediaCollection('organization-event-hero');

    expect($this->federation->getFirstMediaUrl('organization-event-hero'))->not->toBeEmpty();

    Livewire::actingAs($this->adminUser)
        ->test(EventImagesManager::class)
        ->call('removeOrganizationImage')
        ->assertHasNoErrors();

    expect($this->federation->fresh()->getFirstMediaUrl('organization-event-hero'))->toBeEmpty();
});

test('sport image upload validates file type', function () {
    $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

    Livewire::actingAs($this->adminUser)
        ->test(EventImagesManager::class)
        ->set("sportImages.{$this->sport->id}", $file)
        ->call('uploadSportImage', $this->sport->id)
        ->assertHasErrors("sportImages.{$this->sport->id}");
});

test('sport image upload validates file size', function () {
    $file = UploadedFile::fake()->image('large.jpg')->size(3000);

    Livewire::actingAs($this->adminUser)
        ->test(EventImagesManager::class)
        ->set("sportImages.{$this->sport->id}", $file)
        ->call('uploadSportImage', $this->sport->id)
        ->assertHasErrors("sportImages.{$this->sport->id}");
});

test('organization image upload validates file type', function () {
    $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

    Livewire::actingAs($this->adminUser)
        ->test(EventImagesManager::class)
        ->set('organizationImage', $file)
        ->call('uploadOrganizationImage')
        ->assertHasErrors('organizationImage');
});
