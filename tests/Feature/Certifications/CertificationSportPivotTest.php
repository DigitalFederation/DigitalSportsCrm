<?php

use App\Models\Committee;
use App\Models\Sport;
use Domain\Certifications\Models\Certification;
use Domain\Certifications\Models\CertificationAttributed;
use Domain\Certifications\States\ActiveCertificationAttributedState;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Licenses\Models\License;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->sport = Sport::factory()->create();
    $this->committee = Committee::factory()->create(['code' => 'SPORT', 'is_international' => false]);
    $this->professionalRole = ProfessionalRole::factory()->create(['role' => 'COACH']);
});

test('certification can have many sports via pivot', function () {
    $certification = Certification::factory()->create([
        'committee_id' => $this->committee->id,
        'professional_role_id' => $this->professionalRole->id,
        'license_id' => null,
    ]);

    $sport2 = Sport::factory()->create();

    $certification->sports()->attach([$this->sport->id, $sport2->id]);

    expect($certification->sports)->toHaveCount(2)
        ->and($certification->sports->pluck('id')->toArray())->toContain($this->sport->id, $sport2->id);
});

test('sport can have many certifications via pivot', function () {
    $cert1 = Certification::factory()->create([
        'committee_id' => $this->committee->id,
        'license_id' => null,
    ]);
    $cert2 = Certification::factory()->create([
        'committee_id' => $this->committee->id,
        'license_id' => null,
    ]);

    $this->sport->certifications()->attach([$cert1->id, $cert2->id]);

    expect($this->sport->certifications)->toHaveCount(2);
});

test('scopeFilterSport finds certifications via license path', function () {
    $license = License::factory()->create(['sport_id' => $this->sport->id]);
    $certification = Certification::factory()->create([
        'committee_id' => $this->committee->id,
        'license_id' => $license->id,
    ]);

    $results = Certification::filterSport($this->sport->id)->get();

    expect($results->pluck('id')->toArray())->toContain($certification->id);
});

test('scopeFilterSport finds certifications via pivot path', function () {
    $certification = Certification::factory()->create([
        'committee_id' => $this->committee->id,
        'license_id' => null,
    ]);

    $certification->sports()->attach($this->sport->id);

    $results = Certification::filterSport($this->sport->id)->get();

    expect($results->pluck('id')->toArray())->toContain($certification->id);
});

test('scopeFilterSport does not find certifications without matching sport', function () {
    $otherSport = Sport::factory()->create();

    $certification = Certification::factory()->create([
        'committee_id' => $this->committee->id,
        'license_id' => null,
    ]);

    $certification->sports()->attach($otherSport->id);

    $results = Certification::filterSport($this->sport->id)->get();

    expect($results->pluck('id')->toArray())->not->toContain($certification->id);
});

test('CertificationAttributed scopeSport works with pivot path', function () {
    $certification = Certification::factory()->create([
        'committee_id' => $this->committee->id,
        'license_id' => null,
    ]);

    $certification->sports()->attach($this->sport->id);

    $individual = Individual::factory()->create();
    $attributed = CertificationAttributed::factory()->create([
        'certification_id' => $certification->id,
        'individual_id' => $individual->id,
        'status_class' => ActiveCertificationAttributedState::class,
    ]);

    $results = CertificationAttributed::sport($this->sport->id)->get();

    expect($results->pluck('id')->toArray())->toContain($attributed->id);
});

test('CertificationAttributed scopeSport works with license path', function () {
    $license = License::factory()->create(['sport_id' => $this->sport->id]);
    $certification = Certification::factory()->create([
        'committee_id' => $this->committee->id,
        'license_id' => $license->id,
    ]);

    $individual = Individual::factory()->create();
    $attributed = CertificationAttributed::factory()->create([
        'certification_id' => $certification->id,
        'individual_id' => $individual->id,
        'status_class' => ActiveCertificationAttributedState::class,
    ]);

    $results = CertificationAttributed::sport($this->sport->id)->get();

    expect($results->pluck('id')->toArray())->toContain($attributed->id);
});
