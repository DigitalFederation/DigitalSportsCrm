<?php

namespace App\Livewire\EvtEvents;

use App\Enums\EvtAthleteEnrollmentStatusEnum;
use App\Enums\EvtEventEnrollmentRoleEnum;
use App\Enums\EvtEventFeeTypeEnum;
use App\Enums\EvtEventPaymentStatusEnum;
use Domain\Documents\States\CanceledDocumentState;
use Domain\Documents\States\PaidDocumentState;
use Domain\Documents\States\VoidDocumentState;
use Domain\Entities\Models\Entity;
use Domain\EvtEvents\Actions\ManualGenerateAthleteEnrollmentPaymentAction;
use Domain\EvtEvents\Models\AthleteEnrollment;
use Domain\EvtEvents\Models\CoachEnrollment;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\Pricing;
use Domain\EvtEvents\Models\RefereeEnrollment;
use Domain\EvtEvents\Models\TeamOfficialEnrollment;
use Domain\EvtEvents\States\ActiveRefereeEnrollmentState;
use Domain\EvtEvents\States\PendingCoachEnrollmentState;
use Domain\EvtEvents\States\PendingRefereeEnrollmentState;
use Domain\EvtEvents\States\PendingTeamOfficialEnrollmentState;
use Domain\EvtEvents\States\RegisteredCoachEnrollmentState;
use Domain\EvtEvents\States\RegisteredTeamOfficialEnrollmentState;
use Domain\Federations\Models\Federation;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class ReviewAndPay extends Component
{
    public Event $event;

    public Federation|Entity $model;

    public ?Enrollment $enrollment = null;

    public array $costBreakdown = [];

    public float $grandTotal = 0;

    public array $enrollmentsByDiscipline = [];

    public array $otherEnrollments = [];

    public array $disciplineAttributes = [];

    public array $otherAttributes = [];

    public function mount(Event $event, Federation|Entity $model): void
    {
        $this->event = $event;
        $this->model = $model;

        $this->loadEnrollmentData();
    }

    protected function loadEnrollmentData(): void
    {
        // Find the most recent enrollment for this event and model
        $this->enrollment = Enrollment::where('event_id', $this->event->id)
            ->where('enrollable_id', $this->model->id)
            ->where('enrollable_type', get_class($this->model))
            ->latest()
            ->first();

        if (! $this->enrollment) {
            return;
        }

        // Load enrollments organized by discipline
        $this->loadEnrollmentsByDiscipline();

        // Load other enrollments (coaches, referees, officials)
        $this->loadOtherEnrollments();

        // Calculate costs
        $this->calculateCosts();
    }

    protected function loadEnrollmentsByDiscipline(): void
    {
        $this->enrollmentsByDiscipline = [];
        $this->disciplineAttributes = [];

        // Load athlete enrollments with discipline and attributes (exclude already confirmed)
        $athleteEnrollments = $this->event->athleteEnrollments()
            ->where(function ($query) {
                if ($this->model instanceof Federation) {
                    $query->where('federation_id', $this->model->id);
                } else {
                    $query->where('entity_id', $this->model->id);
                }
            })
            ->whereNotIn('status_class', [
                EvtAthleteEnrollmentStatusEnum::DISCIPLINE_ASSIGNED->value,
                EvtAthleteEnrollmentStatusEnum::COMPLETED->value,
                EvtAthleteEnrollmentStatusEnum::CANCELED->value,
            ])
            ->with([
                'individual',
                'discipline.attributes',
                'attributes.attribute',
            ])
            ->get();

        // Track unique individuals for per-person fee (only count once per individual)
        $countedIndividuals = [];

        // Group by discipline
        foreach ($athleteEnrollments as $enrollment) {
            $disciplineId = $enrollment->discipline_id ?? 'unassigned';
            $disciplineName = $enrollment->discipline?->name ?? __('events.no_discipline_assigned');
            $enrollmentType = $enrollment->discipline?->enrollment_type ?? 'individual';
            $isTeamOrRelay = in_array($enrollmentType, ['team', 'relay']);

            if (! isset($this->enrollmentsByDiscipline[$disciplineId])) {
                $this->enrollmentsByDiscipline[$disciplineId] = [
                    'discipline_id' => $disciplineId,
                    'discipline_name' => $disciplineName,
                    'enrollment_type' => $enrollmentType,
                    'is_team_or_relay' => $isTeamOrRelay,
                    'enrollments' => [],
                    'per_person_subtotal' => 0,
                    'discipline_fee' => 0,
                    'subtotal' => 0,
                ];

                // Get attributes for this discipline
                if ($enrollment->discipline) {
                    $this->disciplineAttributes[$disciplineId] = $enrollment->discipline->attributes
                        ->map(fn ($attr) => [
                            'id' => $attr->id,
                            'name' => $attr->name,
                            'type' => $attr->attribute_type,
                        ])
                        ->toArray();
                }
            }

            // Build attribute values map
            $attributeValues = [];
            foreach ($enrollment->attributes as $attrValue) {
                $attributeValues[$attrValue->attribute_id] = $attrValue->value;
            }

            // Calculate per-person price (only once per unique individual across all disciplines)
            $perPersonPrice = 0;
            $individualId = $enrollment->individual_id;
            if (! in_array($individualId, $countedIndividuals) && ($enrollment->per_person_price ?? 0) > 0) {
                $perPersonPrice = $enrollment->per_person_price;
                $countedIndividuals[] = $individualId;
            }

            // For team/relay: discipline fee is global, only count once per discipline
            // For individual: discipline fee is per athlete
            $disciplinePrice = $enrollment->discipline_price ?? 0;

            if ($isTeamOrRelay) {
                // Only set discipline fee once for the entire team/relay
                if ($this->enrollmentsByDiscipline[$disciplineId]['discipline_fee'] === 0 && $disciplinePrice > 0) {
                    $this->enrollmentsByDiscipline[$disciplineId]['discipline_fee'] = $disciplinePrice;
                }
                // For team/relay athletes, their individual contribution is only the per-person fee
                $athleteContribution = $perPersonPrice;
            } else {
                // For individual disciplines: discipline fee is per athlete
                $this->enrollmentsByDiscipline[$disciplineId]['discipline_fee'] += $disciplinePrice;
                $athleteContribution = $perPersonPrice + $disciplinePrice;
            }

            $this->enrollmentsByDiscipline[$disciplineId]['enrollments'][] = [
                'id' => $enrollment->id,
                'type' => 'athlete',
                'individual_id' => $individualId,
                'name' => $enrollment->individual?->full_name ?? __('events.unknown'),
                'per_person_price' => $perPersonPrice,
                'discipline_price' => $isTeamOrRelay ? 0 : $disciplinePrice, // Don't show per-athlete for team/relay
                'event_fee' => $enrollment->event_fee ?? 0,
                'total_price' => $athleteContribution,
                'attributes' => $attributeValues,
                'status' => $enrollment->status_class,
            ];

            $this->enrollmentsByDiscipline[$disciplineId]['per_person_subtotal'] += $perPersonPrice;
        }

        // Calculate final subtotals for each discipline
        foreach ($this->enrollmentsByDiscipline as $disciplineId => &$data) {
            $data['subtotal'] = $data['per_person_subtotal'] + $data['discipline_fee'];
        }
    }

    protected function loadOtherEnrollments(): void
    {
        $this->otherEnrollments = [
            'coaches' => [],
            'referees' => [],
            'officials' => [],
        ];

        $this->otherAttributes = [
            'coaches' => $this->event->coachAttributes->map(fn ($attr) => [
                'id' => $attr->id,
                'name' => $attr->name,
            ])->toArray(),
            'referees' => $this->event->refereeAttributes->map(fn ($attr) => [
                'id' => $attr->id,
                'name' => $attr->name,
            ])->toArray(),
            'officials' => $this->event->officialAttributes->map(fn ($attr) => [
                'id' => $attr->id,
                'name' => $attr->name,
            ])->toArray(),
        ];

        // Load coaches (only pending ones that need confirmation in STEP 2)
        $coachEnrollments = $this->event->coachEnrollments()
            ->where(function ($query) {
                if ($this->model instanceof Federation) {
                    $query->where('federation_id', $this->model->id);
                } else {
                    $query->where('entity_id', $this->model->id);
                }
            })
            ->where('status_class', PendingCoachEnrollmentState::class)
            ->with(['individual', 'attributes.attribute'])
            ->get();

        foreach ($coachEnrollments as $enrollment) {
            $attributeValues = [];
            foreach ($enrollment->attributes as $attrValue) {
                $attributeValues[$attrValue->attribute_id] = $attrValue->value;
            }

            $this->otherEnrollments['coaches'][] = [
                'id' => $enrollment->id,
                'type' => 'coach',
                'individual_id' => $enrollment->individual_id,
                'name' => $enrollment->individual?->full_name ?? __('events.unknown'),
                'price' => $enrollment->price ?? 0,
                'attributes' => $attributeValues,
            ];
        }

        // Load referees (only for federations, only pending ones that need confirmation in STEP 2)
        if ($this->model instanceof Federation) {
            $refereeEnrollments = $this->event->refereeEnrollments()
                ->where('federation_id', $this->model->id)
                ->where('status_class', PendingRefereeEnrollmentState::class)
                ->with(['individual', 'attributes.attribute'])
                ->get();

            foreach ($refereeEnrollments as $enrollment) {
                $attributeValues = [];
                foreach ($enrollment->attributes as $attrValue) {
                    $attributeValues[$attrValue->attribute_id] = $attrValue->value;
                }

                $this->otherEnrollments['referees'][] = [
                    'id' => $enrollment->id,
                    'type' => 'referee',
                    'individual_id' => $enrollment->individual_id,
                    'name' => $enrollment->individual?->full_name ?? __('events.unknown'),
                    'price' => $enrollment->price ?? 0,
                    'attributes' => $attributeValues,
                ];
            }
        }

        // Load team officials (only pending ones that need confirmation in STEP 2)
        $officialEnrollments = $this->event->officialsEnrollments()
            ->where(function ($query) {
                if ($this->model instanceof Federation) {
                    $query->where('federation_id', $this->model->id);
                } else {
                    $query->where('entity_id', $this->model->id);
                }
            })
            ->where('status_class', PendingTeamOfficialEnrollmentState::class)
            ->with(['individual', 'attributes.attribute'])
            ->get();

        foreach ($officialEnrollments as $enrollment) {
            $attributeValues = [];
            foreach ($enrollment->attributes as $attrValue) {
                $attributeValues[$attrValue->attribute_id] = $attrValue->value;
            }

            $this->otherEnrollments['officials'][] = [
                'id' => $enrollment->id,
                'type' => 'official',
                'individual_id' => $enrollment->individual_id,
                'name' => $enrollment->individual?->full_name ?? __('events.unknown'),
                'price' => $enrollment->price ?? 0,
                'attributes' => $attributeValues,
            ];
        }
    }

    protected function calculateCosts(): void
    {
        $this->costBreakdown = [
            'registrations' => [],
            'disciplines' => [],
            'other' => [],
        ];
        $this->grandTotal = 0;

        // === SECTION 1: Per-Person Registration Fees ===

        // Athletes (unique individuals only)
        $uniqueAthletes = [];
        $athletePerPersonPrice = 0;
        $athletePerPersonTotal = 0;
        foreach ($this->enrollmentsByDiscipline as $disciplineData) {
            foreach ($disciplineData['enrollments'] as $enrollment) {
                if (! in_array($enrollment['individual_id'], $uniqueAthletes)) {
                    if ($enrollment['per_person_price'] > 0) {
                        $athletePerPersonPrice = $enrollment['per_person_price']; // Assume same price for all
                        $athletePerPersonTotal += $enrollment['per_person_price'];
                    }
                    $uniqueAthletes[] = $enrollment['individual_id'];
                }
            }
        }

        if (count($uniqueAthletes) > 0 && $athletePerPersonTotal > 0) {
            $this->costBreakdown['registrations']['athletes'] = [
                'label' => __('events.athletes'),
                'count' => count($uniqueAthletes),
                'unit_price' => $athletePerPersonPrice,
                'total' => $athletePerPersonTotal,
            ];
        }

        // Coaches
        $coachCount = count($this->otherEnrollments['coaches']);
        $coachTotal = array_sum(array_column($this->otherEnrollments['coaches'], 'price'));
        $coachUnitPrice = $coachCount > 0 ? ($coachTotal / $coachCount) : 0;
        if ($coachCount > 0) {
            $this->costBreakdown['registrations']['coaches'] = [
                'label' => __('events.coaches'),
                'count' => $coachCount,
                'unit_price' => $coachUnitPrice,
                'total' => $coachTotal,
            ];
        }

        // Referees (federation only)
        $refereeCount = count($this->otherEnrollments['referees']);
        $refereeTotal = array_sum(array_column($this->otherEnrollments['referees'], 'price'));
        $refereeUnitPrice = $refereeCount > 0 ? ($refereeTotal / $refereeCount) : 0;
        if ($refereeCount > 0) {
            $this->costBreakdown['registrations']['referees'] = [
                'label' => __('events.referees'),
                'count' => $refereeCount,
                'unit_price' => $refereeUnitPrice,
                'total' => $refereeTotal,
            ];
        }

        // Team Officials
        $officialCount = count($this->otherEnrollments['officials']);
        $officialTotal = array_sum(array_column($this->otherEnrollments['officials'], 'price'));
        $officialUnitPrice = $officialCount > 0 ? ($officialTotal / $officialCount) : 0;
        if ($officialCount > 0) {
            $this->costBreakdown['registrations']['officials'] = [
                'label' => __('events.team_officials'),
                'count' => $officialCount,
                'unit_price' => $officialUnitPrice,
                'total' => $officialTotal,
            ];
        }

        // === SECTION 2: Discipline Fees (itemized by discipline) ===

        foreach ($this->enrollmentsByDiscipline as $disciplineId => $disciplineData) {
            if ($disciplineData['discipline_fee'] > 0) {
                $isTeamOrRelay = $disciplineData['is_team_or_relay'];
                $enrollmentCount = count($disciplineData['enrollments']);

                if ($isTeamOrRelay) {
                    // For team/relay: fee is flat (1 entry)
                    $entries = 1;
                    $unitPrice = $disciplineData['discipline_fee'];
                } else {
                    // For individual: fee is per athlete
                    $entries = $enrollmentCount;
                    $unitPrice = $enrollmentCount > 0 ? ($disciplineData['discipline_fee'] / $enrollmentCount) : 0;
                }

                $this->costBreakdown['disciplines'][$disciplineId] = [
                    'name' => $disciplineData['discipline_name'],
                    'entries' => $entries,
                    'unit_price' => $unitPrice,
                    'total' => $disciplineData['discipline_fee'],
                    'is_team_or_relay' => $isTeamOrRelay,
                ];
            }
        }

        // === SECTION 3: Event Fee (flat fee for competition) ===

        $eventFeeTotal = 0;
        foreach ($this->enrollmentsByDiscipline as $disciplineData) {
            foreach ($disciplineData['enrollments'] as $enrollment) {
                if (($enrollment['event_fee'] ?? 0) > 0) {
                    $eventFeeTotal = $enrollment['event_fee'];
                    break 2; // Only count once
                }
            }
        }

        if ($eventFeeTotal > 0) {
            $this->costBreakdown['other']['event_fee'] = [
                'label' => __('events.event_fee'),
                'count' => 1,
                'total' => $eventFeeTotal,
            ];
        }

        // === Calculate Grand Total ===

        $registrationTotal = array_sum(array_column($this->costBreakdown['registrations'], 'total'));
        $disciplineTotal = array_sum(array_column($this->costBreakdown['disciplines'], 'total'));
        $otherTotal = array_sum(array_column($this->costBreakdown['other'], 'total'));

        $this->grandTotal = $registrationTotal + $disciplineTotal + $otherTotal;
    }

    public function removeEnrollment(int $enrollmentId, string $type): void
    {
        $model = match ($type) {
            'athlete' => AthleteEnrollment::class,
            'coach' => CoachEnrollment::class,
            'referee' => RefereeEnrollment::class,
            'official' => TeamOfficialEnrollment::class,
            default => null,
        };

        if (! $model) {
            Notification::make()
                ->title(__('events.error'))
                ->body(__('events.invalid_enrollment_type'))
                ->danger()
                ->send();

            return;
        }

        $enrollment = $model::find($enrollmentId);

        if (! $enrollment) {
            Notification::make()
                ->title(__('events.error'))
                ->body(__('events.enrollment_not_found'))
                ->danger()
                ->send();

            return;
        }

        // Delete the enrollment
        $enrollment->delete();

        // Reload data
        $this->loadEnrollmentData();

        Notification::make()
            ->title(__('events.success'))
            ->body(__('events.enrollment_removed'))
            ->success()
            ->send();
    }

    public function confirmAndGeneratePayment(): void
    {
        if (! $this->enrollment) {
            Notification::make()
                ->title(__('events.error'))
                ->body(__('events.no_enrollment_found'))
                ->danger()
                ->send();

            return;
        }

        if ($this->grandTotal <= 0) {
            if ($this->hasActivePaidPricingForCurrentEnrollment()) {
                Log::warning('Blocked event enrollment confirmation with zero total while paid pricing exists', [
                    'event_id' => $this->event->id,
                    'enrollment_id' => $this->enrollment->id,
                    'enrollable_type' => $this->enrollment->enrollable_type,
                    'enrollable_id' => $this->enrollment->enrollable_id,
                ]);

                Notification::make()
                    ->title(__('events.configuration_error'))
                    ->body(__('events.pricing_config_error'))
                    ->danger()
                    ->send();

                return;
            }

            $this->enrollment->update([
                'payment_status' => EvtEventPaymentStatusEnum::PAID->value,
                'activated_at' => now(),
            ]);

            $this->transitionEnrollmentsToConfirmed();

            Notification::make()
                ->title(__('events.no_payment_required'))
                ->body(__('events.registration_complete'))
                ->success()
                ->send();

            $this->redirect($this->step3Route);

            return;
        }

        try {
            $action = app(ManualGenerateAthleteEnrollmentPaymentAction::class);
            $document = $action($this->enrollment);

            if (! $document) {
                Notification::make()
                    ->title(__('events.error'))
                    ->body(__('events.payment_generation_failed'))
                    ->danger()
                    ->send();

                return;
            }

            $this->enrollment->update([
                'document_id' => $document->id,
            ]);

            Notification::make()
                ->title(__('events.success'))
                ->body(__('events.payment_document_generated'))
                ->success()
                ->send();

            // Redirect to the payment document
            $documentRoute = $this->model instanceof Federation
                ? route('federation.document.show', $document->id)
                : route('entity.document.show', $document->id);

            $this->redirect($documentRoute);
        } catch (\Exception $e) {
            Log::error('Failed to generate payment document', [
                'enrollment_id' => $this->enrollment->id,
                'error' => $e->getMessage(),
            ]);

            Notification::make()
                ->title(__('events.error'))
                ->body(__('events.payment_generation_failed'))
                ->danger()
                ->send();
        }
    }

    private function hasActivePaidPricingForCurrentEnrollment(): bool
    {
        if (! $this->enrollment) {
            return false;
        }

        $roles = [];

        if (! empty($this->enrollmentsByDiscipline)) {
            $roles[] = EvtEventEnrollmentRoleEnum::ATHLETE->value;
        }

        if (! empty($this->otherEnrollments['coaches'])) {
            $roles[] = EvtEventEnrollmentRoleEnum::COACH->value;
        }

        if (! empty($this->otherEnrollments['officials'])) {
            $roles[] = EvtEventEnrollmentRoleEnum::OFFICIAL->value;
        }

        if (! empty($this->otherEnrollments['referees'])) {
            $roles[] = EvtEventEnrollmentRoleEnum::TECHNICAL_OFFICIAL->value;
            $roles[] = EvtEventEnrollmentRoleEnum::REFEREE->value;
        }

        if (empty($roles)) {
            return false;
        }

        return Pricing::active()
            ->where('event_id', $this->event->id)
            ->where(function ($query) use ($roles) {
                $query->whereIn('enrollment_role', array_unique($roles))
                    ->orWhereNull('enrollment_role');
            })
            ->whereIn('price_type', [
                EvtEventFeeTypeEnum::PER_PERSON->value,
                EvtEventFeeTypeEnum::PER_DISCIPLINE->value,
                EvtEventFeeTypeEnum::EVENT_FEE->value,
                EvtEventFeeTypeEnum::FLAT_FEE->value,
            ])
            ->where('price', '>', 0)
            ->exists();
    }

    protected function transitionEnrollmentsToConfirmed(): void
    {
        // Athletes: Pending -> Discipline Assigned
        AthleteEnrollment::where('enrollment_id', $this->enrollment->id)
            ->whereNotIn('status_class', [
                EvtAthleteEnrollmentStatusEnum::DISCIPLINE_ASSIGNED->value,
                EvtAthleteEnrollmentStatusEnum::COMPLETED->value,
                EvtAthleteEnrollmentStatusEnum::CANCELED->value,
            ])
            ->update(['status_class' => EvtAthleteEnrollmentStatusEnum::DISCIPLINE_ASSIGNED->value]);

        // Coaches: Pending -> Registered
        CoachEnrollment::where('enrollment_id', $this->enrollment->id)
            ->where('status_class', PendingCoachEnrollmentState::class)
            ->update(['status_class' => RegisteredCoachEnrollmentState::class]);

        // Team Officials: Pending -> Registered
        TeamOfficialEnrollment::where('enrollment_id', $this->enrollment->id)
            ->where('status_class', PendingTeamOfficialEnrollmentState::class)
            ->update(['status_class' => RegisteredTeamOfficialEnrollmentState::class]);

        // Referees: Pending -> Active
        RefereeEnrollment::where('enrollment_id', $this->enrollment->id)
            ->where('status_class', PendingRefereeEnrollmentState::class)
            ->update(['status_class' => ActiveRefereeEnrollmentState::class]);
    }

    public function proceedToPayment(): void
    {
        if (! $this->enrollment || ! $this->enrollment->document_id) {
            Notification::make()
                ->title(__('events.no_document_found'))
                ->body(__('events.please_contact_support'))
                ->danger()
                ->send();

            return;
        }

        $documentRoute = $this->model instanceof Federation
            ? route('federation.document.show', $this->enrollment->document_id)
            : route('entity.document.show', $this->enrollment->document_id);

        $this->redirect($documentRoute);
    }

    public function getStep1RouteProperty(): string
    {
        return $this->model instanceof Federation
            ? route('federation.evt-events.events.enrollments.create', ['event' => $this->event, 'type' => 'athlete'])
            : route('entity.evt-events.events.enrollments.create', ['event' => $this->event, 'type' => 'athlete']);
    }

    public function getStep3RouteProperty(): string
    {
        return $this->model instanceof Federation
            ? route('federation.evt-events.events.confirmed-enrollments', ['event' => $this->event])
            : route('entity.evt-events.events.confirmed-enrollments', ['event' => $this->event]);
    }

    public function render()
    {
        $namespace = $this->model instanceof Federation ? 'federation' : 'entity';

        $hasEnrollments = ! empty($this->enrollmentsByDiscipline) ||
            ! empty($this->otherEnrollments['coaches']) ||
            ! empty($this->otherEnrollments['referees']) ||
            ! empty($this->otherEnrollments['officials']);

        // If document_id references a soft-deleted/missing document, clear the stale reference
        if ($this->enrollment?->document_id && ! $this->enrollment->document) {
            $this->enrollment->update(['document_id' => null]);
            $this->enrollment->document_id = null;
        }

        $hasDocument = $this->enrollment?->document_id !== null;
        $documentIsSettled = $hasDocument && in_array($this->enrollment->document?->status_class, [
            PaidDocumentState::class,
            CanceledDocumentState::class,
            VoidDocumentState::class,
        ]);

        $hasPendingPayment = $hasDocument && ! $documentIsSettled && $this->grandTotal > 0;

        $enrollmentMismatch = false;
        if ($hasPendingPayment && $this->enrollment->document) {
            $enrollmentMismatch = abs((float) $this->enrollment->document->total_value - $this->grandTotal) > 0.01;
        }

        return view('livewire.evt-events.review-and-pay', [
            'namespace' => $namespace,
            'hasEnrollments' => $hasEnrollments,
            'hasPendingPayment' => $hasPendingPayment,
            'needsPaymentDocument' => (! $hasDocument || $documentIsSettled) && $this->grandTotal > 0,
            'enrollmentMismatch' => $enrollmentMismatch,
        ]);
    }
}
