<?php

namespace App\Http\Controllers\Individual\EvtEvents\Enrollments;

use App\Http\Controllers\Controller;
use Domain\EvtEvents\Actions\ActivateEnrollmentsAction;
use Domain\EvtEvents\Actions\CreateIndividualEnrollmentOrderAction;
use Domain\EvtEvents\Actions\GetWaitingListSelectedIndividualsAction;
use Domain\EvtEvents\Models\AthleteEnrollment;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\IndividualEnrollment;
use Domain\EvtEvents\Services\EnrollmentsCostCalculationService;
use Domain\Individuals\Models\Individual;
use Exception;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WaitingListController extends Controller
{
    public function index(Event $event, EnrollmentsCostCalculationService $costCalculationService)
    {
        $individualId = auth()->user()->individual->id;

        $pendingEnrollments = Enrollment::where('event_id', $event->id)
            ->where('enrollable_type', Individual::class)
            ->where('enrollable_id', $individualId)
            ->where(function ($query) use ($individualId) {
                $query->whereHas('individualEnrollments', function ($subQuery) use ($individualId) {
                    $subQuery->where('individual_id', $individualId);
                })->orWhereHas('athleteEnrollments', function ($subQuery) use ($individualId) {
                    $subQuery->where('individual_id', $individualId);
                });
            })
            ->whereNull('activated_at')
            ->with([
                'event',
                'individualEnrollments.individual',
                'individualEnrollments.attributes.attribute',
                'individualEnrollments.enrollment',
                'athleteEnrollments',
                'athleteEnrollments.discipline',
                'athleteEnrollments.individual',
                'athleteEnrollments.attributes.attribute',
                'athleteEnrollments.enrollment',
            ])
            ->get();

        if ($pendingEnrollments->isEmpty()) {
            return redirect()->back()->with('error', "You don't have any registrations in waiting list at the moment.");
        }

        try {
            $totalCost = $costCalculationService->calculateTotalCost($event, $pendingEnrollments, true);
            $costBreakdown = $costCalculationService->getCostBreakdown($event, $pendingEnrollments, true);
        } catch (Exception $e) {
            Log::error("Error calculating total cost for event {$event->id}: " . $e->getMessage());
            $totalCost = 0;
            $costBreakdown = [];
        }

        return view('web.individual.evt_events.pending_enrollments.index', compact(
            'pendingEnrollments',
            'event',
            'totalCost',
            'costBreakdown'
        ));
    }

    public function store(Request $request, Event $event)
    {
        $individualId = auth()->user()->individual->id;
        $individual = Individual::where('id', $individualId)->first();

        $pendingEnrollments = Enrollment::where('event_id', $event->id)
            ->where('enrollable_type', Individual::class)
            ->where('enrollable_id', $individualId)
            ->whereNull('activated_at')
            ->with([
                'event',
                'individualEnrollments.individual',
                'athleteEnrollments.individual',
                'coachEnrollments.individual',
                'refereeEnrollments.individual',
                'teamOfficialEnrollments.individual',
            ])
            ->get();

        // Convert to EloquentCollection
        $pendingEnrollments = new EloquentCollection($pendingEnrollments);

        $individualPricingTiers = $pendingEnrollments->pluck('pricing_id', 'id')->toArray();

        // Call WaitingList individuals to be enrolled
        $getWaitingListSelectedIndividualsAction = new GetWaitingListSelectedIndividualsAction;
        $selectedIndividualsArray = $getWaitingListSelectedIndividualsAction->execute($pendingEnrollments);

        if (empty($selectedIndividualsArray)) {
            return redirect()->back()->with('error', "You don't have any selected registrations in waiting list at the moment.");
        }

        // Convert the array to EloquentCollection
        $selectedIndividuals = new EloquentCollection($selectedIndividualsArray);

        // Calculate total cost for all pending enrollments
        $costCalculationService = new EnrollmentsCostCalculationService;
        $totalCost = $costCalculationService->calculateTotalCost($event, $pendingEnrollments, true);

        $document = DB::transaction(function () use (
            $event,
            $pendingEnrollments,
            $individualId,
            $selectedIndividuals,
            $individualPricingTiers,
            $costCalculationService,
            $totalCost
        ) {
            $document = null;

            if ($totalCost > 0 && $pendingEnrollments->isNotEmpty()) {
                $createIndividualEnrollmentOrderAction = new CreateIndividualEnrollmentOrderAction($costCalculationService);
                $document = $createIndividualEnrollmentOrderAction->execute(
                    $event,
                    $pendingEnrollments->first(),
                    $individualId,
                    Individual::class,
                    $selectedIndividuals,
                    $individualPricingTiers
                );
            } else {
                // Activate enrollments if the total cost is zero
                $activateEnrollmentsAction = new ActivateEnrollmentsAction;
                foreach ($pendingEnrollments as $pendingEnrollment) {
                    $activateEnrollmentsAction->execute($pendingEnrollment->id);
                }
            }

            foreach ($pendingEnrollments as $enrollment) {
                if ($document) {
                    $enrollment->document_id = $document->id;
                    $enrollment->total_price = $totalCost;
                }

                $enrollment->activated_at = now();
                $enrollment->save();
            }

            return $document;
        });

        activity('Enrollment')
            ->performedOn($pendingEnrollments->first()->event)
            ->causedBy(Auth::user())
            ->withProperties(['individual_id' => $individualId, 'enrollments' => $pendingEnrollments])
            ->log('Pending enrollments confirmed for event: ' . $pendingEnrollments->first()->event->name);

        if ($document) {
            return redirect()->route('individual.document.show', $document->id)
                ->with('success', 'Enrollments confirmed and payment document generated.');
        }

        return redirect()->route('individual.evt-events.events.show', $pendingEnrollments->first()->event->id)
            ->with('success', 'Enrollments confirmed.');
    }

    public function destroy(Request $request, Event $event, $enrollmentType, $id)
    {
        $individualId = auth()->user()->individual->id;
        $enrollment = Enrollment::where('event_id', $event->id)
            ->where('enrollable_type', Individual::class)
            ->where('enrollable_id', $individualId)
            ->whereNull('activated_at')
            ->first();

        if ($enrollment && $enrollment->payment_status == \App\Enums\EvtEventPaymentStatusEnum::PENDING->value) {

            try {
                switch ($enrollmentType) {
                    case 'individual':
                        $individualEnrollment = IndividualEnrollment::find($id);
                        if ($individualEnrollment) {
                            $individualEnrollment->delete();
                        } else {
                            Log::warning("Attempt to delete non-existing individual enrollment with ID {$id} from event: {$event->id}");
                        }
                        break;
                    case 'athlete':
                        $athleteEnrollment = AthleteEnrollment::find($id);
                        if ($athleteEnrollment) {
                            $athleteEnrollment->attributes()->delete();
                            $athleteEnrollment->delete();
                        } else {
                            Log::warning("Attempt to delete non-existing athlete enrollment with ID {$id} from event: {$event->id}");
                        }
                        break;
                }
            } catch (Exception $e) {
                Log::error("Error deleting {$enrollmentType} enrollment with ID {$id}: " . $e->getMessage());

                return redirect()->route('individual.evt-events.events.show', $event->id)
                    ->with('error', 'Cannot delete enrollment.');
            }

            return redirect()->route('individual.evt-events.events.show', $event->id)
                ->with('success', 'Enrollment deleted successfully.');
        }

        return redirect()->route('individual.evt-events.events.show', $event->id)
            ->with('error', 'Cannot delete enrollment.');
    }
}
