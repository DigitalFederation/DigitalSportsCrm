<?php

namespace Domain\EvtEvents\Actions;

use App\Enums\EvtAthleteEnrollmentStatusEnum;
use App\Enums\EvtEventFeeTypeEnum;
use App\Enums\EvtEventPaymentStatusEnum;
use App\Exceptions\EnrollmentValidationException;
use Domain\Entities\Models\Entity;
use Domain\EvtEvents\Models\AthleteEnrollment;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\Pricing;
use Domain\Federations\Models\Federation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Class PreRegisterAthletesAction
 *
 * Handles the pre-registration of athletes for an event, including validation,
 * enrollment creation, and payment document generation.
 */
class PreRegisterAthletesAction
{
    /**
     * Execute the pre-registration process for athletes.
     *
     * @param  Event  $event  The event for which athletes are being pre-registered.
     * @param  Federation|Entity  $enrollable  The enrollable entity, either a Federation or Entity.
     * @param  array  $athletes  An array of athletes to be pre-registered.
     * @return Enrollment The created enrollment record.
     *
     * @throws EnrollmentValidationException If validation fails.
     */
    public function execute(Event $event, Federation|Entity $enrollable, array $athletes)
    {
        try {
            $this->validateInput($event, $enrollable, $athletes);

            return DB::transaction(function () use ($event, $enrollable, $athletes) {
                $perPersonPricing = $this->getPerPersonPricing($event);

                $enrollment = $this->createEnrollment($event, $enrollable);

                foreach ($athletes as $athlete) {
                    $this->validateAthlete($athlete);
                    $this->createAthleteEnrollment($enrollment, $athlete, $enrollable, $perPersonPricing);
                }

                if ($perPersonPricing) {
                    $this->createPaymentDocument($event, $enrollment, $enrollable, $athletes, $perPersonPricing);
                }

                return $enrollment;
            });
        } catch (EnrollmentValidationException $e) {
            Log::warning('Pre-registration validation failed', [
                'event_id' => $event->id,
                'enrollable_type' => get_class($enrollable),
                'enrollable_id' => $enrollable->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        } catch (Throwable $e) {
            Log::error('Pre-registration failed', [
                'event_id' => $event->id,
                'enrollable_type' => get_class($enrollable),
                'enrollable_id' => $enrollable->id,
                'error' => $e->getMessage(),
            ]);
            throw new EnrollmentValidationException('Failed to create pre-registration: ' . $e->getMessage());
        }
    }

    /**
     * Validate the input data for pre-registration.
     *
     * @param  Event  $event  The event being validated.
     * @param  Federation|Entity  $enrollable  The enrollable entity.
     * @param  array  $athletes  The athletes to be validated.
     *
     * @throws EnrollmentValidationException If validation fails.
     */
    private function validateInput(Event $event, Federation|Entity $enrollable, array $athletes): void
    {
        if (! ($enrollable instanceof Federation || $enrollable instanceof Entity)) {
            throw new EnrollmentValidationException('Invalid enrollable type. Must be Federation or Entity.');
        }

        if (empty($athletes)) {
            throw new EnrollmentValidationException('No athletes provided for enrollment.');
        }

        if (! $event->allowsEnrollments()) {
            throw new EnrollmentValidationException('Event is not open for enrollment.');
        }
    }

    /**
     * Validate individual athlete data.
     *
     * @param  array  $athlete  The athlete data to validate.
     *
     * @throws EnrollmentValidationException If validation fails.
     */
    private function validateAthlete(array $athlete): void
    {
        if (! isset($athlete['id'])) {
            throw new EnrollmentValidationException('Invalid athlete data: missing ID.');
        }
    }

    /**
     * Retrieve the per-person pricing for an event.
     *
     * @param  Event  $event  The event for which to retrieve pricing.
     * @return Pricing|null The per-person pricing, or null if not found.
     */
    private function getPerPersonPricing(Event $event): ?Pricing
    {
        return Pricing::active()
            ->where('event_id', $event->id)
            ->where('price_type', EvtEventFeeTypeEnum::PER_PERSON->value)
            ->first();
    }

    /**
     * Create an enrollment record for the event.
     *
     * @param  Event  $event  The event for which to create the enrollment.
     * @param  Federation|Entity  $enrollable  The enrollable entity.
     * @return Enrollment The created enrollment record.
     */
    private function createEnrollment(Event $event, Federation|Entity $enrollable): Enrollment
    {
        return Enrollment::create([
            'payment_status' => EvtEventPaymentStatusEnum::PENDING,
            'event_id' => $event->id,
            'enrollable_id' => $enrollable->id,
            'enrollable_type' => get_class($enrollable),
            'user_id' => auth()->id(),
        ]);
    }

    /**
     * Create an athlete enrollment record.
     *
     * @param  Enrollment  $enrollment  The enrollment record.
     * @param  array  $athlete  The athlete data.
     * @param  Federation|Entity  $enrollable  The enrollable entity.
     * @param  Pricing|null  $perPersonPricing  The per-person pricing.
     */
    private function createAthleteEnrollment(Enrollment $enrollment, array $athlete, Federation|Entity $enrollable, ?Pricing $perPersonPricing): void
    {
        $athleteData = [
            'enrollment_id' => $enrollment->id,
            'event_id' => $enrollment->event_id,
            'individual_id' => $athlete['id'],
            'status_class' => EvtAthleteEnrollmentStatusEnum::REGISTERED,
            'per_person_price' => $perPersonPricing?->price ?? 0,
            'per_person_pricing_id' => $perPersonPricing?->id,
        ];

        if ($enrollable instanceof Federation) {
            $athleteData['federation_id'] = $enrollable->id;
        } elseif ($enrollable instanceof Entity) {
            $athleteData['entity_id'] = $enrollable->id;
        }

        AthleteEnrollment::create($athleteData);
    }

    /**
     * Create a payment document for the enrollment.
     *
     * @param  Event  $event  The event for which to create the document.
     * @param  Enrollment  $enrollment  The enrollment record.
     * @param  Federation|Entity  $enrollable  The enrollable entity.
     * @param  array  $athletes  The athletes being enrolled.
     * @param  Pricing  $perPersonPricing  The per-person pricing.
     */
    private function createPaymentDocument(Event $event, Enrollment $enrollment, Federation|Entity $enrollable, array $athletes, Pricing $perPersonPricing): void
    {
        $totalCost = $perPersonPricing->price * count($athletes);

        $selectedIndividuals = array_map(function ($athlete) use ($perPersonPricing) {
            return [
                'individual_id' => $athlete['id'],
                'role' => 'ATHLETE',
                'pricing_id' => $perPersonPricing->id,
            ];
        }, $athletes);

        $createEnrollmentPaymentDocumentAction = new CreateEnrollmentPaymentDocumentAction;
        $createEnrollmentPaymentDocumentAction->execute(
            $event,
            $enrollment,
            $enrollable->id,
            get_class($enrollable),
            $selectedIndividuals,
            $totalCost,
            $perPersonPricing->id
        );
    }

}
