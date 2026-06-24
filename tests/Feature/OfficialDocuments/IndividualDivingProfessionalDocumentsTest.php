<?php

use App\Enums\OfficialDocumentTypeEnum;
use App\Models\Country;
use App\Models\Group;
use App\Models\User;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\OfficialDocuments\States\PendingOfficialDocumentState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create required data
    $this->country = Country::factory()->create();
    $this->group = Group::factory()->create(['code' => 'INDIVIDUAL']);

    // Ensure default federation exists
    $this->defaultFederation = Federation::factory()->create([
        'id' => 141,
        'name' => 'Example Underwater Activities Federation',
        'country_id' => $this->country->id,
        'is_default_federation' => true,
    ]);

    // Create user with individual
    $this->user = User::factory()->create([
        'group_id' => $this->group->id,
    ]);
    $this->individual = Individual::factory()->create();
    $this->user->individuals()->save($this->individual);
    $this->individual->addMedia(UploadedFile::fake()->image('photo.jpg'))
        ->toMediaCollection('profile', 'secure-media');

    // Associate individual with federation
    $this->individual->federations()->attach($this->defaultFederation->id);
});

it('shows only three document types for diving professionals', function () {
    $this->actingAs($this->user);

    $response = $this->get(route('individual.official-documents.index', 'diving-professional'));

    $response->assertStatus(200);
    $response->assertViewHas('official_document_types');

    $types = $response->viewData('official_document_types');

    expect($types)->toHaveCount(3);
    expect($types)->toContain(
        OfficialDocumentTypeEnum::DivingProfessionalMedicalStatement,
        OfficialDocumentTypeEnum::DivingProfessionalInsurance,
        OfficialDocumentTypeEnum::OtherDocument
    );
});

it('does not show federation selection fields in the upload form', function () {
    $this->actingAs($this->user);

    $response = $this->get(route('individual.official-documents.index', 'diving-professional'));

    $response->assertStatus(200);
    // Check that federation selection elements are not present
    $response->assertDontSee('National Organization');
    $response->assertDontSee('Select National Federation');
    $response->assertDontSee('National Local Organization');
});

it('uploads a diving professional medical statement document', function () {
    $this->actingAs($this->user);

    $file = UploadedFile::fake()->create('medical_statement.pdf', 1000);

    Livewire::test(\App\Livewire\OfficialDocumentsUpload::class, [
        'individual' => $this->individual,
        'types' => [
            OfficialDocumentTypeEnum::DivingProfessionalMedicalStatement,
            OfficialDocumentTypeEnum::DivingProfessionalInsurance,
            OfficialDocumentTypeEnum::OtherDocument,
        ],
        'federations' => collect([$this->defaultFederation]),
        'role' => 'Diving Professional',
    ])
        ->set('attachments', [$file])
        ->set('type', OfficialDocumentTypeEnum::DivingProfessionalMedicalStatement->value)
        ->set('issue_date', now()->format('Y-m-d'))
        ->set('expiry_date', now()->addYear()->format('Y-m-d'))
        ->call('save')
        ->assertHasNoErrors();

    // Verify document was created
    $this->assertDatabaseHas('official_documents', [
        'individual_id' => $this->individual->id,
        'type' => OfficialDocumentTypeEnum::DivingProfessionalMedicalStatement->value,
        'federation_id' => 141, // Default federation
        'status_class' => PendingOfficialDocumentState::class,
    ]);
});

it('uploads a diving professional insurance document', function () {
    $this->actingAs($this->user);

    $file = UploadedFile::fake()->create('insurance.pdf', 1000);

    Livewire::test(\App\Livewire\OfficialDocumentsUpload::class, [
        'individual' => $this->individual,
        'types' => [
            OfficialDocumentTypeEnum::DivingProfessionalMedicalStatement,
            OfficialDocumentTypeEnum::DivingProfessionalInsurance,
            OfficialDocumentTypeEnum::OtherDocument,
        ],
        'federations' => collect([$this->defaultFederation]),
        'role' => 'Diving Professional',
    ])
        ->set('attachments', [$file])
        ->set('type', OfficialDocumentTypeEnum::DivingProfessionalInsurance->value)
        ->set('issue_date', now()->format('Y-m-d'))
        ->set('expiry_date', now()->addYear()->format('Y-m-d'))
        ->call('save')
        ->assertHasNoErrors();

    // Verify document was created
    $this->assertDatabaseHas('official_documents', [
        'individual_id' => $this->individual->id,
        'type' => OfficialDocumentTypeEnum::DivingProfessionalInsurance->value,
        'federation_id' => 141, // Default federation
        'status_class' => PendingOfficialDocumentState::class,
    ]);
});

it('uploads an other type document', function () {
    $this->actingAs($this->user);

    $file = UploadedFile::fake()->create('other_document.pdf', 1000);

    Livewire::test(\App\Livewire\OfficialDocumentsUpload::class, [
        'individual' => $this->individual,
        'types' => [
            OfficialDocumentTypeEnum::DivingProfessionalMedicalStatement,
            OfficialDocumentTypeEnum::DivingProfessionalInsurance,
            OfficialDocumentTypeEnum::OtherDocument,
        ],
        'federations' => collect([$this->defaultFederation]),
        'role' => 'Diving Professional',
    ])
        ->set('attachments', [$file])
        ->set('type', OfficialDocumentTypeEnum::OtherDocument->value)
        ->set('issue_date', now()->format('Y-m-d'))
        ->call('save')
        ->assertHasNoErrors();

    // Verify document was created
    $this->assertDatabaseHas('official_documents', [
        'individual_id' => $this->individual->id,
        'type' => OfficialDocumentTypeEnum::OtherDocument->value,
        'federation_id' => 141, // Default federation
        'status_class' => PendingOfficialDocumentState::class,
    ]);
});

it('always uses the default federation for diving professional documents', function () {
    $this->actingAs($this->user);

    // Create another federation
    $otherFederation = Federation::factory()->create([
        'country_id' => $this->country->id,
        'is_default_federation' => false,
    ]);
    $this->individual->federations()->attach($otherFederation->id);

    $file = UploadedFile::fake()->create('document.pdf', 1000);

    Livewire::test(\App\Livewire\OfficialDocumentsUpload::class, [
        'individual' => $this->individual,
        'types' => [
            OfficialDocumentTypeEnum::DivingProfessionalMedicalStatement,
            OfficialDocumentTypeEnum::DivingProfessionalInsurance,
            OfficialDocumentTypeEnum::OtherDocument,
        ],
        'federations' => collect([$this->defaultFederation, $otherFederation]),
        'role' => 'Diving Professional',
    ])
        ->set('attachments', [$file])
        ->set('type', OfficialDocumentTypeEnum::DivingProfessionalMedicalStatement->value)
        ->set('issue_date', now()->format('Y-m-d'))
        ->call('save')
        ->assertHasNoErrors();

    // Verify document was created with default federation (141)
    $this->assertDatabaseHas('official_documents', [
        'individual_id' => $this->individual->id,
        'federation_id' => 141, // Always default federation
    ]);

    // Verify it wasn't created with the other federation
    $this->assertDatabaseMissing('official_documents', [
        'individual_id' => $this->individual->id,
        'federation_id' => $otherFederation->id,
    ]);
});
