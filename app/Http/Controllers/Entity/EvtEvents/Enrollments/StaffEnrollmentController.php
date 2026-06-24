<?php

namespace App\Http\Controllers\Entity\EvtEvents\Enrollments;

use App\Http\Controllers\Controller;
use Domain\Entities\Models\Entity;
use Domain\EvtEvents\Models\Discipline;
use Domain\EvtEvents\Models\Event;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class StaffEnrollmentController extends Controller
{
    public function index(Event $event, ?Discipline $discipline): View
    {
        $entity = Auth::user()->entities()->first();

        $isOrganizer = $event->organizer()
            ->where('organizable_id', $entity->id)
            ->where('organizable_type', Entity::class)
            ->exists();

        if (! $isOrganizer) {
            abort(403, __('events.entity_not_organizer'));
        }

        $enrollments = $event->staffEnrollments()
            ->with(['individual', 'event', 'attributes.attribute', 'federation'])
            ->orderBy('created_at', 'desc')
            ->paginate();

        return view(
            'web.entity.evt_event.staff_enrollment.index',
            compact('event', 'enrollments', 'discipline', 'entity')
        );
    }

    public function create(Event $event, ?Discipline $discipline): View
    {
        $entity = Auth::user()->entities()->first();

        $isOrganizer = $event->organizer()
            ->where('organizable_id', $entity->id)
            ->where('organizable_type', Entity::class)
            ->exists();

        if (! $isOrganizer) {
            abort(403, __('events.entity_not_organizer'));
        }

        return view('web.entity.evt_event.staff_enrollment.create', compact('event', 'entity', 'discipline'));
    }
}
