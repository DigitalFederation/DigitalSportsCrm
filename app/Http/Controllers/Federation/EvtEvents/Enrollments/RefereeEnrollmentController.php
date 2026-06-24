<?php

namespace App\Http\Controllers\Federation\EvtEvents\Enrollments;

use App\Http\Controllers\Common\BaseEnrollmentController;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\RefereeEnrollment;
use Domain\EvtEvents\States\CanceledRefereeEnrollmentState;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RefereeEnrollmentController extends BaseEnrollmentController
{
    public function index(Request $request, Event $event): View
    {
        $query = $event->refereeEnrollments()
            ->where('status_class', '!=', CanceledRefereeEnrollmentState::class)
            ->with([
                'individual',
                'federation',
                'enrollment.enrollable',
                'attributes.attribute',
            ]);

        $this->applyEnrollmentFilters($query, $request->input('filter', []));

        $enrollments = $query
            ->orderBy('created_at', 'desc')
            ->paginate(100)
            ->appends($request->query());

        $uniqueAttributes = collect();
        if ($enrollments->isNotEmpty()) {
            foreach ($enrollments as $enrollment) {
                foreach ($enrollment->attributes as $attribute) {
                    if ($attribute->attribute) {
                        $uniqueAttributes->put($attribute->attribute_id, $attribute->attribute->name);
                    }
                }
            }
            $uniqueAttributes = $uniqueAttributes->sort();
        }

        return view('web.federation.evt_event.referee_enrollment.index', compact('event', 'enrollments', 'uniqueAttributes'));
    }

    public function create(Event $event): View
    {
        return view('web.federation.evt_event.referee_enrollment.create', compact('event'));
    }

    public function destroy(Event $event, RefereeEnrollment $referee_enrollment): RedirectResponse
    {
        $federationId = Auth::user()->federations()->first()->id;

        if ($referee_enrollment->federation_id !== $federationId) {
            return redirect()->back()->with('error', __('events.unauthorized_action'));
        }

        DB::beginTransaction();
        try {
            $referee_enrollment->status_class = CanceledRefereeEnrollmentState::class;
            $referee_enrollment->save();

            activity()
                ->performedOn($referee_enrollment)
                ->withProperties([
                    'event_id' => $event->id,
                    'individual_id' => $referee_enrollment->individual_id,
                    'federation_id' => $federationId,
                    'old_status' => $referee_enrollment->getOriginal('status_class'),
                    'new_status' => CanceledRefereeEnrollmentState::class,
                ])
                ->log(__('events.referee_enrollment_cancelled_log'));

            DB::commit();

            return redirect()->route('federation.evt-events.events.referee-enrollment.index', [
                'event' => $event,
            ])->with('success', __('events.referee_enrollment_cancelled'));
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('RefereeEnrollment Cancellation Error: ' . $e->getMessage());

            return redirect()->route('federation.evt-events.events.referee-enrollment.index', [
                'event' => $event,
            ])->with('error', __('events.failed_to_cancel_referee_enrollment'));
        }
    }
}
