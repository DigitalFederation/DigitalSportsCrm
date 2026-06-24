<?php

use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed --class=RoleAndPermissionSeeder');

    // Create groups
    $this->entityGroup = Group::firstOrCreate(['code' => 'ENTITY'], ['name' => 'Entity']);
    $this->federationGroup = Group::firstOrCreate(['code' => 'FEDERATION'], ['name' => 'Federation']);
});

test('password reset marks email as verified for unverified users', function () {
    // Create a user without verified email (simulating admin-created entity user)
    $user = User::factory()->create([
        'email' => 'unverified@example.test',
        'email_verified_at' => null,
        'group_id' => $this->entityGroup->id,
        'active' => true,
    ]);

    // Ensure email is not verified
    $this->assertNull($user->email_verified_at);

    // Create a password reset token
    $token = Password::createToken($user);

    // Reset password
    $response = $this->post('/reset-password', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ]);

    // Refresh user from database
    $user->refresh();

    // Verify email is now marked as verified
    $this->assertNotNull($user->email_verified_at);

    // Verify password was changed
    $this->assertTrue(Hash::check('newpassword123', $user->password));
});

test('password reset auto-logs in user and redirects to entity dashboard', function () {
    // Create an entity user without verified email
    $user = User::factory()->create([
        'email' => 'entity-user@example.test',
        'email_verified_at' => null,
        'group_id' => $this->entityGroup->id,
        'active' => true,
    ]);

    // Create password reset token
    $token = Password::createToken($user);

    // Reset password (don't follow redirects so we can check the redirect location)
    $response = $this->post('/reset-password', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ]);

    // Verify user is now logged in
    $this->assertAuthenticatedAs($user);

    // Verify redirect to entity dashboard
    $response->assertRedirect(route('entity.dashboard'));
});

test('password reset preserves email verification for already verified users', function () {
    $verifiedAt = now()->subDays(5);

    // Create a user with already verified email
    $user = User::factory()->create([
        'email' => 'verified@example.test',
        'email_verified_at' => $verifiedAt,
        'group_id' => $this->entityGroup->id,
        'active' => true,
    ]);

    // Create password reset token
    $token = Password::createToken($user);

    // Reset password
    $this->post('/reset-password', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ]);

    // Refresh user from database
    $user->refresh();

    // Verify email_verified_at is still set (not changed)
    $this->assertNotNull($user->email_verified_at);
});

test('password reset redirects federation users to federation dashboard', function () {
    $user = User::factory()->create([
        'email' => 'fed-user@example.test',
        'email_verified_at' => null,
        'group_id' => $this->federationGroup->id,
        'active' => true,
    ]);

    $token = Password::createToken($user);

    // Reset password (don't follow redirects so we can check the redirect location)
    $response = $this->post('/reset-password', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('federation.dashboard'));
});
