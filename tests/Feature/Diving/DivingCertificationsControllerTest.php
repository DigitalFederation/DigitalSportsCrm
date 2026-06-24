<?php

use App\Models\User;
use Domain\Diving\Models\DivingProfessionalCertification;
use Domain\Diving\States\ActiveDivingCertificationState;
use Domain\Diving\States\PendingValidationDivingCertificationState;
use Domain\Individuals\Models\Individual;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\artisan;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed user groups first
    artisan('db:seed --class=UserGroupSeeder');

    // Create group and user with correct group_id
    $individualGroup = \App\Models\Group::where('code', 'INDIVIDUAL')->first();
    $this->user = User::factory()->create([
        'group_id' => $individualGroup->id,
    ]);

    $this->individual = Individual::factory()->create([
        'user_id' => $this->user->id,
    ]);

    $this->actingAs($this->user);
});

describe('DivingCertificationsController', function () {
    test('index shows individual diving certifications', function () {
        // Create certifications for the individual
        $certification1 = DivingProfessionalCertification::factory()->create([
            'individual_id' => $this->individual->id,
            'certification_name' => 'Open Water Instructor',
            'certification_system' => 'SSI',
        ]);

        $certification2 = DivingProfessionalCertification::factory()->create([
            'individual_id' => $this->individual->id,
            'certification_name' => 'Advanced Open Water',
            'certification_system' => 'PADI',
        ]);

        // Create certification for different individual (should not appear)
        $otherIndividual = Individual::factory()->create();
        DivingProfessionalCertification::factory()->create([
            'individual_id' => $otherIndividual->id,
        ]);

        $response = $this->get(route('individual.diving_certifications.index'));

        $response->assertOk()
            ->assertViewIs('web.individual.diving_certifications.index')
            ->assertViewHas('certifications')
            ->assertSee('Open Water Instructor')
            ->assertSee('Advanced Open Water')
            ->assertSee('SSI')
            ->assertSee('PADI');

        // Verify only individual's certifications are shown
        $certifications = $response->viewData('certifications');
        expect($certifications)->toHaveCount(2);
    });

    test('show displays certification details', function () {
        $certification = DivingProfessionalCertification::factory()->create([
            'individual_id' => $this->individual->id,
            'certification_name' => 'Rescue Diver Instructor',
            'certification_system' => 'SSI',
            'certification_level' => 'Instructor',
            'certification_number' => 'SSI-123456',
            'national_equivalency' => 'N2',
            'status_class' => ActiveDivingCertificationState::class,
        ]);

        $response = $this->get(route('individual.diving_certifications.show', $certification));

        $response->assertOk()
            ->assertViewIs('web.individual.diving_certifications.show')
            ->assertViewHas('certification')
            ->assertSee('Rescue Diver Instructor')
            ->assertSee('SSI')
            ->assertSee('Instructor')
            ->assertSee('SSI-123456')
            ->assertSee('N2');
    });

    test('user can only view their own certifications', function () {
        $otherIndividual = Individual::factory()->create();
        $otherCertification = DivingProfessionalCertification::factory()->create([
            'individual_id' => $otherIndividual->id,
        ]);

        $response = $this->get(route('individual.diving_certifications.show', $otherCertification));

        $response->assertForbidden();
    });

    test('store creates new certification with document upload', function () {
        Storage::fake('public');

        $certificateFile = UploadedFile::fake()->image('certificate.jpg', 100, 100);

        $certificationData = [
            'certification_name' => 'Divemaster',
            'certification_system' => 'PADI',
            'certification_number' => 'PADI-789012',
            'national_certification_level' => 'instructor_level_1',
            'issue_date' => '2023-01-15',
            'expiration_date' => '2025-01-15',
            'certificate_document' => $certificateFile,
        ];

        $response = $this->post(route('individual.diving_certifications.store'), $certificationData);

        $response->assertRedirect(route('individual.diving_certifications.index'))
            ->assertSessionHas('success');

        // Check certification was created
        $this->assertDatabaseHas('diving_professional_certifications', [
            'individual_id' => $this->individual->id,
            'certification_name' => 'Divemaster',
            'certification_system' => 'PADI',
            'certification_level' => 'instructor_level_1',
            'certification_number' => 'PADI-789012',
            'national_equivalency' => 'instructor_level_1',
            'status_class' => PendingValidationDivingCertificationState::class,
        ]);

        // Check file was uploaded
        $certification = DivingProfessionalCertification::where('individual_id', $this->individual->id)
            ->where('certification_number', 'PADI-789012')
            ->first();

        expect($certification->getFirstMedia('certificate_documents'))->not->toBeNull();
    });

    test('store validates required fields', function () {
        $response = $this->post(route('individual.diving_certifications.store'), []);

        $response->assertSessionHasErrors([
            'certification_name',
            'certification_system',
            'national_certification_level',
            'certification_number',
            'issue_date',
            'certificate_document',
        ]);
    });

    test('store validates certification system', function () {
        $certificationData = [
            'certification_name' => 'Test Certification',
            'certification_system' => 'INVALID_SYSTEM',
            'national_certification_level' => 'instructor_level_1',
            'certification_number' => 'TEST-123',
            'issue_date' => '2023-01-15',
            'certificate_document' => UploadedFile::fake()->create('cert.pdf'),
        ];

        $response = $this->post(route('individual.diving_certifications.store'), $certificationData);

        $response->assertSessionHasErrors(['certification_system']);
    });

    test('store validates file type', function () {
        $invalidFile = UploadedFile::fake()->create('document.txt', 100, 'text/plain');

        $certificationData = [
            'certification_name' => 'Test Certification',
            'certification_system' => 'SSI',
            'certification_level' => 'Professional',
            'certification_number' => 'TEST-123',
            'issue_date' => '2023-01-15',
            'certificate_document' => $invalidFile,
        ];

        $response = $this->post(route('individual.diving_certifications.store'), $certificationData);

        $response->assertSessionHasErrors(['certificate_document']);
    });

    test('store validates issue date is not in future', function () {
        $certificationData = [
            'certification_name' => 'Test Certification',
            'certification_system' => 'SSI',
            'certification_level' => 'Professional',
            'certification_number' => 'TEST-123',
            'issue_date' => now()->addDay()->format('Y-m-d'),
            'certificate_document' => UploadedFile::fake()->create('cert.pdf'),
        ];

        $response = $this->post(route('individual.diving_certifications.store'), $certificationData);

        $response->assertSessionHasErrors(['issue_date']);
    });

    test('store validates expiration date is after issue date', function () {
        $issueDate = '2023-06-01';
        $expirationDate = '2023-01-01'; // Before issue date

        $certificationData = [
            'certification_name' => 'Test Certification',
            'certification_system' => 'SSI',
            'national_certification_level' => 'instructor_level_1',
            'certification_number' => 'TEST-123',
            'issue_date' => $issueDate,
            'expiration_date' => $expirationDate,
            'certificate_document' => UploadedFile::fake()->create('cert.pdf'),
        ];

        $response = $this->post(route('individual.diving_certifications.store'), $certificationData);

        $response->assertSessionHasErrors(['expiration_date']);
    });

});
