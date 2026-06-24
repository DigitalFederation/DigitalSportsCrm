<?php

use App\Enums\UserGroupEnum;
use App\Models\Group;
use App\Models\User;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\IndividualFederation;
use Domain\Individuals\States\ActiveIndividualFederationState;
use Domain\Individuals\States\PendingIndividualFederationState;
use Domain\Individuals\States\RejectedIndividualFederationState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\artisan;

uses(RefreshDatabase::class);

beforeEach(function () {
    artisan('db:seed --class=RoleAndPermissionSeeder');

    Group::forceCreate([
        'id' => UserGroupEnum::FEDERATION->value,
        'name' => 'Federation',
        'code' => 'FEDERATION',
    ]);

    Group::forceCreate([
        'id' => UserGroupEnum::INDIVIDUAL->value,
        'name' => 'Individual',
        'code' => 'INDIVIDUAL',
    ]);

    $this->federation = Federation::factory()->create([
        'parent_id' => null,
        'is_default_federation' => true,
    ]);

    $this->user = User::factory()->create([
        'group_id' => UserGroupEnum::FEDERATION->value,
    ]);
    $this->user->federations()->attach($this->federation->id);

    $this->actingAs($this->user);

    DB::table('member_number_settings')->updateOrInsert(
        ['key' => 'individual_counter'],
        ['value' => 500, 'description' => 'Individual counter', 'created_at' => now(), 'updated_at' => now()]
    );
});

test('accept sets status to active, saves national federation number, and assigns member number', function () {
    $individualUser = User::factory()->create([
        'group_id' => UserGroupEnum::INDIVIDUAL->value,
    ]);

    $individual = Individual::factory()->create([
        'user_id' => $individualUser->id,
        'member_number' => null,
    ]);

    $individualFederation = IndividualFederation::create([
        'individual_id' => $individual->id,
        'federation_id' => $this->federation->id,
        'active' => false,
        'status_class' => PendingIndividualFederationState::class,
    ]);

    $response = $this->post(route('federation.individual-request.accept', $individualFederation->id), [
        'national_federation_number' => 'NFN-12345',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $individualFederation->refresh();
    expect($individualFederation->active)->toBe(1)
        ->and($individualFederation->status_class)->toBe(ActiveIndividualFederationState::class);

    $individual->refresh();
    expect($individual->national_federation_number)->toBe('NFN-12345')
        ->and($individual->member_number)->toBe(500);

    $counter = DB::table('member_number_settings')->where('key', 'individual_counter')->first();
    expect((int) $counter->value)->toBe(501);
});

test('accept works without national federation number', function () {
    $individualUser = User::factory()->create([
        'group_id' => UserGroupEnum::INDIVIDUAL->value,
    ]);

    $individual = Individual::factory()->create([
        'user_id' => $individualUser->id,
        'national_federation_number' => null,
    ]);

    $individualFederation = IndividualFederation::create([
        'individual_id' => $individual->id,
        'federation_id' => $this->federation->id,
        'active' => false,
        'status_class' => PendingIndividualFederationState::class,
    ]);

    $response = $this->post(route('federation.individual-request.accept', $individualFederation->id));

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $individual->refresh();
    expect($individual->national_federation_number)->toBeNull()
        ->and($individual->member_number)->not->toBeNull();
});

test('accept does not overwrite existing member number', function () {
    $individualUser = User::factory()->create([
        'group_id' => UserGroupEnum::INDIVIDUAL->value,
    ]);

    $individual = Individual::factory()->create([
        'user_id' => $individualUser->id,
        'member_number' => 12345,
    ]);

    $individualFederation = IndividualFederation::create([
        'individual_id' => $individual->id,
        'federation_id' => $this->federation->id,
        'active' => false,
        'status_class' => PendingIndividualFederationState::class,
    ]);

    $this->post(route('federation.individual-request.accept', $individualFederation->id), [
        'national_federation_number' => 'NFN-00001',
    ]);

    $individual->refresh();
    expect($individual->member_number)->toBe(12345);

    $counter = DB::table('member_number_settings')->where('key', 'individual_counter')->first();
    expect((int) $counter->value)->toBe(500);
});

test('accept rejects already active records', function () {
    $individualUser = User::factory()->create([
        'group_id' => UserGroupEnum::INDIVIDUAL->value,
    ]);

    $individual = Individual::factory()->create([
        'user_id' => $individualUser->id,
    ]);

    $individualFederation = IndividualFederation::create([
        'individual_id' => $individual->id,
        'federation_id' => $this->federation->id,
        'active' => true,
        'status_class' => ActiveIndividualFederationState::class,
    ]);

    $response = $this->post(route('federation.individual-request.accept', $individualFederation->id), [
        'national_federation_number' => 'NFN-00001',
    ]);

    $response->assertSessionHas('error');
});

test('reject sets status to rejected', function () {
    $individualUser = User::factory()->create([
        'group_id' => UserGroupEnum::INDIVIDUAL->value,
    ]);

    $individual = Individual::factory()->create([
        'user_id' => $individualUser->id,
    ]);

    $individualFederation = IndividualFederation::create([
        'individual_id' => $individual->id,
        'federation_id' => $this->federation->id,
        'active' => false,
        'status_class' => PendingIndividualFederationState::class,
    ]);

    $response = $this->post(route('federation.individual-request.reject', $individualFederation->id));

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $individualFederation->refresh();
    expect($individualFederation->active)->toBe(0)
        ->and($individualFederation->status_class)->toBe(RejectedIndividualFederationState::class)
        ->and($individualFederation->rejected_at)->not->toBeNull();
});

test('reject fails for already rejected records', function () {
    $individualUser = User::factory()->create([
        'group_id' => UserGroupEnum::INDIVIDUAL->value,
    ]);

    $individual = Individual::factory()->create([
        'user_id' => $individualUser->id,
    ]);

    $individualFederation = IndividualFederation::create([
        'individual_id' => $individual->id,
        'federation_id' => $this->federation->id,
        'active' => false,
        'status_class' => RejectedIndividualFederationState::class,
        'rejected_at' => now(),
    ]);

    $response = $this->post(route('federation.individual-request.reject', $individualFederation->id));

    $response->assertSessionHas('error');
});

test('cannot accept request from another federation', function () {
    $otherFederation = Federation::factory()->create(['parent_id' => null]);

    $individualUser = User::factory()->create([
        'group_id' => UserGroupEnum::INDIVIDUAL->value,
    ]);

    $individual = Individual::factory()->create([
        'user_id' => $individualUser->id,
    ]);

    $individualFederation = IndividualFederation::create([
        'individual_id' => $individual->id,
        'federation_id' => $otherFederation->id,
        'active' => false,
        'status_class' => PendingIndividualFederationState::class,
    ]);

    $response = $this->post(route('federation.individual-request.accept', $individualFederation->id), [
        'national_federation_number' => 'NFN-00001',
    ]);

    $response->assertSessionHas('error');

    $individualFederation->refresh();
    expect($individualFederation->status_class)->toBe(PendingIndividualFederationState::class)
        ->and($individualFederation->active)->toBe(0);
});

test('destroy deletes the individual federation record', function () {
    $individualUser = User::factory()->create([
        'group_id' => UserGroupEnum::INDIVIDUAL->value,
    ]);

    $individual = Individual::factory()->create([
        'user_id' => $individualUser->id,
    ]);

    $individualFederation = IndividualFederation::create([
        'individual_id' => $individual->id,
        'federation_id' => $this->federation->id,
        'active' => false,
        'status_class' => PendingIndividualFederationState::class,
    ]);

    $response = $this->delete(route('federation.individual-request.delete', $individualFederation->id));

    $response->assertRedirect();
    $response->assertSessionHas('success');

    expect(IndividualFederation::find($individualFederation->id))->toBeNull();
});

test('destroy fails for non-pending records', function () {
    $individualUser = User::factory()->create([
        'group_id' => UserGroupEnum::INDIVIDUAL->value,
    ]);

    $individual = Individual::factory()->create([
        'user_id' => $individualUser->id,
    ]);

    $individualFederation = IndividualFederation::create([
        'individual_id' => $individual->id,
        'federation_id' => $this->federation->id,
        'active' => true,
        'status_class' => ActiveIndividualFederationState::class,
    ]);

    $response = $this->delete(route('federation.individual-request.delete', $individualFederation->id));

    $response->assertSessionHas('error');

    expect(IndividualFederation::find($individualFederation->id))->not->toBeNull();
});
