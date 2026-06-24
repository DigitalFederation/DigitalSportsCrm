<?php

namespace Domain\EvtEvents\Actions;

use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\IndividualEnrollment;
use Domain\EvtEvents\Models\IndividualEnrollmentAttribute;
use Domain\EvtEvents\Models\Pricing;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateIndividualEnrollmentAction
{
    public function execute(
        Event $event,
        Model $enrollable,
        Individual $individual,
        Enrollment $enrollment,
        ?string $status_class,
        array $attributeValues,
        ?int $pricingId,
        ?string $entityId,
        ?string $priceType
    ): IndividualEnrollment {

        if (! $event->canEnroll('individual')) {
            throw new \Exception('Enrollments are not allowed for this event at this time.');
        }

        try {
            DB::beginTransaction();

            // Fetch pricing information
            $pricing = $pricingId ? Pricing::findOrFail($pricingId) : null;
            $price = $pricing ? $pricing->price : 0.0;

            // Prepare base data
            $enrollmentData = [
                'enrollment_id' => $enrollment->id,
                'event_id' => $event->id,
                'individual_id' => $individual->id,
                'status_class' => $status_class,
                'pricing_id' => $pricingId,
                'price' => $price,
                'entity_id' => $entityId,
                'price_type' => $priceType,
            ];

            // Handle enrollable type
            if ($enrollable instanceof Federation) {
                $enrollmentData['federation_id'] = $enrollable->id;
            } elseif ($enrollable instanceof Entity) {
                $enrollmentData['entity_id'] = $enrollable->id;
            }

            // Create the individual enrollment
            $individualEnrollment = IndividualEnrollment::create($enrollmentData);

            // Save attribute values if present
            if (! empty($attributeValues[$individual->id])) {
                foreach ($attributeValues[$individual->id] as $attributeId => $value) {
                    // Ensure the value is not null or empty
                    if (! is_null($value) && $value !== '') {
                        IndividualEnrollmentAttribute::create([
                            'individual_enrollment_id' => $individualEnrollment->id,
                            'attribute_id' => $attributeId,
                            'value' => $value,
                        ]);
                    }
                }
            }

            DB::commit();

            return $individualEnrollment;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating individual enrollment: ' . $e->getMessage());
            throw $e;
        }
    }
}
