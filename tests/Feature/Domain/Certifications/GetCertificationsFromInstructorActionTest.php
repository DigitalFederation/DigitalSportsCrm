<?php

use App\Models\Committee;
use Domain\Certifications\Actions\GetCertificationsFromInstructorAction;
use Domain\Certifications\Models\Certification;
use Domain\Certifications\Models\CertificationAttributed;
use Domain\Certifications\States\ActiveCertificationAttributedState;
use Domain\Certifications\States\PendingCertificationAttributedState;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\ProfessionalRole;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create main federation
    $this->mainFederation = Federation::factory()->create(['is_default_federation' => true]);

    // Create diving committee (international like production data)
    $this->divingCommittee = Committee::factory()->create([
        'code' => 'DIVING',
        'is_international' => true,
    ]);

    // Create diving instructor professional role
    $this->divingInstructorRole = ProfessionalRole::factory()->create([
        'code' => 'DIVINGINSTRUCTOR',
        'role' => 'INSTRUCTOR',
        'committee_id' => $this->divingCommittee->id,
    ]);

    // Create diver professional role (for student certifications)
    $this->diverRole = ProfessionalRole::factory()->create([
        'code' => 'DIVER',
        'role' => 'DIVER',
        'committee_id' => $this->divingCommittee->id,
    ]);
});

describe('GetCertificationsFromInstructorAction', function () {
    test('returns only certifications that instructor is qualified to teach', function () {
        // Create instructor certifications
        $oneStarInstructor = Certification::factory()->create([
            'name' => 'One Star Instructor',
            'committee_id' => $this->divingCommittee->id,
            'professional_role_id' => $this->divingInstructorRole->id,
        ]);

        $twoStarInstructor = Certification::factory()->create([
            'name' => 'Two Star Instructor',
            'committee_id' => $this->divingCommittee->id,
            'professional_role_id' => $this->divingInstructorRole->id,
        ]);

        // Create student certifications
        $oneStarDiver = Certification::factory()->create([
            'name' => 'One Star Diver',
            'committee_id' => $this->divingCommittee->id,
            'professional_role_id' => $this->diverRole->id,
        ]);

        $twoStarDiver = Certification::factory()->create([
            'name' => 'Two Star Diver',
            'committee_id' => $this->divingCommittee->id,
            'professional_role_id' => $this->diverRole->id,
        ]);

        // Set up parent-child relationships:
        // One Star Diver can be taught by One Star Instructor
        $oneStarDiver->parents()->attach($oneStarInstructor->id);
        // Two Star Diver can be taught by Two Star Instructor
        $twoStarDiver->parents()->attach($twoStarInstructor->id);

        // Create an instructor with ONLY One Star Instructor certification
        $instructor = Individual::factory()->create();
        CertificationAttributed::factory()->create([
            'individual_id' => $instructor->id,
            'certification_id' => $oneStarInstructor->id,
            'federation_id' => $this->mainFederation->id,
            'status_class' => ActiveCertificationAttributedState::class,
        ]);

        // Execute the action
        $action = app(GetCertificationsFromInstructorAction::class);
        $certifications = $action($instructor, $this->mainFederation->id, 'diving');

        // Should return only One Star Diver (not Two Star Diver)
        expect($certifications)->toHaveCount(1)
            ->and($certifications->first()->name)->toBe('One Star Diver');
    });

    test('returns multiple certifications when instructor has multiple instructor certifications', function () {
        // Create instructor certifications
        $oneStarInstructor = Certification::factory()->create([
            'name' => 'One Star Instructor',
            'committee_id' => $this->divingCommittee->id,
            'professional_role_id' => $this->divingInstructorRole->id,
        ]);

        $twoStarInstructor = Certification::factory()->create([
            'name' => 'Two Star Instructor',
            'committee_id' => $this->divingCommittee->id,
            'professional_role_id' => $this->divingInstructorRole->id,
        ]);

        // Create student certifications
        $oneStarDiver = Certification::factory()->create([
            'name' => 'One Star Diver',
            'committee_id' => $this->divingCommittee->id,
            'professional_role_id' => $this->diverRole->id,
        ]);

        $twoStarDiver = Certification::factory()->create([
            'name' => 'Two Star Diver',
            'committee_id' => $this->divingCommittee->id,
            'professional_role_id' => $this->diverRole->id,
        ]);

        // Set up parent-child relationships
        $oneStarDiver->parents()->attach($oneStarInstructor->id);
        $twoStarDiver->parents()->attach($twoStarInstructor->id);

        // Create an instructor with BOTH instructor certifications
        $instructor = Individual::factory()->create();
        CertificationAttributed::factory()->create([
            'individual_id' => $instructor->id,
            'certification_id' => $oneStarInstructor->id,
            'federation_id' => $this->mainFederation->id,
            'status_class' => ActiveCertificationAttributedState::class,
        ]);
        CertificationAttributed::factory()->create([
            'individual_id' => $instructor->id,
            'certification_id' => $twoStarInstructor->id,
            'federation_id' => $this->mainFederation->id,
            'status_class' => ActiveCertificationAttributedState::class,
        ]);

        // Execute the action
        $action = app(GetCertificationsFromInstructorAction::class);
        $certifications = $action($instructor, $this->mainFederation->id, 'diving');

        // Should return both One Star Diver and Two Star Diver
        expect($certifications)->toHaveCount(2);
        $names = $certifications->pluck('name')->toArray();
        expect($names)->toContain('One Star Diver')
            ->and($names)->toContain('Two Star Diver');
    });

    test('returns empty collection when instructor has no active certifications', function () {
        $instructor = Individual::factory()->create();

        $action = app(GetCertificationsFromInstructorAction::class);
        $certifications = $action($instructor, $this->mainFederation->id, 'diving');

        expect($certifications)->not->toBeNull()
            ->and($certifications)->toHaveCount(0);
    });

    test('returns null when individual is null', function () {
        $action = app(GetCertificationsFromInstructorAction::class);
        $certifications = $action(null, $this->mainFederation->id, 'diving');

        expect($certifications)->toBeNull();
    });

    test('returns empty collection for inactive instructor certifications', function () {
        // Create instructor certification
        $oneStarInstructor = Certification::factory()->create([
            'name' => 'One Star Instructor',
            'committee_id' => $this->divingCommittee->id,
            'professional_role_id' => $this->divingInstructorRole->id,
        ]);

        // Create student certification
        $oneStarDiver = Certification::factory()->create([
            'name' => 'One Star Diver',
            'committee_id' => $this->divingCommittee->id,
            'professional_role_id' => $this->diverRole->id,
        ]);

        $oneStarDiver->parents()->attach($oneStarInstructor->id);

        // Create instructor with INACTIVE certification (Pending status)
        $instructor = Individual::factory()->create();
        CertificationAttributed::factory()->create([
            'individual_id' => $instructor->id,
            'certification_id' => $oneStarInstructor->id,
            'federation_id' => $this->mainFederation->id,
            'status_class' => PendingCertificationAttributedState::class,
        ]);

        $action = app(GetCertificationsFromInstructorAction::class);
        $certifications = $action($instructor, $this->mainFederation->id, 'diving');

        // Should return empty since no active instructor certifications
        expect($certifications)->not->toBeNull()
            ->and($certifications)->toHaveCount(0);
    });

    test('returns certifications when instructor cert is from child federation', function () {
        // Create a child federation.
        $childFederation = Federation::factory()->create([
            'parent_id' => $this->mainFederation->id,
            'is_local' => false,
        ]);

        // Create instructor certification
        $oneStarInstructor = Certification::factory()->create([
            'name' => 'One Star Instructor',
            'committee_id' => $this->divingCommittee->id,
            'professional_role_id' => $this->divingInstructorRole->id,
        ]);

        // Create student certification
        $oneStarDiver = Certification::factory()->create([
            'name' => 'One Star Diver',
            'committee_id' => $this->divingCommittee->id,
            'professional_role_id' => $this->diverRole->id,
        ]);

        $oneStarDiver->parents()->attach($oneStarInstructor->id);

        // Create instructor with certification from CHILD federation
        $instructor = Individual::factory()->create();
        CertificationAttributed::withoutEvents(function () use ($instructor, $oneStarInstructor, $childFederation): void {
            CertificationAttributed::factory()->create([
                'individual_id' => $instructor->id,
                'certification_id' => $oneStarInstructor->id,
                'federation_id' => $childFederation->id, // Child federation
                'status_class' => ActiveCertificationAttributedState::class,
            ]);
        });

        $action = app(GetCertificationsFromInstructorAction::class);
        $certifications = $action($instructor, $this->mainFederation->id, 'diving');

        // Should still return One Star Diver because child federations are included
        expect($certifications)->toHaveCount(1)
            ->and($certifications->first()->name)->toBe('One Star Diver');
    });
});
