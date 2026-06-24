<?php

use App\Enums\OfficialDocumentTypeEnum;
use App\Models\Country;
use App\Models\Group;
use App\Models\User;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\OfficialDocuments\Models\OfficialDocument;
use Domain\OfficialDocuments\States\ActiveOfficialDocumentState;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->country = Country::factory()->create();
    $this->group = Group::factory()->create(['code' => 'INDIVIDUAL']);

    $this->federation = Federation::factory()->create([
        'country_id' => $this->country->id,
        'is_default_federation' => true,
        'is_local' => false,
    ]);

    $this->user = User::factory()->create(['group_id' => $this->group->id]);
    $this->individual = Individual::factory()->create();
    $this->user->individuals()->save($this->individual);
});

test('when individual has two medical exam documents, the newest is listed first', function () {
    $oldDoc = OfficialDocument::factory()->active()->create([
        'individual_id' => $this->individual->id,
        'type' => OfficialDocumentTypeEnum::MedicalStatement,
        'federation_id' => $this->federation->id,
        'country_id' => $this->country->id,
        'expiry_date' => now()->addYear()->format('Y-m-d'),
        'created_at' => now()->subMonths(12),
    ]);

    $newDoc = OfficialDocument::factory()->active()->create([
        'individual_id' => $this->individual->id,
        'type' => OfficialDocumentTypeEnum::MedicalStatement,
        'federation_id' => $this->federation->id,
        'country_id' => $this->country->id,
        'expiry_date' => now()->addYears(2)->format('Y-m-d'),
        'created_at' => now()->subMonth(),
    ]);

    $documents = OfficialDocument::where('individual_id', $this->individual->id)
        ->where('type', OfficialDocumentTypeEnum::MedicalStatement)
        ->latest()
        ->get();

    expect($documents->first()->id)->toBe($newDoc->id)
        ->and($documents->last()->id)->toBe($oldDoc->id);
});

test('when individual has two medical exam documents, the newest expiry is considered for eligibility', function () {
    // Old expired document
    OfficialDocument::factory()->create([
        'individual_id' => $this->individual->id,
        'type' => OfficialDocumentTypeEnum::MedicalStatement,
        'federation_id' => $this->federation->id,
        'country_id' => $this->country->id,
        'status_class' => ActiveOfficialDocumentState::class,
        'expiry_date' => now()->subMonth()->format('Y-m-d'),
        'created_at' => now()->subYears(2),
    ]);

    // New valid document
    OfficialDocument::factory()->active()->create([
        'individual_id' => $this->individual->id,
        'type' => OfficialDocumentTypeEnum::MedicalStatement,
        'federation_id' => $this->federation->id,
        'country_id' => $this->country->id,
        'expiry_date' => now()->addYear()->format('Y-m-d'),
        'created_at' => now()->subMonth(),
    ]);

    // The system should find at least one active, non-expired medical statement
    $hasValidDocument = OfficialDocument::where('individual_id', $this->individual->id)
        ->where('type', OfficialDocumentTypeEnum::MedicalStatement)
        ->where('status_class', ActiveOfficialDocumentState::class)
        ->where(function ($q) {
            $q->whereNull('expiry_date')
                ->orWhereDate('expiry_date', '>=', now());
        })
        ->exists();

    expect($hasValidDocument)->toBeTrue();
});

test('individual documents index route returns documents with newest first', function () {
    $this->actingAs($this->user);

    $oldDoc = OfficialDocument::factory()->active()->create([
        'individual_id' => $this->individual->id,
        'type' => OfficialDocumentTypeEnum::MedicalStatement,
        'federation_id' => $this->federation->id,
        'country_id' => $this->country->id,
        'expiry_date' => now()->addYear()->format('Y-m-d'),
        'created_at' => now()->subMonths(12),
    ]);

    $newDoc = OfficialDocument::factory()->active()->create([
        'individual_id' => $this->individual->id,
        'type' => OfficialDocumentTypeEnum::MedicalStatement,
        'federation_id' => $this->federation->id,
        'country_id' => $this->country->id,
        'expiry_date' => now()->addYears(2)->format('Y-m-d'),
        'created_at' => now()->subMonth(),
    ]);

    $response = $this->get(route('individual.official-documents.index', 'athlete'));
    $response->assertSuccessful();

    $returnedDocs = $response->viewData('official_documents');

    expect($returnedDocs->first()->id)->toBe($newDoc->id)
        ->and($returnedDocs->last()->id)->toBe($oldDoc->id);
});
