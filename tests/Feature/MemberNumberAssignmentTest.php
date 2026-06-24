<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\User;
use Domain\Entities\Models\Entity;
use Domain\Individuals\Models\Individual;
use Domain\Memberships\Services\MemberNumberService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberNumberAssignmentTest extends TestCase
{
    use RefreshDatabase;

    private MemberNumberService $memberNumberService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->memberNumberService = new MemberNumberService;
    }

    public function test_assigns_member_number_to_individual()
    {
        $individual = Individual::factory()->create(['member_number' => null]);

        $this->memberNumberService->assignIndividualMemberNumber($individual);

        $this->assertNotNull($individual->fresh()->member_number);
        $this->assertEquals(1, $individual->fresh()->member_number);
    }

    public function test_assigns_member_number_to_entity()
    {
        $entity = Entity::factory()->create(['member_number' => null]);

        $this->memberNumberService->assignEntityMemberNumber($entity);

        $this->assertNotNull($entity->fresh()->member_number);
        $this->assertEquals(1, $entity->fresh()->member_number);
    }

    public function test_increments_counter_after_assignment()
    {
        $individual1 = Individual::factory()->create(['member_number' => null]);
        $individual2 = Individual::factory()->create(['member_number' => null]);

        $this->memberNumberService->assignIndividualMemberNumber($individual1);
        $this->memberNumberService->assignIndividualMemberNumber($individual2);

        $this->assertEquals(1, $individual1->fresh()->member_number);
        $this->assertEquals(2, $individual2->fresh()->member_number);
    }

    public function test_does_not_reassign_existing_member_number()
    {
        $individual = Individual::factory()->create(['member_number' => 999]);

        $this->memberNumberService->assignIndividualMemberNumber($individual);

        $this->assertEquals(999, $individual->fresh()->member_number);
    }

    public function test_handles_concurrent_assignments()
    {
        $individuals = Individual::factory()->count(5)->create(['member_number' => null]);
        $memberNumbers = [];

        // Simulate concurrent assignments
        foreach ($individuals as $individual) {
            $this->memberNumberService->assignIndividualMemberNumber($individual);
            $memberNumbers[] = $individual->fresh()->member_number;
        }

        // All member numbers should be unique
        $this->assertEquals(count($memberNumbers), count(array_unique($memberNumbers)));

        // Should be sequential from 1 to 5
        sort($memberNumbers);
        $this->assertEquals([1, 2, 3, 4, 5], $memberNumbers);
    }

    public function test_can_update_counter_value()
    {
        $this->memberNumberService->updateIndividualCounter(100);

        $individual = Individual::factory()->create(['member_number' => null]);
        $this->memberNumberService->assignIndividualMemberNumber($individual);

        $this->assertEquals(100, $individual->fresh()->member_number);
    }

    public function test_separate_counters_for_individuals_and_entities()
    {
        $individual = Individual::factory()->create(['member_number' => null]);
        $entity = Entity::factory()->create(['member_number' => null]);

        $this->memberNumberService->assignIndividualMemberNumber($individual);
        $this->memberNumberService->assignEntityMemberNumber($entity);

        // Both should have member number 1 since they use separate counters
        $this->assertEquals(1, $individual->fresh()->member_number);
        $this->assertEquals(1, $entity->fresh()->member_number);
    }

    public function test_get_current_counter_values()
    {
        $this->assertEquals(1, $this->memberNumberService->getCurrentIndividualCounter());
        $this->assertEquals(1, $this->memberNumberService->getCurrentEntityCounter());

        $this->memberNumberService->updateIndividualCounter(50);
        $this->memberNumberService->updateEntityCounter(75);

        $this->assertEquals(50, $this->memberNumberService->getCurrentIndividualCounter());
        $this->assertEquals(75, $this->memberNumberService->getCurrentEntityCounter());
    }

    public function test_auto_assignment_skips_manually_assigned_individual_numbers()
    {
        // Simulate an admin manually assigning member_number 1 to an individual
        Individual::factory()->create(['member_number' => 1]);

        // Counter starts at 1, but that number is already taken
        $individual = Individual::factory()->create(['member_number' => null]);
        $this->memberNumberService->assignIndividualMemberNumber($individual);

        // Should skip 1 and assign 2
        $this->assertEquals(2, $individual->fresh()->member_number);

        // Counter should now be at 3
        $this->assertEquals(3, $this->memberNumberService->getCurrentIndividualCounter());
    }

    public function test_auto_assignment_skips_manually_assigned_entity_numbers()
    {
        // Simulate an admin manually assigning member_number 1 to an entity
        Entity::factory()->create(['member_number' => 1]);

        // Counter starts at 1, but that number is already taken
        $entity = Entity::factory()->create(['member_number' => null]);
        $this->memberNumberService->assignEntityMemberNumber($entity);

        // Should skip 1 and assign 2
        $this->assertEquals(2, $entity->fresh()->member_number);

        // Counter should now be at 3
        $this->assertEquals(3, $this->memberNumberService->getCurrentEntityCounter());
    }

    public function test_auto_assignment_skips_multiple_consecutive_taken_numbers()
    {
        // Manually assign numbers 1, 2, and 3
        Individual::factory()->create(['member_number' => 1]);
        Individual::factory()->create(['member_number' => 2]);
        Individual::factory()->create(['member_number' => 3]);

        $individual = Individual::factory()->create(['member_number' => null]);
        $this->memberNumberService->assignIndividualMemberNumber($individual);

        // Should skip 1, 2, 3 and assign 4
        $this->assertEquals(4, $individual->fresh()->member_number);
    }

    public function test_duplicate_member_number_returns_validation_error()
    {
        $this->artisan('db:seed --class=RoleAndPermissionSeeder');
        $this->artisan('db:seed --class=UserGroupSeeder');

        $adminGroup = Group::where('code', 'ADMIN')->first();
        $admin = User::factory()->create([
            'group_id' => $adminGroup->id,
            'active' => true,
        ]);
        $admin->assignRole('admin');

        $existingIndividual = Individual::factory()->create(['member_number' => 115]);
        $individualToEdit = Individual::factory()->create(['member_number' => 200]);

        $response = $this->actingAs($admin)->put(
            route('admin.individual.update', $individualToEdit->id),
            [
                'name' => $individualToEdit->name,
                'surname' => $individualToEdit->surname,
                'country_id' => $individualToEdit->country_id,
                'member_number' => 115,
            ]
        );

        $response->assertSessionHasErrors('member_number');
    }

    public function test_saving_same_member_number_on_own_record_succeeds()
    {
        $this->artisan('db:seed --class=RoleAndPermissionSeeder');
        $this->artisan('db:seed --class=UserGroupSeeder');

        $adminGroup = Group::where('code', 'ADMIN')->first();
        $admin = User::factory()->create([
            'group_id' => $adminGroup->id,
            'active' => true,
        ]);
        $admin->assignRole('admin');

        $individual = Individual::factory()->create(['member_number' => 200]);

        $response = $this->actingAs($admin)->put(
            route('admin.individual.update', $individual->id),
            [
                'name' => $individual->name,
                'surname' => $individual->surname,
                'country_id' => $individual->country_id,
                'member_number' => 200,
            ]
        );

        $response->assertSessionDoesntHaveErrors('member_number');
    }

    public function test_soft_deleted_member_number_does_not_block_edit()
    {
        $this->artisan('db:seed --class=RoleAndPermissionSeeder');
        $this->artisan('db:seed --class=UserGroupSeeder');

        $adminGroup = Group::where('code', 'ADMIN')->first();
        $admin = User::factory()->create([
            'group_id' => $adminGroup->id,
            'active' => true,
        ]);
        $admin->assignRole('admin');

        // Create an individual with member_number 500 and soft-delete it
        $deletedIndividual = Individual::factory()->create(['member_number' => 500]);
        $deletedIndividual->delete();

        // Create another individual and try to edit it with the same member_number
        $activeIndividual = Individual::factory()->create(['member_number' => 600]);

        $response = $this->actingAs($admin)->put(
            route('admin.individual.update', $activeIndividual->id),
            [
                'name' => $activeIndividual->name,
                'surname' => $activeIndividual->surname,
                'country_id' => $activeIndividual->country_id,
                'member_number' => 500,
            ]
        );

        $response->assertSessionDoesntHaveErrors('member_number');
    }
}
