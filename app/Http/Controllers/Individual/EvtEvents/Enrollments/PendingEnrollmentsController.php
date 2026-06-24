<?php

namespace App\Http\Controllers\Individual\EvtEvents\Enrollments;

use App\Enums\EvtEventPaymentStatusEnum;
use App\Http\Controllers\Controller;
use Domain\EvtEvents\Actions\CreateIndividualEnrollmentOrderAction;
use Domain\EvtEvents\Models\Enrollment;
use Domain\Individuals\Models\Individual;
use Illuminate\Support\Facades\Auth;

class PendingEnrollmentsController extends Controller
{
    public function index()
    {
        $individual = Auth::user()->individual;

        $pendingEnrollments = Enrollment::where('enrollable_id', $individual->id)
            ->where('enrollable_type', Individual::class)
            ->where('payment_status', EvtEventPaymentStatusEnum::PENDING)
            ->whereNull('activated_at')
            ->with(['event', 'individualEnrollments', 'athleteEnrollments', 'athleteEnrollments.discipline'])
            ->paginate(50);

        return view('web.individual.evt_events.pending_enrollments.index', compact('pendingEnrollments'));
    }

    public function store()
    {
        $individual = Auth::user()->individual;
        $pendingEnrollments = Enrollment::where('enrollable_id', $individual->id)
            ->where('enrollable_type', Individual::class)
            ->where('payment_status', EvtEventPaymentStatusEnum::PENDING)
            ->whereNull('activated_at')
            ->with(['event', 'individualEnrollments', 'athleteEnrollments', 'athleteEnrollments.discipline'])
            ->get();

        $event = $pendingEnrollments->first()->event;

        $createEnrollmentOrder = app(CreateIndividualEnrollmentOrderAction::class);
        $createEnrollmentOrder->batchConfirmEnrollments($event, $pendingEnrollments, $individual->id, Individual::class);

        return redirect()->route('individual.evt-events.pending-enrollments.index')
            ->with('success', 'Enrollments confirmed and payment document generated.');
    }
}
