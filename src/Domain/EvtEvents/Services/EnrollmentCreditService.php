<?php

namespace Domain\EvtEvents\Services;

use Carbon\Carbon;
use Domain\Entities\Models\Entity;
use Domain\EvtEvents\Models\AthleteEnrollment;
use Domain\EvtEvents\Models\CoachEnrollment;
use Domain\EvtEvents\Models\EnrollmentCredit;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\Pricing;
use Domain\EvtEvents\Models\RefereeEnrollment;
use Domain\EvtEvents\Models\TeamOfficialEnrollment;
use Domain\Federations\Models\Federation;
use Illuminate\Support\Facades\Log;

class EnrollmentCreditService
{
    /**
     * Add credit when a participant is removed
     *
     * @param  AthleteEnrollment|CoachEnrollment|RefereeEnrollment|TeamOfficialEnrollment  $enrollment  The enrollment model being removed
     * @return array Information about the added credit
     */
    public function addCredit(AthleteEnrollment|CoachEnrollment|RefereeEnrollment|TeamOfficialEnrollment $enrollment): array
    {
        try {
            // Determine role type and monetary value based on enrollment model
            $roleType = $this->getRoleTypeFromEnrollment($enrollment);
            $monetaryValue = $enrollment->total_price ?? 0;

            // Get event and enrollable from enrollment
            $event = $enrollment->event;
            if (! $event) {
                Log::error('Failed to add enrollment credit - no event found', [
                    'enrollment_id' => $enrollment->id ?? null,
                    'enrollment_type' => get_class($enrollment),
                ]);

                return $this->getEmptyCreditResult($roleType);
            }

            // Get the main enrollment record and enrollable
            $mainEnrollment = $enrollment->enrollment;
            if (! $mainEnrollment) {
                Log::error('Failed to add enrollment credit - no main enrollment found', [
                    'enrollment_id' => $enrollment->id ?? null,
                    'enrollment_type' => get_class($enrollment),
                    'event_id' => $event->id ?? null,
                ]);

                return $this->getEmptyCreditResult($roleType);
            }

            $enrollable = $mainEnrollment->enrollable;
            if (! $enrollable) {
                Log::error('Failed to add enrollment credit - no enrollable found', [
                    'enrollment_id' => $enrollment->id ?? null,
                    'main_enrollment_id' => $mainEnrollment->id ?? null,
                    'event_id' => $event->id ?? null,
                ]);

                return $this->getEmptyCreditResult($roleType);
            }

            // If monetary value is missing, try to get it from the pricing
            if ($monetaryValue <= 0 && method_exists($enrollment, 'pricing') && $enrollment->pricing) {
                $monetaryValue = $enrollment->pricing->price ?? 0;
            }

            // If still no monetary value, use a default based on role (potential fallback)
            if ($monetaryValue <= 0) {
                $pricing = $this->getPricingForRole($event, $roleType);
                $monetaryValue = $pricing ? $pricing->price : 0;
            }

            // Calculate expiration date (optional)
            $expirationDate = $event->end_date
                ? Carbon::parse($event->end_date)
                : Carbon::now()->addDays(30);

            // Store credit in database
            $this->updateCredit(
                $event,
                $enrollable,
                $roleType,
                1, // Add 1 slot
                $monetaryValue,
                $expirationDate
            );

            Log::info('Added enrollment credit', [
                'event_id' => $event->id,
                'enrollable_type' => get_class($enrollable),
                'enrollable_id' => $enrollable->getKey(),
                'role_type' => $roleType,
                'monetary_value' => $monetaryValue,
                'expires_at' => $expirationDate,
            ]);

            return [
                'role_type' => $roleType,
                'slots_added' => 1,
                'monetary_value' => $monetaryValue,
                'expires_at' => $expirationDate,
            ];
        } catch (\Exception $e) {
            Log::error('Exception when adding enrollment credit', [
                'enrollment_id' => $enrollment->id ?? null,
                'enrollment_type' => get_class($enrollment),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Return empty result rather than failing
            return $this->getEmptyCreditResult($this->getDefaultRoleType($enrollment));
        }
    }

    /**
     * Get an empty credit result array
     */
    private function getEmptyCreditResult(string $roleType): array
    {
        return [
            'role_type' => $roleType,
            'slots_added' => 0,
            'monetary_value' => 0,
            'expires_at' => null,
            'error' => true,
        ];
    }

    /**
     * Get the default role type for an enrollment when normal detection fails
     */
    private function getDefaultRoleType(AthleteEnrollment|CoachEnrollment|RefereeEnrollment|TeamOfficialEnrollment $enrollment): string
    {
        $className = get_class($enrollment);
        if (strpos($className, 'Athlete') !== false) {
            return 'athlete';
        } elseif (strpos($className, 'Coach') !== false) {
            return 'coach';
        } elseif (strpos($className, 'Referee') !== false) {
            return 'referee';
        } elseif (strpos($className, 'Official') !== false) {
            return 'official';
        }

        return 'unknown';
    }

    /**
     * Get pricing for a role type
     */
    private function getPricingForRole(Event $event, string $roleType): ?Pricing
    {
        $enrollmentRole = match ($roleType) {
            'athlete' => 'ATHLETE',
            'coach' => 'COACH',
            'referee', 'technical-official' => 'REFEREE',
            'official' => 'OFFICIAL',
            default => null
        };

        if (! $enrollmentRole) {
            return null;
        }

        return Pricing::query()
            ->where('event_id', $event->id)
            ->where('enrollment_role', $enrollmentRole)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get available credits for a given event and organization
     *
     * @param  Event  $event  The event
     * @param  Federation|Entity  $enrollable  The enrollable model
     * @return array Available credits by role type
     */
    public function getAvailableCredits(Event $event, Federation|Entity $enrollable): array
    {
        $now = Carbon::now();

        $credits = EnrollmentCredit::query()
            ->where('event_id', $event->id)
            ->where('enrollable_id', $enrollable->id)
            ->where('enrollable_type', get_class($enrollable))
            ->where(function ($query) use ($now) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', $now);
            })
            ->get();

        $result = [];
        foreach ($credits as $credit) {
            $result[$credit->role_type] = [
                'available_slots' => $credit->available_slots,
                'monetary_value' => $credit->monetary_value,
                'expires_at' => $credit->expires_at,
            ];
        }

        return $result;
    }

    /**
     * Get available slots for a specific role type
     *
     * @param  Event  $event  The event
     * @param  Federation|Entity  $enrollable  The enrollable model
     * @param  string  $roleType  The role type
     * @return int Number of available slots
     */
    public function getAvailableSlots(Event $event, Federation|Entity $enrollable, string $roleType): int
    {
        $now = Carbon::now();

        return EnrollmentCredit::query()
            ->where('event_id', $event->id)
            ->where('enrollable_id', $enrollable->id)
            ->where('enrollable_type', get_class($enrollable))
            ->where('role_type', $roleType)
            ->where(function ($query) use ($now) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', $now);
            })
            ->value('available_slots') ?? 0;
    }

    /**
     * Update credit in database
     *
     * @param  Event  $event  The event
     * @param  Federation|Entity  $enrollable  The enrollable model
     * @param  string  $roleType  The role type
     * @param  int  $slots  Number of slots to add/subtract
     * @param  float  $monetaryValue  The monetary value to add/subtract
     * @param  Carbon|null  $expiresAt  Expiration date
     * @param  string  $operation  'add' or 'subtract'
     */
    private function updateCredit(
        Event $event,
        Federation|Entity $enrollable,
        string $roleType,
        int $slots,
        float $monetaryValue = 0,
        ?Carbon $expiresAt = null,
        string $operation = 'add'
    ): void {
        $credit = EnrollmentCredit::firstOrNew([
            'event_id' => $event->id,
            'enrollable_id' => $enrollable->id,
            'enrollable_type' => get_class($enrollable),
            'role_type' => $roleType,
        ]);

        $credit->available_slots = ($credit->available_slots ?? 0) + ($operation === 'add' ? $slots : -$slots);
        $credit->monetary_value = ($credit->monetary_value ?? 0) + ($operation === 'add' ? $monetaryValue : -$monetaryValue);
        $credit->expires_at = $expiresAt;
        $credit->save();
    }

    /**
     * Use available credits for new registrations
     *
     * @param  Event  $event  The event
     * @param  Federation|Entity  $enrollable  The enrollable model
     * @param  array  $participants  The participants by role type
     * @return array Credits used by role type
     */
    public function useCredits(
        Event $event,
        Federation|Entity $enrollable,
        array $participants
    ): array {
        $creditsUsed = [];

        foreach ($participants as $roleType => $roleParticipants) {
            $participantCount = count($roleParticipants);
            if ($participantCount > 0) {
                $availableSlots = $this->getAvailableSlots($event, $enrollable, $roleType);
                $usableSlots = min($availableSlots, $participantCount);

                if ($usableSlots > 0) {
                    // Get credit record to calculate monetary value
                    $creditRecord = EnrollmentCredit::query()
                        ->where('event_id', $event->id)
                        ->where('enrollable_id', $enrollable->id)
                        ->where('enrollable_type', get_class($enrollable))
                        ->where('role_type', $roleType)
                        ->first();

                    $monetaryValuePerSlot = $creditRecord && $creditRecord->available_slots > 0
                        ? $creditRecord->monetary_value / $creditRecord->available_slots
                        : 0;

                    $monetaryValueUsed = $usableSlots * $monetaryValuePerSlot;

                    // Update the credit record by deducting used slots
                    $this->updateCredit(
                        $event,
                        $enrollable,
                        $roleType,
                        $usableSlots,
                        $monetaryValueUsed,
                        null,
                        'subtract'
                    );

                    $creditsUsed[$roleType] = [
                        'slots_used' => $usableSlots,
                        'monetary_value' => $monetaryValueUsed,
                    ];

                    Log::info('Used enrollment credits', [
                        'event_id' => $event->id,
                        'enrollable_type' => get_class($enrollable),
                        'enrollable_id' => $enrollable->id,
                        'role_type' => $roleType,
                        'slots_used' => $usableSlots,
                        'monetary_value_used' => $monetaryValueUsed,
                    ]);
                }
            }
        }

        return $creditsUsed;
    }

    /**
     * Determine role type from enrollment model
     *
     * @param  AthleteEnrollment|CoachEnrollment|RefereeEnrollment|TeamOfficialEnrollment  $enrollment  The enrollment model
     * @return string The role type
     *
     * @throws \Exception If the enrollment type is unknown
     */
    private function getRoleTypeFromEnrollment(AthleteEnrollment|CoachEnrollment|RefereeEnrollment|TeamOfficialEnrollment $enrollment): string
    {
        return match (true) {
            $enrollment instanceof AthleteEnrollment => 'athlete',
            $enrollment instanceof CoachEnrollment => 'coach',
            $enrollment instanceof RefereeEnrollment => 'referee',
            $enrollment instanceof TeamOfficialEnrollment => 'official',
            default => throw new \Exception('Unknown enrollment type: ' . get_class($enrollment))
        };
    }

    /**
     * Clear expired credits (could be run via scheduled job)
     *
     * @return int Number of expired credits cleared
     */
    public function clearExpiredCredits(): int
    {
        return EnrollmentCredit::query()
            ->where('expires_at', '<', Carbon::now())
            ->delete();
    }
}
