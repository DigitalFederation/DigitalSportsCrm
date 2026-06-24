<?php

use App\Models\Group;
use App\Models\User;
use Domain\Individuals\Models\Individual;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withMiddleware(\App\Http\Middleware\EnsureProfilePhotoExists::class);
    Storage::fake('secure-media');

    $this->individualGroup = Group::firstOrCreate(
        ['code' => 'INDIVIDUAL'],
        ['name' => 'Individual']
    );
});

test('user without profile photo is redirected to complete-photo page', function () {
    $user = User::factory()->create(['group_id' => $this->individualGroup->id]);
    $individual = Individual::factory()->create(['user_id' => $user->id]);

    // Confirm no profile photo exists
    expect($individual->hasProfileImage())->toBeFalse();

    actingAs($user)
        ->get(route('individual.dashboard'))
        ->assertRedirect(route('profile.complete-photo'));
});

test('user with profile photo can access protected routes', function () {
    $user = User::factory()->create(['group_id' => $this->individualGroup->id]);
    $individual = Individual::factory()->create(['user_id' => $user->id]);

    // Add a profile photo
    $individual->addMedia(UploadedFile::fake()->image('photo.jpg'))
        ->toMediaCollection('profile', 'secure-media');

    // Confirm profile photo exists
    expect($individual->hasProfileImage())->toBeTrue();

    actingAs($user)
        ->get(route('individual.dashboard'))
        ->assertOk();
});

test('complete-photo page is accessible to users without profile photo', function () {
    $user = User::factory()->create(['group_id' => $this->individualGroup->id]);
    Individual::factory()->create(['user_id' => $user->id]);

    actingAs($user)
        ->get(route('profile.complete-photo'))
        ->assertOk();
});

test('middleware allows users without individual profile to pass through', function () {
    // Create a user without an individual profile (e.g., admin)
    $adminGroup = Group::firstOrCreate(
        ['code' => 'ADMIN'],
        ['name' => 'Admin']
    );
    $adminUser = User::factory()->create(['group_id' => $adminGroup->id]);

    // Admin users don't have individual profiles, middleware should not block them
    // They won't be redirected to complete-photo because they have no individual
    actingAs($adminUser)
        ->get(route('profile.complete-photo'))
        ->assertOk();
});
