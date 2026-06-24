<?php

namespace App\Http\Controllers\Federation\EvtEvents\Enrollments;

use App\Enums\EvtAthleteEnrollmentStatusEnum;
use App\Exports\AthleteEnrollmentByDisciplineExport;
use App\Exports\AthleteEnrollmentsExport;
use App\Exports\CoachEnrollmentsExport;
use App\Exports\IndividualEnrollmentsExport;
use App\Exports\StaffEnrollmentsExport;
use App\Exports\TeamOfficialEnrollmentsExport;
use App\Http\Controllers\Common\BaseEnrollmentController;
use App\Scopes\IndividualsFromFederationScope;
use Domain\EvtEvents\Models\AthleteEnrollment;
use Domain\EvtEvents\Models\CoachEnrollment;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\IndividualEnrollment;
use Domain\EvtEvents\Models\StaffEnrollment;
use Domain\EvtEvents\Models\TeamOfficialEnrollment;
use Domain\EvtEvents\States\RegisteredCoachEnrollmentState;
use Domain\EvtEvents\States\RegisteredTeamOfficialEnrollmentState;
use Domain\Federations\Models\Federation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class OrganizerEnrollmentsController extends BaseEnrollmentController
{
    public function index(Request $request, Event $event, string $enrollmentType): View
    {
        $federationId = $request->user()->getFederationId();
        $this->authorizeOrganizer($event, $federationId);

        try {

            $model = $this->getModelForEnrollmentType($enrollmentType);
            $table = (new $model)->getTable();

            $options = $this->getRelationshipOptions($enrollmentType);

            $enrollments = $model::with($options)
                ->with(['individual' => function ($query) {
                    $query->withoutGlobalScope(IndividualsFromFederationScope::class);
                }])
                ->where('event_id', $event->id)
                ->select("{$table}.*")
                ->selectSub($this->getDocumentExistsSubquery($table), 'hasDocument')
                ->selectSub($this->getLatestDocumentIdSubquery($table), 'document_id')
                ->selectSub($this->getPaymentStatusSubquery($table), 'payment_status');

            // Apply active state filters for coaches and team officials
            if ($enrollmentType === 'coach') {
                $enrollments->where('status_class', RegisteredCoachEnrollmentState::class);
            } elseif ($enrollmentType === 'official') {
                $enrollments->where('status_class', RegisteredTeamOfficialEnrollmentState::class);
            } elseif ($enrollmentType === 'athlete') {
                $enrollments->whereIn('status_class', [
                    EvtAthleteEnrollmentStatusEnum::DISCIPLINE_ASSIGNED->value,
                    EvtAthleteEnrollmentStatusEnum::COMPLETED->value,
                ])
                    ->with('discipline');
            }

            $uniqueAttributes = $this->extractUniqueAttributes($enrollments->get());
            $enrollments = $enrollments->paginate(75);

            return view('web.federation.evt_event.organizer_enrollment.index', compact(
                'event',
                'enrollments',
                'enrollmentType',
                'uniqueAttributes'
            ));
        } catch (\Exception $e) {
            Log::error("Error displaying {$enrollmentType} enrollments: " . $e->getMessage(), [
                'event_id' => $event->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return view('web.federation.evt_event.organizer_enrollment.index', [
                'event' => $event,
                'enrollments' => collect(),
                'enrollmentType' => $enrollmentType,
                'uniqueAttributes' => collect(),
                'error' => 'An error occurred while retrieving enrollments.',
            ]);
        }
    }

    public function export(Request $request, Event $event, string $enrollmentType)
    {
        try {
            $federationId = $request->user()->getFederationId();
            $this->authorizeOrganizer($event, $federationId);

            $model = $this->getModelForEnrollmentType($enrollmentType);
            $exportClass = $this->getExportClassForEnrollmentType($enrollmentType);
            $options = $this->getRelationshipOptions($enrollmentType);

            $enrollments = $model::with($options)
                ->with(['individual' => function ($query) {
                    $query->withoutGlobalScope(IndividualsFromFederationScope::class);
                }])
                ->where('event_id', $event->id);

            // Apply active state filters for coaches and team officials
            if ($enrollmentType === 'coach') {
                $enrollments->where('status_class', RegisteredCoachEnrollmentState::class);
            } elseif ($enrollmentType === 'official') {
                $enrollments->where('status_class', RegisteredTeamOfficialEnrollmentState::class);
            } elseif ($enrollmentType === 'athlete') {
                $enrollments->whereIn('status_class', [
                    EvtAthleteEnrollmentStatusEnum::DISCIPLINE_ASSIGNED->value,
                    EvtAthleteEnrollmentStatusEnum::COMPLETED->value,
                ])
                    ->with('discipline');
            }

            // Get the filtered data
            $filteredData = $enrollments->get();
            $uniqueAttributes = $this->extractUniqueAttributes($filteredData);

            // Create export instance with filtered data
            $exportInstance = new $exportClass($event);
            if ($uniqueAttributes) {
                $exportInstance->setUniqueAttributes($uniqueAttributes);
            }

            // Using direct Excel facade
            $fileName = $this->generateExportFileName($event, $enrollmentType);

            return Excel::download($exportInstance, $fileName);
        } catch (\Exception $e) {
            Log::error("Error exporting {$enrollmentType} enrollments: " . $e->getMessage(), [
                'event_id' => $event->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'An error occurred while generating the export.');
        }
    }

    public function exportAthletesByDiscipline(Request $request, Event $event)
    {
        try {
            $federationId = $request->user()->getFederationId();
            $this->authorizeOrganizer($event, $federationId);

            $enrollments = $this->getModelForEnrollmentType('athlete')::whereHas('individual')
                ->where('event_id', $event->id)
                ->with(['individual', 'individual.country', 'enrollment.event', 'enrollment.enrollable', 'attributes.attribute', 'discipline'])
                ->get();

            $uniqueAttributes = $this->extractUniqueAttributes($enrollments);

            return $this->processExport(
                $request,
                AthleteEnrollmentByDisciplineExport::class,
                $event,
                'athletes-by-discipline',
                $uniqueAttributes
            );
        } catch (\Exception $e) {
            Log::error('Error exporting athlete enrollments by discipline: ' . $e->getMessage(), [
                'event_id' => $event->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'An error occurred while generating the export.');
        }
    }

    private function getRelationshipOptions(string $enrollmentType): array
    {
        $options = [
            'enrollment.event',
            'enrollment.enrollable' => function ($query) {
                $query->withTrashed();
            },
            'attributes.attribute',
        ];

        if ($enrollmentType === 'athlete') {
            $options['discipline'] = function ($query) {
                $query->withTrashed();
            };
        }

        return $options;
    }

    private function authorizeOrganizer(Event $event, $federationId)
    {
        $eventOrganizerExists = $event->organizer()
            ->where('organizable_id', $federationId)
            ->where('organizable_type', Federation::class)
            ->exists();

        if (! $eventOrganizerExists) {
            abort(403, 'Unauthorized access.');
        }
    }

    private function getModelForEnrollmentType(string $enrollmentType)
    {
        switch ($enrollmentType) {
            case 'athlete':
                return AthleteEnrollment::class;
            case 'coach':
                return CoachEnrollment::class;
            case 'individual':
                return IndividualEnrollment::class;
            case 'staff':
                return StaffEnrollment::class;
            case 'official':
                return TeamOfficialEnrollment::class;
            default:
                abort(404, 'Enrollment type not found.');
        }
    }

    private function getExportClassForEnrollmentType(string $enrollmentType)
    {
        switch ($enrollmentType) {
            case 'athlete':
                return AthleteEnrollmentsExport::class;
            case 'coach':
                return CoachEnrollmentsExport::class;
            case 'individual':
                return IndividualEnrollmentsExport::class;
            case 'staff':
                return StaffEnrollmentsExport::class;
            case 'official':
                return TeamOfficialEnrollmentsExport::class;
            default:
                abort(404, 'Export class not found for the given enrollment type.');
        }
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
}
