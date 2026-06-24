<?php

namespace App\Http\Controllers\Federation\EvtEvents\Enrollments;

use App\Exports\StaffFederationEnrollmentsExport;
use App\Http\Controllers\Controller;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\StaffEnrollment;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StaffEnrollmentController extends Controller
{
    public function index(Request $request, Event $event): View|BinaryFileResponse
    {
        if ($request->has('export')) {
            return Excel::download(
                new StaffFederationEnrollmentsExport($event),
                'staff-enrollments.xlsx'
            );
        }

        $query = $event->staffEnrollments()
            ->with(['individual', 'event', 'attributes.attribute', 'federation'])
            ->orderBy('created_at', 'desc');

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

        return view(
            'web.federation.evt_event.staff_enrollment.index',
            compact('event', 'enrollments', 'uniqueAttributes')
        );
    }

    public function create(Event $event): View
    {
        $federation = Auth::user()->federations()->first();

        return view('web.federation.evt_event.staff_enrollment.create', compact('event', 'federation'));
    }

    public function destroy(Event $event, StaffEnrollment $staffEnrollment): RedirectResponse
    {
        $staffEnrollment->attributes()->delete();
        $staffEnrollment->delete();

        return back()->with('success', __('events.staff_removed_successfully'));
    }
}
