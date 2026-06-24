<?php

namespace Domain\EvtEvents\Actions;

use App\Enums\EvtEventEnrollmentRoleEnum;
use App\Enums\EvtEventFeeTypeEnum;
use Domain\Entities\Models\Entity;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\TeamOfficialEnrollment;
use Domain\EvtEvents\Services\EnrollmentsCostCalculationService;
use Domain\EvtEvents\States\PendingTeamOfficialEnrollmentState;
use Domain\Federations\Models\Federation;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateTeamOfficialEnrollmentAction
{
    public function execute(
        Event $event,
        Federation|Entity|null $enrollable,
        string $individualId,
        Enrollment $enrollment,
        ?int $pricingId = null,
        array $attributeValues = [],
    ): TeamOfficialEnrollment {
        if (! $event->allowsEnrollments()) {
            throw new \DomainException(__('events.enrollments_closed'));
        }

        DB::beginTransaction();
        try {

            $costCalculationService = new EnrollmentsCostCalculationService;
            $pricing = $costCalculationService->getPricing(
                $event->id,
                null,
                EvtEventEnrollmentRoleEnum::OFFICIAL,
                $pricingId
            );

            // Find existing or create new enrollment
            $teamOfficialEnrollment = TeamOfficialEnrollment::firstOrNew([
                'event_id' => $event->id,
                'individual_id' => $individualId,
                'federation_id' => $enrollable instanceof Federation ? $enrollable->id : null,
                'entity_id' => $enrollable instanceof Entity ? $enrollable->id : null,
            ]);

            $statusClass = PendingTeamOfficialEnrollmentState::class;

            $teamOfficialEnrollment->fill([
                'enrollment_id' => $enrollment->id,
                'pricing_id' => $pricing?->id,
                'price' => $pricing?->price ?? 0,
                'price_type' => $pricing?->price_type ?? EvtEventFeeTypeEnum::FREE->value,
                'status_class' => $statusClass,
            ]);

            $teamOfficialEnrollment->save();

            // Handle attributes
            if (! empty($attributeValues)) {
                foreach ($attributeValues as $attributeId => $attributeData) {
                    // Get proper attribute ID from data structure
                    $id = is_array($attributeData) && isset($attributeData['attribute_data']['id'])
                        ? $attributeData['attribute_data']['id']
                        : $attributeId;

                    $value = is_array($attributeData) ? $attributeData['value'] : $attributeData;

                    $teamOfficialEnrollment->attributes()->updateOrCreate(
                        ['attribute_id' => $id],
                        ['value' => $value]
                    );
                }
            }

            DB::commit();

            // Log the enrollment activity
            activity(__('events.activity_log.official_event_registration'))
                ->causedBy(auth()->user())
                ->performedOn($teamOfficialEnrollment)
                ->event('enrolled')
                ->withProperties([
                    'event_id' => $event->id,
                    'event_name' => $event->name,
                    'individual_id' => $individualId,
                    'enrollable_type' => $enrollable ? get_class($enrollable) : null,
                    'enrollable_id' => $enrollable?->id,
                    'status' => $statusClass,
                ])
                ->log(__('events.activity_log.official_enrolled', ['event' => $event->name]));

            return $teamOfficialEnrollment;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to create team official enrollment: ' . $e->getMessage(), [
                'event_id' => $event->id,
                'enrollable_type' => $enrollable ? get_class($enrollable) : null,
                'enrollable_id' => $enrollable?->id,
                'individual_id' => $individualId,
            ]);
            throw $e;
        }
    }
}
