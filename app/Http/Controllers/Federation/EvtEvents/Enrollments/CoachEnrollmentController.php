<?php

namespace App\Http\Controllers\Federation\EvtEvents\Enrollments;

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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CoachEnrollmentController extends BaseEnrollmentController
{
    public function index(Request $request, Event $event): View|RedirectResponse
    {
        if ($this->isDefaultFederation()) {
            return $this->adminIndex($request, $event);
        }

        $enrollments = $event->coachEnrollments()
            ->whereHas('federation', function ($query) {
                return $query->where('id', Auth::user()->federations()->first()->id);
            })
            ->where('status_class', AssignedCoachEnrollmentState::class)
            ->with(['individual', 'enrollment', 'attributes.attribute'])
            ->paginate();

        // Extract unique attributes
        $allAttributes = $enrollments->pluck('attributes')
            ->flatten()
            ->pluck('attribute.name')
            ->unique();

        return view('web.federation.evt_event.coach_enrollment.index', compact('event', 'enrollments', 'allAttributes'));
    }

    protected function adminIndex(Request $request, Event $event): View
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
                AssignedCoachEnrollmentState::class,
            ]);

        $this->applyEnrollmentFilters($query, $request->input('filter', []));

        $enrollments = $query
            ->orderBy('created_at', 'desc')
            ->paginate(75)
            ->appends($request->query());

        $statuses = [
            AssignedCoachEnrollmentState::class => __('events.assigned'),
        ];

        $genders = $this->getGenderOptions();
        $enrolledByOptions = $this->getEnrolledByOptions($event);

        $allAttributes = $enrollments->pluck('attributes')
            ->flatten()
            ->pluck('attribute.name')
            ->unique()
            ->values()
            ->toArray();

        $navigationLinks = $this->getNavigationLinks($event);

        return view('web.federation.evt_event.coach_enrollment.admin_index', compact(
            'event',
            'enrollments',
            'allAttributes',
            'statuses',
            'genders',
            'enrolledByOptions',
            'navigationLinks',
        ));
    }

    public function registered(Request $request, Event $event): View|RedirectResponse
    {
        if (! $this->isDefaultFederation()) {
            return redirect()->route('federation.evt-events.events.coach-enrollment.index', $event);
        }

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

        $navigationLinks = $this->getNavigationLinks($event);

        return view('web.federation.evt_event.coach_enrollment.admin_registered', compact(
            'event',
            'enrollments',
            'allAttributes',
            'statuses',
            'genders',
            'enrolledByOptions',
            'navigationLinks',
        ));
    }

    public function destroy(Event $event, CoachEnrollment $coach_enrollment): RedirectResponse
    {
        $federationId = Auth::user()->federations()->first()->id;

        // Ensure Enrollment Belongs to Federation
        if ($coach_enrollment->federation_id !== $federationId) {
            return redirect()->back()->with('error', __('events.unauthorized_action'));
        }

        DB::beginTransaction();
        try {
            $coach_enrollment->status_class = CanceledCoachEnrollmentState::class;
            $coach_enrollment->save();

            activity()
                ->performedOn($coach_enrollment)
                ->withProperties([
                    'event_id' => $event->id,
                    'individual_id' => $coach_enrollment->individual_id,
                    'federation_id' => $federationId,
                    'old_status' => $coach_enrollment->getOriginal('status_class'),
                    'new_status' => CanceledCoachEnrollmentState::class,
                ])
                ->log(__('events.coach_enrollment_cancelled_log'));

            DB::commit();

            return redirect()->back()->with('success', __('events.coach_enrollment_cancelled'));
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('CoachEnrollment Cancellation Error: ' . $e->getMessage());

            return redirect()->back()->with('error', __('events.failed_to_cancel_coach_enrollment'));
        }
    }

}
