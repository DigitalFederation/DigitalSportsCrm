<?php

namespace App\Console\Commands;

use Domain\Certifications\Actions\ExpireCertificationAttributedAction;
use Domain\Certifications\Models\CertificationAttributed;
use Domain\Certifications\States\ActiveCertificationAttributedState;
use Illuminate\Console\Command;

class ExpireCertificationAttributedCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:ExpireCertifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to expire certifications when they are past their expiration date.';

    /**
     * Execute the console command.
     */
    public function handle(ExpireCertificationAttributedAction $expireCertification): int
    {
        $certifications = CertificationAttributed::where('current_term_ends_at', '<', now())
            ->where('status_class', ActiveCertificationAttributedState::class)
            ->with(['entity.users', 'individual.user'])
            ->get();

        $this->info(sprintf(
            'Found %d active certification(s) to expire',
            $certifications->count()
        ));

        \Log::info('Found certifications to expire', [
            'count' => $certifications->count(),
            'certification_ids' => $certifications->pluck('id')->toArray(),
        ]);

        $successCount = 0;
        $failureCount = 0;

        $certifications->each(function ($certification) use ($expireCertification, &$successCount, &$failureCount) {
            try {
                $this->info(sprintf(
                    'Processing certification ID: %s (Expired on: %s)',
                    $certification->id,
                    $certification->current_term_ends_at->toDateString()
                ));

                $expireCertification($certification);
                $successCount++;

                $this->info(sprintf(
                    'Successfully expired certification ID: %s',
                    $certification->id
                ));
            } catch (\Exception $e) {
                $failureCount++;
                $this->error(sprintf(
                    'Failed to expire certification ID: %s - Error: %s',
                    $certification->id,
                    $e->getMessage()
                ));

                \Log::error('Failed to expire certification', [
                    'certification_id' => $certification->id,
                    'error' => $e->getMessage(),
                ]);
            }
        });

        $this->info('Finished certification expiration process');
        \Log::info('Completed certification expiration process', [
            'total_processed' => $certifications->count(),
            'success_count' => $successCount,
            'failure_count' => $failureCount,
        ]);

        return $failureCount > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
