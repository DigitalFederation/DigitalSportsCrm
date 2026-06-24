<?php

namespace App\Http\Controllers\Admin\EvtEvents\Enrollments;

use App\Exports\RefereeEnrollmentsExport;
use App\Http\Controllers\Controller;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\RefereeEnrollment;
use Domain\EvtEvents\States\ActiveRefereeEnrollmentState;
use Domain\EvtEvents\States\CanceledRefereeEnrollmentState;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class RefereeEnrollmentController extends Controller
{
    public function index(Request $request, Event $event): RedirectResponse|View
    {
        $query = $event->refereeEnrollments()
            ->where('status_class', '!=', CanceledRefereeEnrollmentState::class)
            ->with([
                'individual',
                'federation',
                'enrollment.enrollable',
                'attributes.attribute',
            ]);

        $filters = $request->input('filter', []);

        $query->when($filters['name'] ?? null, function ($q, $name) {
            $q->whereHas('individual', function ($q) use ($name) {
                $q->where(function ($q) use ($name) {
                    $q->where('name', 'like', "%{$name}%")
                        ->orWhere('surname', 'like', "%{$name}%");
                });
            });
        });

        $query->when($filters['member_number'] ?? null, function ($q, $memberNumber) {
            $q->whereHas('individual', function ($q) use ($memberNumber) {
                $q->where('member_number', 'like', "%{$memberNumber}%");
            });
        });

        $enrollments = $query
            ->orderBy('created_at', 'desc')
            ->paginate(100)
            ->appends($request->query());

        // Extract unique attributes for display in the view
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

        return view('web.admin.evt_events.referee_enrollment.index', compact('event', 'enrollments', 'uniqueAttributes'));
    }

    public function create(Event $event): View
    {
        // admin users don't need to pass a federation - this will be handled by the Livewire component
        return view('web.admin.evt_events.referee_enrollment.create', compact('event'));
    }

    public function export(Request $request, Event $event)
    {
        $enrollments = $event->refereeEnrollments()
            ->where('status_class', '!=', CanceledRefereeEnrollmentState::class)
            ->with(['individual', 'enrollment.event', 'enrollment.enrollable', 'attributes.attribute'])
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

        $export = new RefereeEnrollmentsExport($event);
        $export->setUniqueAttributes($uniqueAttributes);

        return Excel::download($export, 'team_officials_enrollments.xlsx');
    }

    /**
     * Cancel a referee enrollment.
     *
     * Following the state pattern, this method transitions the enrollment to canceled state
     * instead of deleting the record. This preserves the enrollment history and maintains
     * data integrity while providing an audit trail.
     */
    public function destroy(Event $event, RefereeEnrollment $referee_enrollment): RedirectResponse
    {
        DB::beginTransaction();
        try {
            // Cancel the referee enrollment instead of deleting it
            $referee_enrollment->status_class = CanceledRefereeEnrollmentState::class;
            $referee_enrollment->save();

            // Log the cancellation
            activity()
                ->performedOn($referee_enrollment)
                ->withProperties([
                    'event_id' => $event->id,
                    'individual_id' => $referee_enrollment->individual_id,
                    'old_status' => $referee_enrollment->getOriginal('status_class'),
                    'new_status' => CanceledRefereeEnrollmentState::class,
                ])
                ->log('Referee enrollment cancelled');

            DB::commit();

            return redirect()->route('admin.evt-events.events.referee-enrollment.index', [
                'event' => $event,
                'status' => request('status', ActiveRefereeEnrollmentState::class),
            ])->with('success', 'Referee enrollment successfully cancelled.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('RefereeEnrollment Cancellation Error: ' . $e->getMessage());

            return redirect()->route('admin.evt-events.events.referee-enrollment.index', [
                'event' => $event,
                'status' => request('status', ActiveRefereeEnrollmentState::class),
            ])->with('error', 'Failed to cancel referee enrollment.');
        }
    }
}
