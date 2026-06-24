<?php

namespace Domain\EvtEvents\Actions;

use App\Enums\EvtEventPaymentStatusEnum;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Illuminate\Support\Facades\Auth;

class CreateEnrollmentAction
{
    public function execute($enrollable, Event $event, $pricingId = null)
    {
        // Define the criteria to find an existing enrollment
        $criteria = [
            'user_id' => Auth::id(),
            'event_id' => $event->id,
            'enrollable_type' => get_class($enrollable),
            'enrollable_id' => $enrollable->id,
            'payment_status' => EvtEventPaymentStatusEnum::PENDING,
            'activated_at' => null,
        ];

        // Check if there is an existing pending enrollment
        $existingEnrollment = Enrollment::where($criteria)->first();

        // If there is no existing pending enrollment, create a new one
        if (! $existingEnrollment) {
            return Enrollment::create([
                'user_id' => Auth::id(),
                'event_id' => $event->id,
                'enrollable_type' => get_class($enrollable),
                'enrollable_id' => $enrollable->id,
                'pricing_id' => $pricingId,
                'payment_status' => EvtEventPaymentStatusEnum::PENDING,
            ]);
        }

        if ($pricingId !== null && (int) $existingEnrollment->pricing_id !== (int) $pricingId) {
            $existingEnrollment->pricing_id = $pricingId;
            $existingEnrollment->save();
        }

        // Create or update the enrollment record based on the criteria
        return $existingEnrollment;
    }
}
