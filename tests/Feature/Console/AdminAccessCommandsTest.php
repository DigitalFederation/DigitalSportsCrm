<?php

use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Database\Seeders\UserGroupSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
    $this->seed(UserGroupSeeder::class);
});

it('creates an admin user only with explicit interactive credentials', function () {
    $this->artisan('user:create-admin')
        ->expectsQuestion('What is the user\'s name?', 'Example Admin')
        ->expectsQuestion('What is the user\'s email?', 'admin@example.test')
        ->expectsQuestion('What is the user\'s password?', 'strong-password')
        ->expectsQuestion('Confirm the user\'s password', 'strong-password')
        ->assertExitCode(Command::SUCCESS);

    $user = User::where('email', 'admin@example.test')->firstOrFail();

    expect($user->name)->toBe('Example Admin')
        ->and(Hash::check('strong-password', $user->password))->toBeTrue()
        ->and($user->hasRole('admin'))->toBeTrue();
});

it('rejects admin user creation when the password confirmation does not match', function () {
    $this->artisan('user:create-admin')
        ->expectsQuestion('What is the user\'s name?', 'Example Admin')
        ->expectsQuestion('What is the user\'s email?', 'admin@example.test')
        ->expectsQuestion('What is the user\'s password?', 'strong-password')
        ->expectsQuestion('Confirm the user\'s password', 'different-password')
        ->expectsOutput('Password confirmation does not match.')
        ->assertExitCode(Command::FAILURE);

    expect(User::where('email', 'admin@example.test')->exists())->toBeFalse();
});

it('does not seed a default admin unless explicitly enabled', function () {
    Config::set('seeding.default_admin.enabled', false);

    $this->artisan('db:seed --class=UserSeeder')
        ->expectsOutput('Default admin user was not seeded. Set SEED_DEFAULT_ADMIN=true and DEFAULT_ADMIN_PASSWORD to opt in.')
        ->assertExitCode(Command::SUCCESS);

    expect(User::where('email', 'admin@example.test')->exists())->toBeFalse();
});

it('seeds the default admin when explicitly enabled', function () {
    Config::set('seeding.default_admin.enabled', true);
    Config::set('seeding.default_admin.password', 'change-me-now');
    Config::set('seeding.default_admin.email', 'seeded.admin@example.test');

    $this->artisan('db:seed --class=UserSeeder')->assertExitCode(Command::SUCCESS);

    $user = User::where('email', 'seeded.admin@example.test')->firstOrFail();

    expect(Hash::check('change-me-now', $user->password))->toBeTrue()
        ->and($user->hasRole('admin'))->toBeTrue()
        ->and((bool) $user->active)->toBeTrue();
});

it('seeds the default admin as part of the full database seed when enabled', function () {
    Config::set('seeding.default_admin.enabled', true);
    Config::set('seeding.default_admin.password', 'change-me-now');
    Config::set('seeding.default_admin.email', 'seeded.admin@example.test');

    $this->artisan('db:seed')->assertExitCode(Command::SUCCESS);

    expect(User::where('email', 'seeded.admin@example.test')->firstOrFail()->hasRole('admin'))->toBeTrue();
});

it('requires an explicit email before restoring admin access', function () {
    User::factory()->create(['email' => 'first.user@example.test']);

    $this->artisan('restore:admin-access')
        ->expectsOutput('A valid --email option is required.')
        ->assertExitCode(Command::FAILURE);

    expect(User::where('email', 'first.user@example.test')->first()->hasRole('admin'))->toBeFalse();
});

it('requires confirmation before restoring admin access unless forced', function () {
    $user = User::factory()->create(['email' => 'target.user@example.test']);

    $this->artisan('restore:admin-access --email=target.user@example.test')
        ->expectsConfirmation('Grant full admin access to target.user@example.test?', 'no')
        ->expectsOutput('Operation cancelled.')
        ->assertExitCode(Command::FAILURE);

    expect($user->fresh()->hasRole('admin'))->toBeFalse();
});

it('restores admin access only for the explicitly requested user', function () {
    $target = User::factory()->create(['email' => 'target.user@example.test']);
    $other = User::factory()->create(['email' => 'other.user@example.test']);

    $this->artisan('restore:admin-access --email=target.user@example.test --force')
        ->assertExitCode(Command::SUCCESS);

    $target->refresh();
    $other->refresh();

    expect($target->hasRole('admin'))->toBeTrue()
        ->and($target->getAllPermissions())->toHaveCount(Permission::count())
        ->and($other->hasRole('admin'))->toBeFalse()
        ->and($other->getAllPermissions())->toHaveCount(0);
});
