<?php

use App\Models\Role;
use Domain\Certifications\Actions\CreateCertificationAction;
use Domain\Certifications\Actions\EditCertificationAction;
use Domain\Certifications\DataTransferObject\CertificationData;
use Domain\Certifications\Models\Certification;

use function Pest\Laravel\artisan;

beforeEach(function () {
    artisan('db:seed --class=RoleAndPermissionSeeder');
});

it('syncs roles when creating a certification', function () {
    $roles = Role::take(2)->get();
    $certification = Certification::factory()->create();

    $data = CertificationData::fromArray([
        'committee_id' => $certification->committee_id,
        'name' => 'Test Certification',
        'requester_model' => 'Entity',
        'roles' => $roles->pluck('id')->toArray(),
    ]);

    $action = new CreateCertificationAction;
    $created = $action($data);

    expect($created->roles)->toHaveCount(2)
        ->and($created->roles->pluck('id')->toArray())
        ->toEqual($roles->pluck('id')->toArray());
});

it('syncs roles when editing a certification', function () {
    $roles = Role::take(3)->get();
    $certification = Certification::factory()->create();

    $data = CertificationData::fromArray([
        'committee_id' => $certification->committee_id,
        'name' => $certification->name,
        'roles' => $roles->pluck('id')->toArray(),
    ]);

    $action = new EditCertificationAction;
    $action($data, $certification->id);

    $certification->refresh()->load('roles');

    expect($certification->roles)->toHaveCount(3);
});

it('does not clear roles when roles is null', function () {
    $roles = Role::take(2)->get();
    $certification = Certification::factory()->create();
    $certification->roles()->sync($roles->pluck('id'));

    $data = CertificationData::fromArray([
        'committee_id' => $certification->committee_id,
        'name' => $certification->name,
    ]);

    expect($data->roles)->toBeNull();

    $action = new EditCertificationAction;
    $action($data, $certification->id);

    $certification->refresh()->load('roles');

    expect($certification->roles)->toHaveCount(2);
});

it('can clear all roles by passing empty array', function () {
    $roles = Role::take(2)->get();
    $certification = Certification::factory()->create();
    $certification->roles()->sync($roles->pluck('id'));

    $data = CertificationData::fromArray([
        'committee_id' => $certification->committee_id,
        'name' => $certification->name,
        'roles' => [],
    ]);

    $action = new EditCertificationAction;
    $action($data, $certification->id);

    $certification->refresh()->load('roles');

    expect($certification->roles)->toHaveCount(0);
});
