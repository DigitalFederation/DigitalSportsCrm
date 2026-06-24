<?php

namespace Tests\Unit\Models;

use App\Models\Country;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Geographic\Models\District;
use Domain\Individuals\Models\Individual;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DistrictTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_create_a_district()
    {
        $country = Country::factory()->create();

        $district = District::factory()->create([
            'name' => 'Test District',
            'code' => 'TD01',
            'country_id' => $country->id,
            'description' => 'Test description',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('districts', [
            'name' => 'Test District',
            'code' => 'TD01',
            'country_id' => $country->id,
            'is_active' => true,
        ]);
    }

    #[Test]
    public function it_belongs_to_a_country()
    {
        $country = Country::factory()->create();
        $district = District::factory()->create(['country_id' => $country->id]);

        $this->assertInstanceOf(Country::class, $district->country);
        $this->assertEquals($country->id, $district->country->id);
    }

    #[Test]
    public function it_can_filter_by_active_status()
    {
        District::factory()->create(['is_active' => true]);
        District::factory()->create(['is_active' => false]);

        $activeDistricts = District::active()->get();

        $this->assertCount(1, $activeDistricts);
        $this->assertTrue($activeDistricts->first()->is_active);
    }

    #[Test]
    public function it_can_search_by_name_code_or_description()
    {
        District::factory()->create(['name' => 'Northern District', 'code' => 'ND01']);
        District::factory()->create(['name' => 'Southern District', 'description' => 'Contains northern cities']);
        District::factory()->create(['name' => 'Eastern District', 'code' => 'ED01']);

        $searchResults = District::search('north')->get();

        $this->assertCount(2, $searchResults);
    }

    #[Test]
    public function it_has_display_name_attribute()
    {
        $districtWithCode = District::factory()->create([
            'name' => 'Test District',
            'code' => 'TD01',
        ]);

        $districtWithoutCode = District::factory()->create([
            'name' => 'Test District',
            'code' => null,
        ]);

        $this->assertEquals('Test District (TD01)', $districtWithCode->display_name);
        $this->assertEquals('Test District', $districtWithoutCode->display_name);
    }

    #[Test]
    public function it_can_have_entities()
    {
        $district = District::factory()->create();
        $entity = Entity::factory()->create(['district_id' => $district->id]);

        $this->assertCount(1, $district->entities);
        $this->assertEquals($entity->id, $district->entities->first()->id);
    }

    #[Test]
    public function it_can_have_federations()
    {
        $district = District::factory()->create();
        $federation = Federation::factory()->create(['district_id' => $district->id]);

        $this->assertCount(1, $district->federations);
        $this->assertEquals($federation->id, $district->federations->first()->id);
    }

    #[Test]
    public function it_can_have_individuals()
    {
        $district = District::factory()->create();
        $individual = Individual::factory()->create(['district_id' => $district->id]);

        $this->assertCount(1, $district->individuals);
        $this->assertEquals($individual->id, $district->individuals->first()->id);
    }
}
