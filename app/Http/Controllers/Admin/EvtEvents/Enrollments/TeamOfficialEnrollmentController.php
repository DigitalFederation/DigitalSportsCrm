<?php

namespace App\Http\Controllers\Admin\EvtEvents\Enrollments;

use App\Exports\TeamOfficialEnrollmentsExport;
use App\Http\Controllers\Common\BaseEnrollmentController;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\TeamOfficialEnrollment;
use Domain\EvtEvents\States\AssignedTeamOfficialEnrollmentState;
use Domain\EvtEvents\States\CanceledTeamOfficialEnrollmentState;
use Domain\EvtEvents\States\PendingTeamOfficialEnrollmentState;
use Domain\EvtEvents\States\RegisteredTeamOfficialEnrollmentState;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Team Official Enrollment Controller for Administrators
 *
 * This controller implements the state pattern for enrollment management.
 * Instead of deleting records, we use a state pattern to track enrollment status:
 * - RegisteredTeamOfficialEnrollmentState: Initial registered state
 * - AssignedTeamOfficialEnrollmentState: Team Official has been assigned attributes
 * - PendingTeamOfficialEnrollmentState: Initial or processing state
 * - CanceledTeamOfficialEnrollmentState: Soft-deleted/inactive state
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
class TeamOfficialEnrollmentController extends BaseEnrollmentController
{
    public function index(Request $request, Event $event): View
    {
        $query = $event->officialsEnrollments()
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
            PendingTeamOfficialEnrollmentState::class => __('events.pending'),
            RegisteredTeamOfficialEnrollmentState::class => __('events.registered'),
            AssignedTeamOfficialEnrollmentState::class => __('events.assigned'),
            CanceledTeamOfficialEnrollmentState::class => __('events.canceled'),
        ];

        $genders = $this->getGenderOptions();
        $enrolledByOptions = $this->getEnrolledByOptions($event);

        $allAttributes = $enrollments->pluck('attributes')
            ->flatten()
            ->pluck('attribute.name')
            ->unique()
            ->values()
            ->toArray();

        return view('web.admin.evt_events.team_official_enrollment.index', compact(
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
        $query = $event->officialsEnrollments()
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
                PendingTeamOfficialEnrollmentState::class,
                RegisteredTeamOfficialEnrollmentState::class,
                CanceledTeamOfficialEnrollmentState::class,
            ]);

        $this->applyEnrollmentFilters($query, $request->input('filter', []));

        $enrollments = $query
            ->orderBy('created_at', 'desc')
            ->paginate(75)
            ->appends($request->query());

        $statuses = [
            PendingTeamOfficialEnrollmentState::class => __('events.pending'),
            RegisteredTeamOfficialEnrollmentState::class => __('events.registered'),
            CanceledTeamOfficialEnrollmentState::class => __('events.canceled'),
        ];

        $genders = $this->getGenderOptions();
        $enrolledByOptions = $this->getEnrolledByOptions($event);

        $allAttributes = $enrollments->pluck('attributes')
            ->flatten()
            ->pluck('attribute.name')
            ->unique()
            ->values()
            ->toArray();

        return view('web.admin.evt_events.team_official_enrollment.registered', compact(
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
        try {
            // Get requested status filter, default to assigned
            $statusFilter = $request->get('status', AssignedTeamOfficialEnrollmentState::class);

            // Build base query
            $query = $event->officialsEnrollments()
                ->with([
                    'individual',
                    'federation:id,name,member_code',
                    'enrollment.event',
                    'enrollment.enrollable',
                    'attributes.attribute',
                ]);

            // Apply status filter if specified
            if ($statusFilter !== 'all') {
                $query->where('status_class', $statusFilter);
            }

            $enrollments = $query->get();

            // Log export information
            Log::info('Team Official Enrollments Export:', [
                'event_id' => $event->id,
                'status_filter' => $statusFilter,
                'count' => $enrollments->count(),
            ]);

            $uniqueAttributes = collect();
            if ($enrollments->isNotEmpty()) {
                foreach ($enrollments as $enrollment) {
                    foreach ($enrollment->attributes as $attribute) {
                        $uniqueAttributes->put($attribute->attribute_id, $attribute->attribute->name);
                    }
                }
                $uniqueAttributes = $uniqueAttributes->sort()->values();
            }

            $export = new TeamOfficialEnrollmentsExport($event);
            $export->setUniqueAttributes($uniqueAttributes);
            $export->setFilteredData($enrollments);

            $filename = sprintf('team_official_enrollments_%s.xlsx', strtolower($statusFilter === 'all' ? 'all' : (new $statusFilter($enrollments->first()))->name()));

            return Excel::download($export, $filename);
        } catch (Exception $e) {
            Log::error('Error in TeamOfficialEnrollmentController@export', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'event_id' => $event->id,
                'url' => request()->url(),
                'full_url' => request()->fullUrl(),
            ]);

            return redirect()->route('admin.evt-events.events.officials-enrollment.index', [
                'event' => $event,
                'status' => $statusFilter,
            ])->with('error', __('events.error_generating_export'));
        }
    }

    /**
     * Cancel a team official enrollment.
     *
     * Following the state pattern, this method transitions the enrollment to canceled state
     * instead of deleting the record. This preserves the enrollment history and maintains
     * data integrity while providing an audit trail.
     */
    public function destroy(Event $event, TeamOfficialEnrollment $officials_enrollment): RedirectResponse
    {
        DB::beginTransaction();
        try {
            $officials_enrollment->status_class = CanceledTeamOfficialEnrollmentState::class;
            $officials_enrollment->save();

            activity()
                ->performedOn($officials_enrollment)
                ->withProperties([
                    'event_id' => $event->id,
                    'individual_id' => $officials_enrollment->individual_id,
                    'old_status' => $officials_enrollment->getOriginal('status_class'),
                    'new_status' => CanceledTeamOfficialEnrollmentState::class,
                ])
                ->log('Team official enrollment cancelled');

            DB::commit();

            return redirect()->route('admin.evt-events.events.officials-enrollment.registered', $event)
                ->with('success', __('events.enrollment_canceled'));
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('TeamOfficialEnrollment Cancellation Error: ' . $e->getMessage(), [
                'event_id' => $event->id,
                'enrollment_id' => $officials_enrollment->id ?? 'ID not available',
                'exception_trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('admin.evt-events.events.officials-enrollment.index', [
                'event' => $event,
            ])->with('error', __('events.enrollment_status_update_error'));
        }
    }

    public function forceDelete(Event $event, TeamOfficialEnrollment $officials_enrollment): RedirectResponse
    {
        abort_unless($officials_enrollment->state->isCanceled(), 403);

        try {
            DB::beginTransaction();

            $officials_enrollment->attributes()->delete();
            $officials_enrollment->delete();

            DB::commit();

            return redirect()->route('admin.evt-events.events.officials-enrollment.registered', $event)
                ->with('success', __('events.enrollment_deleted'));
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('TeamOfficialEnrollment Deletion Error: ' . $e->getMessage());

            return redirect()->route('admin.evt-events.events.officials-enrollment.registered', $event)
                ->with('error', __('events.enrollment_status_update_error'));
        }
    }
}
