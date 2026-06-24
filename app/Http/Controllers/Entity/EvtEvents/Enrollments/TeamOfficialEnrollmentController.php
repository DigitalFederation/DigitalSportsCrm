<?php

namespace App\Http\Controllers\Entity\EvtEvents\Enrollments;

use App\Http\Controllers\Controller;
use Domain\Entities\Models\Entity;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\TeamOfficialEnrollment;
use Domain\EvtEvents\States\AssignedTeamOfficialEnrollmentState;
use Domain\EvtEvents\States\CanceledTeamOfficialEnrollmentState;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TeamOfficialEnrollmentController extends Controller
{
    public function index(Event $event): View|RedirectResponse
    {
        // Check if the event's registration period has ended
        if (now()->isAfter($event->end_registration)) {
            return redirect()->route('entity.evt-events.events.show', $event->id)
                ->with('error', __('The registration period for this event has ended.'));
        }

        $entityId = Auth::user()->entities()->first()?->id;

        $enrollments = $event->officialsEnrollments()
            ->where('entity_id', $entityId)
            ->where('status_class', AssignedTeamOfficialEnrollmentState::class)
            ->with(['individual', 'enrollment', 'attributes.attribute'])
            ->paginate();

        // Extract unique attributes
        $allAttributes = $enrollments->pluck('attributes')
            ->flatten()
            ->pluck('attribute.name')
            ->unique();

        return view('web.entity.evt_event.team_official_enrollment.index', compact('event', 'enrollments', 'allAttributes'));
    }

    public function create(Event $event): View|RedirectResponse
    {
        // Check if the event's registration period has ended
        if (now()->isAfter($event->end_registration)) {
            return redirect()->route('entity.evt-events.events.show', $event->id)
                ->with('error', __('The registration period for this event has ended.'));
        }

        $entity = Auth::user()->entities()->first();

        return view('web.entity.evt_event.team_official_enrollment.create', compact('event', 'entity'));
    }

    public function destroy(Event $event, TeamOfficialEnrollment $team_official_enrollment): RedirectResponse
    {
        $entityId = Auth::user()->entities()->first()?->id;

        // Ensure Enrollment Belongs to Entity
        if ($team_official_enrollment->entity_id !== $entityId) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        DB::beginTransaction();
        try {
            // Cancel the team official enrollment instead of deleting it
            $team_official_enrollment->status_class = CanceledTeamOfficialEnrollmentState::class;
            $team_official_enrollment->save();

            // Log the cancellation
            activity()
                ->performedOn($team_official_enrollment)
                ->withProperties([
                    'event_id' => $event->id,
                    'individual_id' => $team_official_enrollment->individual_id,
                    'entity_id' => $entityId,
                    'old_status' => $team_official_enrollment->getOriginal('status_class'),
                    'new_status' => CanceledTeamOfficialEnrollmentState::class,
                ])
                ->log('Team official enrollment cancelled by entity');

            DB::commit();

            return redirect()->back()->with('success', 'Team official enrollment successfully cancelled.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('TeamOfficialEnrollment Cancellation Error: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Failed to cancel team official enrollment.');
        }
    }
}
