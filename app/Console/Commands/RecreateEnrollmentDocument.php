<?php

namespace App\Console\Commands;

use Domain\Documents\Models\Document;
use Domain\EvtEvents\Actions\CreateEnrollmentPaymentDocumentAction;
use Domain\EvtEvents\Actions\GetWaitingListSelectedIndividualsAction;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Services\EnrollmentsCostCalculationService;
use Domain\Federations\Models\Federation;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RecreateEnrollmentDocument extends Command
{
    protected $signature = 'enrollment:recalculate-document {enrollmentId} {--dry-run} {--force}';

    protected $description = 'Recalculate and recreate a payment document for an enrollment';

    public function __construct(
        private EnrollmentsCostCalculationService $costCalculationService,
        private GetWaitingListSelectedIndividualsAction $getSelectedIndividualsAction,
        private CreateEnrollmentPaymentDocumentAction $createDocumentAction
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $enrollmentId = $this->argument('enrollmentId');
        $isDryRun = $this->option('dry-run');
        $forceRecreate = $this->option('force');

        $this->info("Starting document recalculation process for enrollment ID: {$enrollmentId}");
        $this->info($isDryRun ? 'DRY RUN MODE: No changes will be made to the database.' : 'LIVE MODE: Changes will be applied to the database.');

        DB::beginTransaction();

        try {
            $enrollment = Enrollment::with([
                'event',
                'athleteEnrollments.individual',
                'coachEnrollments.individual',
                'refereeEnrollments.individual',
                'teamOfficialEnrollments.individual',
            ])->findOrFail($enrollmentId);

            $event = $enrollment->event;
            $federation = Federation::findOrFail($enrollment->enrollable_id);

            $this->info("Event: {$event->name}");
            $this->info("Federation: {$federation->name}");

            // Create an Eloquent Collection with just this enrollment
            $enrollmentCollection = new EloquentCollection([$enrollment]);

            // Calculate total cost
            $totalCost = $this->costCalculationService->calculateTotalCost($event, $enrollmentCollection);
            $this->info("Calculated Total Cost: {$totalCost}");

            // Get selected individuals
            $selectedIndividuals = $this->getSelectedIndividualsAction->execute($enrollmentCollection);

            // Check for existing document
            $existingDocument = Document::where('owner_type', Enrollment::class)
                ->where('owner_id', $enrollment->id)
                ->first();

            if ($existingDocument && ! $forceRecreate) {
                $this->warn("An existing document (ID: {$existingDocument->id}) was found. Use --force to recreate.");
                DB::rollBack();

                return;
            }

            if (! $isDryRun) {
                // Delete existing document if it exists
                if ($existingDocument) {
                    $existingDocument->delete();
                    $this->info("Deleted existing document (ID: {$existingDocument->id})");
                }

                // Create new document
                $newDocument = $this->createDocumentAction->execute(
                    $event,
                    $enrollment,
                    $federation->id,
                    Federation::class,
                    $selectedIndividuals,
                    $totalCost,
                    null
                );

                $this->info("New Document ID: {$newDocument->id}");
                $this->info("New Document Total Value: {$newDocument->total_value}");
                $this->info('New Document Details:');
                foreach ($newDocument->details as $detail) {
                    $this->line("- {$detail->description}: {$detail->quantity} x {$detail->unit_value} = {$detail->total_value}");
                }

                DB::commit();
                $this->info('Changes have been saved to the database.');

                Log::info('Enrollment document recalculated and recreated', [
                    'enrollment_id' => $enrollment->id,
                    'event_id' => $event->id,
                    'federation_id' => $federation->id,
                    'old_document_id' => $existingDocument->id ?? null,
                    'new_document_id' => $newDocument->id,
                    'total_cost' => $totalCost,
                ]);
            } else {
                $this->info('Dry run completed. No changes were made to the database.');
                DB::rollBack();
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("An error occurred: {$e->getMessage()}");
            Log::error('Error recalculating enrollment document', [
                'enrollment_id' => $enrollmentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
