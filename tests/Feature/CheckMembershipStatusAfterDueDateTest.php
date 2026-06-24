<?php

use App\Jobs\CancelExpirationMembershipsJob;
use Domain\Memberships\Models\Membership;
use Domain\Memberships\States\ActiveMembershipState;
use Domain\Memberships\States\ActiveToExpiredTransition;
use Domain\Memberships\States\ExpiredMembershipState;

it('executes the cancel expiration memberships command successfully', function () {
    // Act
    $this->artisan('memberships:cancel-expiration')
        ->expectsOutput('Memberships canceled successfully.')
        ->assertExitCode(0);
});

it('dispatches the cancel expiration memberships job', function () {
    Queue::fake();

    // Act
    $this->artisan('memberships:cancel-expiration');

    // Assert
    Queue::assertPushed(CancelExpirationMembershipsJob::class);
});

it('expires memberships with past expiration dates', function () {
    // Arrange: Create memberships with past expiration dates
    $membership = Membership::factory()->create([
        'current_term_ends_at' => now()->subDay(),
        'status_class' => ActiveMembershipState::class,
    ]);

    $job = new CancelExpirationMembershipsJob;

    // Act: Execute the job
    $job->handle(new ActiveToExpiredTransition);

    // Refresh the instance to get the updated state
    $membership->refresh();

    // Assert: Check if the membership status has been updated
    expect($membership->status_class)->toBe(ExpiredMembershipState::class);
});
