<?php

use App\Models\Group;
use App\Models\User;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->group = Group::factory()->create(['code' => 'FEDERATION']);

    $this->mainFederation = Federation::factory()->create([
        'is_local' => false,
        'parent_id' => null,
        'can_issue_certifications' => true,
    ]);

    $this->specialFederation = Federation::factory()->create([
        'is_local' => true,
        'is_default_federation' => true,
        'can_issue_certifications' => true,
    ]);

    $this->localFederation = Federation::factory()->create([
        'is_local' => true,
        'parent_id' => $this->mainFederation->id,
        'can_issue_certifications' => false,
    ]);

    $this->individual = Individual::factory()->create();
});

test('main federation can access certification index', function () {
    $user = User::factory()->create(['group_id' => $this->group->id]);
    $user->federations()->attach($this->mainFederation->id);

    $response = $this->actingAs($user)
        ->get(route('federation.certification-attributed.index'));

    $response->assertSuccessful();
});

test('special federation with permission can access certification index', function () {
    $user = User::factory()->create(['group_id' => $this->group->id]);
    $user->federations()->attach($this->specialFederation->id);

    $response = $this->actingAs($user)
        ->get(route('federation.certification-attributed.index'));

    $response->assertSuccessful();
});

test('local federation without permission cannot access certification index', function () {
    $user = User::factory()->create(['group_id' => $this->group->id]);
    $user->federations()->attach($this->localFederation->id);

    $response = $this->actingAs($user)
        ->get(route('federation.certification-attributed.index'));

    $response->assertForbidden();
    expect($response->exception->getMessage())->toContain(__('federation.cannot_issue_certifications'));
});

test('main federation can access certification create', function () {
    $user = User::factory()->create(['group_id' => $this->group->id]);
    $user->federations()->attach($this->mainFederation->id);

    $response = $this->actingAs($user)
        ->get(route('federation.certification-attributed.create'));

    $response->assertSuccessful();
});

test('local federation cannot access certification create', function () {
    $user = User::factory()->create(['group_id' => $this->group->id]);
    $user->federations()->attach($this->localFederation->id);

    $response = $this->actingAs($user)
        ->get(route('federation.certification-attributed.create'));

    $response->assertForbidden();
});

test('main federation can access certification wizard', function () {
    $user = User::factory()->create(['group_id' => $this->group->id]);
    $user->federations()->attach($this->mainFederation->id);

    $response = $this->actingAs($user)
        ->get(route('federation.certification-attributed.wizard.create'));

    $response->assertSuccessful();
});

test('local federation cannot access certification wizard', function () {
    $user = User::factory()->create(['group_id' => $this->group->id]);
    $user->federations()->attach($this->localFederation->id);

    $response = $this->actingAs($user)
        ->get(route('federation.certification-attributed.wizard.create'));

    $response->assertForbidden();
});

test('main federation canIssueCertifications returns true', function () {
    expect($this->mainFederation->canIssueCertifications())->toBeTrue();
});

test('special federation canIssueCertifications returns true', function () {
    expect($this->specialFederation->canIssueCertifications())->toBeTrue();
});

test('local federation canIssueCertifications returns false', function () {
    expect($this->localFederation->canIssueCertifications())->toBeFalse();
});

test('federation with can_issue_certifications true returns true', function () {
    $federation = Federation::factory()->create([
        'can_issue_certifications' => true,
    ]);

    expect($federation->canIssueCertifications())->toBeTrue();
});

test('federation with can_issue_certifications false returns false', function () {
    $federation = Federation::factory()->create([
        'can_issue_certifications' => false,
    ]);

    expect($federation->canIssueCertifications())->toBeFalse();
});

test('all federations can view certification details', function () {
    $user = User::factory()->create(['group_id' => $this->group->id]);
    $user->federations()->attach($this->localFederation->id);

    // Create a certification to view
    $certification = \Domain\Certifications\Models\CertificationAttributed::factory()->create([
        'federation_id' => $this->mainFederation->id,
        'individual_id' => $this->individual->id,
    ]);

    $response = $this->actingAs($user)
        ->get(route('federation.certification-attributed.show', $certification->id));

    $response->assertSuccessful();
});
