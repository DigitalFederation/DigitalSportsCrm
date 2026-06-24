<?php

namespace App\Http\Controllers\Entity\EvtEvents\Enrollments;

use App\Exports\IndividualEnrollmentsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateIndividualEnrollmentRequest;
use Domain\Entities\Models\Entity;
use Domain\EvtEvents\Models\Event;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class IndividualEnrollmentController extends Controller
{
    public function index(Event $event): RedirectResponse|View
    {
        $entity = auth()->user()->entities()->first();

        $enrollments = $event->individualEnrollments()
            ->whereHas('entity', function ($query) use ($entity) {
                return $query->where('id', $entity->id);
            })
            ->with('individual', 'enrollment.event', 'attributes.attribute')
            ->paginate();

        // Check if enrollments are empty and redirect to create if so
        if ($enrollments->isEmpty() && request()->query('view') !== 'list') {
            return redirect()->action([self::class, 'create'], ['event' => $event]);
        }

        return view('web.entity.evt_event.individual_enrollment.index', compact('event', 'enrollments', 'entity'));
    }

    public function create(Event $event): View|RedirectResponse
    {
        if (! $event->allowsEnrollments()) {
            abort(403, __('Enrollments are currently closed for this event.'));
        }

        $entity = Auth::user()->entities()->first();

        // Validate event type
        if ($event->isSportEvent()) {
            return redirect()
                ->route('entity.evt-events.events.registrations.index', $event)
                ->with('error', 'This registration type is not available for competition events');
        }

        // Get the federation associated with this entity
        $federation = $entity->localFederationIfExists();

        if (! $federation) {
            return redirect()
                ->route('entity.evt-events.events.show', $event->id)
                ->with('error', 'Entity must be associated with a federation to register members.');
        }

        return view('web.entity.evt_event.individual_enrollment.create', compact('event', 'entity', 'federation'));
    }

    public function store(CreateIndividualEnrollmentRequest $request, Event $event)
    {
        if (! $event->allowsEnrollments()) {
            abort(403, __('Enrollments are currently closed for this event.'));
        }

        $entity = Auth::user()->entities()->first();

        // ... existing code ...
    }

    public function export(Event $event): BinaryFileResponse
    {
        $entity = auth()->user()->entities()->first();
        $federation = $entity->localFederationIfExists();

        return Excel::download(
            new IndividualEnrollmentsExport($event, 'entity'),
            "{$event->name}-{$entity->name}-enrollments.xlsx"
        );
    }
}
