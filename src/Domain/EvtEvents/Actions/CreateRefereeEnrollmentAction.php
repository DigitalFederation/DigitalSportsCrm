<?php

namespace Domain\EvtEvents\Actions;

use App\Enums\EvtEventFeeTypeEnum;
use Domain\Entities\Models\Entity;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\RefereeEnrollment;
use Domain\EvtEvents\States\PendingRefereeEnrollmentState;
use Domain\Federations\Models\Federation;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateRefereeEnrollmentAction
{
    public function execute(
        Event $event,
        Federation|Entity|null $enrollable,
        string $individualId,
        Enrollment $enrollment,
        ?int $pricingId = null,
        array $attributeValues = [],
    ): RefereeEnrollment {

        // Validate if the event and federation are in a state that allows enrollment.
        if (! $event->allowsEnrollments()) {
            throw new \DomainException(__('events.enrollments_closed'));
        }

        DB::beginTransaction();
        try {
            // Find existing or create new enrollment
            $refereeEnrollment = RefereeEnrollment::firstOrNew([
                'event_id' => $event->id,
                'individual_id' => $individualId,
                'federation_id' => $enrollable instanceof Federation ? $enrollable->id : null,
                'entity_id' => $enrollable instanceof Entity ? $enrollable->id : null,
            ]);

            $refereeEnrollment->fill([
                'enrollment_id' => $enrollment->id,
                'pricing_id' => $pricingId,
                'price' => 0,
                'price_type' => EvtEventFeeTypeEnum::FREE->value,
                'status_class' => PendingRefereeEnrollmentState::class,
            ]);

            $refereeEnrollment->save();

            // Handle attributes
            if (! empty($attributeValues)) {
                foreach ($attributeValues as $attributeId => $attributeData) {
                    $id = is_array($attributeData) && isset($attributeData['attribute_data']['id'])
                        ? $attributeData['attribute_data']['id']
                        : $attributeId;

                    $value = is_array($attributeData) ? $attributeData['value'] : $attributeData;

                    $refereeEnrollment->attributes()->updateOrCreate(
                        [
                            'referee_enrollment_id' => $refereeEnrollment->id,
                            'attribute_id' => $id,
                        ],
                        ['value' => $value]
                    );
                }
            }

            DB::commit();

            // Log the enrollment activity
            activity(__('events.activity_log.referee_event_registration'))
                ->causedBy(auth()->user())
                ->performedOn($refereeEnrollment)
                ->event('enrolled')
                ->withProperties([
                    'event_id' => $event->id,
                    'event_name' => $event->name,
                    'individual_id' => $individualId,
                    'enrollable_type' => $enrollable ? get_class($enrollable) : null,
                    'enrollable_id' => $enrollable?->id,
                    'status' => PendingRefereeEnrollmentState::class,
                ])
                ->log(__('events.activity_log.referee_enrolled', ['event' => $event->name]));

            return $refereeEnrollment;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to create referee enrollment: ' . $e->getMessage(), [
                'event_id' => $event->id,
                'enrollable_type' => $enrollable ? get_class($enrollable) : null,
                'enrollable_id' => $enrollable?->id,
                'individual_id' => $individualId,
            ]);
            throw $e;
        }
    }
}
