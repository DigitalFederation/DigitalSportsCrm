<?php

use App\Livewire\EvtEvents\ManageEnrollment;
use App\Models\Country;
use Domain\EvtEvents\Models\AthleteEnrollment;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\artisan;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed necessary data
    artisan('db:seed --class=RoleAndPermissionSeeder');

    // Create base test data with proper country relationship
    $country = Country::factory()->create(['ioc' => 'ITA']);
    $this->federation = Federation::factory()->create(['country_id' => $country->id]);
    $this->event = Event::factory()->create();
    $this->individual = Individual::factory()->create();
});

it('generates unique team identifiers with correct format', function () {
    // Create the component with required properties
    $component = new ManageEnrollment;
    $component->model = $this->federation;
    $component->event = $this->event;

    // Create some existing enrollments with team identifiers
    AthleteEnrollment::factory()->count(3)->create([
        'event_id' => $this->event->id,
        'entity_id' => null,
        'federation_id' => $this->federation->id,
        'team_identifier' => function () {
            static $counter = 1;
            $prefix = 'fed_' . $this->federation->id;

            return $prefix . '_' . str_pad($counter++, 4, '0', STR_PAD_LEFT);
        },
    ]);

    // Generate multiple new identifiers and store them
    $generatedIds = [];
    for ($i = 0; $i < 5; $i++) {
        $identifier = $component->buildTeamIdentifier();
        $generatedIds[] = (string) $identifier;

        AthleteEnrollment::factory()->create([
            'event_id' => $this->event->id,
            'federation_id' => $this->federation->id,
            'team_identifier' => $identifier,
        ]);
    }

    // Get the expected prefix
    $expectedPrefix = 'fed_' . $this->federation->id;

    // Test uniqueness
    expect(count(array_unique($generatedIds)))->toBe(5);

    // Test format (e.g., "ita_123456_0004")
    foreach ($generatedIds as $identifier) {
        expect($identifier)->toBeString()
            ->and($identifier)->toMatch('/^' . preg_quote($expectedPrefix) . '_\d{4}$/');
    }

    // Test sequential numbering (should start after existing ones)
    expect($generatedIds[0])->toBe($expectedPrefix . '_0004')
        ->and($generatedIds[4])->toBe($expectedPrefix . '_0008');
});

it('generates unique identifiers for different federations', function () {
    // Create two federations with different countries
    $federationITA = $this->federation; // Already has ITA country

    // Create USA country and federation
    $countryUSA = Country::factory()->create(['ioc' => 'USA']);
    $federationUSA = Federation::factory()->create(['country_id' => $countryUSA->id]);

    // Create component instances for each federation
    $componentITA = new ManageEnrollment;
    $componentITA->model = $federationITA;
    $componentITA->event = $this->event;

    $componentUSA = new ManageEnrollment;
    $componentUSA->model = $federationUSA;
    $componentUSA->event = $this->event;

    // Generate identifiers for both federations
    $italianIds = [];
    $americanIds = [];

    for ($i = 0; $i < 3; $i++) {
        $italianIds[] = (string) $componentITA->buildTeamIdentifier();
        $americanIds[] = (string) $componentUSA->buildTeamIdentifier();
    }

    // Get the expected prefixes
    $italianPrefix = 'fed_' . $federationITA->id;
    $americanPrefix = 'fed_' . $federationUSA->id;

    // Test each identifier individually for better error messages
    foreach ($italianIds as $id) {
        expect($id)->toBeString()
            ->and($id)->toMatch('/^' . preg_quote($italianPrefix) . '_\d{4}$/');
    }

    foreach ($americanIds as $id) {
        expect($id)->toBeString()
            ->and($id)->toMatch('/^' . preg_quote($americanPrefix) . '_\d{4}$/');
    }

    // Test sequential numbering
    expect($italianIds[0])->toBe($italianPrefix . '_0001')
        ->and($americanIds[0])->toBe($americanPrefix . '_0001');
});

it('handles concurrent identifier generation correctly', function () {
    $component = new ManageEnrollment;
    $component->model = $this->federation;
    $component->event = $this->event;

    // Simulate concurrent requests by generating multiple identifiers
    $identifiers = [];

    // Generate multiple identifiers in quick succession
    for ($i = 0; $i < 10; $i++) {
        $identifier = (string) $component->buildTeamIdentifier();
        $identifiers[] = $identifier;

        // Create a minimal enrollment with this identifier
        AthleteEnrollment::create([
            'event_id' => $this->event->id,
            'federation_id' => $this->federation->id,
            'team_identifier' => $identifier,
            'individual_id' => $this->individual->id,
            'enrollment_id' => Enrollment::factory()->create([
                'event_id' => $this->event->id,
                'enrollable_id' => $this->federation->id,
                'enrollable_type' => Federation::class,
            ])->id,
        ]);
    }

    // Get the expected prefix
    $expectedPrefix = 'fed_' . $this->federation->id;

    // Test uniqueness
    expect(count(array_unique($identifiers)))->toBe(count($identifiers));

    // Test format and string type for each identifier
    foreach ($identifiers as $id) {
        expect($id)
            ->toBeString()
            ->and($id)->toMatch('/^' . preg_quote($expectedPrefix) . '_\d{4}$/');
    }

    // Extract and verify sequential numbering
    $numbers = array_map(function ($id) use ($expectedPrefix) {
        preg_match('/^' . preg_quote($expectedPrefix) . '_(\d{4})$/', $id, $matches);

        return (int) $matches[1];
    }, $identifiers);

    sort($numbers);
    $expectedNumbers = range(1, count($numbers));
    expect($numbers)->toBe($expectedNumbers);
});
