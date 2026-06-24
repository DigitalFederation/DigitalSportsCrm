<?php

namespace App\Console\Commands;

use Domain\Entities\Models\EntityAthlete;
use Domain\Entities\States\ActiveEntityProfessionalRoleState;
use Domain\Entities\States\PendingEntityProfessionalRoleState;
use Illuminate\Console\Command;

class AuditAthleteSportViolationsCommand extends Command
{
    protected $signature = 'audit:athlete-sport-violations {--fix : Automatically fix violations by keeping only the most recent association}';

    protected $description = 'Find athletes who are associated with multiple entities for the same sport (violation of exclusivity rule)';

    public function handle(): int
    {
        $this->info('Auditing athlete sport exclusivity violations...');
        $this->newLine();

        $violations = $this->findViolations();

        if ($violations->isEmpty()) {
            $this->info('No violations found. All athletes comply with the sport exclusivity rule.');

            return self::SUCCESS;
        }

        $this->error("Found {$violations->count()} athletes with violations:");
        $this->newLine();

        $tableData = [];
        foreach ($violations as $violation) {
            $tableData[] = [
                'individual_id' => $violation->individual_id,
                'individual_name' => $violation->individual_name,
                'sport_id' => $violation->sport_id,
                'sport_name' => $violation->sport_name,
                'entity_count' => $violation->entity_count,
            ];
        }

        $this->table(
            ['Individual ID', 'Individual Name', 'Sport ID', 'Sport', 'Entities Count'],
            $tableData
        );

        $this->newLine();

        // Show detailed breakdown
        foreach ($violations as $violation) {
            $this->warn("Athlete: {$violation->individual_name} (ID: {$violation->individual_id})");
            $this->warn("Sport: {$violation->sport_name} (ID: {$violation->sport_id})");

            $associations = EntityAthlete::where('individual_id', $violation->individual_id)
                ->where('sport_id', $violation->sport_id)
                ->whereIn('status_class', [
                    ActiveEntityProfessionalRoleState::class,
                    PendingEntityProfessionalRoleState::class,
                ])
                ->with('entity')
                ->orderBy('created_at', 'desc')
                ->get();

            $this->table(
                ['Entity ID', 'Entity Name', 'Status', 'Created At'],
                $associations->map(fn ($a) => [
                    $a->entity_id,
                    $a->entity_name,
                    class_basename($a->status_class),
                    $a->created_at->format('Y-m-d H:i:s'),
                ])->toArray()
            );
            $this->newLine();
        }

        if ($this->option('fix')) {
            $this->fixViolations($violations);
        } else {
            $this->info('Run with --fix to automatically resolve violations by keeping only the most recent association.');
        }

        return self::FAILURE;
    }

    private function findViolations()
    {
        return EntityAthlete::select('individual_id', 'individual_name', 'sport_id', 'sport_name')
            ->selectRaw('COUNT(DISTINCT entity_id) as entity_count')
            ->whereIn('status_class', [
                ActiveEntityProfessionalRoleState::class,
                PendingEntityProfessionalRoleState::class,
            ])
            ->groupBy('individual_id', 'individual_name', 'sport_id', 'sport_name')
            ->havingRaw('COUNT(DISTINCT entity_id) > 1')
            ->get();
    }

    private function fixViolations($violations): void
    {
        if (! $this->confirm('This will cancel all but the most recent association for each violation. Continue?')) {
            return;
        }

        $fixed = 0;

        foreach ($violations as $violation) {
            $associations = EntityAthlete::where('individual_id', $violation->individual_id)
                ->where('sport_id', $violation->sport_id)
                ->whereIn('status_class', [
                    ActiveEntityProfessionalRoleState::class,
                    PendingEntityProfessionalRoleState::class,
                ])
                ->orderBy('created_at', 'desc')
                ->get();

            // Keep the first (most recent), cancel the rest
            $toCancel = $associations->skip(1);

            foreach ($toCancel as $association) {
                $association->update([
                    'status_class' => \Domain\Entities\States\CanceledEntityProfessionalRoleState::class,
                ]);

                $this->line("Canceled: {$association->individual_name} at {$association->entity_name} for {$association->sport_name}");
                $fixed++;
            }
        }

        $this->newLine();
        $this->info("Fixed {$fixed} violations by canceling older associations.");
    }
}
