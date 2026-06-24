<?php

namespace App\Http\Controllers\Entity\EvtEvents\Enrollments;

use App\Http\Controllers\Controller;
use Domain\Entities\Models\Entity;
use Domain\EvtEvents\Actions\ActivateEnrollmentsAction;
use Domain\EvtEvents\Actions\CreateEnrollmentPaymentDocumentAction;
use Domain\EvtEvents\Actions\GetWaitingListSelectedIndividualsAction;
use Domain\EvtEvents\Models\AthleteEnrollment;
use Domain\EvtEvents\Models\CoachEnrollment;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\IndividualEnrollment;
use Domain\EvtEvents\Models\TeamOfficialEnrollment;
use Domain\EvtEvents\Services\EnrollmentsCostCalculationService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WaitingListController extends Controller
{
    public function index(Event $event, EnrollmentsCostCalculationService $costCalculationService)
    {
        $entityId = auth()->user()->getEntityId();

        $enrollmentQuery = Enrollment::where('event_id', $event->id)
            ->where('enrollable_type', Entity::class)
            ->where('enrollable_id', $entityId)
            ->whereNull('activated_at')
            ->where(function ($query) {
                $query->has('individualEnrollments')
                    ->orHas('athleteEnrollments')
                    ->orHas('coachEnrollments')
                    ->orHas('refereeEnrollments')
                    ->orHas('teamOfficialEnrollments');
            })
            ->with([
                'event',
                'individualEnrollments.individual',
                'athleteEnrollments.discipline',
                'athleteEnrollments.individual',
                'athleteEnrollments.attributes.attribute',
                'coachEnrollments.individual',
                'refereeEnrollments.individual',
                'teamOfficialEnrollments.individual',
                'teamOfficialEnrollments.attributes.attribute',
            ]);

        $totalEnrollments = $enrollmentQuery->get();
        $pendingEnrollments = $enrollmentQuery->paginate(100);

        try {
            $totalCost = $costCalculationService->calculateTotalCost($event, $totalEnrollments, false);
            $costBreakdown = $costCalculationService->getCostBreakdown($event, $totalEnrollments, false);
        } catch (Exception $e) {
            Log::error("Error calculating total cost for event {$event->id}: " . $e->getMessage());
            $totalCost = 0;
            $costBreakdown = [];
        }

        return view('web.entity.evt_event.pending_enrollments.index', compact(
            'pendingEnrollments',
            'event',
            'costBreakdown',
            'totalCost',
        ));
    }

    public function store(Request $request, Event $event)
    {
        $entityId = auth()->user()->getEntityId();

        DB::beginTransaction();
        try {
            $pendingEnrollments = Enrollment::where('event_id', $event->id)
                ->where('enrollable_type', Entity::class)
                ->where('enrollable_id', $entityId)
                ->whereNull('activated_at')
                ->with(
                    'event',
                    'individualEnrollments',
                    'athleteEnrollments.individual',
                    'coachEnrollments.individual',
                    'refereeEnrollments.individual',
                    'teamOfficialEnrollments.individual'
                )
                ->lockForUpdate()  // Add a lock to prevent concurrent modifications
                ->get();

            if ($pendingEnrollments->isEmpty()) {
                throw new Exception('No pending enrollments found.');
            }

            $getWaitingListSelectedIndividualsAction = new GetWaitingListSelectedIndividualsAction;
            $selectedIndividuals = $getWaitingListSelectedIndividualsAction->execute($pendingEnrollments);

            $costCalculationService = new EnrollmentsCostCalculationService;
            $totalCost = $costCalculationService->calculateTotalCost($event, $pendingEnrollments);

            if ($totalCost > 0) {
                $createEnrollmentPaymentDocumentAction = new CreateEnrollmentPaymentDocumentAction;
                $createEnrollmentPaymentDocumentAction->execute(
                    $event,
                    $pendingEnrollments->first(),
                    $entityId,
                    Entity::class,
                    $selectedIndividuals,
                    $totalCost,
                    null
                );
            } else {
                $activateEnrollmentsAction = new ActivateEnrollmentsAction;
                foreach ($pendingEnrollments as $pendingEnrollment) {
                    $activateEnrollmentsAction->execute($pendingEnrollment->id);
                }
            }

            foreach ($pendingEnrollments as $enrollment) {
                $enrollment->activated_at = now();
                $enrollment->save();
            }

            DB::commit();

            activity('Enrollment')
                ->performedOn($pendingEnrollments->first()->event)
                ->causedBy(auth()->user())
                ->withProperties(['entity_id' => $entityId, 'enrollments' => $selectedIndividuals])
                ->log('Pending enrollments confirmed for event: ' . $pendingEnrollments->first()->event->name);

            return redirect()->route('entity.evt-events.events.show', $pendingEnrollments->first()->event->id)
                ->with('success', 'Waiting list enrollments were successfully confirmed.');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error confirming waiting list enrollments: ' . $e->getMessage());

            return back()->with('error', 'An error occurred while confirming enrollments. Please try again.');
        }
    }

    public function destroy(Request $request, Event $event, $enrollmentType, $id)
    {
        $enrollment = Enrollment::where('event_id', $event->id)
            ->where('enrollable_type', Entity::class)
            ->where('enrollable_id', auth()->user()->getEntityId())
            ->whereNull('activated_at')
            ->first();

        if ($enrollment && $enrollment->payment_status == \App\Enums\EvtEventPaymentStatusEnum::PENDING->value) {
            try {
                switch ($enrollmentType) {
                    case 'individual':
                        $individualEnrollment = IndividualEnrollment::findOrFail($id);
                        if ($individualEnrollment) {
                            $individualEnrollment->delete();
                        } else {
                            Log::warning("Attempt to delete non-existing individual enrollment with ID {$id} from event: {$event->id}");
                        }
                        break;
                    case 'athlete':
                        $athleteEnrollment = AthleteEnrollment::findOrFail($id);
                        if ($athleteEnrollment) {
                            $athleteEnrollment->attributes()->delete();
                            $athleteEnrollment->delete();
                        } else {
                            Log::warning("Attempt to delete non-existing athlete enrollment with ID {$id} from event: {$event->id}");
                        }
                        break;
                    case 'coach':
                        $coachEnrollment = CoachEnrollment::findOrFail($id);
                        if ($coachEnrollment) {
                            $coachEnrollment->delete();
                        } else {
                            Log::warning("Attempt to delete non-existing coach enrollment with ID {$id} from event: {$event->id}");
                        }
                        break;
                    case 'teamofficial':
                        $teamOfficialEnrollment = TeamOfficialEnrollment::findOrFail($id);
                        if ($teamOfficialEnrollment) {
                            $teamOfficialEnrollment->attributes()->delete();
                            $teamOfficialEnrollment->delete();
                        } else {
                            Log::warning("Attempt to delete non-existing team official enrollment with ID {$id} from event: {$event->id}");
                        }
                        break;
                }
            } catch (Exception $e) {
                Log::error("Error deleting {$enrollmentType} enrollment with ID {$id}: " . $e->getMessage());

                return redirect()->back()->with('error', 'An error occurred while deleting the enrollment.');
            }

            return redirect()->back()->with('success', ucfirst($enrollmentType) . ' enrollment deleted successfully.');
        }

        return redirect()->back()->with('error', 'Cannot delete ' . $enrollmentType . ' enrollment.');
    }

    private function getSelectedEnrollments($pendingEnrollments)
    {
        $selectedEnrollments = [];

        foreach ($pendingEnrollments as $enrollment) {
            foreach ($enrollment->individualEnrollments as $individualEnrollment) {
                $selectedEnrollments[] = [
                    'discipline_id' => null,
                    'count' => 1,
                    'role' => 'Individual',
                    'pricing_id' => $enrollment->pricing_id ?? null,
                ];
            }
            foreach ($enrollment->athleteEnrollments as $athleteEnrollment) {
                $selectedEnrollments[] = [
                    'discipline_id' => $athleteEnrollment->discipline_id,
                    'count' => 1,
                    'role' => 'Athlete',
                    'pricing_id' => $enrollment->pricing_id ?? null,
                ];
            }
            foreach ($enrollment->coachEnrollments as $coachEnrollment) {
                $selectedEnrollments[] = [
                    'discipline_id' => null,
                    'count' => 1,
                    'role' => 'Coach',
                    'pricing_id' => $enrollment->pricing_id ?? null,
                ];
            }
        }

        return $selectedEnrollments;
    }
}
