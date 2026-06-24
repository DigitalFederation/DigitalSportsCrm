<?php

use App\Jobs\CancelExpirationMembershipsJob;
use Domain\Memberships\Models\Membership;
use Domain\Memberships\States\ActiveMembershipState;
use Domain\Memberships\States\ActiveToExpiredTransition;
use Domain\Memberships\States\ExpiredMembershipState;
use Illuminate\Support\Facades\Log;

use function Pest\Laravel\artisan;

beforeEach(function () {
    // Reference the seeder pattern seen in other tests
    artisan('db:seed --class=RoleAndPermissionSeeder');
    artisan('db:seed --class=UserGroupSeeder');
});

it('cancels expired memberships', function () {
    // Create active memberships with expired dates
    $expiredMembership = Membership::factory()->create([
        'current_term_ends_at' => now()->subDay(),
        'status_class' => ActiveMembershipState::class,
    ]);

    $activeMembership = Membership::factory()->create([
        'current_term_ends_at' => now()->addDay(),
        'status_class' => ActiveMembershipState::class,
    ]);

    // Run the job
    (new CancelExpirationMembershipsJob)->handle(new ActiveToExpiredTransition);

    // Assert expired membership was processed
    expect($expiredMembership->fresh()->status_class)
        ->toBe(ExpiredMembershipState::class);

    // Assert active membership was not affected
    expect($activeMembership->fresh()->status_class)
        ->toBe(ActiveMembershipState::class);
});

it('logs errors when cancellation fails', function () {
    // Create an expired membership
    $expiredMembership = Membership::factory()->create([
        'current_term_ends_at' => now()->subDay(),
        'status_class' => ActiveMembershipState::class,
    ]);

    // Create a failing transition
    $failingTransition = new class extends ActiveToExpiredTransition
    {
        public function __invoke(Membership $membership): Membership
        {
            throw new Exception('Test error message');
        }
    };

    // Mock the Log facade
    Log::spy();

    // Run the job with the failing transition
    (new CancelExpirationMembershipsJob)->handle($failingTransition);

    // Assert error was logged
    Log::shouldHaveReceived('error')
        ->with('Test error message')
        ->once();
});
