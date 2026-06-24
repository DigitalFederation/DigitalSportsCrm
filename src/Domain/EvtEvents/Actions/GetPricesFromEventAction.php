<?php

namespace Domain\EvtEvents\Actions;

use App\Enums\EvtEventEnrollmentRoleEnum;
use Domain\EvtEvents\Models\Pricing;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class GetPricesFromEventAction
{
    /**
     * Returns all prices of an Event respecting the selected discipline and role
     */
    public function execute(int $eventId, ?int $disciplineId = null, ?EvtEventEnrollmentRoleEnum $role = null): Collection
    {
        $now = Carbon::now();

        $query = Pricing::active()
            ->where('event_id', $eventId)
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now);

        // Handle discipline, role, and price_type conditions
        $query->where(function ($q) use ($disciplineId, $role) {
            // Price type and discipline conditions
            $q->where(function ($subQ) use ($disciplineId) {
                $subQ->where(function ($priceQ) {
                    $priceQ->where('price_type', 'PER_PERSON')
                        ->whereNull('discipline_id');
                })->orWhere(function ($priceQ) use ($disciplineId) {
                    $priceQ->where('price_type', 'PER_DISCIPLINE')
                        ->when($disciplineId, function ($innerQ) use ($disciplineId) {
                            $innerQ->where('discipline_id', $disciplineId);
                        });
                })->orWhere(function ($priceQ) {
                    $priceQ->where('price_type', 'EVENT_FEE')
                        ->whereNull('discipline_id');
                });
            });

            // Add role condition if specified
            if ($role) {
                $q->where(function ($subQ) use ($role) {
                    $subQ->where('enrollment_role', $role->value)
                        ->orWhereNull('enrollment_role')
                        ->orWhere('enrollment_role', '');
                });
            }
        });

        return $query->get();
    }
}
