<?php

namespace App\Http\Controllers\Federation\EvtEvents\Enrollments;

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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TeamOfficialEnrollmentController extends BaseEnrollmentController
{
    public function index(Request $request, Event $event): View|RedirectResponse
    {
        if ($this->isDefaultFederation()) {
            return $this->adminIndex($request, $event);
        }

        $enrollments = $event->officialsEnrollments()
            ->whereHas('federation', function ($query) {
                return $query->where('id', Auth::user()->federations()->first()->id);
            })
            ->where('status_class', AssignedTeamOfficialEnrollmentState::class)
            ->with(['individual', 'enrollment', 'attributes.attribute'])
            ->paginate();

        // Extract unique attributes
        $allAttributes = $enrollments->pluck('attributes')
            ->flatten()
            ->pluck('attribute.name')
            ->unique();

        return view('web.federation.evt_event.officials_enrollment.index', compact('event', 'enrollments', 'allAttributes'));
    }

    protected function adminIndex(Request $request, Event $event): View
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
                AssignedTeamOfficialEnrollmentState::class,
            ]);

        $this->applyEnrollmentFilters($query, $request->input('filter', []));

        $enrollments = $query
            ->orderBy('created_at', 'desc')
            ->paginate(75)
            ->appends($request->query());

        $statuses = [
            AssignedTeamOfficialEnrollmentState::class => __('events.assigned'),
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

        return view('web.federation.evt_event.officials_enrollment.admin_index', compact(
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
            return redirect()->route('federation.evt-events.events.officials-enrollment.index', $event);
        }

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

        $navigationLinks = $this->getNavigationLinks($event);

        return view('web.federation.evt_event.officials_enrollment.admin_registered', compact(
            'event',
            'enrollments',
            'allAttributes',
            'statuses',
            'genders',
            'enrolledByOptions',
            'navigationLinks',
        ));
    }

    public function destroy(Event $event, TeamOfficialEnrollment $team_official_enrollment): RedirectResponse
    {
        $federationId = Auth::user()->federations()->first()->id;

        // Ensure Enrollment Belongs to Federation
        if ($team_official_enrollment->federation_id !== $federationId) {
            return redirect()->back()->with('error', __('events.unauthorized_action'));
        }

        DB::beginTransaction();
        try {
            $team_official_enrollment->status_class = CanceledTeamOfficialEnrollmentState::class;
            $team_official_enrollment->save();

            activity()
                ->performedOn($team_official_enrollment)
                ->withProperties([
                    'event_id' => $event->id,
                    'individual_id' => $team_official_enrollment->individual_id,
                    'federation_id' => $federationId,
                    'old_status' => $team_official_enrollment->getOriginal('status_class'),
                    'new_status' => CanceledTeamOfficialEnrollmentState::class,
                ])
                ->log(__('events.team_official_enrollment_cancelled_log'));

            DB::commit();

            return redirect()->back()->with('success', __('events.team_official_enrollment_cancelled'));
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('TeamOfficialEnrollment Cancellation Error: ' . $e->getMessage());

            return redirect()->back()->with('error', __('events.failed_to_cancel_team_official_enrollment'));
        }
    }

}
