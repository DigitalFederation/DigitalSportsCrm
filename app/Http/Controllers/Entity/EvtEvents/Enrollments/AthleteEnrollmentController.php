<?php

namespace App\Http\Controllers\Entity\EvtEvents\Enrollments;

use App\Enums\EvtAthleteEnrollmentStatusEnum;
use App\Enums\EvtEnrollmentStatusEnum;
use App\Exports\AthleteEntityEnrollmentsExport;
use App\Http\Controllers\Controller;
use Domain\EvtEvents\Actions\GetDisciplinesFromEventAction;
use Domain\EvtEvents\Models\AthleteEnrollment;
use Domain\EvtEvents\Models\Discipline;
use Domain\EvtEvents\Models\Event;
use Exception;
use Illuminate\Contracts\View\View; // adjust namespace as needed
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AthleteEnrollmentController extends Controller
{
    public function publicIndex(Event $event): View|RedirectResponse
    {
        // Logic moved to Livewire\Common\EvtEvent\PublicAthleteEnrollmentList
        return view('web.common.evt_event.athlete_enrollment.public_index', [
            'event' => $event,
        ]);
    }

    /**
     * Group enrollments by discipline using the discipline collection (by ID) and then by team.
     *
     * @param  Collection  $disciplines  Collection of Discipline models keyed by their ID.
     */
    private function groupEnrollmentsByDisciplineAndTeam(Collection $enrollments, Collection $disciplines): Collection
    {
        // This method is now handled within the Livewire component
        // App\Livewire\Common\EvtEvent\PublicAthleteEnrollmentList
        // It can be potentially removed if not used elsewhere in this controller.
        // For now, we leave it but the publicIndex no longer calls it.
        $disciplinesById = $disciplines->pluck('name', 'id');
        $result = collect();

        // Group enrollments with disciplines
        foreach ($enrollments as $enrollment) {
            $disciplineId = $enrollment->discipline_id;
            if ($disciplineId && isset($disciplinesById[$disciplineId])) {
                $disciplineName = $disciplinesById[$disciplineId];
                $organizationName = $enrollment->enrollment?->enrollable?->name ?? 'Independent';

                if (! $result->has($disciplineName)) {
                    $result[$disciplineName] = collect();
                }
                if (! $result[$disciplineName]->has($organizationName)) {
                    $result[$disciplineName][$organizationName] = collect();
                }
                $result[$disciplineName][$organizationName]->push($enrollment);
            } else {
                // Handle no discipline
                $organizationName = $enrollment->enrollment?->enrollable?->name ?? 'Independent';
                if (! $result->has('No Discipline')) {
                    $result['No Discipline'] = collect();
                }
                if (! $result['No Discipline']->has($organizationName)) {
                    $result['No Discipline'][$organizationName] = collect();
                }
                $result['No Discipline'][$organizationName]->push($enrollment);
            }
        }

        // Add empty collections for unused disciplines
        foreach ($disciplinesById as $id => $name) {
            if (! $result->has($name)) {
                $result[$name] = collect();
            }
        }

        return $result;
    }

    public function index(
        Event $event,
        ?Discipline $discipline = null
    ): View|RedirectResponse|BinaryFileResponse {
        $entityId = Auth::user()->entities()->first()?->id;

        // Check if the event's registration period has ended
        if (now()->isAfter($event->end_registration)) {
            return redirect()->route('entity.evt-events.events.show', $event->id)
                ->with('error', __('The registration period for this event has ended.'));
        }

        $table = (new AthleteEnrollment)->getTable();

        $query = $event->athleteEnrollments()
            ->where('entity_id', $entityId)
            ->with([
                'attributes.attribute',
                'individual' => function ($query) {
                    $query->select('id', 'name', 'surname', 'member_number', 'gender', 'birthdate');
                },
                'event' => function ($query) {
                    $query->select('id', 'name');
                },
                'discipline' => function ($query) {
                    $query->select('id', 'name');
                },
                'enrollment' => function ($query) {
                    $query->with('enrollable');
                },
                'enrollment.event' => function ($query) {
                    $query->select('id', 'name');
                },
            ])
            ->whereNotNull('discipline_id')
            ->select("{$table}.*")
            ->selectSub($this->getDocumentExistsSubquery($table), 'hasDocument')
            ->selectSub($this->getLatestDocumentIdSubquery($table), 'document_id')
            ->selectSub($this->getPaymentStatusSubquery($table), 'payment_status');

        // Handle status filtering based on event type
        if ($event->isSportEvent()) {
            // Include all paid athletes (with or without disciplines)
            // and those with discipline_assigned status
            $query->where(function ($query) {
                $query->where('status_class', EvtAthleteEnrollmentStatusEnum::PAID->value)
                    ->orWhere('status_class', EvtAthleteEnrollmentStatusEnum::DISCIPLINE_ASSIGNED->value)
                    ->orWhere('status_class', EvtAthleteEnrollmentStatusEnum::COMPLETED->value);
            });
        } else {
            $query->where('status_class', EvtEnrollmentStatusEnum::ACTIVE->value);
        }

        if ($discipline) {
            $query->where('discipline_id', $discipline->id);
        }

        // Get all enrollments for stats calculation
        $allEnrollments = $query->get();

        // Get disciplines for the event
        $disciplines = (new GetDisciplinesFromEventAction)->execute($event)['disciplines'] ?? collect();

        // Group enrollments by discipline and team
        $groupedEnrollments = $this->groupEnrollmentsByDisciplineAndTeam($allEnrollments, $disciplines);

        // Paginate for display
        $enrollments = $query->paginate(75);

        $uniqueAttributes = $this->extractUniqueAttributes($allEnrollments);

        if (request()->has('export')) {
            $export = new AthleteEntityEnrollmentsExport($event, $discipline);

            // Set unique attributes if available
            if (! empty($uniqueAttributes)) {
                $export->setUniqueAttributes($uniqueAttributes);
            }

            // Create a more descriptive filename
            $disciplineName = $discipline ? '-' . \Illuminate\Support\Str::slug($discipline->name) : '';
            $date = now()->format('Y-m-d');
            $eventName = \Illuminate\Support\Str::slug($event->name);
            $filename = "entity-athletes-{$eventName}{$disciplineName}-{$date}.xlsx";

            return Excel::download($export, $filename);
        }

        // Pass a flag to indicate entity context
        $entity = true;

        return view('web.common.evt_event.athlete_enrollment.index', compact(
            'event',
            'enrollments',
            'uniqueAttributes',
            'disciplines',
            'groupedEnrollments',
            'entity'
        ));
    }

    private function getDocumentExistsSubquery($table)
    {
        return function ($query) use ($table) {
            $query->from('document_detail')
                ->join('document', 'document.id', '=', 'document_detail.document_id')
                ->select(DB::raw('1'))
                ->whereColumn('document_detail.owner_id', "{$table}.id")
                ->where('document_detail.owner_type', 'Domain\\EvtEvents\\Models\\Enrollment')
                ->whereNull('document.deleted_at')
                ->limit(1);
        };
    }

    private function getLatestDocumentIdSubquery($table)
    {
        return function ($query) use ($table) {
            $query->from('document_detail')
                ->join('document', 'document.id', '=', 'document_detail.document_id')
                ->select('document.id')
                ->whereColumn('document_detail.owner_id', "{$table}.id")
                ->where('document_detail.owner_type', '=', 'Domain\\EvtEvents\\Models\\Enrollment')
                ->orderByDesc('document.created_at')
                ->limit(1);
        };
    }

    private function getPaymentStatusSubquery($table)
    {
        return function ($query) use ($table) {
            $query->from('document_detail')
                ->join('document', 'document.id', '=', 'document_detail.document_id')
                ->select('document.status_class')
                ->whereColumn('document_detail.owner_id', "{$table}.id")
                ->where('document_detail.owner_type', '=', 'Domain\\EvtEvents\\Models\\Enrollment')
                ->orderByDesc('document.created_at')
                ->limit(1);
        };
    }

    private function extractUniqueAttributes($enrollments)
    {
        return $enrollments->flatMap->attributes
            ->pluck('attribute.name', 'attribute.id')
            ->unique()
            ->sort()
            ->values();
    }

    public function destroy(Event $event, AthleteEnrollment $athleteEnrollment): RedirectResponse
    {
        try {
            // Only allow deletion of relay team records
            if (! $athleteEnrollment->team_identifier) {
                throw new \Exception('Only relay team records can be removed from this interface.');
            }

            DB::beginTransaction();

            $entityId = Auth::user()->entities()->first()?->id;

            if (! $entityId) {
                // It's good practice to handle cases where the user might not be associated with an entity.
                // Depending on application logic, this might be an error, or handled by route middleware.
                throw new \Exception('User is not associated with an entity. Action unauthorized.');
            }

            // First verify this team belongs to the current entity
            $firstTeamMember = AthleteEnrollment::where('team_identifier', $athleteEnrollment->team_identifier)
                ->where('event_id', $event->id)
                ->where('entity_id', $entityId) // Changed from federation_id
                ->first();

            if (! $firstTeamMember) {
                // Updated exception message for clarity and correctness
                throw new \Exception('Unauthorized: The specified team does not belong to your entity or could not be found.');
            }

            // If authorized, update all athletes in the same relay team belonging to this entity
            // We need to be very specific here to only affect the correct team for the correct entity
            AthleteEnrollment::where('team_identifier', $athleteEnrollment->team_identifier)
                ->where('event_id', $event->id)
                ->where('entity_id', $entityId) // Changed from federation_id
                ->where('discipline_id', $athleteEnrollment->discipline_id) // Add discipline check to ensure same team
                ->each(function ($teamMember) {
                    $teamMember->update([
                        'discipline_id' => null,
                        'team_identifier' => null,
                        'status_class' => EvtAthleteEnrollmentStatusEnum::PAID->value,
                    ]);
                    $teamMember->attributes()->delete();
                });

            DB::commit();

            return redirect()->back()->with('success', 'Relay team has been removed from the discipline and can be reassigned.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());

            return redirect()->back()->with('error', 'Failed to remove relay team: ' . $e->getMessage());
        }
    }
}
