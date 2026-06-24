<?php

namespace App\Console\Commands;

use Domain\Memberships\Actions\ExpireMemberSubscriptionAction;
use Domain\Memberships\Models\MemberSubscription;
use Domain\Memberships\States\ActiveMemberSubscriptionState;
use Illuminate\Console\Command;

class ExpireMemberSubscriptionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:ExpireMemberSubscriptions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire member subscriptions that are past their end date.';

    /**
     * Execute the console command.
     */
    public function handle(ExpireMemberSubscriptionAction $expireSubscription): int
    {
        $subscriptions = MemberSubscription::where('end_date', '<', now())
            ->where('status_class', ActiveMemberSubscriptionState::class)
            ->get();

        $this->info(sprintf(
            'Found %d active member subscription(s) to expire',
            $subscriptions->count()
        ));

        \Log::info('Found member subscriptions to expire', [
            'count' => $subscriptions->count(),
            'subscription_ids' => $subscriptions->pluck('id')->toArray(),
        ]);

        $successCount = 0;
        $failureCount = 0;

        $subscriptions->each(function ($subscription) use ($expireSubscription, &$successCount, &$failureCount) {
            try {
                $this->info(sprintf(
                    'Processing subscription ID: %s (Expired on: %s)',
                    $subscription->id,
                    $subscription->end_date->toDateString()
                ));

                $expireSubscription($subscription);
                $successCount++;

                $this->info(sprintf(
                    'Successfully expired subscription ID: %s',
                    $subscription->id
                ));
            } catch (\Exception $e) {
                $failureCount++;
                $this->error(sprintf(
                    'Failed to expire subscription ID: %s - Error: %s',
                    $subscription->id,
                    $e->getMessage()
                ));

                \Log::error('Failed to expire member subscription', [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                ]);
            }
        });

        $this->info('Finished member subscription expiration process');
        \Log::info('Completed member subscription expiration process', [
            'total_processed' => $subscriptions->count(),
            'success_count' => $successCount,
            'failure_count' => $failureCount,
        ]);

        return Command::SUCCESS;
    }
}
