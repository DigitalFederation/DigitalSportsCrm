<?php

namespace App\Http\Controllers\Admin\EvtEvents\Enrollments;

use App\Exports\CoachEnrollmentsExport;
use App\Http\Controllers\Common\BaseEnrollmentController;
use Domain\EvtEvents\Models\CoachEnrollment;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\States\AssignedCoachEnrollmentState;
use Domain\EvtEvents\States\CanceledCoachEnrollmentState;
use Domain\EvtEvents\States\PendingCoachEnrollmentState;
use Domain\EvtEvents\States\RegisteredCoachEnrollmentState;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Coach Enrollment Controller for Administrators
 *
 * This controller implements the state pattern for enrollment management.
 * Instead of deleting records, we use a state pattern to track enrollment status:
 * - RegisteredCoachEnrollmentState: Initial registered state
 * - AssignedCoachEnrollmentState: Coach has been assigned attributes
 * - PendingCoachEnrollmentState: Initial or processing state
 * - CanceledCoachEnrollmentState: Soft-deleted/inactive state
 *
 * Key Implementation Notes:
 * 1. No Hard Deletions: Use state transitions instead of deleting records
 * 2. Full Visibility: Admins can view all states via status filter
 * 3. Audit Trail: All state changes are logged
 * 4. Data Integrity: Enrollment history is preserved
 *
 * For full documentation of the enrollment state pattern, see:
 * docs/enrollment-states.md
 */
class CoachEnrollmentController extends BaseEnrollmentController
{
    public function index(Request $request, Event $event): View
    {
        $query = $event->coachEnrollments()
            ->with([
                'individual',
                'federation:id,name,member_code',
                'enrollment.event',
                'enrollment.enrollable' => function ($query) {
                    $query->withTrashed();
                },
                'attributes.attribute',
            ]);

        $this->applyEnrollmentFilters($query, $request->input('filter', []));

        $enrollments = $query
            ->orderBy('created_at', 'desc')
            ->paginate(75)
            ->appends($request->query());

        $statuses = [
            PendingCoachEnrollmentState::class => __('events.pending'),
            RegisteredCoachEnrollmentState::class => __('events.registered'),
            AssignedCoachEnrollmentState::class => __('events.assigned'),
            CanceledCoachEnrollmentState::class => __('events.canceled'),
        ];

        $genders = $this->getGenderOptions();
        $enrolledByOptions = $this->getEnrolledByOptions($event);

        $allAttributes = $enrollments->pluck('attributes')
            ->flatten()
            ->pluck('attribute.name')
            ->unique()
            ->values()
            ->toArray();

        return view('web.admin.evt_events.coach_enrollment.index', compact(
            'event',
            'enrollments',
            'allAttributes',
            'statuses',
            'genders',
            'enrolledByOptions',
        ));
    }

    public function registered(Request $request, Event $event): View
    {
        $query = $event->coachEnrollments()
            ->with([
                'individual',
                'federation:id,name,member_code',
                'enrollment.event',
                'enrollment.enrollable' => function ($query) {
                    $query->withTrashed();
                },
                'attributes.attribute',
            ])
            ->whereIn('status_class', [
                PendingCoachEnrollmentState::class,
                RegisteredCoachEnrollmentState::class,
                CanceledCoachEnrollmentState::class,
            ]);

        $this->applyEnrollmentFilters($query, $request->input('filter', []));

        $enrollments = $query
            ->orderBy('created_at', 'desc')
            ->paginate(75)
            ->appends($request->query());

        $statuses = [
            PendingCoachEnrollmentState::class => __('events.pending'),
            RegisteredCoachEnrollmentState::class => __('events.registered'),
            CanceledCoachEnrollmentState::class => __('events.canceled'),
        ];

        $genders = $this->getGenderOptions();
        $enrolledByOptions = $this->getEnrolledByOptions($event);

        $allAttributes = $enrollments->pluck('attributes')
            ->flatten()
            ->pluck('attribute.name')
            ->unique()
            ->values()
            ->toArray();

        return view('web.admin.evt_events.coach_enrollment.registered', compact(
            'event',
            'enrollments',
            'allAttributes',
            'statuses',
            'genders',
            'enrolledByOptions',
        ));
    }

    public function export(Request $request, Event $event)
    {
        // Get requested status filter, default to assigned
        $statusFilter = $request->get('status', AssignedCoachEnrollmentState::class);

        // Build base query
        $query = $event->coachEnrollments()
            ->with(['individual', 'enrollment.event', 'enrollment.enrollable', 'attributes.attribute']);

        // Apply status filter if specified
        if ($statusFilter !== 'all') {
            $query->where('status_class', $statusFilter);
        }

        $enrollments = $query->get();

        $uniqueAttributes = collect();
        if ($enrollments->isNotEmpty()) {
            foreach ($enrollments as $enrollment) {
                foreach ($enrollment->attributes as $attribute) {
                    $uniqueAttributes->put($attribute->attribute_id, $attribute->attribute->name);
                }
            }
            $uniqueAttributes = $uniqueAttributes->sort()->values();
        }

        $export = new CoachEnrollmentsExport($event);
        $export->setUniqueAttributes($uniqueAttributes);

        // Format event name for filename (remove special chars and spaces)
        $cleanEventName = preg_replace('/[^A-Za-z0-9]/', '_', $event->name);
        $cleanEventName = trim(preg_replace('/_+/', '_', $cleanEventName), '_'); // Remove multiple underscores

        $filename = sprintf(
            'coach_enrollments_%s_%s.xlsx',
            strtolower($statusFilter === 'all' ? 'all' : (new $statusFilter($enrollments->first()))->name()),
            $cleanEventName
        );

        return Excel::download($export, $filename);
    }

    /**
     * Cancel a coach enrollment.
     *
     * Following the state pattern, this method transitions the enrollment to canceled state
     * instead of deleting the record. This preserves the enrollment history and maintains
     * data integrity while providing an audit trail.
     */
    public function destroy(Event $event, CoachEnrollment $coach_enrollment): RedirectResponse
    {
        DB::beginTransaction();
        try {
            // Cancel the coach enrollment instead of deleting it
            $coach_enrollment->status_class = CanceledCoachEnrollmentState::class;
            $coach_enrollment->save();

            // Log the cancellation
            activity()
                ->performedOn($coach_enrollment)
                ->withProperties([
                    'event_id' => $event->id,
                    'individual_id' => $coach_enrollment->individual_id,
                    'old_status' => $coach_enrollment->getOriginal('status_class'),
                    'new_status' => CanceledCoachEnrollmentState::class,
                ])
                ->log('Coach enrollment cancelled');

            DB::commit();

            return redirect()->route('admin.evt-events.events.enrollments.coach.registered', $event)
                ->with('success', __('events.enrollment_canceled'));
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('CoachEnrollment Cancellation Error: ' . $e->getMessage());

            return redirect()->route('admin.evt-events.events.enrollments.coach.index', [
                'event' => $event,
            ])->with('error', __('events.enrollment_status_update_error'));
        }
    }

    public function forceDelete(Event $event, CoachEnrollment $coach_enrollment): RedirectResponse
    {
        abort_unless($coach_enrollment->state->isCanceled(), 403);

        try {
            DB::beginTransaction();

            $coach_enrollment->attributes()->delete();
            $coach_enrollment->delete();

            DB::commit();

            return redirect()->route('admin.evt-events.events.enrollments.coach.registered', $event)
                ->with('success', __('events.enrollment_deleted'));
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('CoachEnrollment Deletion Error: ' . $e->getMessage());

            return redirect()->route('admin.evt-events.events.enrollments.coach.registered', $event)
                ->with('error', __('events.enrollment_status_update_error'));
        }
    }
}
