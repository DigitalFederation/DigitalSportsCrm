<?php

namespace Domain\EvtEvents\Actions;

use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\StaffEnrollment;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateStaffEnrollmentAction
{
    public function execute(
        Event $event,
        Federation $federation,
        Individual $individual,
        array $attributes = []
    ) {
        try {
            DB::beginTransaction();

            $staffEnrollment = StaffEnrollment::create([
                'event_id' => $event->id,
                'federation_id' => $federation->id,
                'individual_id' => $individual->id,
            ]);

            // Save attributes if any
            foreach ($attributes as $attributeId => $value) {
                $staffEnrollment->attributes()->create([
                    'attribute_id' => $attributeId,
                    'value' => $value,
                ]);
            }

            DB::commit();

            return $staffEnrollment;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating staff enrollment: ' . $e->getMessage());
            throw $e;
        }
    }
}
