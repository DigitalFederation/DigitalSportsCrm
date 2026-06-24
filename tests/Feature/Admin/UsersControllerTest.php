<?php

use App\Models\Group;
use App\Models\User;
use Domain\Federations\Models\Federation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed --class=RoleAndPermissionSeeder');
    $this->artisan('db:seed --class=UserGroupSeeder');

    $this->adminGroup = Group::where('code', 'ADMIN')->first();
    $this->individualGroup = Group::where('code', 'INDIVIDUAL')->first();
    $this->federationGroup = Group::where('code', 'FEDERATION')->first();

    $this->admin = User::factory()->create([
        'email' => 'admin@example.test',
        'group_id' => $this->adminGroup->id,
        'active' => true,
    ]);
    $this->admin->assignRole('admin');
});

describe('user update', function () {
    it('can update a user with valid data', function () {
        $userToEdit = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.test',
            'group_id' => $this->individualGroup->id,
            'active' => true,
        ]);

        $this->actingAs($this->admin);

        $response = $this->put(route('admin.user.update', $userToEdit->id), [
            'name' => 'Updated Name',
            'email' => 'original@example.test',
            'group_id' => $this->individualGroup->id,
            'active' => '1',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $userToEdit->refresh();
        expect($userToEdit->name)->toBe('Updated Name');
    });

    it('fails validation when group_id is missing', function () {
        $userToEdit = User::factory()->create([
            'group_id' => $this->individualGroup->id,
        ]);

        $this->actingAs($this->admin);

        $response = $this->put(route('admin.user.update', $userToEdit->id), [
            'name' => 'Updated Name',
            'email' => $userToEdit->email,
            'active' => '1',
        ]);

        $response->assertSessionHasErrors('group_id');
    });

    it('fails validation when group_id does not exist', function () {
        $userToEdit = User::factory()->create([
            'group_id' => $this->individualGroup->id,
        ]);

        $this->actingAs($this->admin);

        $response = $this->put(route('admin.user.update', $userToEdit->id), [
            'name' => 'Updated Name',
            'email' => $userToEdit->email,
            'group_id' => 99999,
            'active' => '1',
        ]);

        $response->assertSessionHasErrors('group_id');
    });

    it('can update user roles', function () {
        $userToEdit = User::factory()->create([
            'group_id' => $this->individualGroup->id,
        ]);

        $role = Role::where('name', 'entity-admin')->first();

        $this->actingAs($this->admin);

        $response = $this->put(route('admin.user.update', $userToEdit->id), [
            'name' => $userToEdit->name,
            'email' => $userToEdit->email,
            'group_id' => $this->individualGroup->id,
            'active' => '1',
            'roles' => [$role->id],
        ]);

        $response->assertRedirect(route('admin.users.index'));

        $userToEdit->refresh();
        expect($userToEdit->hasRole('entity-admin'))->toBeTrue();
    });

    it('syncs federation when group is FEDERATION', function () {
        $federation = Federation::factory()->create();
        $userToEdit = User::factory()->create([
            'group_id' => $this->federationGroup->id,
        ]);

        $this->actingAs($this->admin);

        $response = $this->put(route('admin.user.update', $userToEdit->id), [
            'name' => $userToEdit->name,
            'email' => $userToEdit->email,
            'group_id' => $this->federationGroup->id,
            'active' => '1',
            'federation' => $federation->id,
        ]);

        $response->assertRedirect(route('admin.users.index'));

        $userToEdit->refresh();
        expect($userToEdit->federations->pluck('id')->toArray())->toContain($federation->id);
    });
});

describe('user store', function () {
    it('fails validation when group_id is missing on create', function () {
        $this->actingAs($this->admin);

        $response = $this->post(route('admin.user.store'), [
            'name' => 'New User',
            'email' => 'newuser@example.test',
            'active' => '1',
        ]);

        $response->assertSessionHasErrors('group_id');
    });

    it('fails validation when group_id does not exist on create', function () {
        $this->actingAs($this->admin);

        $response = $this->post(route('admin.user.store'), [
            'name' => 'New User',
            'email' => 'newuser@example.test',
            'group_id' => 99999,
            'active' => '1',
        ]);

        $response->assertSessionHasErrors('group_id');
    });
});

describe('authorization', function () {
    it('redirects unauthenticated users to login', function () {
        $response = $this->get(route('admin.users.index'));

        $response->assertRedirect(route('login'));
    });

    it('allows admin to access user edit page', function () {
        $userToEdit = User::factory()->create([
            'group_id' => $this->individualGroup->id,
        ]);

        $this->actingAs($this->admin);

        $response = $this->get(route('admin.user.edit', $userToEdit->id));

        $response->assertSuccessful();
    });
});

describe('integration - combined field validation', function () {
    it('fails validation when updating roles with invalid group_id', function () {
        $userToEdit = User::factory()->create([
            'group_id' => $this->individualGroup->id,
        ]);

        $role = Role::where('name', 'entity-admin')->first();

        $this->actingAs($this->admin);

        $response = $this->put(route('admin.user.update', $userToEdit->id), [
            'name' => $userToEdit->name,
            'email' => $userToEdit->email,
            'group_id' => 99999,
            'active' => '1',
            'roles' => [$role->id],
        ]);

        $response->assertSessionHasErrors('group_id');
    });

    it('fails validation when syncing federation with invalid group_id', function () {
        $federation = Federation::factory()->create();
        $userToEdit = User::factory()->create([
            'group_id' => $this->federationGroup->id,
        ]);

        $this->actingAs($this->admin);

        $response = $this->put(route('admin.user.update', $userToEdit->id), [
            'name' => $userToEdit->name,
            'email' => $userToEdit->email,
            'group_id' => 99999,
            'active' => '1',
            'federation' => $federation->id,
        ]);

        $response->assertSessionHasErrors('group_id');
    });

    it('fails validation when updating all fields with invalid group_id', function () {
        $federation = Federation::factory()->create();
        $userToEdit = User::factory()->create([
            'group_id' => $this->federationGroup->id,
        ]);

        $role = Role::where('name', 'entity-admin')->first();

        $this->actingAs($this->admin);

        $response = $this->put(route('admin.user.update', $userToEdit->id), [
            'name' => 'New Name',
            'email' => 'newemail@example.test',
            'group_id' => 99999,
            'active' => '1',
            'roles' => [$role->id],
            'federation' => $federation->id,
        ]);

        $response->assertSessionHasErrors('group_id');
        $userToEdit->refresh();
        expect($userToEdit->name)->not->toBe('New Name');
    });

    it('can update user with roles and valid federation group', function () {
        $federation = Federation::factory()->create();
        $userToEdit = User::factory()->create([
            'group_id' => $this->federationGroup->id,
        ]);

        $role = Role::where('name', 'federation-admin')->first();

        $this->actingAs($this->admin);

        $response = $this->put(route('admin.user.update', $userToEdit->id), [
            'name' => 'Updated Federation User',
            'email' => $userToEdit->email,
            'group_id' => $this->federationGroup->id,
            'active' => '1',
            'roles' => [$role->id],
            'federation' => $federation->id,
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $userToEdit->refresh();
        expect($userToEdit->name)->toBe('Updated Federation User');
        expect($userToEdit->hasRole('federation-admin'))->toBeTrue();
        expect($userToEdit->federations->pluck('id')->toArray())->toContain($federation->id);
    });
});
