<?php

use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\CanceledLicenseAttributedState;
use Domain\Licenses\States\PendingTechnicalDirectorApprovalLicenseAttributedState;
use Domain\Licenses\States\PendingValidationLicenseAttributedState;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->license = LicenseAttributed::factory()->create([
        'status_class' => PendingTechnicalDirectorApprovalLicenseAttributedState::class,
    ]);
    $this->state = new PendingTechnicalDirectorApprovalLicenseAttributedState($this->license);
});

test('state has correct name', function () {
    expect($this->state->name())->toBe(__('licenses.state_pending_technical_director_approval'));
});

test('state has correct color', function () {
    expect($this->state->color())->toBe('#F59E0B');
});

test('state is not active', function () {
    expect($this->state->isActive())->toBeFalse();
});

test('state can only transition to pending validation or canceled', function () {
    $transitions = $this->state->canTransitionTo();

    expect($transitions)
        ->toHaveCount(2)
        ->toContain(PendingValidationLicenseAttributedState::class)
        ->toContain(CanceledLicenseAttributedState::class);
});
