<?php

use App\Models\User;
use Domain\Diving\Models\DivingProfessionalCertification;
use Domain\Diving\States\ActiveDivingCertificationState;
use Domain\Diving\States\PendingValidationDivingCertificationState;
use Domain\Individuals\Models\Individual;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    // Create role and permissions if they don't exist
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'association-admin']);
    Permission::firstOrCreate(['name' => 'access diving certifications attributed']);
    Permission::firstOrCreate(['name' => 'delete diving certifications']);

    // Create ADMIN group
    $adminGroup = \App\Models\Group::firstOrCreate(
        ['code' => 'ADMIN'],
        ['name' => 'Admin', 'code' => 'ADMIN']
    );

    $this->user = User::factory()->create(['group_id' => $adminGroup->id]);
    $this->user->assignRole('admin');
    $this->user->givePermissionTo(['access diving certifications attributed', 'delete diving certifications']);

    $this->individual = Individual::factory()->create();
    $this->certification = DivingProfessionalCertification::factory()->create([
        'individual_id' => $this->individual->id,
        'status_class' => PendingValidationDivingCertificationState::class,
    ]);
});

test('authenticated user can delete diving professional certification', function () {
    $this->actingAs($this->user);

    $response = $this->delete(route('admin.diving_professional_certifications.destroy', $this->certification));

    $response->assertRedirect(route('admin.diving_professional_certifications.index'));
    $response->assertSessionHas('success', __('diving.certification_deleted_successfully'));

    // Verify certification is deleted
    $this->assertModelMissing($this->certification);
});

test('delete request does not require a reason', function () {
    $this->actingAs($this->user);

    // Delete without reason should succeed now
    $response = $this->delete(route('admin.diving_professional_certifications.destroy', $this->certification));

    $response->assertRedirect(route('admin.diving_professional_certifications.index'));
    $response->assertSessionHas('success');

    // Verify certification is deleted
    $this->assertModelMissing($this->certification);
});

test('delete action logs activity with proper details', function () {
    $this->actingAs($this->user);

    $this->delete(route('admin.diving_professional_certifications.destroy', $this->certification));

    // Verify activity log was created
    $activity = Activity::latest()->first();

    expect($activity)->not->toBeNull()
        ->and($activity->causer_id)->toBe($this->user->id)
        ->and($activity->log_name)->toBe('DivingCertificationDeleted')
        ->and($activity->description)->toContain('Deleted')
        ->and($activity->description)->toContain($this->certification->certification_system)
        ->and($activity->description)->toContain($this->certification->certification_name)
        ->and($activity->description)->toContain($this->individual->full_name)
        ->and($activity->properties['certification_name'])->toBe($this->certification->certification_name)
        ->and($activity->properties['individual_name'])->toBe($this->individual->full_name)
        ->and($activity->properties['deleted_by'])->toBe($this->user->name);
});

test('can delete active certification', function () {
    $this->actingAs($this->user);

    $this->certification->update(['status_class' => ActiveDivingCertificationState::class]);

    $response = $this->delete(route('admin.diving_professional_certifications.destroy', $this->certification), [
        'reason' => 'Administrative cleanup',
    ]);

    $response->assertRedirect(route('admin.diving_professional_certifications.index'));
    $this->assertModelMissing($this->certification);
});

test('unauthenticated user cannot delete certification', function () {
    $response = $this->delete(route('admin.diving_professional_certifications.destroy', $this->certification), [
        'reason' => 'Test reason',
    ]);

    $response->assertRedirect('/login');
    $this->assertModelExists($this->certification);
});

// Test removed: reason field is no longer required for deletion
