<?php

use Domain\Certifications\Models\Certification;
use Domain\Certifications\Models\CertificationAttributed;
use Domain\Certifications\States\ActiveCertificationAttributedState;
use Domain\Certifications\States\PendingCertificationAttributedState;
use Domain\Diagnostics\Actions\DiagnoseRefereeEligibilityAction;
use Domain\Diagnostics\Data\DiagnosticResult;
use Domain\EvtEvents\Models\Competition;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\Organizer;
use Domain\EvtEvents\Models\RefereeEnrollment;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Individuals\States\ActiveIndividualFederationState;
use Domain\Individuals\States\PendingIndividualFederationState;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->federation = Federation::factory()->create();
    $this->event = Event::factory()->create();

    // Create organizer linking event to federation
    Organizer::create([
        'event_id' => $this->event->id,
        'organizable_type' => Federation::class,
        'organizable_id' => $this->federation->id,
    ]);

    $this->competition = Competition::factory()->create(['event_id' => $this->event->id]);
    $this->individual = Individual::factory()->create();
    $this->refereeRole = ProfessionalRole::factory()->create(['role' => 'TECHNICAL_OFFICIAL', 'name' => 'Technical Official']);
    $this->refereeCertification = Certification::factory()->create([
        'professional_role_id' => $this->refereeRole->id,
        'name' => 'Referee Certification Level 1',
    ]);

    $this->action = app(DiagnoseRefereeEligibilityAction::class);
});

test('returns DiagnosticResult instance', function () {
    $result = $this->action->execute($this->individual, $this->event);

    expect($result)->toBeInstanceOf(DiagnosticResult::class);
});

test('fails when individual has no federation membership', function () {
    $result = $this->action->execute($this->individual, $this->event);

    expect($result->isEligible)->toBeFalse();
    expect($result->failedChecks)->toContain('federation_membership');
});

test('fails when federation membership is pending', function () {
    $this->individual->individualFederations()->create([
        'federation_id' => $this->federation->id,
        'status_class' => PendingIndividualFederationState::class,
    ]);

    $result = $this->action->execute($this->individual, $this->event);

    expect($result->isEligible)->toBeFalse();
    expect($result->failedChecks)->toContain('federation_membership');
});

test('passes federation check with active membership', function () {
    $this->individual->individualFederations()->create([
        'federation_id' => $this->federation->id,
        'status_class' => ActiveIndividualFederationState::class,
    ]);

    $result = $this->action->execute($this->individual, $this->event);

    expect($result->failedChecks)->not->toContain('federation_membership');
    $federationCheck = collect($result->checks)->firstWhere('key', 'federation_membership');
    expect($federationCheck->passed)->toBeTrue();
});

test('fails when individual has no referee role', function () {
    $this->individual->individualFederations()->create([
        'federation_id' => $this->federation->id,
        'status_class' => ActiveIndividualFederationState::class,
    ]);

    $result = $this->action->execute($this->individual, $this->event);

    expect($result->isEligible)->toBeFalse();
    expect($result->failedChecks)->toContain('referee_role');
});

test('fails with suggestion when certification is pending', function () {
    $this->individual->individualFederations()->create([
        'federation_id' => $this->federation->id,
        'status_class' => ActiveIndividualFederationState::class,
    ]);

    CertificationAttributed::factory()->create([
        'individual_id' => $this->individual->id,
        'certification_id' => $this->refereeCertification->id,
        'status_class' => PendingCertificationAttributedState::class,
    ]);

    $result = $this->action->execute($this->individual, $this->event);

    expect($result->isEligible)->toBeFalse();
    $roleCheck = collect($result->checks)->firstWhere('key', 'referee_role');
    expect($roleCheck->passed)->toBeFalse();
    expect($roleCheck->suggestion)->not->toBeNull();
});

test('passes when individual has referee role and active certification', function () {
    $this->individual->individualFederations()->create([
        'federation_id' => $this->federation->id,
        'status_class' => ActiveIndividualFederationState::class,
    ]);

    $this->individual->professionalRoles()->attach($this->refereeRole->id);

    CertificationAttributed::factory()->create([
        'individual_id' => $this->individual->id,
        'certification_id' => $this->refereeCertification->id,
        'status_class' => ActiveCertificationAttributedState::class,
    ]);

    $result = $this->action->execute($this->individual, $this->event);

    expect($result->isEligible)->toBeTrue();
    expect($result->failedChecks)->toBeEmpty();
});

test('fails when no referee certification exists', function () {
    $this->individual->individualFederations()->create([
        'federation_id' => $this->federation->id,
        'status_class' => ActiveIndividualFederationState::class,
    ]);

    $result = $this->action->execute($this->individual, $this->event);

    expect($result->failedChecks)->toContain('referee_cert_exists');
});

test('fails when referee certification is not active', function () {
    $this->individual->individualFederations()->create([
        'federation_id' => $this->federation->id,
        'status_class' => ActiveIndividualFederationState::class,
    ]);

    CertificationAttributed::factory()->create([
        'individual_id' => $this->individual->id,
        'certification_id' => $this->refereeCertification->id,
        'status_class' => PendingCertificationAttributedState::class,
    ]);

    $result = $this->action->execute($this->individual, $this->event);

    expect($result->failedChecks)->toContain('referee_cert_active');
    $certCheck = collect($result->checks)->firstWhere('key', 'referee_cert_active');
    expect($certCheck->passed)->toBeFalse();
});

test('fails when already enrolled in event', function () {
    $this->individual->individualFederations()->create([
        'federation_id' => $this->federation->id,
        'status_class' => ActiveIndividualFederationState::class,
    ]);

    $this->individual->professionalRoles()->attach($this->refereeRole->id);

    CertificationAttributed::factory()->create([
        'individual_id' => $this->individual->id,
        'certification_id' => $this->refereeCertification->id,
        'status_class' => ActiveCertificationAttributedState::class,
    ]);

    RefereeEnrollment::factory()->create([
        'individual_id' => $this->individual->id,
        'event_id' => $this->event->id,
        'federation_id' => $this->federation->id,
    ]);

    $result = $this->action->execute($this->individual, $this->event);

    expect($result->isEligible)->toBeFalse();
    expect($result->failedChecks)->toContain('not_enrolled');
});

test('checks required certifications when competition has requirements', function () {
    $requiredCert = Certification::factory()->create([
        'professional_role_id' => $this->refereeRole->id,
        'name' => 'Required Referee Cert',
    ]);

    $this->competition->update(['required_referee_certifications' => [$requiredCert->id]]);

    $this->individual->individualFederations()->create([
        'federation_id' => $this->federation->id,
        'status_class' => ActiveIndividualFederationState::class,
    ]);

    $this->individual->professionalRoles()->attach($this->refereeRole->id);

    // Has a referee cert but not the required one
    CertificationAttributed::factory()->create([
        'individual_id' => $this->individual->id,
        'certification_id' => $this->refereeCertification->id,
        'status_class' => ActiveCertificationAttributedState::class,
    ]);

    $result = $this->action->execute($this->individual, $this->event, $this->competition);

    expect($result->failedChecks)->toContain('required_certifications');
});

test('passes required certifications check when individual has them', function () {
    $this->competition->update(['required_referee_certifications' => [$this->refereeCertification->id]]);

    $this->individual->individualFederations()->create([
        'federation_id' => $this->federation->id,
        'status_class' => ActiveIndividualFederationState::class,
    ]);

    $this->individual->professionalRoles()->attach($this->refereeRole->id);

    CertificationAttributed::factory()->create([
        'individual_id' => $this->individual->id,
        'certification_id' => $this->refereeCertification->id,
        'status_class' => ActiveCertificationAttributedState::class,
    ]);

    $result = $this->action->execute($this->individual, $this->event, $this->competition);

    expect($result->failedChecks)->not->toContain('required_certifications');
});

test('provides actionable suggestions for each failure', function () {
    $result = $this->action->execute($this->individual, $this->event);

    expect($result->suggestions)->not->toBeEmpty();
    // Each failed check should have a suggestion
    foreach ($result->getFailedChecks() as $check) {
        if ($check->suggestion) {
            expect($result->suggestions)->toContain($check->suggestion);
        }
    }
});

test('toArray returns correct structure', function () {
    $result = $this->action->execute($this->individual, $this->event);
    $array = $result->toArray();

    expect($array)->toHaveKeys(['isEligible', 'checks', 'failedChecks', 'suggestions', 'debugData']);
    expect($array['checks'])->toBeArray();
    foreach ($array['checks'] as $check) {
        expect($check)->toHaveKeys(['key', 'label', 'passed', 'message']);
    }
});
