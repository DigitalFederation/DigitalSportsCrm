<?php

use App\Events\CertificationAttributedCreatedEvent;
use App\Models\Committee;
use Domain\Certifications\Actions\ApproveCertificationByDirectorAction;
use Domain\Certifications\Models\Certification;
use Domain\Certifications\Models\CertificationAttributed;
use Domain\Certifications\States\ActiveCertificationAttributedState;
use Domain\Certifications\States\DirectorApprovalCertificationAttributedState;
use Domain\Certifications\States\DirectorApprovedCertificationAttributedState;
use Domain\Certifications\States\PendingCertificationAttributedState;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create main federation
    $this->mainFederation = Federation::factory()->create(['is_default_federation' => true]);

    // Create diving committee
    $this->divingCommittee = Committee::factory()->create([
        'code' => 'DIVING',
        'is_international' => true,
    ]);

    // Create a certification
    $this->certification = Certification::factory()->create([
        'name' => 'Test Certification',
        'committee_id' => $this->divingCommittee->id,
        'requires_admin_validation' => true,
    ]);
});

describe('ApproveCertificationByDirectorAction', function () {
    test('transitions certification from DirectorApproval to DirectorApproved state', function () {
        $individual = Individual::factory()->create();

        $certificationAttributed = CertificationAttributed::factory()->create([
            'individual_id' => $individual->id,
            'certification_id' => $this->certification->id,
            'federation_id' => $this->mainFederation->id,
            'status_class' => DirectorApprovalCertificationAttributedState::class,
            'price_paid' => 50.00,
        ]);

        Event::fake();

        $action = app(ApproveCertificationByDirectorAction::class);
        $result = $action($certificationAttributed);

        expect($result->status_class)->toBe(DirectorApprovedCertificationAttributedState::class);
    });

    test('fires CertificationAttributedCreatedEvent when price is greater than zero', function () {
        $individual = Individual::factory()->create();

        $certificationAttributed = CertificationAttributed::factory()->create([
            'individual_id' => $individual->id,
            'certification_id' => $this->certification->id,
            'federation_id' => $this->mainFederation->id,
            'status_class' => DirectorApprovalCertificationAttributedState::class,
            'price_paid' => 50.00,
        ]);

        Event::fake();

        $action = app(ApproveCertificationByDirectorAction::class);
        $action($certificationAttributed);

        Event::assertDispatched(CertificationAttributedCreatedEvent::class, function ($event) use ($certificationAttributed) {
            return $event->certificationAttributed->id === $certificationAttributed->id
                && $event->price === 50.00;
        });
    });

    test('does not fire event when price is zero', function () {
        $individual = Individual::factory()->create();

        $certificationAttributed = CertificationAttributed::factory()->create([
            'individual_id' => $individual->id,
            'certification_id' => $this->certification->id,
            'federation_id' => $this->mainFederation->id,
            'status_class' => DirectorApprovalCertificationAttributedState::class,
            'price_paid' => 0,
        ]);

        Event::fake();

        $action = app(ApproveCertificationByDirectorAction::class);
        $action($certificationAttributed);

        Event::assertNotDispatched(CertificationAttributedCreatedEvent::class);
    });

    test('does not fire event when price_paid is null', function () {
        $individual = Individual::factory()->create();

        $certificationAttributed = CertificationAttributed::factory()->create([
            'individual_id' => $individual->id,
            'certification_id' => $this->certification->id,
            'federation_id' => $this->mainFederation->id,
            'status_class' => DirectorApprovalCertificationAttributedState::class,
            'price_paid' => null,
        ]);

        Event::fake();

        $action = app(ApproveCertificationByDirectorAction::class);
        $action($certificationAttributed);

        Event::assertNotDispatched(CertificationAttributedCreatedEvent::class);
    });

    test('throws exception when certification is not in DirectorApproval state', function () {
        $individual = Individual::factory()->create();

        $certificationAttributed = CertificationAttributed::factory()->create([
            'individual_id' => $individual->id,
            'certification_id' => $this->certification->id,
            'federation_id' => $this->mainFederation->id,
            'status_class' => PendingCertificationAttributedState::class,
        ]);

        $action = app(ApproveCertificationByDirectorAction::class);

        expect(fn () => $action($certificationAttributed))
            ->toThrow(Exception::class, 'Certification is not in a state that can be approved by director');
    });

    test('throws exception when certification is already active', function () {
        $individual = Individual::factory()->create();

        $certificationAttributed = CertificationAttributed::factory()->create([
            'individual_id' => $individual->id,
            'certification_id' => $this->certification->id,
            'federation_id' => $this->mainFederation->id,
            'status_class' => ActiveCertificationAttributedState::class,
        ]);

        $action = app(ApproveCertificationByDirectorAction::class);

        expect(fn () => $action($certificationAttributed))
            ->toThrow(Exception::class, 'Certification is not in a state that can be approved by director');
    });

    test('persists the state change to the database', function () {
        $individual = Individual::factory()->create();

        $certificationAttributed = CertificationAttributed::factory()->create([
            'individual_id' => $individual->id,
            'certification_id' => $this->certification->id,
            'federation_id' => $this->mainFederation->id,
            'status_class' => DirectorApprovalCertificationAttributedState::class,
            'price_paid' => 25.00,
        ]);

        Event::fake();

        $action = app(ApproveCertificationByDirectorAction::class);
        $action($certificationAttributed);

        $this->assertDatabaseHas('certification_attributed', [
            'id' => $certificationAttributed->id,
            'status_class' => DirectorApprovedCertificationAttributedState::class,
        ]);
    });
});
