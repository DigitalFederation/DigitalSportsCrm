<?php

namespace Domain\EvtEvents\Actions;

use App\Enums\EvtDisciplineEnrollmentTypeEnum;
use Domain\EvtEvents\Models\Discipline;

class ValidateTeamCompositionAction
{
    public function execute(Discipline $discipline, array $selectedIndividuals, array &$errorMessages): bool
    {
        if ($discipline->enrollment_type === EvtDisciplineEnrollmentTypeEnum::individual->value) {
            return true;
        }

        // Validate requirements exist for relay
        if ($discipline->enrollment_type === EvtDisciplineEnrollmentTypeEnum::relay->value) {
            if (! $discipline->team_composition_requirements) {
                $errorMessages[] = 'Team composition requirements are mandatory for relay disciplines.';

                return false;
            }
        }

        $requirements = $discipline->team_composition_requirements;

        // Validate requirements structure
        if (! is_array($requirements) || empty($requirements)) {
            $errorMessages[] = 'Invalid team composition requirements format.';

            return false;
        }

        // Validate all individuals have gender
        foreach ($selectedIndividuals as $indiv) {
            if (! isset($indiv['gender'])) {
                $errorMessages[] = 'All participants must have gender specified.';

                return false;
            }
        }

        $composition = $this->getCurrentComposition($selectedIndividuals);

        // Specific relay validations
        if ($discipline->enrollment_type === EvtDisciplineEnrollmentTypeEnum::relay->value) {
            // Check total participants
            $totalParticipants = array_sum($composition);
            if ($totalParticipants !== array_sum($requirements)) {
                $errorMessages[] = sprintf(
                    'Relay team requires exactly %d participants (%s).',
                    array_sum($requirements),
                    $this->formatRequirements($requirements)
                );

                return false;
            }

            // Validate exact gender requirements
            foreach ($requirements as $gender => $required) {
                $actual = $composition[$gender] ?? 0;
                if ($actual !== $required) {
                    $errorMessages[] = "Relay team requires exactly {$required} {$gender} participants, got {$actual}.";

                    return false;
                }
            }

            return true;
        }

        // Regular team validations
        foreach ($requirements as $gender => $maxCount) {
            $actual = $composition[$gender] ?? 0;
            if ($actual > $maxCount) {
                $errorMessages[] = "Team can have maximum {$maxCount} {$gender} participants, got {$actual}.";

                return false;
            }
        }

        return true;
    }

    private function getCurrentComposition(array $individuals): array
    {
        return collect($individuals)
            ->groupBy(fn ($i) => strtolower($i['gender']))
            ->map(fn ($group) => $group->count())
            ->toArray();
    }

    private function formatRequirements(array $requirements): string
    {
        return collect($requirements)
            ->map(fn ($count, $gender) => "{$count} {$gender}")
            ->join(', ');
    }
}
