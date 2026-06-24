<?php

namespace App\Http\Controllers\Entity\EvtEvents\Enrollments;

use App\Http\Controllers\Controller;
use Domain\Entities\Models\Entity;
use Domain\EvtEvents\Models\CoachEnrollment;
use Domain\EvtEvents\Models\Discipline;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\States\AssignedCoachEnrollmentState;
use Domain\EvtEvents\States\CanceledCoachEnrollmentState;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CoachEnrollmentController extends Controller
{
    public function index(Event $event): View|RedirectResponse
    {
        // Check if the event's registration period has ended
        if (now()->isAfter($event->end_registration)) {
            return redirect()->route('entity.evt-events.events.show', $event->id)
                ->with('error', __('The registration period for this event has ended.'));
        }

        $entityId = Auth::user()->entities()->first()?->id;

        $enrollments = $event->coachEnrollments()
            ->where('entity_id', $entityId)
            ->where('status_class', AssignedCoachEnrollmentState::class)
            ->with(['individual', 'enrollment', 'attributes.attribute'])
            ->paginate();

        // Extract unique attributes
        $allAttributes = $enrollments->pluck('attributes')
            ->flatten()
            ->pluck('attribute.name')
            ->unique();

        return view('web.entity.evt_event.coach_enrollment.index', compact('event', 'enrollments', 'allAttributes'));
    }

    public function create(Event $event, ?Discipline $discipline = null): View|RedirectResponse
    {
        // Check if the event's registration period has ended
        if (now()->isAfter($event->end_registration)) {
            return redirect()->route('entity.evt-events.events.show', $event->id)
                ->with('error', __('The registration period for this event has ended.'));
        }

        $entity = Auth::user()->entities()->first();

        return view('web.entity.evt_event.coach_enrollment.create', compact('event', 'entity', 'discipline'));
    }

    public function destroy(Event $event, CoachEnrollment $coach_enrollment): RedirectResponse
    {
        $entityId = Auth::user()->entities()->first()?->id;

        // Ensure Enrollment Belongs to Entity
        if ($coach_enrollment->entity_id !== $entityId) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

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
                    'entity_id' => $entityId,
                    'old_status' => $coach_enrollment->getOriginal('status_class'),
                    'new_status' => CanceledCoachEnrollmentState::class,
                ])
                ->log('Coach enrollment cancelled by entity');

            DB::commit();

            return redirect()->back()->with('success', 'Coach enrollment successfully cancelled.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('CoachEnrollment Cancellation Error: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Failed to cancel coach enrollment.');
        }
    }
}
