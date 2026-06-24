<?php

namespace App\Http\Controllers\Federation\EvtEvents\Enrollments;

use App\Enums\EvtAthleteEnrollmentStatusEnum;
use App\Enums\EvtEnrollmentStatusEnum;
use App\Exports\AthleteFederationEnrollmentsExport;
use App\Http\Controllers\Common\BaseEnrollmentController;
use Domain\EvtEvents\Models\AthleteEnrollment;
use Domain\EvtEvents\Models\Discipline;
use Domain\EvtEvents\Models\Event;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AthleteEnrollmentController extends BaseEnrollmentController
{
    public function index(
        Request $request,
        Event $event,
        ?Discipline $discipline = null
    ): View|RedirectResponse|BinaryFileResponse {
        if ($this->isDefaultFederation()) {
            return $this->adminIndex($request, $event);
        }

        $federationId = Auth::user()->getFederationId();

        // Check if the event's registration period has ended
        if (now()->isAfter($event->end_registration)) {
            return redirect()->route('federation.evt-events.events.show', $event->id)
                ->with('error', __('The registration period for this event has ended.'));
        }

        $table = (new AthleteEnrollment)->getTable();

        $enrollments = $event->athleteEnrollments()
            ->whereHas('federation', function ($query) use ($federationId) {
                return $query->where('id', $federationId);
            })
            ->with([
                'attributes.attribute',
                'individual' => function ($query) {
                    $query->select('id', 'name', 'surname', 'member_code', 'gender', 'birthdate');
                },
                'event' => function ($query) {
                    $query->select('id', 'name');
                },
                'discipline' => function ($query) {
                    $query->select('id', 'name');
                },
                'enrollment.event' => function ($query) {
                    $query->select('id', 'name');
                },
                'enrollment' => function ($query) {
                    $query->with(['enrollable']);
                },
            ])
            ->whereNotNull('discipline_id')
            ->select("{$table}.*")
            ->selectSub($this->getDocumentExistsSubquery($table), 'hasDocument')
            ->selectSub($this->getLatestDocumentIdSubquery($table), 'document_id')
            ->selectSub($this->getPaymentStatusSubquery($table), 'payment_status');

        // Handle status filtering based on event type
        if ($event->isSportEvent()) {
            $enrollments->where(function ($query) {
                $query->where('status_class', EvtAthleteEnrollmentStatusEnum::PAID->value)
                    ->orWhere('status_class', EvtAthleteEnrollmentStatusEnum::DISCIPLINE_ASSIGNED->value)
                    ->orWhere('status_class', EvtAthleteEnrollmentStatusEnum::COMPLETED->value);
            });
        } else {
            $enrollments->where('status_class', EvtEnrollmentStatusEnum::ACTIVE->value);
        }

        if ($discipline) {
            $enrollments->where('discipline_id', $discipline->id);
        }

        $uniqueAttributes = $this->extractUniqueAttributes($enrollments->get());
        $enrollments = $enrollments->paginate(75);

        if ($request->has('export')) {
            $export = new AthleteFederationEnrollmentsExport($event, $discipline);

            // Set unique attributes if available
            if (! empty($uniqueAttributes)) {
                $export->setUniqueAttributes($uniqueAttributes);
            }

            // Create a more descriptive filename
            $disciplineName = $discipline ? '-' . Str::slug($discipline->name) : '';
            $date = now()->format('Y-m-d');
            $eventName = Str::slug($event->name);
            $filename = "federation-athletes-{$eventName}{$disciplineName}-{$date}.xlsx";

            return Excel::download($export, $filename);
        }

        // Use the common view and pass federation flag
        return view('web.common.evt_event.athlete_enrollment.index', compact('event', 'enrollments', 'uniqueAttributes'));
    }

    protected function adminIndex(Request $request, Event $event): View
    {
        $query = $event->athleteEnrollments()
            ->with([
                'individual',
                'individual.country',
                'enrollment.event',
                'enrollment.enrollable' => function ($query) {
                    $query->withTrashed();
                },
                'attributes.attribute',
                'discipline',
            ])
            ->select('evt_athletes_enrollment.*')
            ->selectSub(function ($query) {
                $query->from('document_detail')
                    ->join('document', 'document.id', '=', 'document_detail.document_id')
                    ->select(DB::raw('1'))
                    ->whereColumn('document_detail.owner_id', 'evt_athletes_enrollment.enrollment_id')
                    ->where('document_detail.owner_type', '=', 'Domain\\EvtEvents\\Models\\Enrollment')
                    ->whereNull('document.deleted_at')
                    ->limit(1);
            }, 'hasDocument')
            ->selectSub(function ($query) {
                $query->from('document_detail')
                    ->join('document', 'document.id', '=', 'document_detail.document_id')
                    ->select('document.id')
                    ->whereColumn('document_detail.owner_id', 'evt_athletes_enrollment.enrollment_id')
                    ->where('document_detail.owner_type', '=', 'Domain\\EvtEvents\\Models\\Enrollment')
                    ->orderByDesc('document.created_at')
                    ->limit(1);
            }, 'document_id')
            ->selectSub(function ($query) {
                $query->from('document_detail')
                    ->join('document', 'document.id', '=', 'document_detail.document_id')
                    ->select('document.status_class')
                    ->whereColumn('document_detail.owner_id', 'evt_athletes_enrollment.enrollment_id')
                    ->where('document_detail.owner_type', '=', 'Domain\\EvtEvents\\Models\\Enrollment')
                    ->orderByDesc('document.created_at')
                    ->limit(1);
            }, 'payment_status')
            ->whereIn('status_class', [
                EvtAthleteEnrollmentStatusEnum::PAID->value,
                EvtAthleteEnrollmentStatusEnum::DISCIPLINE_ASSIGNED->value,
                EvtAthleteEnrollmentStatusEnum::COMPLETED->value,
            ])
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->whereIn('status_class', [
                        EvtAthleteEnrollmentStatusEnum::DISCIPLINE_ASSIGNED->value,
                        EvtAthleteEnrollmentStatusEnum::COMPLETED->value,
                    ])->whereNotNull('discipline_id');
                })->orWhere('status_class', EvtAthleteEnrollmentStatusEnum::PAID->value);
            });

        $this->applyEnrollmentFilters($query, $request->input('filter', []));

        $enrollments = $query
            ->orderBy('evt_athletes_enrollment.created_at', 'desc')
            ->paginate(75)
            ->appends($request->query());

        $disciplines = $event->disciplines()->orderBy('name')->get();

        $statuses = [
            EvtAthleteEnrollmentStatusEnum::PAID->value => EvtAthleteEnrollmentStatusEnum::toString(EvtAthleteEnrollmentStatusEnum::PAID),
            EvtAthleteEnrollmentStatusEnum::DISCIPLINE_ASSIGNED->value => EvtAthleteEnrollmentStatusEnum::toString(EvtAthleteEnrollmentStatusEnum::DISCIPLINE_ASSIGNED),
            EvtAthleteEnrollmentStatusEnum::COMPLETED->value => EvtAthleteEnrollmentStatusEnum::toString(EvtAthleteEnrollmentStatusEnum::COMPLETED),
        ];

        $genders = $this->getGenderOptions();
        $enrolledByOptions = $this->getEnrolledByOptions($event);
        $navigationLinks = $this->getNavigationLinks($event);

        return view('web.federation.evt_event.athlete_enrollment.admin_index', compact(
            'event',
            'enrollments',
            'disciplines',
            'statuses',
            'genders',
            'enrolledByOptions',
            'navigationLinks',
        ));
    }

    public function registered(Request $request, Event $event): View|RedirectResponse
    {
        if (! $this->isDefaultFederation()) {
            return redirect()->route('federation.evt-events.events.athlete-enrollment.index', $event);
        }

        $query = $event->athleteEnrollments()
            ->with([
                'individual',
                'individual.country',
                'enrollment.event',
                'enrollment.enrollable' => function ($query) {
                    $query->withTrashed();
                },
                'attributes.attribute',
                'discipline',
            ])
            ->select('evt_athletes_enrollment.*')
            ->selectSub(function ($query) {
                $query->from('document_detail')
                    ->join('document', 'document.id', '=', 'document_detail.document_id')
                    ->select(DB::raw('1'))
                    ->whereColumn('document_detail.owner_id', 'evt_athletes_enrollment.enrollment_id')
                    ->where('document_detail.owner_type', '=', 'Domain\\EvtEvents\\Models\\Enrollment')
                    ->whereNull('document.deleted_at')
                    ->limit(1);
            }, 'hasDocument')
            ->selectSub(function ($query) {
                $query->from('document_detail')
                    ->join('document', 'document.id', '=', 'document_detail.document_id')
                    ->select('document.id')
                    ->whereColumn('document_detail.owner_id', 'evt_athletes_enrollment.enrollment_id')
                    ->where('document_detail.owner_type', '=', 'Domain\\EvtEvents\\Models\\Enrollment')
                    ->orderByDesc('document.created_at')
                    ->limit(1);
            }, 'document_id')
            ->selectSub(function ($query) {
                $query->from('document_detail')
                    ->join('document', 'document.id', '=', 'document_detail.document_id')
                    ->select('document.status_class')
                    ->whereColumn('document_detail.owner_id', 'evt_athletes_enrollment.enrollment_id')
                    ->where('document_detail.owner_type', '=', 'Domain\\EvtEvents\\Models\\Enrollment')
                    ->orderByDesc('document.created_at')
                    ->limit(1);
            }, 'payment_status')
            ->whereIn('status_class', [
                EvtAthleteEnrollmentStatusEnum::REGISTERED->value,
                EvtAthleteEnrollmentStatusEnum::PENDING_PAYMENT->value,
                EvtAthleteEnrollmentStatusEnum::PAID->value,
                EvtAthleteEnrollmentStatusEnum::CANCELED->value,
            ]);

        $this->applyEnrollmentFilters($query, $request->input('filter', []));

        $enrollments = $query
            ->orderBy('evt_athletes_enrollment.created_at', 'desc')
            ->paginate(75)
            ->appends($request->query());

        $disciplines = $event->disciplines()->orderBy('name')->get();

        $statuses = [
            EvtAthleteEnrollmentStatusEnum::REGISTERED->value => EvtAthleteEnrollmentStatusEnum::toString(EvtAthleteEnrollmentStatusEnum::REGISTERED),
            EvtAthleteEnrollmentStatusEnum::PENDING_PAYMENT->value => EvtAthleteEnrollmentStatusEnum::toString(EvtAthleteEnrollmentStatusEnum::PENDING_PAYMENT),
            EvtAthleteEnrollmentStatusEnum::PAID->value => EvtAthleteEnrollmentStatusEnum::toString(EvtAthleteEnrollmentStatusEnum::PAID),
            EvtAthleteEnrollmentStatusEnum::CANCELED->value => EvtAthleteEnrollmentStatusEnum::toString(EvtAthleteEnrollmentStatusEnum::CANCELED),
        ];

        $genders = $this->getGenderOptions();
        $enrolledByOptions = $this->getEnrolledByOptions($event);
        $navigationLinks = $this->getNavigationLinks($event);

        return view('web.federation.evt_event.athlete_enrollment.admin_registered', compact(
            'event',
            'enrollments',
            'disciplines',
            'statuses',
            'genders',
            'enrolledByOptions',
            'navigationLinks',
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

    public function publicIndex(Event $event): View
    {
        // Logic moved to Livewire\Common\EvtEvent\PublicAthleteEnrollmentList
        return view('web.common.evt_event.athlete_enrollment.public_index', [
            'event' => $event,
        ]);
    }

    public function create(Event $event, ?Discipline $discipline): View|RedirectResponse
    {
        // Check if the event's registration period has ended
        if (now()->isAfter($event->end_registration)) {
            return redirect()->route('federation.evt-events.events.show', $event->id)
                ->with('error', __('events.registration_period_ended'));
        }

        $federation = Auth::user()->federations()->first();

        return view('web.federation.evt_event.athlete_enrollment.create', compact('event', 'federation', 'discipline'));
    }

    public function destroy(Event $event, AthleteEnrollment $athleteEnrollment): RedirectResponse
    {
        try {
            // Only allow deletion of relay team records
            if (! $athleteEnrollment->team_identifier) {
                throw new \Exception('Only relay team records can be removed from this interface.');
            }

            DB::beginTransaction();

            // First verify this team belongs to the current federation
            $firstTeamMember = AthleteEnrollment::where('team_identifier', $athleteEnrollment->team_identifier)
                ->where('event_id', $event->id)
                ->where('federation_id', Auth::user()->getFederationId())
                ->first();

            if (! $firstTeamMember) {
                throw new \Exception('Unauthorized: Cannot remove team members from another federation');
            }

            // If authorized, update all athletes in the same relay team
            // We need to be very specific here to only affect the correct team
            AthleteEnrollment::where('team_identifier', $athleteEnrollment->team_identifier)
                ->where('event_id', $event->id)
                ->where('federation_id', Auth::user()->getFederationId())
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

            return redirect()->back()->with('success', __('events.relay_team_removed'));
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());

            return redirect()->back()->with('error', __('events.failed_to_remove_relay_team'));
        }
    }

}
