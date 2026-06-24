<?php

use App\Reports\EntitiesListReport;
use App\Reports\ReportTemplate;
use App\Services\ReportGeneratorService;
use Database\Factories\DistrictFactory;
use Database\Factories\ZoneFactory;
use Domain\Entities\Models\Entity;
use Domain\Entities\Models\EntityFederation;
use Domain\Entities\States\ActiveEntityFederationState;
use Domain\Entities\States\PendingEntityFederationState;
use Domain\Entities\States\RejectedEntityFederationState;
use Domain\Federations\Models\Federation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('report implements ReportTemplate interface', function () {
    $report = new EntitiesListReport;

    expect($report)->toBeInstanceOf(ReportTemplate::class);
});

test('report is discoverable by ReportGeneratorService', function () {
    $service = new ReportGeneratorService;
    $types = $service->getAvailableReportTypes();

    expect($types)->toHaveKey('EntitiesListReport');
});

test('report has correct display name', function () {
    expect(EntitiesListReport::getDisplayName())->toBe(__('reports.entities_list'));
});

test('query returns Entity query builder', function () {
    $report = new EntitiesListReport;
    $query = $report->query([]);

    expect($query->getModel())->toBeInstanceOf(Entity::class);
});

test('columns returns 24 column headers', function () {
    $report = new EntitiesListReport;
    $columns = $report->columns();

    expect($columns)->toHaveCount(24);
});

test('processData maps all 24 columns correctly', function () {
    $country = \App\Models\Country::factory()->create(['name' => 'Portugal']);
    $district = DistrictFactory::new()->create(['name' => 'Lisboa', 'country_id' => $country->id]);
    $zone = ZoneFactory::new()->create(['name' => 'Zona Sul']);

    $entity = Entity::factory()->create([
        'name' => 'Example Club',
        'legal_name' => 'Example Club Legal Entity',
        'member_number' => 12345,
        'legal_responsible_person' => 'Example Responsible Person',
        'vat_number' => '000000000',
        'country_id' => $country->id,
        'district_id' => $district->id,
        'address' => 'Example Street 1',
        'location' => 'Example City',
        'postal_code' => '0000-000',
        'email' => 'info@example.test',
        'phone' => '+15550101000',
        'website' => 'https://club.example.test',
        'facebook_url' => 'https://social.example.test/club',
        'x_url' => 'https://x.example.test/club',
        'instagram_url' => 'https://photos.example.test/club',
        'linkedin_url' => 'https://network.example.test/company/club',
        'has_international_portal' => true,
    ]);

    $entity->zones()->attach($zone->id);

    $mainFederation = Federation::factory()->create([
        'name' => 'Primary Federation',
        'is_default_federation' => true,
        'is_local' => false,
    ]);

    EntityFederation::create([
        'entity_id' => $entity->id,
        'federation_id' => $mainFederation->id,
        'active' => true,
        'status_class' => ActiveEntityFederationState::class,
    ]);

    $report = new EntitiesListReport;
    $data = $report->query([])->get();
    $processed = $report->processData($data);

    $row = $processed->first();

    expect($row)->toHaveCount(24)
        ->and($row[__('reports.columns.entity_name')])->toBe('Example Club')
        ->and($row[__('reports.columns.legal_name')])->toBe('Example Club Legal Entity')
        ->and($row[__('reports.columns.member_number')])->toBe(12345)
        ->and($row[__('reports.columns.member_id')])->toBe($entity->id)
        ->and($row[__('reports.columns.federation')])->toBe('Primary Federation')
        ->and($row[__('reports.columns.responsible_person')])->toBe('Example Responsible Person')
        ->and($row[__('reports.columns.vat_number')])->toBe('000000000')
        ->and($row[__('reports.columns.country')])->toBe('Portugal')
        ->and($row[__('reports.columns.district')])->toBe('Lisboa')
        ->and($row[__('reports.columns.zone')])->toBe('Zona Sul')
        ->and($row[__('reports.columns.address')])->toBe('Example Street 1')
        ->and($row[__('reports.columns.locality')])->toBe('Example City')
        ->and($row[__('reports.columns.postal_code')])->toBe('0000-000')
        ->and($row[__('reports.columns.email')])->toBe('info@example.test')
        ->and($row[__('reports.columns.phone')])->toBe('+15550101000')
        ->and($row[__('reports.columns.website')])->toBe('https://club.example.test')
        ->and($row[__('reports.columns.facebook')])->toBe('https://social.example.test/club')
        ->and($row[__('reports.columns.x')])->toBe('https://x.example.test/club')
        ->and($row[__('reports.columns.instagram')])->toBe('https://photos.example.test/club')
        ->and($row[__('reports.columns.linkedin')])->toBe('https://network.example.test/company/club')
        ->and($row[__('reports.columns.cmas_portal')])->toBe(__('reports.yes'))
        ->and($row[__('reports.columns.affiliation_status')])->toBe(__('states.active'));
});

test('federation classification works correctly', function () {
    $entity = Entity::factory()->create();

    $mainFederation = Federation::factory()->create([
        'name' => 'Primary Federation',
        'is_default_federation' => true,
        'is_local' => false,
    ]);

    $localFederation = Federation::factory()->create([
        'name' => 'Associacao Norte',
        'is_default_federation' => false,
        'is_local' => true,
    ]);

    $modalidadeFederation = Federation::factory()->create([
        'name' => 'International Diving Federation',
        'is_default_federation' => false,
        'is_local' => false,
    ]);

    EntityFederation::create([
        'entity_id' => $entity->id,
        'federation_id' => $mainFederation->id,
        'active' => true,
        'status_class' => ActiveEntityFederationState::class,
    ]);

    EntityFederation::create([
        'entity_id' => $entity->id,
        'federation_id' => $localFederation->id,
        'active' => true,
        'status_class' => ActiveEntityFederationState::class,
    ]);

    EntityFederation::create([
        'entity_id' => $entity->id,
        'federation_id' => $modalidadeFederation->id,
        'active' => true,
        'status_class' => ActiveEntityFederationState::class,
    ]);

    $report = new EntitiesListReport;
    $data = $report->query([])->get();
    $processed = $report->processData($data);

    $row = $processed->first();

    expect($row[__('reports.columns.federation')])->toBe('Primary Federation')
        ->and($row[__('reports.columns.territorial_association')])->toBe('Associacao Norte')
        ->and($row[__('reports.columns.sport_association')])->toBe('International Diving Federation');
});

test('has_international_portal renders as yes or no', function () {
    $entityWithPortal = Entity::factory()->create(['has_international_portal' => true]);
    $entityWithoutPortal = Entity::factory()->create(['has_international_portal' => false]);

    $report = new EntitiesListReport;
    $data = $report->query([])->get();
    $processed = $report->processData($data);

    $rows = $processed->keyBy(fn ($row) => $row[__('reports.columns.member_id')]);

    expect($rows[$entityWithPortal->id][__('reports.columns.cmas_portal')])->toBe(__('reports.yes'))
        ->and($rows[$entityWithoutPortal->id][__('reports.columns.cmas_portal')])->toBe(__('reports.no'));
});

test('affiliation status renders correctly for all states', function () {
    $entityActive = Entity::factory()->create();
    $entityPending = Entity::factory()->create();
    $entityRejected = Entity::factory()->create();
    $entityNoFederation = Entity::factory()->create();

    $mainFederation = Federation::factory()->create([
        'is_default_federation' => true,
        'is_local' => false,
    ]);

    EntityFederation::create([
        'entity_id' => $entityActive->id,
        'federation_id' => $mainFederation->id,
        'active' => true,
        'status_class' => ActiveEntityFederationState::class,
    ]);

    EntityFederation::create([
        'entity_id' => $entityPending->id,
        'federation_id' => $mainFederation->id,
        'active' => false,
        'status_class' => PendingEntityFederationState::class,
    ]);

    EntityFederation::create([
        'entity_id' => $entityRejected->id,
        'federation_id' => $mainFederation->id,
        'active' => false,
        'status_class' => RejectedEntityFederationState::class,
    ]);

    $report = new EntitiesListReport;
    $data = $report->query([])->get();
    $processed = $report->processData($data);

    $rows = $processed->keyBy(fn ($row) => $row[__('reports.columns.member_id')]);

    expect($rows[$entityActive->id][__('reports.columns.affiliation_status')])->toBe(__('states.active'))
        ->and($rows[$entityPending->id][__('reports.columns.affiliation_status')])->toBe(__('states.pending'))
        ->and($rows[$entityRejected->id][__('reports.columns.affiliation_status')])->toBe(__('states.rejected'))
        ->and($rows[$entityNoFederation->id][__('reports.columns.affiliation_status')])->toBe(__('reports.not_available'));
});
