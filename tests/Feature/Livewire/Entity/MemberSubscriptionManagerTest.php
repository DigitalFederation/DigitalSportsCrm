<?php

namespace Tests\Feature\Livewire\Entity;

use App\Enums\UserGroupEnum;
use App\Livewire\Entity\MemberSubscriptionManager;
use App\Models\User;
use Domain\Entities\Models\Entity;
use Domain\Memberships\Services\ValidationPlanPrivilegeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MemberSubscriptionManagerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_shows_validation_plan_message_when_entity_lacks_privileges()
    {
        // Set locale to English for consistent testing
        app()->setLocale('en');

        // Create an entity user with correct group_id
        $user = User::factory()->create(['group_id' => UserGroupEnum::ENTITY->value]);
        $entity = Entity::factory()->create();
        $user->entities()->attach($entity);

        // Mock the validation plan service to return false
        $this->mock(ValidationPlanPrivilegeService::class, function ($mock) {
            $mock->shouldReceive('canSubscribeMembersToPackages')
                ->with(\Mockery::type(Entity::class))
                ->andReturn(false);
            $mock->shouldReceive('getValidationPlanReason')
                ->with(\Mockery::type(Entity::class), 'entity_member_subscriptions')
                ->andReturn('No active affiliation found');
        });

        // Act as the entity user
        $this->actingAs($user);

        // Test the component - check for the actual rendered content
        Livewire::test(MemberSubscriptionManager::class, ['insurance_filter' => false])
            ->assertSee('You cannot subscribe members to packages. No active affiliation found');
    }

    #[Test]
    public function it_disables_actions_when_entity_lacks_validation_plan_privileges()
    {
        // Set locale to English for consistent testing
        app()->setLocale('en');

        // Create an entity user with correct group_id
        $user = User::factory()->create(['group_id' => UserGroupEnum::ENTITY->value]);
        $entity = Entity::factory()->create();
        $user->entities()->attach($entity);

        // Mock the validation plan service to return false
        $this->mock(ValidationPlanPrivilegeService::class, function ($mock) {
            $mock->shouldReceive('canSubscribeMembersToPackages')
                ->with(\Mockery::type(Entity::class))
                ->andReturn(false);
            $mock->shouldReceive('getValidationPlanReason')
                ->with(\Mockery::type(Entity::class), 'entity_member_subscriptions')
                ->andReturn('No active affiliation found');
        });

        // Act as the entity user
        $this->actingAs($user);

        // Test the component
        $component = Livewire::test(MemberSubscriptionManager::class, ['insurance_filter' => false]);

        // Assert that the component has the correct properties
        $component->assertSet('hasValidationPlanPrivileges', false)
            ->assertSet('validationPlanMessage', 'You cannot subscribe members to packages. No active affiliation found');
    }

    #[Test]
    public function it_allows_actions_when_entity_has_validation_plan_privileges()
    {
        // Set locale to English for consistent testing
        app()->setLocale('en');

        // Create an entity user with correct group_id
        $user = User::factory()->create(['group_id' => UserGroupEnum::ENTITY->value]);
        $entity = Entity::factory()->create();
        $user->entities()->attach($entity);

        // Mock the validation plan service to return true
        $this->mock(ValidationPlanPrivilegeService::class, function ($mock) {
            $mock->shouldReceive('canSubscribeMembersToPackages')
                ->with(\Mockery::type(Entity::class))
                ->andReturn(true);
        });

        // Act as the entity user
        $this->actingAs($user);

        // Test the component
        $component = Livewire::test(MemberSubscriptionManager::class, ['insurance_filter' => false]);

        // Assert that the component has the correct properties
        $component->assertSet('hasValidationPlanPrivileges', true)
            ->assertSet('validationPlanMessage', '')
            ->assertDontSee('Validation Plan Required');
    }
}
