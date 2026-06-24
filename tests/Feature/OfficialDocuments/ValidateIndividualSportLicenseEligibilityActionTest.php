<?php

use App\Enums\OfficialDocumentTypeEnum;
use Domain\Individuals\Models\Individual;
use Domain\OfficialDocuments\Actions\ValidateIndividualSportLicenseEligibilityAction;
use Domain\OfficialDocuments\Models\OfficialDocument;
use Domain\OfficialDocuments\States\ActiveOfficialDocumentState;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses()->group('official-documents');

beforeEach(function () {
    // Setup storage fakes
    Storage::fake('public');
    Storage::fake('media');

    // Create required assets
    Storage::disk('public')->put(
        'img/user_placeholder.png',
        file_get_contents(base_path('public/img/user_placeholder.png'))
    );

    // Create a fake image file
    $this->fakeImage = UploadedFile::fake()->image('profile.jpg');
});

it('validates individual eligibility with photo', function () {
    // Arrange
    $individual = Individual::factory()->create();

    // Add fake media
    $individual->addMedia($this->fakeImage)
        ->toMediaCollection('profile');

    // Create required documents
    OfficialDocument::factory()->create([
        'individual_id' => $individual->id,
        'type' => OfficialDocumentTypeEnum::ADELCertificate->value,
        'status_class' => ActiveOfficialDocumentState::class,
    ]);

    $action = new ValidateIndividualSportLicenseEligibilityAction;

    // Act
    $result = $action($individual);

    // Assert
    expect($result['is_valid'])->toBeTrue()
        ->and($result['errors'])->toBeEmpty();
});
