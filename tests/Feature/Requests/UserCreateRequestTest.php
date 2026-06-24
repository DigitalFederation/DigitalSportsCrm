<?php

use App\Http\Requests\UserCreateRequest;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed --class=UserGroupSeeder');
    $this->group = Group::where('code', 'INDIVIDUAL')->first();
});

describe('group_id validation', function () {
    it('requires group_id field', function () {
        $request = new UserCreateRequest;
        $validator = Validator::make([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ], $request->rules());

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('group_id'))->toBeTrue();
    });

    it('rejects non-existent group_id', function () {
        $request = new UserCreateRequest;
        $validator = Validator::make([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'group_id' => 99999,
        ], $request->rules());

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('group_id'))->toBeTrue();
    });

    it('accepts valid group_id', function () {
        $request = new UserCreateRequest;
        $validator = Validator::make([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'group_id' => $this->group->id,
        ], $request->rules());

        expect($validator->errors()->has('group_id'))->toBeFalse();
    });
});

describe('name validation', function () {
    it('requires name field', function () {
        $request = new UserCreateRequest;
        $validator = Validator::make([
            'email' => 'test@example.com',
            'group_id' => $this->group->id,
        ], $request->rules());

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('name'))->toBeTrue();
    });

    it('rejects name exceeding 100 characters', function () {
        $request = new UserCreateRequest;
        $validator = Validator::make([
            'name' => str_repeat('a', 101),
            'email' => 'test@example.com',
            'group_id' => $this->group->id,
        ], $request->rules());

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('name'))->toBeTrue();
    });
});

describe('email validation', function () {
    it('requires email for new users', function () {
        $request = new UserCreateRequest;
        $validator = Validator::make([
            'name' => 'Test User',
            'group_id' => $this->group->id,
        ], $request->rules());

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('email'))->toBeTrue();
    });

    it('rejects invalid email format', function () {
        $request = new UserCreateRequest;
        $validator = Validator::make([
            'name' => 'Test User',
            'email' => 'not-an-email',
            'group_id' => $this->group->id,
        ], $request->rules());

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('email'))->toBeTrue();
    });

    it('rejects duplicate email for new users', function () {
        User::factory()->create(['email' => 'existing@example.com']);

        $request = new UserCreateRequest;
        $validator = Validator::make([
            'name' => 'Test User',
            'email' => 'existing@example.com',
            'group_id' => $this->group->id,
        ], $request->rules());

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('email'))->toBeTrue();
    });
});

describe('roles validation', function () {
    it('accepts null roles', function () {
        $request = new UserCreateRequest;
        $validator = Validator::make([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'group_id' => $this->group->id,
            'roles' => null,
        ], $request->rules());

        expect($validator->errors()->has('roles'))->toBeFalse();
    });

    it('requires roles to be an array when provided', function () {
        $request = new UserCreateRequest;
        $validator = Validator::make([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'group_id' => $this->group->id,
            'roles' => 'not-an-array',
        ], $request->rules());

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('roles'))->toBeTrue();
    });

    it('rejects non-existent role ids', function () {
        $request = new UserCreateRequest;
        $validator = Validator::make([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'group_id' => $this->group->id,
            'roles' => [99999],
        ], $request->rules());

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('roles.0'))->toBeTrue();
    });
});

describe('federation validation', function () {
    it('accepts null federation', function () {
        $request = new UserCreateRequest;
        $validator = Validator::make([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'group_id' => $this->group->id,
            'federation' => null,
        ], $request->rules());

        expect($validator->errors()->has('federation'))->toBeFalse();
    });

    it('rejects non-existent federation id', function () {
        $request = new UserCreateRequest;
        $validator = Validator::make([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'group_id' => $this->group->id,
            'federation' => 99999,
        ], $request->rules());

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('federation'))->toBeTrue();
    });
});
