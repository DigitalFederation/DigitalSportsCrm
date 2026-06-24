<?php

namespace App\Console\Commands;

use Domain\Insurance\Actions\ExpireInsuranceAction;
use Domain\Insurance\Models\Insurance;
use Domain\Insurance\States\ActiveInsuranceState;
use Illuminate\Console\Command;

class ExpireInsurancesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:ExpireInsurances';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire insurances that are past their end date.';

    /**
     * Execute the console command.
     */
    public function handle(ExpireInsuranceAction $expireInsurance): int
    {
        $insurances = Insurance::where('end_date', '<', now())
            ->where('status_class', ActiveInsuranceState::class)
            ->get();

        $this->info(sprintf(
            'Found %d active insurance(s) to expire',
            $insurances->count()
        ));

        \Log::info('Found insurances to expire', [
            'count' => $insurances->count(),
            'insurance_ids' => $insurances->pluck('id')->toArray(),
        ]);

        $successCount = 0;
        $failureCount = 0;

        $insurances->each(function ($insurance) use ($expireInsurance, &$successCount, &$failureCount) {
            try {
                $this->info(sprintf(
                    'Processing insurance ID: %s (Expired on: %s)',
                    $insurance->id,
                    $insurance->end_date->toDateString()
                ));

                $expireInsurance($insurance);
                $successCount++;

                $this->info(sprintf(
                    'Successfully expired insurance ID: %s',
                    $insurance->id
                ));
            } catch (\Exception $e) {
                $failureCount++;
                $this->error(sprintf(
                    'Failed to expire insurance ID: %s - Error: %s',
                    $insurance->id,
                    $e->getMessage()
                ));

                \Log::error('Failed to expire insurance', [
                    'insurance_id' => $insurance->id,
                    'error' => $e->getMessage(),
                ]);
            }
        });

        $this->info('Finished insurance expiration process');
        \Log::info('Completed insurance expiration process', [
            'total_processed' => $insurances->count(),
            'success_count' => $successCount,
            'failure_count' => $failureCount,
        ]);

        return Command::SUCCESS;
    }
}
