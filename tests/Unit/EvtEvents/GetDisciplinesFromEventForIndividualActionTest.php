<?php

declare(strict_types=1);

use Domain\EvtEvents\Actions\GetDisciplinesFromEventForIndividualAction;
use Domain\EvtEvents\Models\Competition;
use Domain\EvtEvents\Models\Discipline;
use Domain\EvtEvents\Models\DisciplineTemplate;
use Domain\EvtEvents\Models\Event;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->sport = \App\Models\Sport::factory()->create();
    $this->event = Event::factory()->create([
        'start_date' => Carbon::now(),
        'start_registration' => Carbon::now()->subDay(),
        'end_registration' => Carbon::now()->addDay(),
    ]);
    $this->template = DisciplineTemplate::factory()->create();
    $this->competition = Competition::factory()->create([
        'event_id' => $this->event->id,
        'discipline_template_id' => $this->template->id,
        'sport_id' => $this->sport->id,
    ]);
    $this->event->update(['competition_id' => $this->competition->id]);
    $this->event->refresh();
    $this->discipline = Discipline::factory()->create([
        'enrollment_type' => 'individual',
        'sport_id' => $this->sport->id,
        'gender' => 'male',
    ]);
    $this->template->disciplines()->attach($this->discipline->id);
    $this->license = License::factory()->create([
        'sport_id' => $this->sport->id,
    ]);
    $this->discipline->licenses()->attach($this->license->id);
    $this->discipline->refresh();
    $this->individual = Individual::factory()->create([
        'gender' => 'male',
        'birthdate' => '2000-01-01',
    ]);
});

it('returns discipline as eligible if individual has active and valid license', function () {
    // Create an active, valid license for the individual
    LicenseAttributed::factory()->create([
        'license_id' => $this->license->id,
        'model_type' => 'individual',
        'model_id' => $this->individual->id,
        'status_class' => ActiveLicenseAttributedState::class,
        'current_term_ends_at' => Carbon::now()->addMonth(),
    ]);

    $action = new GetDisciplinesFromEventForIndividualAction;
    $result = $action->execute($this->event, $this->individual);
    expect($result)->toHaveCount(1);
    expect($result->first()->id)->toBe($this->discipline->id);
});

it('does not return discipline if individual license is expired', function () {
    // Create an expired license for the individual
    LicenseAttributed::factory()->create([
        'license_id' => $this->license->id,
        'model_type' => 'individual',
        'model_id' => $this->individual->id,
        'status_class' => ActiveLicenseAttributedState::class,
        'current_term_ends_at' => Carbon::now()->subDay(),
    ]);

    $action = new GetDisciplinesFromEventForIndividualAction;
    $result = $action->execute($this->event, $this->individual);
    expect($result)->toHaveCount(0);
});
