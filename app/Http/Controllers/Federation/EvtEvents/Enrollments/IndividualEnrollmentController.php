<?php

namespace App\Http\Controllers\Federation\EvtEvents\Enrollments;

use App\Exports\IndividualEnrollmentsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateIndividualEnrollmentRequest;
use Domain\EvtEvents\Models\Event;
use Domain\Federations\Models\Federation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class IndividualEnrollmentController extends Controller
{
    public function index(Event $event): RedirectResponse|View
    {
        $federation = Auth::user()->federations()->first();

        $enrollments = $event->individualEnrollments()
            ->whereHas('federation', function ($query) use ($federation) {
                return $query->where('id', $federation->id);
            })
            ->with('individual', 'enrollment.event', 'attributes.attribute')
            ->paginate();

        // Check if enrollments are empty and redirect to create if so
        if ($enrollments->isEmpty() && request()->query('view') !== 'list') {
            return redirect()->action([self::class, 'create'], ['event' => $event]);
        }

        return view('web.federation.evt_event.individual_enrollment.index', compact('event', 'enrollments', 'federation'));
    }

    public function create(Event $event): View|Redirect|RedirectResponse
    {
        if (! $event->allowsEnrollments()) {
            abort(403, __('Enrollments are currently closed for this event.'));
        }

        $federation = Auth::user()->getFederation();

        // Validate event type
        if ($event->isSportEvent()) {
            return redirect()
                ->route('federation.evt-events.registrations.index', $event)
                ->with('error', 'This registration type is not available for competition events');
        }

        return view('web.federation.evt_event.individual_enrollment.create', compact('event', 'federation'));
    }

    public function export(Event $event): BinaryFileResponse
    {
        $federation = Auth::user()->federations()->first();

        return Excel::download(
            new IndividualEnrollmentsExport($event, 'federation'),
            "{$event->name}-{$federation->name}-enrollments.xlsx"
        );
    }

    /**
     * Remove an individual enrollment from the event.
     */
    public function destroy(
        Event $event,
        \Domain\EvtEvents\Models\IndividualEnrollment $individualEnrollment
    ): RedirectResponse {
        // Get the current federation
        $federation = Auth::user()->federations()->first();

        // Check if the enrollment belongs to the current federation
        if ($individualEnrollment->federation_id !== $federation->id) {
            abort(403, 'You are not authorized to delete this enrollment.');
        }

        try {
            DB::beginTransaction();

            // Delete related attributes first
            $individualEnrollment->attributes()->delete();

            // Then delete the individual enrollment
            $individualEnrollment->delete();

            DB::commit();

            return redirect()->route('federation.evt-events.events.individual-enrollment.index', $event)
                ->with('success', 'Individual enrollment deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error deleting individual enrollment: ' . $e->getMessage());

            return redirect()->route('federation.evt-events.events.individual-enrollment.index', $event)
                ->with('error', 'An error occurred while deleting the individual enrollment.');
        }
    }

    public function store(CreateIndividualEnrollmentRequest $request, Event $event)
    {
        if (! $event->allowsEnrollments()) {
            abort(403, __('Enrollments are currently closed for this event.'));
        }

        $validated = $request->validated();
        // ... existing code ...
    }
}
