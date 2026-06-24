<?php

namespace App\Http\Controllers\Admin\EvtEvents\Enrollments;

use App\Exports\StaffEnrollmentsExport;
use App\Http\Controllers\Common\BaseEnrollmentController;
use Domain\EvtEvents\Models\Discipline;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\StaffEnrollment;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StaffEnrollmentController extends BaseEnrollmentController
{
    public function index(Request $request, Event $event): RedirectResponse|View
    {
        $query = $event->staffEnrollments()
            ->with(['individual', 'federation', 'attributes.attribute']);

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

        return view('web.admin.evt_events.staff_enrollment.index', compact(
            'event',
            'enrollments',
            'uniqueAttributes'
        ));
    }

    public function create(Event $event, ?Discipline $discipline): View
    {
        // admin users don't need to pass a federation - this will be handled by the Livewire component
        return view('web.admin.evt_events.staff_enrollment.create', compact('event', 'discipline'));
    }

    public function export(Request $request, Event $event)
    {
        // First extract unique attributes, similar to the index method
        $enrollments = $event->staffEnrollments()
            ->with(['individual', 'federation', 'attributes.attribute'])
            ->get();

        // Extract unique attributes for the export
        $uniqueAttributes = collect();
        if ($enrollments->isNotEmpty()) {
            foreach ($enrollments as $enrollment) {
                foreach ($enrollment->attributes as $attribute) {
                    if ($attribute->attribute) {
                        // Maintain the attribute_id as the key
                        $uniqueAttributes->put($attribute->attribute_id, $attribute->attribute->name);
                    }
                }
            }
            // Keep attribute IDs as keys
            $uniqueAttributes = $uniqueAttributes->sort();
        }

        return $this->processExport($request, StaffEnrollmentsExport::class, $event, 'staff', $uniqueAttributes);
    }

    public function destroy(Event $event, StaffEnrollment $staffEnrollment): RedirectResponse
    {
        // admin users can delete any staff enrollment
        $staffEnrollment->attributes()->delete();
        $staffEnrollment->delete();

        return back()->with('success', 'Staff member removed successfully.');
    }
}
