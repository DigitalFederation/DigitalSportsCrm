<?php

use App\Models\Group;
use App\Models\User;
use Domain\Users\Actions\CreateUserAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('creates a new user successfully', function () {
    // Create a group to associate with the user
    $group = Group::factory()->create(['code' => 'INDIVIDUAL']);

    // Data to create the user
    $userData = [
        'email' => 'person.one@example.test',
        'name' => 'Example User',
        'role' => 'INDIVIDUAL',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ];

    $createUserAction = new CreateUserAction;

    $result = $createUserAction($userData);

    // Check if a user got created
    expect($result['user'])->toBeInstanceOf(User::class);

    // Check if the user's email and name are set correctly
    expect($result['user']->email)->toBe('person.one@example.test');
    expect($result['user']->name)->toBe('Example User');

    // Check if the user's group is set correctly
    expect($result['user']->group_id)->toBe($group->id);

    // Check if the token exists
    $resetRecord = DB::table('password_resets')->where('email', $result['user']->email)->first();
    expect($resetRecord)->not()->toBeNull();

    // Check if the token exists
    expect($result['token'])->not()->toBeNull();

});

it('creates an active user when is_active parameter is true', function () {
    // Create a group to associate with the user
    $group = Group::factory()->create(['code' => 'INDIVIDUAL']);

    // Data to create the user
    $userData = [
        'email' => 'person.one@example.test',
        'name' => 'Example User',
        'role' => 'INDIVIDUAL',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ];

    $createUserAction = new CreateUserAction;

    $result = $createUserAction($userData, true);

    // Check if the user is active
    expect($result['user']->active)->toBe(true);
});
