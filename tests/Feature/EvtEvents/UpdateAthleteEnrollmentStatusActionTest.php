<?php

use App\Enums\EvtAthleteEnrollmentStatusEnum;
use App\Models\Group;
use App\Models\User;
use Domain\EvtEvents\Actions\UpdateAthleteEnrollmentStatusAction;
use Domain\EvtEvents\Models\AthleteEnrollment;
use Domain\Federations\Models\Federation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;

use function Pest\Laravel\artisan;

uses(RefreshDatabase::class);

beforeEach(function () {
    artisan('db:seed --class=RoleAndPermissionSeeder');

    $this->group = Group::factory()->create(['code' => 'FEDERATION']);
    $this->user = User::factory()->create(['group_id' => $this->group->id]);
    $this->federation = Federation::factory()->create();
    $this->user->federations()->attach($this->federation->id);
});

it('updates athlete enrollment status and logs activity', function () {
    // Arrange
    $athleteEnrollment = AthleteEnrollment::factory()->create([
        'status_class' => EvtAthleteEnrollmentStatusEnum::REGISTERED,
        'federation_id' => $this->federation->id,
    ]);

    $newStatus = EvtAthleteEnrollmentStatusEnum::PAID->value;

    // Act
    $action = new UpdateAthleteEnrollmentStatusAction;
    $action->execute($athleteEnrollment, $newStatus, $this->user);

    // Assert
    $athleteEnrollment->refresh();

    // Check if status was updated
    expect($athleteEnrollment->status_class)->toEqual(EvtAthleteEnrollmentStatusEnum::PAID);

    // Check if activity was logged
    $activity = Activity::where('subject_type', AthleteEnrollment::class)
        ->where('subject_id', $athleteEnrollment->id)
        ->where('description', 'like', '%status%')
        ->latest()
        ->first();
    expect($activity)->not->toBeNull()
        ->and($activity->description)
        ->toContain('Registado', 'Pago');
});

it('throws error when invalid status is provided', function () {
    // Arrange
    $athleteEnrollment = AthleteEnrollment::factory()->create([
        'status_class' => EvtAthleteEnrollmentStatusEnum::REGISTERED,
        'federation_id' => $this->federation->id,
    ]);

    // Act & Assert
    expect(fn () => (new UpdateAthleteEnrollmentStatusAction)
        ->execute($athleteEnrollment, 'INVALID_STATUS', $this->user))
        ->toThrow(\ValueError::class);

    // Verify the original status
    $athleteEnrollment->refresh();
    expect($athleteEnrollment->status_class)->toEqual(EvtAthleteEnrollmentStatusEnum::REGISTERED);
});
