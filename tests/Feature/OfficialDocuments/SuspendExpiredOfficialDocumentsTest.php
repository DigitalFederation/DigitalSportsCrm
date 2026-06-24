<?php

use App\Models\Group;
use App\Models\User;
use Domain\Individuals\Models\Individual;
use Domain\OfficialDocuments\Actions\SuspendExpiredOfficialDocumentsAction;
use Domain\OfficialDocuments\Models\OfficialDocument;
use Domain\OfficialDocuments\States\ActiveOfficialDocumentState;
use Domain\OfficialDocuments\States\ExpiredOfficialDocumentState;
use Domain\OfficialDocuments\States\PendingOfficialDocumentState;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->action = app(SuspendExpiredOfficialDocumentsAction::class);
    $group = Group::factory()->create(['code' => 'INDIVIDUAL']);
    $this->user1 = User::factory()->create(['group_id' => $group->id]);
    $this->individual1 = Individual::factory()->create(['user_id' => $this->user1->id]);

});

it('suspends expired official documents', function () {
    // Arrange
    $expiredDocument = OfficialDocument::factory()->create([
        'individual_id' => $this->individual1,
        'expiry_date' => now()->subDay(),
        'status_class' => ActiveOfficialDocumentState::class,
    ]);

    $activeDocument = OfficialDocument::factory()->create([
        'individual_id' => $this->individual1,
        'expiry_date' => now()->addDay(),
        'status_class' => ActiveOfficialDocumentState::class,
    ]);

    // Act
    $suspendedCount = $this->action->execute();

    // Assert
    expect($suspendedCount)->toBe(1);
    $expiredDocument->refresh();
    $activeDocument->refresh();

    expect($expiredDocument->status_class)->toBe(ExpiredOfficialDocumentState::class);
    expect($activeDocument->status_class)->toBe(ActiveOfficialDocumentState::class);
});

it('does not suspend already expired documents', function () {
    // Arrange
    $alreadyExpiredDocument = OfficialDocument::factory()->create([
        'individual_id' => $this->individual1,
        'expiry_date' => now()->subDays(2),
        'status_class' => ExpiredOfficialDocumentState::class,
    ]);

    // Act
    $suspendedCount = $this->action->execute();

    // Assert
    expect($suspendedCount)->toBe(0);
    $alreadyExpiredDocument->refresh();
    expect($alreadyExpiredDocument->status_class)->toBe(ExpiredOfficialDocumentState::class);
});

it('suspends documents that expire today', function () {
    // Arrange
    $expiringToday = OfficialDocument::factory()->create([
        'individual_id' => $this->individual1,
        'expiry_date' => now()->toDateString(),
        'status_class' => ActiveOfficialDocumentState::class,
    ]);

    // Act
    $suspendedCount = $this->action->execute();

    // Assert
    expect($suspendedCount)->toBe(1);
    $expiringToday->refresh();
    expect($expiringToday->status_class)->toBe(ExpiredOfficialDocumentState::class);
});

it('suspends multiple expired documents', function () {
    // Arrange
    OfficialDocument::factory()->count(3)->create([
        'individual_id' => $this->individual1,
        'expiry_date' => now()->subDay(),
        'status_class' => ActiveOfficialDocumentState::class,
    ]);

    OfficialDocument::factory()->create([
        'individual_id' => $this->individual1,
        'expiry_date' => now()->addDay(),
        'status_class' => ActiveOfficialDocumentState::class,
    ]);

    // Act
    $suspendedCount = $this->action->execute();

    // Assert
    expect($suspendedCount)->toBe(3);
    expect(OfficialDocument::where('status_class', ExpiredOfficialDocumentState::class)->count())->toBe(3);
    expect(OfficialDocument::where('status_class', ActiveOfficialDocumentState::class)->count())->toBe(1);
});

it('returns expired state in real-time when expiry_date is in the past', function () {
    $document = OfficialDocument::factory()->create([
        'individual_id' => $this->individual1,
        'expiry_date' => now()->subDay(),
        'status_class' => ActiveOfficialDocumentState::class,
    ]);

    expect($document->state)->toBeInstanceOf(ExpiredOfficialDocumentState::class);
    expect($document->stateName())->toBe(__('states.expired'));
});

it('returns actual state when expiry_date is in the future', function () {
    $document = OfficialDocument::factory()->create([
        'individual_id' => $this->individual1,
        'expiry_date' => now()->addDay(),
        'status_class' => ActiveOfficialDocumentState::class,
    ]);

    expect($document->state)->toBeInstanceOf(ActiveOfficialDocumentState::class);
});

it('returns actual state when expiry_date is null', function () {
    $document = OfficialDocument::factory()->create([
        'individual_id' => $this->individual1,
        'expiry_date' => null,
        'status_class' => PendingOfficialDocumentState::class,
    ]);

    expect($document->state)->toBeInstanceOf(PendingOfficialDocumentState::class);
});

it('filters expired status including date-expired documents not yet processed by cron', function () {
    // Document expired by date but status_class still Active (cron hasn't run)
    $dateExpired = OfficialDocument::factory()->create([
        'individual_id' => $this->individual1,
        'expiry_date' => now()->subDay(),
        'status_class' => ActiveOfficialDocumentState::class,
    ]);

    // Document already marked expired by cron
    $cronExpired = OfficialDocument::factory()->create([
        'individual_id' => $this->individual1,
        'expiry_date' => now()->subDays(5),
        'status_class' => ExpiredOfficialDocumentState::class,
    ]);

    // Active document with future expiry
    $active = OfficialDocument::factory()->create([
        'individual_id' => $this->individual1,
        'expiry_date' => now()->addDay(),
        'status_class' => ActiveOfficialDocumentState::class,
    ]);

    $expiredResults = OfficialDocument::query()->filterStatus('expired')->get();
    $activeResults = OfficialDocument::query()->filterStatus('active')->get();

    expect($expiredResults)->toHaveCount(2)
        ->and($expiredResults->pluck('id')->toArray())->toContain($dateExpired->id, $cronExpired->id);

    expect($activeResults)->toHaveCount(1)
        ->and($activeResults->first()->id)->toBe($active->id);
});

it('suspends expired documents with different initial states', function () {
    // Arrange
    $expiredActive = OfficialDocument::factory()->create([
        'individual_id' => $this->individual1,
        'expiry_date' => now()->subDay(),
        'status_class' => ActiveOfficialDocumentState::class,
    ]);

    $expiredPending = OfficialDocument::factory()->create([
        'individual_id' => $this->individual1,
        'expiry_date' => now()->subDay(),
        'status_class' => PendingOfficialDocumentState::class,
    ]);

    // Act
    $suspendedCount = $this->action->execute();

    // Assert
    expect($suspendedCount)->toBe(2);
    $expiredActive->refresh();
    $expiredPending->refresh();
    expect($expiredActive->status_class)->toBe(ExpiredOfficialDocumentState::class);
    expect($expiredPending->status_class)->toBe(ExpiredOfficialDocumentState::class);
});
