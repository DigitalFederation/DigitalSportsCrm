<?php

namespace App\Http\Controllers\Individual\EvtEvents\Enrollments;

use App\Enums\EvtEventCategoryTypeEnum;
use App\Http\Controllers\Controller;
use Domain\EvtEvents\Actions\CreateEnrollmentAction;
use Domain\EvtEvents\Actions\CreateIndividualEnrollmentAction;
use Domain\EvtEvents\Actions\CreateIndividualEnrollmentOrderAction;
use Domain\EvtEvents\Actions\PricingTierResolverAction;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\Pricing;
use Domain\EvtEvents\States\PendingIndividualEnrollmentState;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IndividualEnrollmentController extends Controller
{
    private $pricingTierResolver;
    private $createEnrollment;
    private $createIndividualEnrollment;
    private $createIndividualEnrollmentOrder;

    /**
     * Create a new controller instance.
     */
    public function __construct(
        PricingTierResolverAction $pricingTierResolverAction,
        CreateEnrollmentAction $createEnrollmentAction,
        CreateIndividualEnrollmentAction $createIndividualEnrollmentAction,
        CreateIndividualEnrollmentOrderAction $createIndividualEnrollmentOrderAction
    ) {
        $this->pricingTierResolver = $pricingTierResolverAction;
        $this->createEnrollment = $createEnrollmentAction;
        $this->createIndividualEnrollment = $createIndividualEnrollmentAction;
        $this->createIndividualEnrollmentOrder = $createIndividualEnrollmentOrderAction;
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $user = Auth::user();
        $event = Event::findOrFail($request->event_id);
        $individual = $user->individual;

        if (! $event->allowsEnrollments()) {
            return redirect()->route('individual.evt-events.events.show', $event->id)
                ->with('error', 'Sorry, this event is not open for enrollments.');
        }

        if ($event->event_category != EvtEventCategoryTypeEnum::organization->name) {
            return redirect()->route('individual.evt-events.events.show', $event->id)
                ->with('error', 'Sorry, Contact your National Federation to enroll in this event.');
        }

        $pricing_id = $request->input('price_id') ?? null;
        $pricing = $pricing_id ? Pricing::find($pricing_id) : null;
        $price_type = $pricing ? $pricing->price_type : null;

        try {
            $enrollment = $this->createEnrollment->execute($individual, $event, $pricing_id);
            $attributes = $request->input('attributes', []);

            $individualEnrollment = $this->createIndividualEnrollment->execute(
                $event,
                $individual,
                $individual,
                $enrollment,
                PendingIndividualEnrollmentState::class,
                [$individual->id => $attributes],
                $pricing_id,
                null,
                $price_type
            );

            return redirect()->route('individual.evt-events.events.waiting-list.index', $event->id)
                ->with('success', 'Your registration is now in your Waiting List. Review and confirm when ready.');
        } catch (\Exception $e) {
            logger()->error($e->getMessage());

            return redirect()->route('individual.evt-events.events.show', $event->id)
                ->with('error', 'An error occurred during the enrollment process.');
        }
    }
}
