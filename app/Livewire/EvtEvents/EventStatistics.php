<?php

namespace App\Livewire\EvtEvents;

use App\Enums\EvtAthleteEnrollmentStatusEnum;
use App\Enums\EvtDisciplineGenderEnum;
use Domain\EvtEvents\Models\Event;
use Illuminate\Support\Collection;
use Livewire\Component;

class EventStatistics extends Component
{
    public Event $event;
    public $activeTab = 'country'; // Default tab

    protected $athleteEnrollments;
    protected $coachEnrollments;
    protected $officialsEnrollments;
    protected $refereeEnrollments;

    public function mount(Event $event)
    {
        $this->event = $event;
    }

    protected function loadEnrollments(): void
    {
        if ($this->athleteEnrollments !== null) {
            return;
        }

        $this->event->load([
            'athleteEnrollments.individual.federations.country',
            'athleteEnrollments.individual.entities.country',
            'athleteEnrollments.individual.country',
            'athleteEnrollments.discipline',
            'athleteEnrollments.federation.country',
            'athleteEnrollments.entity.country',
            'athleteEnrollments.enrollment.enrollable',
            'coachEnrollments.federation.country',
            'coachEnrollments.individual.country',
            'officialsEnrollments.federation.country',
            'officialsEnrollments.individual.country',
            'refereeEnrollments.federation.country',
            'refereeEnrollments.individual.country',
        ]);

        $this->athleteEnrollments = $this->event->athleteEnrollments;
        $this->coachEnrollments = $this->event->coachEnrollments;
        $this->officialsEnrollments = $this->event->officialsEnrollments;
        $this->refereeEnrollments = $this->event->refereeEnrollments;
    }

    protected function getAthleteEnrollments(): Collection
    {
        $this->loadEnrollments();

        return $this->athleteEnrollments ?? collect();
    }

    protected function getCoachEnrollments(): Collection
    {
        $this->loadEnrollments();

        return $this->coachEnrollments ?? collect();
    }

    protected function getOfficialsEnrollments(): Collection
    {
        $this->loadEnrollments();

        return $this->officialsEnrollments ?? collect();
    }

    protected function getRefereeEnrollments(): Collection
    {
        $this->loadEnrollments();

        return $this->refereeEnrollments ?? collect();
    }

    public function getStatistics()
    {
        return [
            'summary' => $this->getSummaryStats(),
            'byCountry' => $this->getCountryStats(),
            'byEntity' => $this->getEntityStats(),
            'byDiscipline' => $this->getDisciplineStats(),
        ];
    }

    protected function getSummaryStats()
    {
        // Filter enrollments by status first
        $validEnrollments = $this->getAthleteEnrollments()
            ->filter(fn ($entry) => $entry->discipline_id !== null)
            ->filter(
                fn ($entry) => $entry->status_class === EvtAthleteEnrollmentStatusEnum::DISCIPLINE_ASSIGNED ||
                    $entry->status_class === EvtAthleteEnrollmentStatusEnum::COMPLETED
            );

        $individualEntries = $validEnrollments
            ->whereNull('team_identifier');

        // Get all individuals (for unique counting)
        $individuals = $validEnrollments
            ->pluck('individual')
            ->filter(); // Remove any null values

        // Count unique athletes by gender
        $totalUniqueFemale = $individuals
            ->where('gender', 'female')
            ->unique('id')
            ->count();

        $totalUniqueMale = $individuals
            ->where('gender', 'male')
            ->unique('id')
            ->count();

        // Count of unique athletes (total)
        $totalUniqueAthletes = $validEnrollments
            ->pluck('individual.id')
            ->unique()
            ->count();

        // Count total entries by gender (not unique)
        $totalFemaleEntries = $individualEntries
            ->filter(fn ($entry) => $entry->individual && $entry->individual->gender === 'female')
            ->count();

        $totalMaleEntries = $individualEntries
            ->filter(fn ($entry) => $entry->individual && $entry->individual->gender === 'male')
            ->count();

        // Relay teams count
        $totalRelayTeams = $validEnrollments
            ->where('discipline.enrollment_type', 'relay')
            ->whereNotNull('team_identifier')
            ->groupBy(function ($entry) {
                return $entry->discipline_id . '_' . $entry->entity_id . '_' . $entry->team_identifier;
            })
            ->map(function ($entries) {
                if ($entries->isEmpty()) {
                    return 0;
                }

                $discipline = $entries->first()->discipline;
                $requirements = $discipline->team_composition_requirements;

                if (empty($requirements)) {
                    return 0;
                }

                // Count athletes by gender
                $maleCount = $entries->filter(
                    fn ($entry) => $entry->individual && $entry->individual->gender === 'male'
                )->count();

                $femaleCount = $entries->filter(
                    fn ($entry) => $entry->individual && $entry->individual->gender === 'female'
                )->count();

                // Check if the team meets ALL requirements
                foreach ($requirements as $gender => $required) {
                    $actual = $gender === 'male' ? $maleCount : $femaleCount;
                    if ($actual < $required) {
                        return 0; // Incomplete team
                    }
                }

                return 1; // Complete team
            })
            ->sum();

        // Teams count
        $totalTeams = $validEnrollments
            ->where('discipline.enrollment_type', 'team')
            ->whereNotNull('team_identifier')
            ->groupBy(function ($entry) {
                return $entry->discipline_id . '_' . $entry->entity_id . '_' . $entry->team_identifier;
            })
            ->map(function ($entries) {
                if ($entries->isEmpty()) {
                    return 0;
                }

                $discipline = $entries->first()->discipline;
                $requirements = $discipline->team_composition_requirements;

                if (empty($requirements)) {
                    return 0;
                }

                // Count athletes by gender
                $maleCount = $entries->filter(
                    fn ($entry) => $entry->individual && $entry->individual->gender === 'male'
                )->count();

                $femaleCount = $entries->filter(
                    fn ($entry) => $entry->individual && $entry->individual->gender === 'female'
                )->count();

                // Check if the team meets ALL requirements
                foreach ($requirements as $gender => $required) {
                    $actual = $gender === 'male' ? $maleCount : $femaleCount;
                    if ($actual < $required) {
                        return 0; // Incomplete team
                    }
                }

                return 1; // Complete team
            })
            ->sum();

        // Total discipline enrollments (this is the TOTAL entries - all enrollments across disciplines)
        $totalEntries = $validEnrollments->count();

        // Athlete entries (unique athletes + teams + relays)
        $athleteEntries = $totalUniqueAthletes + $totalRelayTeams + $totalTeams;

        return [
            'total_entries' => $totalEntries, // All enrollments across disciplines
            'athlete_entries' => $athleteEntries, // Unique athletes + teams + relays
            'total_athletes' => $totalUniqueAthletes,
            'total_unique_female' => $totalUniqueFemale, // Total unique female athletes
            'total_unique_male' => $totalUniqueMale, // Total unique male athletes
            'individual_female' => $totalFemaleEntries, // All female entries (not unique)
            'individual_male' => $totalMaleEntries, // All male entries (not unique)
            'relay_stats' => $this->getRelayStats(),
            'team_stats' => $this->getTeamStats(),
        ];
    }

    protected function getGenderCount($gender)
    {
        return $this->getAthleteEnrollments()
            ->filter(fn ($entry) => $entry->individual)
            ->filter(fn ($entry) => $entry->discipline_id !== null)
            ->where('individual.gender', $gender)
            ->groupBy('individual.id')
            ->count();
    }

    protected function getRelayStats()
    {
        return [
            'male' => $this->getTeamTypeCount('relay', EvtDisciplineGenderEnum::MALE),
            'female' => $this->getTeamTypeCount('relay', EvtDisciplineGenderEnum::FEMALE),
            'mixed' => $this->getTeamTypeCount('relay', EvtDisciplineGenderEnum::MIXED),
        ];
    }

    protected function getTeamStats()
    {
        return [
            'male' => $this->getTeamTypeCount('team', EvtDisciplineGenderEnum::MALE),
            'female' => $this->getTeamTypeCount('team', EvtDisciplineGenderEnum::FEMALE),
            'mixed' => $this->getTeamTypeCount('team', EvtDisciplineGenderEnum::MIXED),
        ];
    }

    protected function getTeamTypeCount($type, EvtDisciplineGenderEnum $gender)
    {
        $entries = $this->getAthleteEnrollments()
            ->filter(
                fn ($entry) => $entry->discipline_id !== null &&
                    in_array($entry->status_class, [
                        EvtAthleteEnrollmentStatusEnum::DISCIPLINE_ASSIGNED,
                        EvtAthleteEnrollmentStatusEnum::COMPLETED,
                    ])
            )
            ->where('discipline.enrollment_type', $type)
            ->whereNotNull('team_identifier');

        // Debug: Print the count of entries that match our initial criteria
        \Log::info("Initial entries count for {$type} {$gender->value}: " . $entries->count());

        if ($entries->isEmpty()) {
            \Log::info("No entries found for {$type} {$gender->value}");

            return 0;
        }

        // Group entries by team
        $teams = $entries->groupBy(function ($entry) {
            return $entry->discipline_id . '_' . $entry->entity_id . '_' . $entry->team_identifier;
        });

        \Log::info('Number of unique teams found: ' . $teams->count());

        return $teams->map(function ($entries) use ($gender) {
            $discipline = $entries->first()->discipline;
            $requirements = $discipline->team_composition_requirements;

            if (empty($requirements)) {
                \Log::info('Team skipped - no requirements');

                return 0;
            }

            // Count athletes by gender
            $maleCount = $entries->filter(
                fn ($entry) => $entry->individual && $entry->individual->gender === 'male'
            )->count();

            $femaleCount = $entries->filter(
                fn ($entry) => $entry->individual && $entry->individual->gender === 'female'
            )->count();

            \Log::info("Team composition - Male: {$maleCount}, Female: {$femaleCount}, Requirements: " . json_encode($requirements));

            // For mixed teams
            if ($gender === EvtDisciplineGenderEnum::MIXED) {
                // Check if team has both male and female athletes AND meets requirements
                if ($maleCount > 0 && $femaleCount > 0) {
                    foreach ($requirements as $reqGender => $required) {
                        $actual = $reqGender === 'male' ? $maleCount : $femaleCount;
                        if ($actual < $required) {
                            \Log::info("Mixed team rejected - doesn't meet {$reqGender} requirement: needed {$required}, has {$actual}");

                            return 0;
                        }
                    }
                    \Log::info('Mixed team accepted - meets all requirements');

                    return 1;
                }
                \Log::info("Mixed team rejected - doesn't have both genders");

                return 0;
            }

            // For male teams
            if ($gender === EvtDisciplineGenderEnum::MALE) {
                if ($femaleCount > 0 || ! isset($requirements['male'])) {
                    \Log::info('Male team rejected - has females or no male requirement');

                    return 0;
                }
                $result = $maleCount >= $requirements['male'] ? 1 : 0;
                \Log::info('Male team ' . ($result ? 'accepted' : 'rejected') . " - has {$maleCount} males, needs {$requirements['male']}");

                return $result;
            }

            // For female teams
            if ($gender === EvtDisciplineGenderEnum::FEMALE) {
                if ($maleCount > 0 || ! isset($requirements['female'])) {
                    \Log::info('Female team rejected - has males or no female requirement');

                    return 0;
                }
                $result = $femaleCount >= $requirements['female'] ? 1 : 0;
                \Log::info('Female team ' . ($result ? 'accepted' : 'rejected') . " - has {$femaleCount} females, needs {$requirements['female']}");

                return $result;
            }

            return 0;
        })->sum();
    }

    protected function getCountryStats()
    {
        // First, create maps of coaches and officials by country
        $coachesByCountry = $this->getCoachEnrollments()
            ->filter(fn ($entry) => $entry->individual && $entry->individual->country)
            ->groupBy('individual.country.name')
            ->map(fn ($entries) => $entries->count());

        $officialsByCountry = $this->getOfficialsEnrollments()
            ->filter(fn ($entry) => $entry->individual && $entry->individual->relationLoaded('country') && $entry->individual->country)
            ->groupBy('individual.country.name')
            ->map(fn ($entries) => $entries->count());

        return $this->getAthleteEnrollments()
            // Make sure we only process entries that have an individual with a country
            // and have the correct status
            ->filter(
                fn ($entry) => $entry->individual &&
                    $entry->individual->relationLoaded('country') &&
                    $entry->individual->country &&
                    $entry->discipline_id !== null &&
                    in_array($entry->status_class, [
                        EvtAthleteEnrollmentStatusEnum::DISCIPLINE_ASSIGNED,
                        EvtAthleteEnrollmentStatusEnum::COMPLETED,
                    ])
            )
            // Group by country name
            ->groupBy('individual.country.name')
            ->map(function ($entries, $countryName) use ($coachesByCountry, $officialsByCountry) {
                // Get country ISO code for flag
                $countryIso = $entries->first()->individual->country->iso ?? '';

                // Get unique athletes for this country
                $athletes = $entries->pluck('individual')
                    ->unique('id')
                    ->filter(); // Remove any null values

                // Count athletes by gender
                $maleAthletes = $athletes->where('gender', 'male')->count();
                $femaleAthletes = $athletes->where('gender', 'female')->count();

                // Individual entries (no team identifier)
                $individualEntries = $entries->whereNull('team_identifier')->count();

                // Relay teams (unique team identifiers for relay type)
                $relayTeams = $entries
                    ->where('discipline.enrollment_type', 'relay')
                    ->whereNotNull('team_identifier')
                    ->groupBy(function ($entry) {
                        return $entry->discipline_id . '_' . $entry->entity_id . '_' . $entry->team_identifier;
                    })
                    ->map(function ($entries) {
                        if ($entries->isEmpty()) {
                            return 0;
                        }

                        $discipline = $entries->first()->discipline;
                        $requirements = $discipline->team_composition_requirements;

                        if (empty($requirements)) {
                            return 0;
                        }

                        // Count athletes by gender
                        $maleCount = $entries->filter(
                            fn ($entry) => $entry->individual && $entry->individual->gender === 'male'
                        )->count();

                        $femaleCount = $entries->filter(
                            fn ($entry) => $entry->individual && $entry->individual->gender === 'female'
                        )->count();

                        // Check if the team meets ALL requirements
                        foreach ($requirements as $gender => $required) {
                            $actual = $gender === 'male' ? $maleCount : $femaleCount;
                            if ($actual < $required) {
                                return 0; // Incomplete team
                            }
                        }

                        return 1; // Complete team
                    })
                    ->sum();

                // Team entries (unique team identifiers for team type)
                $teamEntries = $entries
                    ->where('discipline.enrollment_type', 'team')
                    ->whereNotNull('team_identifier')
                    ->groupBy(function ($entry) {
                        return $entry->discipline_id . '_' . $entry->entity_id . '_' . $entry->team_identifier;
                    })
                    ->map(function ($entries) {
                        if ($entries->isEmpty()) {
                            return 0;
                        }

                        $discipline = $entries->first()->discipline;
                        $requirements = $discipline->team_composition_requirements;

                        if (empty($requirements)) {
                            return 0;
                        }

                        // Count athletes by gender
                        $maleCount = $entries->filter(
                            fn ($entry) => $entry->individual && $entry->individual->gender === 'male'
                        )->count();

                        $femaleCount = $entries->filter(
                            fn ($entry) => $entry->individual && $entry->individual->gender === 'female'
                        )->count();

                        // Check if the team meets ALL requirements
                        foreach ($requirements as $gender => $required) {
                            $actual = $gender === 'male' ? $maleCount : $femaleCount;
                            if ($actual < $required) {
                                return 0; // Incomplete team
                            }
                        }

                        return 1; // Complete team
                    })
                    ->sum();

                // This is now the actual total entries (all enrollments across disciplines)
                // This represents the total number of discipline enrollments (not unique athletes)
                $totalEntries = $entries->count();

                // This represents unique athletes plus unique teams/relays
                $athleteEntries = $athletes->count() + $relayTeams + $teamEntries;

                // Get coaches and officials count for this country
                $totalCoaches = $coachesByCountry->get($countryName, 0);
                $totalOfficials = $officialsByCountry->get($countryName, 0);

                return [
                    'country_iso' => $countryIso,
                    'total_entries' => $totalEntries, // All enrollments across disciplines
                    'athlete_entries' => $athleteEntries, // Unique athletes + teams + relays
                    'entries' => $individualEntries, // Individual entries (no teams)
                    'individual_athletes' => $athletes->count(), // Unique individual athletes
                    'male_athletes' => $maleAthletes,
                    'female_athletes' => $femaleAthletes,
                    'relay_teams' => $relayTeams,
                    'team_athletes' => $teamEntries,
                    'total_coaches' => $totalCoaches,
                    'total_officials' => $totalOfficials,
                ];
            })
            ->filter(
                fn ($stats) => $stats['entries'] > 0 ||
                    $stats['relay_teams'] > 0 ||
                    $stats['team_athletes'] > 0 ||
                    $stats['total_coaches'] > 0 ||
                    $stats['total_officials'] > 0
            ); // Only show countries that have some participation
    }

    protected function getEntityStats()
    {
        // Group entries by federation that enrolled them
        $federationStats = $this->getAthleteEnrollments()
            ->filter(fn ($entry) => $entry->federation_id)
            ->filter(fn ($entry) => $entry->discipline_id !== null)
            ->filter(
                fn ($entry) => $entry->status_class === EvtAthleteEnrollmentStatusEnum::DISCIPLINE_ASSIGNED ||
                    $entry->status_class === EvtAthleteEnrollmentStatusEnum::COMPLETED
            )
            ->groupBy('federation_id')
            ->map(function ($entries, $federationId) {
                $federation = $entries->first()->federation;

                if (! $federation) {
                    return null;
                }

                $federation->load('country');
                // Safely get country name
                $countryName = $federation->relationLoaded('country') && $federation->country
                    ? $federation->country->name
                    : 'N/A';
                // Get country ISO for flag
                $countryIso = $federation->country->iso ?? '';

                // Get individual entries (no team identifier)
                $individualEntries = $entries->whereNull('team_identifier');

                // Individual entries by gender
                $individualMale = $individualEntries
                    ->filter(fn ($entry) => $entry->individual && $entry->individual->gender === 'male')
                    ->count();

                $individualFemale = $individualEntries
                    ->filter(fn ($entry) => $entry->individual && $entry->individual->gender === 'female')
                    ->count();

                // Relay entries by gender
                $relayMale = $entries
                    ->whereNotNull('team_identifier')
                    ->where('discipline.enrollment_type', 'relay')
                    ->where('discipline.gender', 'male')
                    ->pluck('team_identifier')
                    ->unique()
                    ->count();

                $relayFemale = $entries
                    ->whereNotNull('team_identifier')
                    ->where('discipline.enrollment_type', 'relay')
                    ->where('discipline.gender', 'female')
                    ->pluck('team_identifier')
                    ->unique()
                    ->count();

                $relayMixed = $entries
                    ->whereNotNull('team_identifier')
                    ->where('discipline.enrollment_type', 'relay')
                    ->where('discipline.gender', 'mixed')
                    ->pluck('team_identifier')
                    ->unique()
                    ->count();

                $teamCount = $entries
                    ->whereNotNull('team_identifier')
                    ->where('discipline.enrollment_type', 'team')
                    ->pluck('team_identifier')
                    ->unique()
                    ->count();

                return [
                    'name' => $federation->name,
                    'type' => 'Federation',
                    'country' => $countryName,
                    'country_iso' => $countryIso,
                    'total_entries' => $entries->count(),
                    'individual_male' => $individualMale,
                    'individual_female' => $individualFemale,
                    'relay_male' => $relayMale,
                    'relay_female' => $relayFemale,
                    'relay_mixed' => $relayMixed,
                    'team_athletes' => $teamCount,
                ];
            })
            ->filter(); // Remove null values

        // Group entries by entity that enrolled them
        $entityStats = $this->getAthleteEnrollments()
            ->filter(fn ($entry) => $entry->entity_id)
            ->filter(fn ($entry) => $entry->discipline_id !== null)
            ->filter(
                fn ($entry) => $entry->status_class === EvtAthleteEnrollmentStatusEnum::DISCIPLINE_ASSIGNED ||
                    $entry->status_class === EvtAthleteEnrollmentStatusEnum::COMPLETED
            )
            ->groupBy('entity_id')
            ->map(function ($entries, $entityId) {
                $entity = $entries->first()->entity;

                if (! $entity) {
                    return null;
                }

                $entity->load('country');

                // Safely get country name
                $countryName = $entity->relationLoaded('country') && $entity->country
                    ? $entity->country->name
                    : 'N/A';
                // Get country ISO for flag
                $countryIso = $entity->country->iso ?? '';

                // Get individual entries (no team identifier)
                $individualEntries = $entries->whereNull('team_identifier');

                // Individual entries by gender
                $individualMale = $individualEntries
                    ->filter(fn ($entry) => $entry->individual && $entry->individual->gender === 'male')
                    ->count();

                $individualFemale = $individualEntries
                    ->filter(fn ($entry) => $entry->individual && $entry->individual->gender === 'female')
                    ->count();

                // Relay entries by gender
                $relayMale = $entries
                    ->whereNotNull('team_identifier')
                    ->where('discipline.enrollment_type', 'relay')
                    ->where('discipline.gender', 'male')
                    ->pluck('team_identifier')
                    ->unique()
                    ->count();

                $relayFemale = $entries
                    ->whereNotNull('team_identifier')
                    ->where('discipline.enrollment_type', 'relay')
                    ->where('discipline.gender', 'female')
                    ->pluck('team_identifier')
                    ->unique()
                    ->count();

                $relayMixed = $entries
                    ->whereNotNull('team_identifier')
                    ->where('discipline.enrollment_type', 'relay')
                    ->where('discipline.gender', 'mixed')
                    ->pluck('team_identifier')
                    ->unique()
                    ->count();

                $teamCount = $entries
                    ->whereNotNull('team_identifier')
                    ->where('discipline.enrollment_type', 'team')
                    ->pluck('team_identifier')
                    ->unique()
                    ->count();

                return [
                    'name' => $entity->name,
                    'type' => 'Club',
                    'country' => $countryName,
                    'country_iso' => $countryIso,
                    'total_entries' => $entries->count(),
                    'individual_male' => $individualMale,
                    'individual_female' => $individualFemale,
                    'relay_male' => $relayMale,
                    'relay_female' => $relayFemale,
                    'relay_mixed' => $relayMixed,
                    'team_athletes' => $teamCount,
                ];
            })
            ->filter(); // Remove null values

        // Merge both collections
        return $federationStats->union($entityStats);
    }

    protected function getDisciplineStats()
    {
        return $this->getAthleteEnrollments()
            ->filter(fn ($entry) => $entry->discipline_id !== null)
            ->filter(
                fn ($entry) => $entry->status_class === EvtAthleteEnrollmentStatusEnum::DISCIPLINE_ASSIGNED ||
                    $entry->status_class === EvtAthleteEnrollmentStatusEnum::COMPLETED
            )
            ->groupBy('discipline.name')
            ->map(function ($entries) {
                // For individual disciplines, count entries without team identifier
                // For relay disciplines, count all entries
                $totalEntries = $entries->first()->discipline->enrollment_type === 'relay'
                    ? $entries->count()  // Count all entries for relay disciplines
                    : $entries->whereNull('team_identifier')->count();  // Only count non-team entries for individual disciplines

                // Count relay teams using the same logic as elsewhere
                $relayTeams = $entries
                    ->whereNotNull('team_identifier')
                    ->where('discipline.enrollment_type', 'relay')
                    ->groupBy(function ($entry) {
                        return $entry->discipline_id . '_' . $entry->entity_id . '_' . $entry->team_identifier;
                    })
                    ->map(function ($teamEntries) {
                        if ($teamEntries->isEmpty()) {
                            return 0;
                        }

                        $discipline = $teamEntries->first()->discipline;
                        $requirements = $discipline->team_composition_requirements;

                        if (empty($requirements)) {
                            return 0;
                        }

                        // Count athletes by gender
                        $maleCount = $teamEntries->filter(
                            fn ($entry) => $entry->individual && $entry->individual->gender === 'male'
                        )->count();

                        $femaleCount = $teamEntries->filter(
                            fn ($entry) => $entry->individual && $entry->individual->gender === 'female'
                        )->count();

                        // Check if the team meets ALL requirements
                        foreach ($requirements as $gender => $required) {
                            $actual = $gender === 'male' ? $maleCount : $femaleCount;
                            if ($actual < $required) {
                                return 0; // Incomplete team
                            }
                        }

                        return 1; // Complete team
                    })
                    ->sum();

                // Count unique federations (nations)
                $nations = $entries
                    ->filter(fn ($entry) => $entry->federation)
                    ->pluck('federation.id')
                    ->unique()
                    ->count();

                // Count unique entities (clubs)
                $clubs = $entries
                    ->filter(fn ($entry) => $entry->entity)
                    ->pluck('entity.id')
                    ->unique()
                    ->count();

                return [
                    'entries' => $totalEntries,
                    'relay_teams' => $relayTeams,
                    'organizations' => $nations,
                    'clubs' => $clubs,
                ];
            });
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        return view('livewire.evt-events.event-statistics', [
            'statistics' => $this->getStatistics(),
            'tabs' => [
                'country' => __('events.statistics.tab_country'),
                'discipline' => __('events.statistics.tab_discipline'),
                'entity' => __('events.statistics.tab_entity'),
            ],
        ]);
    }

    public function exportEntityStatsCSV()
    {
        $entityStats = $this->getEntityStats();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="entity-statistics.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $columns = [
            'Organization Name',
            'Type',
            'Country',
            'Total Entries',
            'Individual Male',
            'Individual Female',
            'Relay Male',
            'Relay Female',
            'Relay Mixed',
            'Team Athletes',
        ];

        $callback = function () use ($entityStats, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($entityStats as $stat) {
                fputcsv($file, [
                    $stat['name'],
                    $stat['type'],
                    $stat['country'],
                    $stat['total_entries'],
                    $stat['individual_male'],
                    $stat['individual_female'],
                    $stat['relay_male'],
                    $stat['relay_female'],
                    $stat['relay_mixed'],
                    $stat['team_athletes'],
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportCountryStatsCSV()
    {
        $countryStats = $this->getCountryStats();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="country-statistics.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $columns = [
            'Country',
            'Total Entries',
            'Athlete Entries',
            'Individual Athletes',
            'Male Athletes',
            'Female Athletes',
            'Relay Teams',
            'Team Athletes',
            'Total Coaches',
            'Total Officials',
        ];

        $callback = function () use ($countryStats, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($countryStats as $country => $stat) {
                fputcsv($file, [
                    $country,
                    $stat['total_entries'],
                    $stat['athlete_entries'],
                    $stat['individual_athletes'],
                    $stat['male_athletes'],
                    $stat['female_athletes'],
                    $stat['relay_teams'],
                    $stat['team_athletes'],
                    $stat['total_coaches'],
                    $stat['total_officials'],
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportDisciplineStatsCSV()
    {
        $disciplineStats = $this->getDisciplineStats();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="discipline-statistics.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $columns = [
            'Discipline',
            'Entries',
            'Relay Teams',
            'Clubs',
            'Nations',
        ];

        $callback = function () use ($disciplineStats, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($disciplineStats as $discipline => $stat) {
                fputcsv($file, [
                    $discipline,
                    $stat['entries'],
                    $stat['relay_teams'],
                    $stat['clubs'],
                    $stat['organizations'],
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Count valid teams based on composition requirements
     *
     * @param  Collection  $entries  The athlete enrollments
     * @param  string  $type  The enrollment type ('relay' or 'team')
     * @param  EvtDisciplineGenderEnum|null  $gender  The gender filter (optional)
     * @return int The count of valid teams
     */
    protected function countValidTeams($entries, $type, ?EvtDisciplineGenderEnum $gender = null)
    {
        // Filter entries by type
        $filteredEntries = $entries
            ->whereNotNull('team_identifier')
            ->where('discipline.enrollment_type', $type);

        // Apply gender filter if provided
        if ($gender !== null) {
            $filteredEntries = $filteredEntries->where('discipline.gender', $gender->value);
        }

        \Log::info("Counting teams for {$type}" . ($gender ? " {$gender->value}" : '') . ': Found ' . $filteredEntries->count() . ' entries');

        // Group entries by team
        $teams = $filteredEntries->groupBy(function ($entry) {
            return $entry->discipline_id . '_' . $entry->entity_id . '_' . $entry->team_identifier;
        });

        \Log::info('Found ' . $teams->count() . ' unique teams after grouping');

        // Fall back to the old counting method if no teams are found with our new method
        if ($teams->isEmpty() && $filteredEntries->isNotEmpty()) {
            \Log::warning('No teams found despite having entries - falling back to simple counting');

            return $filteredEntries->pluck('team_identifier')->unique()->count();
        }

        return $teams->map(function ($teamEntries) use ($gender) {
            if ($teamEntries->isEmpty()) {
                return 0;
            }

            $discipline = $teamEntries->first()->discipline;
            $requirements = $discipline->team_composition_requirements;

            // Fall back if no requirements are defined
            if (empty($requirements)) {
                \Log::info('No requirements defined for team - counting as valid team');

                return 1; // Count as valid if no requirements are defined
            }

            // Count athletes by gender
            $maleCount = $teamEntries->filter(
                fn ($entry) => $entry->individual && $entry->individual->gender === 'male'
            )->count();

            $femaleCount = $teamEntries->filter(
                fn ($entry) => $entry->individual && $entry->individual->gender === 'female'
            )->count();

            \Log::info("Team composition - Male: {$maleCount}, Female: {$femaleCount}, Requirements: " . json_encode($requirements));

            // If gender is specified, apply specific validation rules
            if ($gender !== null) {
                // For mixed teams
                if ($gender === EvtDisciplineGenderEnum::MIXED) {
                    // Check if team has both male and female athletes AND meets requirements
                    if ($maleCount > 0 && $femaleCount > 0) {
                        $isValid = true;
                        foreach ($requirements as $reqGender => $required) {
                            $actual = $reqGender === 'male' ? $maleCount : $femaleCount;
                            if ($actual < $required) {
                                $isValid = false;
                                break;
                            }
                        }
                        \Log::info('Mixed team validation result: ' . ($isValid ? 'valid' : 'invalid'));

                        return $isValid ? 1 : 0;
                    }
                    \Log::info("Mixed team rejected - doesn't have both genders");

                    return 0;
                }

                // For male teams
                if ($gender === EvtDisciplineGenderEnum::MALE) {
                    // Only check male requirements, but less strictly
                    if (! isset($requirements['male'])) {
                        \Log::info('Male team - no male requirement specified, counting as valid');

                        return 1; // Count as valid if no specific requirement
                    }
                    $isValid = $maleCount >= $requirements['male'];
                    \Log::info('Male team validation result: ' . ($isValid ? 'valid' : 'invalid'));

                    return $isValid ? 1 : 0;
                }

                // For female teams
                if ($gender === EvtDisciplineGenderEnum::FEMALE) {
                    // Only check female requirements, but less strictly
                    if (! isset($requirements['female'])) {
                        \Log::info('Female team - no female requirement specified, counting as valid');

                        return 1; // Count as valid if no specific requirement
                    }
                    $isValid = $femaleCount >= $requirements['female'];
                    \Log::info('Female team validation result: ' . ($isValid ? 'valid' : 'invalid'));

                    return $isValid ? 1 : 0;
                }
            }

            // For team count without gender filtering, consider valid by default
            \Log::info('No gender filtering or unhandled gender - counting as valid team');

            return 1;
        })->sum();
    }
}
