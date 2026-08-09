<?php

use App\Models\Country;
use Database\Factories\DistrictFactory;
use Database\Factories\EntityFactory;
use Database\Factories\ZoneFactory;
use Domain\Entities\Models\Entity;
use Domain\Entities\States\ActiveEntityFederationState;
use Domain\Federations\Actions\ReconcileIndividualTerritorialFederationAction;
use Domain\Federations\Actions\ResolveIndividualTerritorialFederationAction;
use Domain\Federations\Enums\TerritorialAssignmentSource;
use Domain\Federations\Enums\TerritorialResolutionOutcome;
use Domain\Federations\Models\Federation;
use Domain\Geographic\Enums\ZoneKind;
use Domain\Geographic\Models\District;
use Domain\Geographic\Models\Zone;
use Domain\Individuals\Actions\EditIndividualAction;
use Domain\Individuals\Actions\SyncIndividualLocalFederationsAction;
use Domain\Individuals\DataTransferObject\IndividualData;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\IndividualFederation;
use Domain\Individuals\States\ActiveIndividualEntityState;
use Domain\Individuals\States\ActiveIndividualFederationState;
use Domain\Individuals\States\PendingIndividualFederationState;

function territorialSetup(): array
{
    $country = Country::factory()->create(['iso' => 'BR']);
    /** @var Zone $zone */
    $zone = ZoneFactory::new()->create([
        'country_id' => $country->id,
        'kind' => ZoneKind::ADMINISTRATIVE_LEVEL_1,
    ]);
    /** @var District $district */
    $district = DistrictFactory::new()->create([
        'country_id' => $country->id,
        'administrative_zone_id' => $zone->id,
    ]);
    $federation = Federation::factory()->create([
        'country_id' => $country->id,
        'is_local' => true,
    ]);
    $federation->zones()->attach($zone);

    return compact('country', 'zone', 'district', 'federation');
}

it('assigns the territorial federation from residence when no active club applies', function () {
    ['district' => $district, 'federation' => $federation, 'zone' => $zone] = territorialSetup();
    $individual = Individual::factory()->create([
        'country_id' => $district->country_id,
        'district_id' => $district->id,
    ]);

    $resolution = (new ReconcileIndividualTerritorialFederationAction)->execute($individual);
    $membership = IndividualFederation::query()
        ->where('individual_id', $individual->id)
        ->where('federation_id', $federation->id)
        ->firstOrFail();

    expect($resolution->outcome)->toBe(TerritorialResolutionOutcome::RESOLVED)
        ->and($resolution->source)->toBe(TerritorialAssignmentSource::RESIDENCE)
        ->and($membership->assignment_source)->toBe(TerritorialAssignmentSource::RESIDENCE)
        ->and($membership->assignment_zone_id)->toBe($zone->id)
        ->and($membership->assignment_district_id)->toBe($district->id)
        ->and($membership->status_class)->toBe(PendingIndividualFederationState::class)
        ->and((bool) $membership->active)->toBeFalse();
});

it('gives an active club precedence over residence', function () {
    ['country' => $country, 'district' => $district, 'federation' => $residenceFederation] = territorialSetup();
    $clubFederation = Federation::factory()->create([
        'country_id' => $country->id,
        'is_local' => true,
    ]);
    /** @var Entity $club */
    $club = EntityFactory::new()->create(['country_id' => $country->id]);
    $club->federations()->attach($clubFederation, [
        'status_class' => ActiveEntityFederationState::class,
    ]);
    $individual = Individual::factory()->create([
        'country_id' => $country->id,
        'district_id' => $district->id,
    ]);

    (new ReconcileIndividualTerritorialFederationAction)->execute($individual);
    $individual->entities()->attach($club, [
        'status_class' => ActiveIndividualEntityState::class,
    ]);
    $resolution = (new ReconcileIndividualTerritorialFederationAction)->execute($individual);

    expect($resolution->source)->toBe(TerritorialAssignmentSource::CLUB)
        ->and($resolution->federation->is($clubFederation))->toBeTrue()
        ->and(IndividualFederation::query()
            ->where('individual_id', $individual->id)
            ->where('federation_id', $residenceFederation->id)
            ->exists())->toBeFalse();

    $membership = IndividualFederation::query()
        ->where('individual_id', $individual->id)
        ->where('federation_id', $clubFederation->id)
        ->firstOrFail();

    expect($membership->assignment_source)->toBe(TerritorialAssignmentSource::CLUB)
        ->and($membership->assignment_entity_id)->toBe($club->id)
        ->and($membership->status_class)->toBe(ActiveIndividualFederationState::class)
        ->and((bool) $membership->active)->toBeTrue();
});

it('does not let an address change replace an active club assignment', function () {
    ['country' => $country, 'district' => $district] = territorialSetup();
    ['district' => $otherDistrict, 'federation' => $otherFederation] = territorialSetup();
    $clubFederation = Federation::factory()->create([
        'country_id' => $country->id,
        'is_local' => true,
    ]);
    /** @var Entity $club */
    $club = EntityFactory::new()->create(['country_id' => $country->id]);
    $club->federations()->attach($clubFederation, [
        'status_class' => ActiveEntityFederationState::class,
    ]);
    $individual = Individual::factory()->create([
        'country_id' => $country->id,
        'district_id' => $district->id,
    ]);
    $individual->entities()->attach($club, [
        'status_class' => ActiveIndividualEntityState::class,
    ]);

    (new ReconcileIndividualTerritorialFederationAction)->execute($individual);
    $individual->update([
        'country_id' => $otherDistrict->country_id,
        'district_id' => $otherDistrict->id,
    ]);
    $resolution = (new ReconcileIndividualTerritorialFederationAction)->execute($individual->fresh());

    expect($resolution->source)->toBe(TerritorialAssignmentSource::CLUB)
        ->and($resolution->federation->is($clubFederation))->toBeTrue()
        ->and(IndividualFederation::query()
            ->where('individual_id', $individual->id)
            ->where('federation_id', $otherFederation->id)
            ->exists())->toBeFalse();
});

it('returns an ambiguous outcome instead of selecting the first territorial federation', function () {
    ['country' => $country, 'district' => $district, 'zone' => $zone, 'federation' => $firstFederation] = territorialSetup();
    $secondFederation = Federation::factory()->create([
        'country_id' => $country->id,
        'is_local' => true,
    ]);
    $secondFederation->zones()->attach($zone);
    $individual = Individual::factory()->create([
        'country_id' => $country->id,
        'district_id' => $district->id,
    ]);

    $resolution = (new ResolveIndividualTerritorialFederationAction)->execute($individual);

    expect($resolution->outcome)->toBe(TerritorialResolutionOutcome::AMBIGUOUS)
        ->and($resolution->federation)->toBeNull()
        ->and($resolution->candidates->pluck('id')->all())
        ->toContain($firstFederation->id, $secondFederation->id);

    (new ReconcileIndividualTerritorialFederationAction)->execute($individual);

    expect(IndividualFederation::query()->where('individual_id', $individual->id)->exists())->toBeFalse();
});

it('treats conflicting active club mappings as ambiguous', function () {
    ['country' => $country] = territorialSetup();
    $firstFederation = Federation::factory()->create(['country_id' => $country->id, 'is_local' => true]);
    $secondFederation = Federation::factory()->create(['country_id' => $country->id, 'is_local' => true]);
    /** @var Entity $club */
    $club = EntityFactory::new()->create(['country_id' => $country->id]);
    $club->federations()->attach([
        $firstFederation->id => ['status_class' => ActiveEntityFederationState::class],
        $secondFederation->id => ['status_class' => ActiveEntityFederationState::class],
    ]);
    $individual = Individual::factory()->create(['country_id' => $country->id]);
    $individual->entities()->attach($club, ['status_class' => ActiveIndividualEntityState::class]);

    $resolution = (new ResolveIndividualTerritorialFederationAction)->execute($individual);

    expect($resolution->outcome)->toBe(TerritorialResolutionOutcome::AMBIGUOUS)
        ->and($resolution->source)->toBe(TerritorialAssignmentSource::CLUB)
        ->and($resolution->candidates)->toHaveCount(2);
});

it('falls back to residence when the active club relationship is removed', function () {
    ['country' => $country, 'district' => $district, 'federation' => $residenceFederation] = territorialSetup();
    $clubFederation = Federation::factory()->create(['country_id' => $country->id, 'is_local' => true]);
    /** @var Entity $club */
    $club = EntityFactory::new()->create(['country_id' => $country->id]);
    $club->federations()->attach($clubFederation, ['status_class' => ActiveEntityFederationState::class]);
    $individual = Individual::factory()->create([
        'country_id' => $country->id,
        'district_id' => $district->id,
    ]);
    $individual->entities()->attach($club, ['status_class' => ActiveIndividualEntityState::class]);
    $action = new SyncIndividualLocalFederationsAction;
    $action->execute($individual, $club);

    $action->removeOnDeactivation($individual, $club);

    expect(IndividualFederation::query()
        ->where('individual_id', $individual->id)
        ->where('federation_id', $clubFederation->id)
        ->exists())->toBeFalse();

    $residenceMembership = IndividualFederation::query()
        ->where('individual_id', $individual->id)
        ->where('federation_id', $residenceFederation->id)
        ->firstOrFail();

    expect($residenceMembership->assignment_source)->toBe(TerritorialAssignmentSource::RESIDENCE)
        ->and($residenceMembership->status_class)->toBe(PendingIndividualFederationState::class);
});

it('can record import as the origin of a residence mapping', function () {
    ['country' => $country, 'district' => $district, 'federation' => $federation] = territorialSetup();
    $individual = Individual::factory()->create([
        'country_id' => $country->id,
        'district_id' => $district->id,
    ]);

    (new ReconcileIndividualTerritorialFederationAction)->execute(
        $individual,
        sourceOverride: TerritorialAssignmentSource::IMPORT,
    );

    expect(IndividualFederation::query()
        ->where('individual_id', $individual->id)
        ->where('federation_id', $federation->id)
        ->firstOrFail()
        ->assignment_source)->toBe(TerritorialAssignmentSource::IMPORT);
});

it('preserves manually assigned territorial memberships', function () {
    ['country' => $country, 'district' => $district] = territorialSetup();
    $manualFederation = Federation::factory()->create([
        'country_id' => $country->id,
        'is_local' => true,
    ]);
    $individual = Individual::factory()->create([
        'country_id' => $country->id,
        'district_id' => $district->id,
    ]);
    $membership = IndividualFederation::create([
        'individual_id' => $individual->id,
        'federation_id' => $manualFederation->id,
        'assignment_source' => TerritorialAssignmentSource::MANUAL,
        'status_class' => ActiveIndividualFederationState::class,
        'active' => true,
    ]);

    (new ReconcileIndividualTerritorialFederationAction)->execute($individual);

    expect($membership->fresh()->assignment_source)->toBe(TerritorialAssignmentSource::MANUAL)
        ->and($membership->fresh()->status_class)->toBe(ActiveIndividualFederationState::class)
        ->and(IndividualFederation::query()
            ->where('individual_id', $individual->id)
            ->count())->toBe(1);
});

it('lets an active club supersede an imported residence assignment', function () {
    ['country' => $country, 'district' => $district, 'federation' => $residenceFederation] = territorialSetup();
    $clubFederation = Federation::factory()->create(['country_id' => $country->id, 'is_local' => true]);
    /** @var Entity $club */
    $club = EntityFactory::new()->create(['country_id' => $country->id]);
    $club->federations()->attach($clubFederation, ['status_class' => ActiveEntityFederationState::class]);
    $individual = Individual::factory()->create([
        'country_id' => $country->id,
        'district_id' => $district->id,
    ]);

    (new ReconcileIndividualTerritorialFederationAction)->execute(
        $individual,
        sourceOverride: TerritorialAssignmentSource::IMPORT,
    );
    $individual->entities()->attach($club, ['status_class' => ActiveIndividualEntityState::class]);

    (new ReconcileIndividualTerritorialFederationAction)->execute($individual);

    expect(IndividualFederation::query()
        ->where('individual_id', $individual->id)
        ->where('federation_id', $residenceFederation->id)
        ->exists())->toBeFalse()
        ->and(IndividualFederation::query()
            ->where('individual_id', $individual->id)
            ->where('federation_id', $clubFederation->id)
            ->firstOrFail()
            ->assignment_source)->toBe(TerritorialAssignmentSource::CLUB);
});

it('marks an explicit local federation edit as manual', function () {
    ['country' => $country, 'federation' => $federation] = territorialSetup();
    $individual = Individual::factory()->create(['country_id' => $country->id]);
    $data = IndividualData::fromArray([
        'name' => $individual->name,
        'country_id' => $country->id,
        'federation_id' => [$federation->id],
    ], $individual->user_id);

    (new EditIndividualAction)($data, $individual->id);

    expect(IndividualFederation::query()
        ->where('individual_id', $individual->id)
        ->where('federation_id', $federation->id)
        ->firstOrFail()
        ->assignment_source)->toBe(TerritorialAssignmentSource::MANUAL);
});
