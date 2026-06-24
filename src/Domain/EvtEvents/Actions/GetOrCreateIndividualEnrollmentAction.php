<?php

declare(strict_types=1);

namespace Domain\EvtEvents\Actions;

use App\Enums\EvtEventFeeTypeEnum;
use App\Enums\EvtEventPaymentStatusEnum;
use App\Models\User;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\Pricing;
use Domain\Individuals\Models\Individual;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Cache;

final readonly class GetOrCreateIndividualEnrollmentAction
{
    public function __construct(private DatabaseManager $db) {}

    /**
     * Returns a reusable parent `Enrollment` when the event charges **PER_PERSON** or
     * **EVENT_FEE**; otherwise a fresh record is created.
     */
    public function execute(Event $event, Individual $individual, string $userId): Enrollment
    {
        $causer = User::find($userId);

        return $this->db->connection()->transaction(
            fn () => tap(
                $this->shouldReuseEnrollment($event)
                    ? $this->firstOrCreateEnrollment($event, $individual, $userId)
                    : $this->createNewEnrollment($event, $individual, $userId),
                fn (Enrollment $enrollment) => $this->logActivity(
                    $event,
                    $individual,
                    $causer,
                    $enrollment
                )
            ),
            attempts: 3
        );
    }

    // ────────────────────────── helpers ─────────────────────────

    private function shouldReuseEnrollment(Event $event): bool
    {
        return Cache::remember(
            "evt:$event->id:has_single_charge_pricing",
            now()->addMinutes(10),
            static fn () => Pricing::query()
                ->where('event_id', $event->id)
                ->active()               // local scope with date/window logic
                ->whereIn('price_type', [
                    EvtEventFeeTypeEnum::PER_PERSON,
                    EvtEventFeeTypeEnum::EVENT_FEE,
                ])
                ->exists()
        );
    }

    /** Reuse-path: find or create a single enrollment keyed by event + enrollable. */
    private function firstOrCreateEnrollment(
        Event $event,
        Individual $individual,
        string $userId
    ): Enrollment {
        return Enrollment::query()->firstOrCreate(
            [
                'event_id' => $event->id,
                'enrollable_id' => $individual->id,
                'enrollable_type' => Individual::class,
                'user_id' => $userId,
            ],
            [
                'payment_status' => EvtEventPaymentStatusEnum::PENDING,
                'total_price' => 0,
            ],
        );
    }

    /** Fresh-path: always insert a brand-new enrollment row. */
    private function createNewEnrollment(
        Event $event,
        Individual $individual,
        string $userId
    ): Enrollment {
        return Enrollment::query()->create([
            'event_id' => $event->id,
            'enrollable_id' => $individual->id,
            'enrollable_type' => Individual::class,
            'user_id' => $userId,
            'payment_status' => EvtEventPaymentStatusEnum::PENDING,
            'total_price' => 0,
        ]);
    }

    private function logActivity(
        Event $event,
        Individual $individual,
        ?User $causer,
        Enrollment $enrollment
    ): void {
        activity('enrollment_process')
            ->causedBy($causer)
            ->performedOn($enrollment)
            ->withProperties([
                'event_id' => $event->id,
                'individual_id' => $individual->id,
                'reused' => ! $enrollment->wasRecentlyCreated,
            ])
            ->log($enrollment->wasRecentlyCreated
                ? 'Created parent enrollment'
                : 'Reused parent enrollment');
    }
}
