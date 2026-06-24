<?php

namespace App\Http\Controllers\Admin\EvtEvents\Enrollments;

use App\Enums\EvtAthleteEnrollmentStatusEnum;
use App\Exports\AthleteEnrollmentByDisciplineExport;
use App\Exports\AthleteEnrollmentsExport;
use App\Http\Controllers\Common\BaseEnrollmentController;
use App\Http\Requests\GenerateAthleteEnrollmentPaymentRequest;
use App\Http\Requests\UpdateAthleteEnrollmentStatusRequest;
use Domain\Documents\Models\DocumentDetail;
use Domain\EvtEvents\Actions\ConfirmAthleteEnrollmentCompletionAction;
use Domain\EvtEvents\Actions\GetDisciplinesFromEventAction;
use Domain\EvtEvents\Actions\ManualGenerateAthleteEnrollmentPaymentAction;
use Domain\EvtEvents\Actions\UpdateAthleteEnrollmentStatusAction;
use Domain\EvtEvents\Models\AthleteEnrollment;
use Domain\EvtEvents\Models\Discipline;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Maatwebsite\Excel\Facades\Excel;

class AthleteEnrollmentController extends BaseEnrollmentController
{
    public function index(Request $request, Event $event): RedirectResponse|View
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

        $disciplines = (new GetDisciplinesFromEventAction)->execute($event)['disciplines'] ?? collect();

        $statuses = [
            EvtAthleteEnrollmentStatusEnum::PAID->value => EvtAthleteEnrollmentStatusEnum::toString(EvtAthleteEnrollmentStatusEnum::PAID),
            EvtAthleteEnrollmentStatusEnum::DISCIPLINE_ASSIGNED->value => EvtAthleteEnrollmentStatusEnum::toString(EvtAthleteEnrollmentStatusEnum::DISCIPLINE_ASSIGNED),
            EvtAthleteEnrollmentStatusEnum::COMPLETED->value => EvtAthleteEnrollmentStatusEnum::toString(EvtAthleteEnrollmentStatusEnum::COMPLETED),
        ];

        $genders = $this->getGenderOptions();
        $enrolledByOptions = $this->getEnrolledByOptions($event);

        return view('web.admin.evt_events.athlete_enrollment.index', compact(
            'event',
            'enrollments',
            'disciplines',
            'statuses',
            'genders',
            'enrolledByOptions',
        ));
    }

    /**
     * Export athlete enrollments.
     *
     * @return RedirectResponse
     */
    public function export(Request $request, Event $event, ?Discipline $discipline)
    {
        // Collect unique attributes for athlete enrollments
        $enrollments = $event->athleteEnrollments()->with([
            'individual',
            'individual.country',
            'enrollment.event',
            'enrollment.enrollable',
            'attributes.attribute',
            'discipline',
        ])
            ->get();

        $uniqueAttributes = collect();
        if ($enrollments->isNotEmpty()) {
            foreach ($enrollments as $enrollment) {
                foreach ($enrollment->attributes as $attribute) {
                    $uniqueAttributes->put($attribute->attribute_id, $attribute->attribute->name);
                }
            }
            $uniqueAttributes = $uniqueAttributes->sort()->values();
        }

        return $this->processExport($request, AthleteEnrollmentsExport::class, $event, 'athlete', $uniqueAttributes, $discipline);
    }

    /**
     * Export athlete enrollments by discipline.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|RedirectResponse
     */
    public function exportByDiscipline(Request $request, Event $event)
    {
        try {
            // Collect unique attributes for athlete enrollments
            $enrollments = $event->athleteEnrollments()
                ->whereHas('individual')
                ->with([
                    'individual',
                    'individual.country',
                    'enrollment.event',
                    'enrollment.enrollable',
                    'attributes.attribute',
                    'discipline',
                ])
                ->get();

            $uniqueAttributes = $enrollments->flatMap->attributes
                ->pluck('attribute.name', 'attribute.id')
                ->unique()
                ->sort()
                ->values();

            $fileName = Str::slug($event->name) . '-individual-athletes-by-discipline-' . now()->timestamp . '.xlsx';

            $export = new AthleteEnrollmentByDisciplineExport($event);
            $export->setUniqueAttributes($uniqueAttributes);

            return Excel::download($export, $fileName);
        } catch (\Exception $e) {
            Log::error('Error exporting individual athletes by discipline: ' . $e->getMessage());

            return back()->with('error', __('events.error_generating_export'));
        }
    }

    public function destroy(
        Event $event,
        AthleteEnrollment $athleteEnrollment
    ): RedirectResponse {
        try {
            $athleteEnrollment->status_class = EvtAthleteEnrollmentStatusEnum::CANCELED;
            $athleteEnrollment->save();

            activity()
                ->performedOn($athleteEnrollment)
                ->withProperties([
                    'event_id' => $event->id,
                    'individual_id' => $athleteEnrollment->individual_id,
                    'old_status' => $athleteEnrollment->getOriginal('status_class'),
                    'new_status' => EvtAthleteEnrollmentStatusEnum::CANCELED->value,
                ])
                ->log('Athlete enrollment cancelled');

            return redirect()->route('admin.evt-events.events.enrollments.athlete.registered', $event)
                ->with('success', __('events.enrollment_canceled'));
        } catch (\Exception $e) {
            Log::error('Error canceling athlete enrollment: ' . $e->getMessage());

            return redirect()->route('admin.evt-events.events.enrollments.athlete.index', $event)
                ->with('error', __('events.enrollment_status_update_error'));
        }
    }

    public function forceDelete(
        Event $event,
        AthleteEnrollment $athleteEnrollment
    ): RedirectResponse {
        abort_unless($athleteEnrollment->status_class === EvtAthleteEnrollmentStatusEnum::CANCELED, 403);

        try {
            DB::beginTransaction();

            $athleteEnrollment->attributes()->forceDelete();
            $athleteEnrollment->forceDelete();

            DB::commit();

            return redirect()->route('admin.evt-events.events.enrollments.athlete.registered', $event)
                ->with('success', __('events.enrollment_deleted'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting athlete enrollment: ' . $e->getMessage());

            return redirect()->route('admin.evt-events.events.enrollments.athlete.registered', $event)
                ->with('error', __('events.enrollment_status_update_error'));
        }
    }

    public function updateStatus(
        Event $event,
        AthleteEnrollment $athleteEnrollment,
        UpdateAthleteEnrollmentStatusRequest $request,
        UpdateAthleteEnrollmentStatusAction $updateStatusAction
    ): RedirectResponse {
        try {
            $updateStatusAction->execute(
                athleteEnrollment: $athleteEnrollment,
                newStatus: $request->validated('new_status'),
                user: Auth::user()
            );

            return redirect()->back()->with('success', __('events.enrollment_status_updated'));
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            Log::error("Error updating athlete enrollment status: {$e->getMessage()}");

            return redirect()->back()->with('error', __('events.enrollment_status_update_error'));
        }
    }

    /**
     * Confirm athlete enrollment as completed.
     * This is the only way to set an enrollment to COMPLETED status.
     */
    public function confirmCompletion(
        Event $event,
        AthleteEnrollment $athleteEnrollment,
        ConfirmAthleteEnrollmentCompletionAction $confirmAction
    ): RedirectResponse {
        try {
            $confirmAction->execute(
                athleteEnrollment: $athleteEnrollment,
                user: Auth::user()
            );

            return redirect()->back()->with('success', __('events.enrollment_confirmed_completed'));
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            Log::error("Error confirming athlete enrollment completion: {$e->getMessage()}");

            return redirect()->back()->with('error', __('events.enrollment_confirmation_error'));
        }
    }

    /**
     * Confirm multiple athlete enrollments as completed (bulk action).
     */
    public function confirmCompletionBulk(
        Event $event,
        Request $request,
        ConfirmAthleteEnrollmentCompletionAction $confirmAction
    ): RedirectResponse {
        $enrollmentIds = $request->input('enrollment_ids', []);

        if (empty($enrollmentIds)) {
            return redirect()->back()->with('error', __('events.no_enrollments_selected'));
        }

        $enrollments = AthleteEnrollment::whereIn('id', $enrollmentIds)
            ->where('event_id', $event->id)
            ->get();

        $result = $confirmAction->executeMany($enrollments, Auth::user());

        $message = __('events.bulk_confirmation_result', [
            'confirmed' => $result['confirmed'],
            'skipped' => $result['skipped'],
        ]);

        if (! empty($result['errors'])) {
            return redirect()->back()->with('warning', $message);
        }

        return redirect()->back()->with('success', $message);
    }

    public function generatePaymentDocument(
        GenerateAthleteEnrollmentPaymentRequest $request,
        Event $event,
        AthleteEnrollment $athleteEnrollment,
        ManualGenerateAthleteEnrollmentPaymentAction $generatePaymentAction
    ): RedirectResponse {
        DB::beginTransaction();
        try {

            $enrollment = $athleteEnrollment->enrollment;

            // Check if a payment document already exists
            $existingDetail = DocumentDetail::where('owner_id', $enrollment->id)
                ->where('owner_type', Enrollment::class)
                ->exists();

            if ($existingDetail) {

                return redirect()->route('admin.evt-events.events.enrollments.athlete.index', $event)
                    ->with('success', __('events.payment_document_already_exists'));
            }

            $document = $generatePaymentAction($enrollment);

            if ($document) {
                activity()
                    ->performedOn($document)
                    ->causedBy(Auth::user())
                    ->log('Payment document manually generated for athlete');
            } else {
                Log::error("Failed to generate payment document for athlete enrollment ID: {$athleteEnrollment->id}");
            }

            DB::commit();

            return redirect()->route('admin.evt-events.events.enrollments.athlete.index', $event)
                ->with('success', __('events.payment_document_generated'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error generating payment document: {$e->getMessage()}");

            return redirect()->route('admin.evt-events.events.enrollments.athlete.index', $event)
                ->with('error', __('events.payment_generation_failed'));
        }
    }

    public function registered(Request $request, Event $event): View
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

        $disciplines = (new GetDisciplinesFromEventAction)->execute($event)['disciplines'] ?? collect();

        $statuses = [
            EvtAthleteEnrollmentStatusEnum::REGISTERED->value => EvtAthleteEnrollmentStatusEnum::toString(EvtAthleteEnrollmentStatusEnum::REGISTERED),
            EvtAthleteEnrollmentStatusEnum::PENDING_PAYMENT->value => EvtAthleteEnrollmentStatusEnum::toString(EvtAthleteEnrollmentStatusEnum::PENDING_PAYMENT),
            EvtAthleteEnrollmentStatusEnum::PAID->value => EvtAthleteEnrollmentStatusEnum::toString(EvtAthleteEnrollmentStatusEnum::PAID),
            EvtAthleteEnrollmentStatusEnum::CANCELED->value => EvtAthleteEnrollmentStatusEnum::toString(EvtAthleteEnrollmentStatusEnum::CANCELED),
        ];

        $genders = $this->getGenderOptions();
        $enrolledByOptions = $this->getEnrolledByOptions($event);

        return view('web.admin.evt_events.athlete_enrollment.registered', compact(
            'event',
            'enrollments',
            'disciplines',
            'statuses',
            'genders',
            'enrolledByOptions',
        ));
    }
}
