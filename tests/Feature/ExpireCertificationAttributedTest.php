<?php

use Domain\Certifications\Actions\ExpireCertificationAttributedAction;
use Domain\Certifications\Models\CertificationAttributed;
use Domain\Certifications\States\ActiveCertificationAttributedState;
use Domain\Certifications\States\CanceledCertificationAttributedState;
use Domain\Certifications\States\ExpiredCertificationAttributedState;
use Domain\Certifications\States\PendingCertificationAttributedState;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\artisan;

uses(RefreshDatabase::class);

beforeEach(function () {
    artisan('db:seed --class=UserGroupSeeder');
    artisan('db:seed --class=RoleAndPermissionSeeder');

    Federation::factory()->create(['is_default_federation' => true, 'is_local' => false]);
});

it('expires an active certification past its end date', function () {
    $certification = CertificationAttributed::factory()->create([
        'status_class' => ActiveCertificationAttributedState::class,
        'current_term_ends_at' => now()->subDay(),
    ]);

    $action = new ExpireCertificationAttributedAction;
    $action($certification);

    $certification->refresh();
    expect($certification->status_class)->toBe(ExpiredCertificationAttributedState::class);
});

it('expires an individual certification without lazy loading relationships', function () {
    $individual = Individual::factory()->create();

    $certification = CertificationAttributed::factory()->create([
        'entity_id' => null,
        'individual_id' => $individual->id,
        'status_class' => ActiveCertificationAttributedState::class,
        'current_term_ends_at' => now()->subDay(),
    ]);

    $action = new ExpireCertificationAttributedAction;
    $action($certification);

    $certification->refresh();
    expect($certification->status_class)->toBe(ExpiredCertificationAttributedState::class);
});

it('throws exception when trying to expire a non-active certification', function () {
    $certification = CertificationAttributed::factory()->create([
        'status_class' => PendingCertificationAttributedState::class,
        'current_term_ends_at' => now()->subDay(),
    ]);

    $action = new ExpireCertificationAttributedAction;
    $action($certification);
})->throws(\Exception::class, 'Certification must be in Active state to expire');

it('does not expire active certifications that have not reached their end date via command', function () {
    CertificationAttributed::factory()->create([
        'status_class' => ActiveCertificationAttributedState::class,
        'current_term_ends_at' => now()->addMonth(),
    ]);

    artisan('command:ExpireCertifications')->assertSuccessful();

    expect(CertificationAttributed::where('status_class', ActiveCertificationAttributedState::class)->count())->toBe(1);
    expect(CertificationAttributed::where('status_class', ExpiredCertificationAttributedState::class)->count())->toBe(0);
});

it('expires only active certifications past their end date via command', function () {
    // Should be expired
    CertificationAttributed::factory()->create([
        'status_class' => ActiveCertificationAttributedState::class,
        'current_term_ends_at' => now()->subDays(10),
    ]);

    // Should NOT be expired (still active, future date)
    CertificationAttributed::factory()->create([
        'status_class' => ActiveCertificationAttributedState::class,
        'current_term_ends_at' => now()->addMonth(),
    ]);

    // Should NOT be expired (already canceled)
    CertificationAttributed::factory()->create([
        'status_class' => CanceledCertificationAttributedState::class,
        'current_term_ends_at' => now()->subDays(10),
    ]);

    artisan('command:ExpireCertifications')->assertSuccessful();

    expect(CertificationAttributed::where('status_class', ExpiredCertificationAttributedState::class)->count())->toBe(1);
    expect(CertificationAttributed::where('status_class', ActiveCertificationAttributedState::class)->count())->toBe(1);
    expect(CertificationAttributed::where('status_class', CanceledCertificationAttributedState::class)->count())->toBe(1);
});

it('expires individual certifications via command without lazy loading relationships', function () {
    $individual = Individual::factory()->create();

    CertificationAttributed::factory()->create([
        'entity_id' => null,
        'individual_id' => $individual->id,
        'status_class' => ActiveCertificationAttributedState::class,
        'current_term_ends_at' => now()->subDays(10),
    ]);

    artisan('command:ExpireCertifications')->assertSuccessful();

    expect(CertificationAttributed::where('status_class', ExpiredCertificationAttributedState::class)->count())->toBe(1);
});

it('logs activity when certification is expired', function () {
    $certification = CertificationAttributed::factory()->create([
        'status_class' => ActiveCertificationAttributedState::class,
        'current_term_ends_at' => now()->subDay(),
    ]);

    $action = new ExpireCertificationAttributedAction;
    $action($certification);

    $this->assertDatabaseHas('activity_log', [
        'subject_type' => CertificationAttributed::class,
        'subject_id' => $certification->id,
        'event' => 'expired',
    ]);
});
