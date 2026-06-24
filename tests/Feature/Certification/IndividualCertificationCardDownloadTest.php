<?php

use App\Models\Group;
use App\Models\User;
use Domain\Certifications\Models\Certification;
use Domain\Certifications\Models\CertificationAttributed;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\artisan;
use function Pest\Laravel\get;

beforeEach(function () {
    artisan('db:seed --class=UserGroupSeeder');
    artisan('db:seed --class=RoleAndPermissionSeeder');

    // Setup Storage for media handling
    Storage::fake('public');
    Storage::fake('media');
});

it('fails gracefully when missing required fields', function () {
    // Arrange
    $group = Group::factory()->create(['code' => 'INDIVIDUAL']);
    $federation = Federation::factory()->create();

    $user = User::factory()->for($group, 'group')->create();
    $individual = Individual::factory()
        ->for($user, 'user')
        ->create();

    $certification = Certification::factory()->create();

    $certificationAttributed = CertificationAttributed::factory()->create([
        'certification_id' => $certification->id,
        'federation_id' => $federation->id,
        'individual_id' => $individual->id,
        'national_code' => null, // Missing required field
        'current_term_starts_at' => null, // Missing required field
    ]);

    actingAs($user);

    // Act
    $response = get(route('individual.certification-card.download', $certificationAttributed->id));

    // Assert
    $response->assertRedirect();
    $response->assertSessionHas('error');
    expect(session('error'))->toContain('Cannot generate certification card');
});

it('handles non-existent certification attributed', function () {
    // Arrange
    $user = User::factory()->create();
    actingAs($user);

    // Act
    $response = get(route('individual.certification-card.download', 'non-existent-id'));

    // Assert
    $response->assertNotFound();
});

it('prevents unauthorized access to certification card download', function () {
    // Arrange
    $certificationAttributed = CertificationAttributed::factory()->create();

    // Act - Don't log in any user
    $response = get(route('individual.certification-card.download', $certificationAttributed->id));

    // Assert
    $response->assertRedirect(route('login'));
});

it('prevents access to another user\'s certification card', function () {
    // Arrange
    $certificationAttributed = CertificationAttributed::factory()->create();

    // Create different user
    $differentUser = User::factory()->create();
    actingAs($differentUser);

    // Act
    $response = get(route('individual.certification-card.download', $certificationAttributed->id));

    // Assert
    $response->assertForbidden();
});
