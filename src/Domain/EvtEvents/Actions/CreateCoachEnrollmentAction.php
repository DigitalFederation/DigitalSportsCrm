<?php

namespace Domain\EvtEvents\Actions;

use App\Enums\EvtEventEnrollmentRoleEnum;
use App\Enums\EvtEventFeeTypeEnum;
use Domain\Entities\Models\Entity;
use Domain\EvtEvents\Models\CoachEnrollment;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Services\EnrollmentsCostCalculationService;
use Domain\EvtEvents\States\PendingCoachEnrollmentState;
use Domain\Federations\Models\Federation;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateCoachEnrollmentAction
{
    public function execute(
        Event $event,
        Federation|Entity|null $enrollable,
        string $individualId,
        Enrollment $enrollment,
        ?int $pricingId = null,
        array $attributeValues = [],
    ): CoachEnrollment {
        if (! $event->allowsEnrollments()) {
            throw new \DomainException(__('events.enrollments_closed'));
        }

        DB::beginTransaction();
        try {
            $costCalculationService = new EnrollmentsCostCalculationService;
            $pricing = $costCalculationService->getPricing(
                $event->id,
                null,
                EvtEventEnrollmentRoleEnum::COACH,
                $pricingId
            );

            // Find existing or create new enrollment
            $coachEnrollment = CoachEnrollment::firstOrNew([
                'event_id' => $event->id,
                'individual_id' => $individualId,
                'federation_id' => $enrollable instanceof Federation ? $enrollable->id : null,
                'entity_id' => $enrollable instanceof Entity ? $enrollable->id : null,
            ]);

            $statusClass = PendingCoachEnrollmentState::class;

            $coachEnrollment->fill([
                'enrollment_id' => $enrollment->id,
                'pricing_id' => $pricing?->id,
                'price' => $pricing?->price ?? 0,
                'price_type' => $pricing?->price_type ?? EvtEventFeeTypeEnum::FREE->value,
                'status_class' => $statusClass,
            ]);

            $coachEnrollment->save();

            // Handle attributes
            if (! empty($attributeValues)) {
                foreach ($attributeValues as $attributeId => $attributeData) {
                    // Get proper attribute ID from data structure
                    $id = is_array($attributeData) && isset($attributeData['attribute_data']['id'])
                        ? $attributeData['attribute_data']['id']
                        : $attributeId;

                    $value = is_array($attributeData) ? $attributeData['value'] : $attributeData;

                    $coachEnrollment->attributes()->updateOrCreate(
                        ['attribute_id' => $id],
                        ['value' => $value]
                    );
                }
            }

            DB::commit();

            // Log the enrollment activity
            activity(__('events.activity_log.coach_event_registration'))
                ->causedBy(auth()->user())
                ->performedOn($coachEnrollment)
                ->event('enrolled')
                ->withProperties([
                    'event_id' => $event->id,
                    'event_name' => $event->name,
                    'individual_id' => $individualId,
                    'enrollable_type' => $enrollable ? get_class($enrollable) : null,
                    'enrollable_id' => $enrollable?->id,
                    'status' => $statusClass,
                ])
                ->log(__('events.activity_log.coach_enrolled', ['event' => $event->name]));

            return $coachEnrollment;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to create coach enrollment: ' . $e->getMessage(), [
                'event_id' => $event->id,
                'enrollable_type' => $enrollable ? get_class($enrollable) : null,
                'enrollable_id' => $enrollable?->id,
                'individual_id' => $individualId,
            ]);
            throw $e;
        }
    }
}
