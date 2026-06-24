<?php

namespace Domain\EvtEvents\Actions;

use App\Enums\EvtAthleteEnrollmentStatusEnum;
use App\Enums\EvtEventEnrollmentRoleEnum;
use App\Enums\EvtEventFeeTypeEnum;
use Domain\Entities\Models\Entity;
use Domain\EvtEvents\Models\AthleteEnrollment;
use Domain\EvtEvents\Models\AthleteEnrollmentAttributes;
use Domain\EvtEvents\Models\Discipline;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\Pricing;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CreateAthleteEnrollmentAction
{
    public function execute(
        Event $event,
        ?Federation $federation,
        string $individualId,
        Enrollment $enrollment,
        ?int $perPersonPricingId,
        ?int $disciplinePricingId = null,
        ?int $eventFeePricingId = null,
        ?string $disciplineId = null,
        array $attributeValues = [],
        ?Entity $entity = null,
        ?string $teamIdentifier = null
    ): AthleteEnrollment {

        if (! $event->allowsEnrollments()) {
            throw new \DomainException(__('events.enrollments_closed'));
        }

        if (! $event->canEnroll('athlete')) {
            Log::error('Athlete enrollments not allowed', ['event' => $event->id]);

            throw new \DomainException(__('events.athlete_enrollments_not_allowed'));
        }

        // Get the discipline only if disciplineId is provided
        $discipline = null;
        if ($disciplineId) {
            $discipline = Discipline::findOrFail($disciplineId);

            // Only validate discipline-related attributes if discipline exists
            $attributeData = (new GetAttributesAndRulesFromDisciplineAction)->execute($disciplineId);
            $individual = Individual::with(['individualFederations', 'individualEntities'])->findOrFail($individualId);
            $selectedIndividuals = [$individual->toArray()];

            // First validate required attributes
            $validateAttributes = new ValidateAttributeRulesAction;
            $validateAttributes->validateRequiredAttributes(
                $attributeValues,
                $attributeData['attributes']
            );

            $validationSummary = (new ValidateAndSummarizeAthleteEnrollmentsAction)->execute(
                $selectedIndividuals,
                $attributeData,
                $attributeValues
            );

            foreach ($validationSummary as $summary) {
                if (! $summary['valid']) {
                    $failedRules = array_column($summary['failed_rules'], 'rule');
                    throw ValidationException::withMessages([
                        'attributes' => 'Attribute validation failed: ' . implode(', ', $failedRules),
                    ]);
                }
            }
        }

        DB::beginTransaction();

        try {
            // Handle PER_PERSON pricing (optional)
            $perPersonPricing = $perPersonPricingId ? Pricing::find($perPersonPricingId) : null;
            if (! $perPersonPricing && $this->requiresPerPersonPricing($event)) {
                throw ValidationException::withMessages([
                    'pricing' => __('events.select_pricing_option'),
                ]);
            }

            $perPersonPrice = $perPersonPricing ? $perPersonPricing->price : 0.0;

            // Handle discipline pricing (only if discipline exists)
            $disciplinePricing = ($discipline && $disciplinePricingId) ? Pricing::find($disciplinePricingId) : null;
            $disciplinePrice = $disciplinePricing ? $disciplinePricing->price : 0.0;

            // Handle event fee pricing
            $eventFeePricing = $eventFeePricingId ? Pricing::find($eventFeePricingId) : null;
            $eventFee = $eventFeePricing ? $eventFeePricing->price : 0.0;

            $totalPrice = $perPersonPrice + $disciplinePrice + $eventFee;

            $baseEnrollment = AthleteEnrollment::where([
                'event_id' => $event->id,
                'individual_id' => $individualId,
                'federation_id' => $federation?->id,
                'entity_id' => $entity?->id,
                'discipline_id' => null,
            ])
                ->whereIn('status_class', [
                    EvtAthleteEnrollmentStatusEnum::PAID->value,
                    EvtAthleteEnrollmentStatusEnum::DISCIPLINE_ASSIGNED->value,
                    EvtAthleteEnrollmentStatusEnum::REGISTERED->value,
                ])
                ->first();

            if ($baseEnrollment && $discipline) {
                // Update base enrollment with first discipline
                $baseEnrollment->update([
                    'discipline_id' => $discipline->id,
                    'status_class' => $totalPrice > 0
                        ? EvtAthleteEnrollmentStatusEnum::PENDING_PAYMENT->value
                        : EvtAthleteEnrollmentStatusEnum::REGISTERED->value,
                    'team_identifier' => $teamIdentifier,
                    'per_person_pricing_id' => $perPersonPricingId,
                    'per_person_price' => $perPersonPrice,
                    'discipline_pricing_id' => $disciplinePricingId,
                    'discipline_price' => $disciplinePrice,
                    'event_fee_pricing_id' => $eventFeePricingId,
                    'event_fee' => $eventFee,
                    'total_price' => $totalPrice,
                ]);

                $athleteEnrollment = $baseEnrollment;
            } else {

                // Create the Athlete Enrollment
                $athleteEnrollment = AthleteEnrollment::create([
                    'enrollment_id' => $enrollment->id,
                    'event_id' => $event->id,
                    'individual_id' => $individualId,
                    'federation_id' => $federation?->id,
                    'entity_id' => $entity?->id,
                    'discipline_id' => $discipline?->id,  // Now safely handles null
                    'team_identifier' => $teamIdentifier,
                    'per_person_pricing_id' => $perPersonPricingId,
                    'per_person_price' => $perPersonPrice,
                    'discipline_pricing_id' => $disciplinePricingId,
                    'discipline_price' => $disciplinePrice,
                    'event_fee_pricing_id' => $eventFeePricingId,
                    'event_fee' => $eventFee,
                    'total_price' => $totalPrice,
                    'status_class' => $totalPrice > 0
                        ? EvtAthleteEnrollmentStatusEnum::PENDING_PAYMENT->value
                        : EvtAthleteEnrollmentStatusEnum::REGISTERED->value,
                ]);
            }

            if (! $athleteEnrollment) {
                throw new \DomainException(__('events.failed_create_athlete_enrollment'));
            }

            // Only create attributes if discipline exists
            if ($discipline && ! empty($attributeValues)) {
                foreach ($attributeValues as $attributeId => $value) {
                    AthleteEnrollmentAttributes::create([
                        'athlete_enrollment_id' => $athleteEnrollment->id,
                        'attribute_id' => $attributeId,
                        'value' => $value,
                    ]);
                }
            }

            DB::commit();

            // Log the enrollment activity
            $logMessage = $discipline
                ? __('events.activity_log.athlete_enrolled', ['discipline' => $discipline->name, 'event' => $event->name])
                : __('events.activity_log.athlete_enrolled_no_discipline', ['event' => $event->name]);

            activity(__('events.activity_log.athlete_event_registration'))
                ->causedBy(auth()->user())
                ->performedOn($athleteEnrollment)
                ->event('enrolled')
                ->withProperties([
                    'event_id' => $event->id,
                    'event_name' => $event->name,
                    'individual_id' => $individualId,
                    'federation_id' => $federation?->id,
                    'entity_id' => $entity?->id,
                    'discipline_id' => $discipline?->id,
                    'discipline_name' => $discipline?->name,
                    'status' => $athleteEnrollment->status_class,
                ])
                ->log($logMessage);

            return $athleteEnrollment;
        } catch (\Exception $e) {

            DB::rollBack();

            Log::error('Failed to create athlete enrollment: ' . $e->getMessage(), [
                'event_id' => $event->id,
                'federation_id' => $federation?->id,
                'individual_id' => $individualId,
                'entity_id' => $entity?->id,
                'discipline_id' => $disciplineId,
            ]);

            throw $e;
        }
    }

    private function requiresPerPersonPricing(Event $event): bool
    {
        return Pricing::active()
            ->where('event_id', $event->id)
            ->where('price_type', EvtEventFeeTypeEnum::PER_PERSON->value)
            ->where(function ($query) {
                $query->where('enrollment_role', EvtEventEnrollmentRoleEnum::ATHLETE->value)
                    ->orWhereNull('enrollment_role');
            })
            ->where('price', '>', 0)
            ->exists();
    }
}
