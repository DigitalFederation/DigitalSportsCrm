<?php

namespace App\Livewire\Common\EvtEvent;

use App\Enums\EvtAthleteEnrollmentStatusEnum;
use Domain\EvtEvents\Actions\GetDisciplinesFromEventAction;
use Domain\EvtEvents\Models\Discipline;
use Domain\EvtEvents\Models\Event;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class PublicAthleteEnrollmentList extends Component
{
    public Event $event;
    public ?int $selectedDisciplineId = null;
    public string $searchOrganization = '';

    // Mount the component with the event
    public function mount(Event $event): void
    {
        $this->event = $event;
    }

    // Method to clear filters
    public function clearFilters(): void
    {
        $this->selectedDisciplineId = null;
        $this->searchOrganization = '';
    }

    // Group enrollments by discipline using the discipline collection (by ID) and then by team.
    private function groupEnrollmentsByDisciplineAndTeam(Collection $enrollments, Collection $disciplines): Collection
    {
        $disciplinesById = $disciplines->pluck('name', 'id');
        $result = collect();

        // Group enrollments with disciplines
        foreach ($enrollments as $enrollment) {
            $disciplineId = $enrollment->discipline_id;
            $disciplineName = $disciplineId && isset($disciplinesById[$disciplineId]) ? $disciplinesById[$disciplineId] : 'No Discipline';
            $organizationName = $enrollment->enrollment?->enrollable?->name ?? 'Independent';

            // Apply organization filter (case-insensitive)
            if ($this->searchOrganization === '' || stripos($organizationName, $this->searchOrganization) !== false) {
                if (! $result->has($disciplineName)) {
                    $result[$disciplineName] = collect();
                }
                if (! $result[$disciplineName]->has($organizationName)) {
                    $result[$disciplineName][$organizationName] = collect();
                }
                $result[$disciplineName][$organizationName]->push($enrollment);
            }
        }

        // Add empty collections for unused disciplines if no discipline filter is active
        if ($this->selectedDisciplineId === null) {
            foreach ($disciplinesById as $id => $name) {
                if (! $result->has($name)) {
                    $result[$name] = collect();
                }
            }
        }

        // Ensure "No Discipline" key exists if relevant enrollments were found
        if ($result->has('No Discipline') && ! $result['No Discipline']->isEmpty()) {
            // Keep it
        } elseif ($result->has('No Discipline') && $result['No Discipline']->isEmpty()) {
            // Remove empty "No Discipline" group if searchOrganization cleared it
            unset($result['No Discipline']);
        }

        return $result;
    }

    public function render(): View
    {
        // Build the base query with eager loading.
        $query = $this->event->athleteEnrollments()
            ->withoutGlobalScopes()
            ->whereNull('evt_athletes_enrollment.deleted_at') // Exclude soft-deleted enrollments
            ->with([
                'individual' => function ($query) {
                    $query->withoutGlobalScopes();
                },
                'individual.country',
                'enrollment' => function ($query) {
                    $query->with('enrollable');
                },
                'enrollment.event',
                'attributes.attribute',
                'discipline',
            ])
            ->select('evt_athletes_enrollment.*')
            ->whereNot('status_class', EvtAthleteEnrollmentStatusEnum::PENDING_PAYMENT->value)
            ->whereNotNull('discipline_id'); // Initially ensure discipline is set, filtering can override

        // Apply discipline filter
        if ($this->selectedDisciplineId) {
            $query->where('discipline_id', $this->selectedDisciplineId);
        }

        $enrollments = $query->get();

        // START: CmasWorld Customization - Modify country data for Russia and Belarus
        $enrollments->each(function ($enrollment) {
            if ($enrollment->individual && $enrollment->individual->country) {
                switch ($enrollment->individual->country->name) {
                    case 'Russia':
                        $enrollment->individual->country->name = 'CMAS1';
                        // Prevent flag display by altering ISO or a similar identifier
                        // Assuming 'iso' is the correct property used for flags as seen in the blade file.
                        $enrollment->individual->country->iso = 'en'; // Or set to null/empty if that reliably hides the flag
                        break;
                    case 'Belarus':
                        $enrollment->individual->country->name = 'CMAS2';
                        $enrollment->individual->country->iso = 'en'; // Or set to null/empty
                        break;
                }
            }
        });
        // END: CmasWorld Customization

        // Fetch disciplines for the filter dropdown and grouping
        $disciplines = (new GetDisciplinesFromEventAction)->execute($this->event)['disciplines'] ?? collect();

        // Group the filtered (or all) enrollments
        $groupedEnrollments = $this->groupEnrollmentsByDisciplineAndTeam($enrollments, $disciplines);

        // Calculate total stats based on *filtered* enrollments
        $totalAthletes = $enrollments->count();
        $totalTeams = $enrollments->groupBy(
            fn ($enrollment) => $enrollment->enrollment?->enrollable?->name ?? 'Independent'
        )->count();

        $totalStats = [
            'totalAthletes' => $totalAthletes,
            'totalTeams' => $totalTeams,
        ];

        return view('livewire.common.evt-event.public-athlete-enrollment-list', [
            'groupedEnrollments' => $groupedEnrollments,
            'totalStats' => $totalStats,
            'disciplines' => $disciplines, // For the filter dropdown
        ]);
    }
}
