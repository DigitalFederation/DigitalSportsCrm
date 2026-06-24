<?php

namespace App\Livewire\EvtEvents;

use App\Enums\EvtAthleteEnrollmentStatusEnum;
use App\Enums\EvtEventEnrollmentRoleEnum;
use App\Traits\ValidatesEventAttributes;
use Domain\EvtEvents\Actions\CheckExistingEventEnrollmentAction;
use Domain\EvtEvents\Actions\CheckIndividualCompetitionEligibilityAction;
use Domain\EvtEvents\Actions\CreateIndividualAthleteEnrollmentAction;
use Domain\EvtEvents\Actions\FinalizeIndividualEnrollmentAction;
use Domain\EvtEvents\Actions\GetAttributesAndRulesFromDisciplineAction;
use Domain\EvtEvents\Actions\GetDisciplinesFromEventForIndividualAction;
use Domain\EvtEvents\Actions\GetIneligibleDisciplinesForIndividualAction;
use Domain\EvtEvents\Actions\GetOrCreateIndividualEnrollmentAction;
use Domain\EvtEvents\Actions\LoadEventPricingDataAction;
use Domain\EvtEvents\Actions\RemoveIndividualAthleteEnrollmentAction;
use Domain\EvtEvents\Models\AthleteEnrollment;
use Domain\EvtEvents\Models\Discipline;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\Pricing;
use Domain\Individuals\Models\Individual;
use Exception;
use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class IndividualCreateAthleteEnrollment extends Component
{
    use ValidatesEventAttributes;

    public $errorMessage = '';
    public array $errorMessages = [];
    public Event $event;
    public Individual $individual;
    public $selectedDiscipline;
    /** @var \Illuminate\Support\Collection */
    public $disciplines;
    /** @var \Illuminate\Support\Collection */
    public $ineligibleDisciplines;
    /** @var \Illuminate\Support\Collection */
    public $pricingData;
    public $selectedPricingIds = [
        'perPerson' => null,
        'discipline' => null,
        'eventFee' => null,
    ];
    public $disciplineAttributes = [];
    public $disciplineAttributeValues = [];
    /** @var array Holds the disciplines selected by the user before finalization (in-memory cart). */
    public array $enrollmentItems = []; // << NEW: Replaced $pendingEnrollments
    public $totalCost = 0;
    public $costBreakdown = [];
    /** @var \Illuminate\Support\Collection|null Holds athlete enrollments that are already confirmed (paid, pending payment, etc.). */
    public $confirmedAthleteEnrollments = null; // << Keeps track of already finalized enrollments
    public bool $hasEnrollmentItems = false; // << NEW: Tracks if the cart has items
    public bool $isFinalizing = false;
    public bool $showSuccessModal = false;
    public array $successModalData = [];
    public float $currentDisciplineCost = 0;
    public bool $showRemoveConfirmationModal = false;
    public ?int $enrollmentToRemoveId = null;
    public $attributesKey;

    protected $listeners = [
        'resetForm' => 'resetForm',
    ];

    public function mount(Event $event, Individual $individual)
    {
        $this->individual = $individual;
        $this->event = $event;
        $this->pricingData = collect();
        $this->disciplines = collect();
        $this->ineligibleDisciplines = collect();
        $this->errorMessages = [];
        $this->enrollmentItems = []; // Initialize cart
        $this->confirmedAthleteEnrollments = collect();
        $this->attributesKey = uniqid();
        $this->selectedDiscipline = null; // << ENSURE IT STARTS NULL

        // Load existing confirmed enrollments
        $this->loadConfirmedEnrollments();
        $this->loadIneligibleDisciplines();

        if (! $this->event->isRegistrationOpen()) {
            if (! $this->event->allowsEnrollments()) {
                $this->errorMessage = __('events.enrollments_not_permitted', ['state' => $this->event->stateName()]);
            } elseif ($this->event->isRegistrationNotStarted()) {
                $this->errorMessage = __('events.registration_not_opened', ['date' => $this->event->start_registration->format('Y-m-d')]);
            } elseif ($this->event->isRegistrationClosed()) {
                $this->errorMessage = __('events.registration_closed', ['date' => $this->event->end_registration->format('Y-m-d')]);
            }
        } else {
            // Check competition-level eligibility (required documents, licenses)
            $eligibilityReasons = app(CheckIndividualCompetitionEligibilityAction::class)
                ->execute($this->event, $this->individual);

            if (! empty($eligibilityReasons)) {
                $this->errorMessages = $eligibilityReasons;
            } else {
                // Enrollments are *actually* open - proceed with loading disciplines etc.
                $this->loadEligibleDisciplines(); // Load disciplines available for adding
            }
        }
    }

    /**
     * Load disciplines the individual can still enroll in, excluding confirmed ones
     * and those already added to the current enrollmentItems list.
     */
    protected function loadEligibleDisciplines()
    {
        $action = new GetDisciplinesFromEventForIndividualAction;
        $allDisciplines = $action->execute($this->event, $this->individual);

        $confirmedDisciplineIds = $this->confirmedAthleteEnrollments->pluck('discipline_id')->filter()->unique();
        $cartDisciplineIds = collect($this->enrollmentItems)->pluck('discipline_id')->filter()->unique(); // Check cart
        $registeredDisciplineIds = $confirmedDisciplineIds->merge($cartDisciplineIds)->unique()->toArray();

        // Filter out already registered disciplines (confirmed or in cart)
        $this->disciplines = $allDisciplines->reject(function ($discipline) use ($registeredDisciplineIds) {
            return in_array($discipline->id, $registeredDisciplineIds);
        });

        // Also reload ineligible disciplines whenever eligible ones change
        $this->loadIneligibleDisciplines();

        // Reset selected discipline if it's no longer available or not in the eligible list
        if ($this->selectedDiscipline && ! $this->disciplines->contains('id', $this->selectedDiscipline)) {
            $this->selectedDiscipline = null; // << RESET TO NULL
            // Trigger updates to clear attributes etc. if selection becomes invalid
            $this->updatedSelectedDiscipline($this->selectedDiscipline);
        }
    }

    /**
     * Load disciplines the individual CANNOT enroll in and the reasons why.
     */
    protected function loadIneligibleDisciplines()
    {
        $action = new GetIneligibleDisciplinesForIndividualAction;
        $this->ineligibleDisciplines = $action->execute($this->event, $this->individual);
    }

    public function loadPricingData()
    {
        if (! $this->selectedDiscipline) {
            return;
        }

        $disciplineId = is_numeric($this->selectedDiscipline) ? (int) $this->selectedDiscipline : null;

        $loadPricingDataAction = new LoadEventPricingDataAction;
        $result = $loadPricingDataAction->execute(
            $this->event,
            $disciplineId,
            EvtEventEnrollmentRoleEnum::ATHLETE
        );

        $this->pricingData = collect($result['pricingData']);
        $this->selectedPricingIds = $this->getSelectedPricingIds($result['selectedPricingIds']);

        // Ensure PER_PERSON pricing is always set if available for the event
        if ($this->selectedPricingIds['perPerson'] === null) {
            $perPersonPricing = $this->pricingData->where('price_type', 'PER_PERSON')->first();
            $this->selectedPricingIds['perPerson'] = $perPersonPricing ? $perPersonPricing->id : null;
        }
    }

    private function getSelectedPricingIds(array $pricingIds): array
    {
        $selected = [];

        foreach ($pricingIds as $key => $ids) {
            $selected[$key] = $ids->count() === 1 ? $ids->first() : null;
        }

        return $selected;
    }

    public function getDisciplineAttributes()
    {
        if (empty($this->selectedDiscipline)) {
            return;
        }

        $attributesAndRules = new GetAttributesAndRulesFromDisciplineAction;
        $result = $attributesAndRules->execute($this->selectedDiscipline);

        // Transform the data to match expected structure
        $this->disciplineAttributes = collect($result['attributes'])
            ->map(function ($attribute) {
                return [
                    'id' => $attribute['attribute_data']['id'],
                    'name' => $attribute['attribute_data']['name'],
                    'type' => $attribute['attribute_data']['type'],
                    'required' => $attribute['attribute_data']['required'] ?? false,
                    'default_value' => $attribute['attribute_data']['default_value'] ?? null,
                    'options' => $attribute['attribute_data']['options'] ?? [],
                    'attribute_data' => $attribute['attribute_data'],
                    'rules' => $attribute['rules'] ?? [],
                ];
            })
            ->keyBy('id')
            ->toArray();

        // Initialize attribute values for the individual based on the CURRENT discipline's attributes
        if ($this->individual && ! empty($this->disciplineAttributes)) {
            // Always ensure the individual's key exists, even if empty initially
            if (! isset($this->disciplineAttributeValues[$this->individual->id])) {
                $this->disciplineAttributeValues[$this->individual->id] = [];
            }

            // Create a temporary array for the new default values for the current discipline
            $newDefaultValues = [];
            foreach ($this->disciplineAttributes as $attributeId => $attribute) {
                $newDefaultValues[$attributeId] =
                    $attribute['type'] === 'OUTOFRACE' ? 'no' : ($attribute['default_value'] ?? null);
            }
            // Replace the individual's attribute values entirely with the new set of defaults
            $this->disciplineAttributeValues[$this->individual->id] = $newDefaultValues;
        }

        $this->loadPricingData();
        $this->calculateCurrentDisciplineCost();
    }

    /**
     * Calculate the cost for the *currently selected* discipline based on selected pricing.
     * This is for display before adding the item to the cart.
     */
    private function calculateCurrentDisciplineCost(): void
    {
        if (empty($this->selectedDiscipline) || empty($this->selectedPricingIds)) {
            $this->currentDisciplineCost = 0;

            return;
        }

        $this->currentDisciplineCost = 0;

        // Calculate per person price if applicable
        if ($this->selectedPricingIds['perPerson']) {
            $pricing = Pricing::find($this->selectedPricingIds['perPerson']);
            if ($pricing) {
                $this->currentDisciplineCost += $pricing->price;
            }
        }

        // Add discipline price if applicable
        if ($this->selectedPricingIds['discipline']) {
            $pricing = Pricing::find($this->selectedPricingIds['discipline']);
            if ($pricing && $pricing->discipline_id == $this->selectedDiscipline) {
                $this->currentDisciplineCost += $pricing->price;
            }
        }

        // Add event fee if applicable
        if ($this->selectedPricingIds['eventFee']) {
            $pricing = Pricing::find($this->selectedPricingIds['eventFee']);
            if ($pricing) {
                $this->currentDisciplineCost += $pricing->price;
            }
        }
    }

    /**
     * Reset the state related to the discipline selection form.
     */
    public function resetFormState(): void
    {
        $this->disciplineAttributeValues = [];
        $this->errorMessage = '';
        $this->errorMessages = [];
        $this->selectedPricingIds = [
            'perPerson' => null,
            'discipline' => null,
            'eventFee' => null,
        ];
        $this->totalCost = 0;
        $this->costBreakdown = [];
    }

    /**
     * Process attribute values to extract the actual value, handling different input formats.
     */
    protected function processAttributeValues(array $attributeValues): array
    {
        $processed = [];

        // Handle direct key-value pairs
        foreach ($attributeValues as $attributeId => $value) {
            if (is_array($value) && isset($value['value'])) {
                $processed[$attributeId] = $value['value'];
            } else {
                $processed[$attributeId] = $value;
            }
        }

        return $processed;
    }

    /**
     * Validate the attributes for the currently selected discipline and individual.
     */
    private function validateAttributes()
    {
        if (! isset($this->disciplineAttributeValues[$this->individual->id])) {
            return false;
        }

        $attributeValues = $this->disciplineAttributeValues[$this->individual->id];
        $processedValues = $this->processAttributeValues($attributeValues);

        try {
            $this->validateAttributesAndRules($processedValues, ['attributes' => array_values($this->disciplineAttributes)]);

            return true;
        } catch (ValidationException $e) {
            // Store the error message in both arrays for compatibility
            $this->errorMessage = $e->getMessage();
            $this->errorMessages[] = $e->getMessage();

            Notification::make()
                ->title(__('events.validation_error'))
                ->body($e->getMessage())
                ->danger()
                ->send();

            return false;
        }
    }

    public function render()
    {
        // Update flag based on cart content
        $this->hasEnrollmentItems = ! empty($this->enrollmentItems);

        return view('livewire.evt-events.individual-create-athlete-enrollment', [
            'event' => $this->event,
            'individual' => $this->individual,
            'multiplePerPersonPricing' => ! empty($this->selectedPricingIds['perPerson']) && is_array($this->selectedPricingIds['perPerson']),
            'multipleDisciplinePricing' => ! empty($this->selectedPricingIds['discipline']) && is_array($this->selectedPricingIds['discipline']),
            'multipleEventFeePricing' => ! empty($this->selectedPricingIds['eventFee']) && is_array($this->selectedPricingIds['eventFee']),
            'enrollmentItems' => $this->enrollmentItems, // Pass cart items to view
            'hasEnrollmentItems' => $this->hasEnrollmentItems, // Pass flag to view
            'ineligibleDisciplines' => $this->ineligibleDisciplines,
        ]);
    }

    /**
     * Reset the entire form, including discipline selection and cart.
     * Typically called via listener.
     */
    public function resetForm()
    {
        $this->reset([
            'selectedDiscipline', // Ensure this is reset
            'disciplineAttributes',
            'disciplineAttributeValues',
            'enrollmentItems', // Clear the cart
            'totalCost',
            'costBreakdown',
            'currentDisciplineCost',
            'errorMessage',
            'errorMessages',
            'selectedPricingIds',
        ]);
        $this->selectedDiscipline = null; // << EXPLICITLY SET TO NULL
        $this->hasEnrollmentItems = false;
        $this->attributesKey = uniqid(); // Also reset key on full reset

        // Reload the initial state
        $this->loadConfirmedEnrollments(); // Reload confirmed
        $this->loadEligibleDisciplines(); // Reload eligible disciplines
    }

    /**
     * Load ONLY confirmed/finalized athlete enrollments for this individual and event.
     * This replaces the previous `loadEnrollments` logic for pending items.
     */
    public function loadConfirmedEnrollments()
    {
        $this->confirmedAthleteEnrollments = AthleteEnrollment::where('event_id', $this->event->id)
            ->where('individual_id', $this->individual->id)
            ->whereIn('status_class', [
                // EvtAthleteEnrollmentStatusEnum::REGISTERED->value, // Exclude REGISTERED status
                EvtAthleteEnrollmentStatusEnum::DISCIPLINE_ASSIGNED->value,
                EvtAthleteEnrollmentStatusEnum::PENDING_PAYMENT->value,
                EvtAthleteEnrollmentStatusEnum::PAID->value,
                EvtAthleteEnrollmentStatusEnum::COMPLETED->value,
            ])
            ->whereNotNull('discipline_id') // Confirmed enrollments must have a discipline
            ->with([
                'discipline:id,name',
                'enrollment' => function ($query) {
                    $query->with(['user' => function ($userQuery) {
                        $userQuery->with(['entities:id,name', 'federations:id,name']); // Eager load creator's entities/federations
                    }]);
                },
                'attributes.attribute',
            ])
            ->get();

        // Note: Cost calculation for the cart ($enrollmentItems) is handled separately
        // by calculateEnrollmentItemsCost() when items are added/removed.
        // We don't calculate total cost from confirmed enrollments here.
    }

    /**
     * Calculate the total cost and breakdown based on items currently in the $enrollmentItems cart.
     */
    private function calculateEnrollmentItemsCost()
    {
        $this->totalCost = 0;
        $costDetails = []; // Temporary array to collect details for breakdown

        foreach ($this->enrollmentItems as $item) {
            $itemTotal = 0;
            // Add per-person cost for this item
            if (isset($item['calculated_costs']['per_person']) && $item['calculated_costs']['per_person'] > 0) {
                $price = $item['calculated_costs']['per_person'];
                $itemTotal += $price;
                $costDetails[] = [
                    'description' => 'Per Person Fee', // Use a consistent description
                    'amount' => $price,
                    'type' => 'per_person', // Add type for potential grouping
                ];
            }
            // Add discipline cost for this item
            if (isset($item['calculated_costs']['discipline']) && $item['calculated_costs']['discipline'] > 0) {
                $price = $item['calculated_costs']['discipline'];
                $itemTotal += $price;
                $costDetails[] = [
                    'description' => $item['discipline_name'] ?? 'Discipline Fee',
                    'amount' => $price,
                    'type' => 'discipline',
                ];
            }
            // Add event fee cost for this item
            if (isset($item['calculated_costs']['event_fee']) && $item['calculated_costs']['event_fee'] > 0) {
                $price = $item['calculated_costs']['event_fee'];
                $itemTotal += $price;
                $costDetails[] = [
                    'description' => 'Event Fee', // Use a consistent description
                    'amount' => $price,
                    'type' => 'event_fee',
                ];
            }
            $this->totalCost += $itemTotal;
        }

        // Aggregate costs for the breakdown display (optional, could show per item)
        // Example: Group by description
        $groupedBreakdown = collect($costDetails)->groupBy('description')->map(function ($group) {
            return [
                'description' => $group->first()['description'],
                'amount' => $group->sum('amount'),
            ];
        })->values()->toArray();

        $this->costBreakdown = $groupedBreakdown;
        $this->hasEnrollmentItems = ! empty($this->enrollmentItems); // Update flag
    }

    /**
     * Reset the component's cost state.
     */
    private function resetCosts()
    {
        $this->totalCost = 0;
        $this->costBreakdown = [];
    }

    /**
     * Remove a selected discipline item from the in-memory $enrollmentItems cart.
     * Replaces the old removeEnrollment which deleted DB records.
     */
    public function removeDisciplineFromEnrollmentItems($cartItemId)
    {
        $initialCount = count($this->enrollmentItems);
        // Find the key of the item to remove
        $keyToRemove = null;
        foreach ($this->enrollmentItems as $key => $item) {
            if (isset($item['cart_item_id']) && $item['cart_item_id'] === $cartItemId) {
                $keyToRemove = $key;
                break;
            }
        }

        // Remove the item if found
        if ($keyToRemove !== null) {
            unset($this->enrollmentItems[$keyToRemove]);
            // Re-index array numerically if needed, although not strictly necessary for foreach loops
            $this->enrollmentItems = array_values($this->enrollmentItems);

            // Recalculate costs and refresh eligible disciplines
            $this->calculateEnrollmentItemsCost();
            $this->loadEligibleDisciplines();

            Notification::make()
                ->title(__('events.discipline_removed'))
                ->body(__('events.discipline_removed_from_list'))
                ->info()
                ->send();
        } else {
            Notification::make()
                ->title(__('Error'))
                ->body(__('events.could_not_find_discipline'))
                ->danger()
                ->send();
        }
    }

    /**
     * Validate the selected discipline and attributes, then add it to the
     * in-memory $enrollmentItems cart. Does NOT save to the database.
     * Replaces the old enroll() method.
     */
    public function addDisciplineToEnrollmentItems()
    {
        // Validate if discipline is selected
        if (empty($this->selectedDiscipline)) {
            Notification::make()
                ->title(__('Error'))
                ->body(__('events.please_select_discipline'))
                ->danger()
                ->send();

            return;
        }

        $discipline = Discipline::find($this->selectedDiscipline);
        if (! $discipline) {
            Notification::make()->title(__('Error'))->body(__('events.discipline_not_found'))->danger()->send();

            return;
        }

        // Basic check if already in cart (though loadEligibleDisciplines should prevent this)
        if (collect($this->enrollmentItems)->contains('discipline_id', $this->selectedDiscipline)) {
            Notification::make()->title(__('events.already_added'))->body(__('events.discipline_already_in_list'))->warning()->send();

            return;
        }

        // Check for existing *confirmed* enrollment (using DB check)
        // Note: CheckExistingEventEnrollmentAction might need refinement if it checks statuses we no longer use initially.
        // Let's assume it primarily checks for PAID/PENDING_PAYMENT/COMPLETED for now.
        $checkExistingEnrollment = app(CheckExistingEventEnrollmentAction::class)
            ->execute(
                $this->event,
                $this->individual,
                $this->selectedDiscipline,
                null // null because individual is registering themselves
            );

        if (! $checkExistingEnrollment['can_register']) {
            Notification::make()
                ->title(__('events.registration_not_allowed'))
                ->body($checkExistingEnrollment['message'])
                ->danger()
                ->send();

            return;
        }

        // Validate attributes for the selected discipline
        if (! $this->validateAttributes()) {
            // Notification is sent within validateAttributes
            return;
        }

        // Validate discipline limits (considering confirmed + current cart items)
        if (! $this->validateDisciplineLimits()) {
            Notification::make()
                ->title(__('events.limit_exceeded'))
                ->body($this->errorMessage ?: __('events.discipline_limit_validation_failed'))
                ->danger()
                ->send();

            return;
        }

        // Process attribute values
        $attributeValues = $this->disciplineAttributeValues[$this->individual->id] ?? [];
        $processedAttributes = $this->processAttributeValues($attributeValues);

        // Generate a unique ID for this cart item
        $cartItemId = uniqid('item_', true);

        // Prepare the item data
        $newItem = [
            'cart_item_id' => $cartItemId,
            'discipline_id' => (int) $this->selectedDiscipline,
            'discipline_name' => $discipline->name,
            'attribute_values' => $processedAttributes,
            'selected_pricing_ids' => $this->selectedPricingIds, // Store the selected IDs
            'calculated_costs' => [ // Store pre-calculated costs for this item
                'per_person' => Pricing::find($this->selectedPricingIds['perPerson'])?->price ?? 0,
                'discipline' => Pricing::find($this->selectedPricingIds['discipline'])?->price ?? 0,
                'event_fee' => Pricing::find($this->selectedPricingIds['eventFee'])?->price ?? 0,
            ],
        ];

        // Add item to the cart
        $this->enrollmentItems[] = $newItem;

        // Recalculate total cost based on the updated cart
        $this->calculateEnrollmentItemsCost();

        // Refresh the list of eligible disciplines
        $this->loadEligibleDisciplines();

        // Reset form fields for the next entry (optional, could keep price selection)
        $this->resetFormState();

        // << EXPLICITLY reset selected discipline dropdown to placeholder >>
        $this->selectedDiscipline = null;
        // Trigger the update cycle for selectedDiscipline to clear attributes/pricing display
        $this->updatedSelectedDiscipline(null);

        Notification::make()
            ->title(__('events.discipline_added'))
            ->body(__('events.discipline_added_to_list'))
            ->success()
            ->send();
    }

    /**
     * Finalize the registration by processing all items in the $enrollmentItems cart.
     * This method now orchestrates calls to domain actions to create records.
     * It uses firstOrCreate for the parent Enrollment and passes new AthleteEnrollments
     * to the Finalize action.
     */
    public function finalizeRegistrations()
    {
        if (empty($this->enrollmentItems)) {
            Notification::make()
                ->title(__('events.list_empty'))
                ->body(__('events.add_disciplines_before_finalizing'))
                ->warning()
                ->send();

            return;
        }

        $this->isFinalizing = true;

        try {
            DB::beginTransaction();

            // 1. Get or Create the parent Enrollment using the dedicated action
            $getOrCreateEnrollmentAction = app(GetOrCreateIndividualEnrollmentAction::class);
            /** @var Enrollment $enrollment */
            $enrollment = $getOrCreateEnrollmentAction->execute(
                $this->event,
                $this->individual,
                Auth::id() // Pass the user ID string instead of the User object
            );

            // Activity logging for this step is handled *inside* the action.
            // 2. Prepare collection for newly created athlete enrollments
            $newAthleteEnrollments = collect();

            // 3. Loop through cart items and create AthleteEnrollment records
            foreach ($this->enrollmentItems as $item) {
                // Determine initial status - refinement happens in Finalize action
                // Use a simple check based on whether the parent has cost OR new items add cost
                $itemCost = ($item['calculated_costs']['per_person'] ?? 0) +
                    ($item['calculated_costs']['discipline'] ?? 0) +
                    ($item['calculated_costs']['event_fee'] ?? 0);

                // Restore original calculation for initialStatus
                $initialStatus = ($itemCost > 0 || $enrollment->total_price > 0)
                    ? EvtAthleteEnrollmentStatusEnum::PENDING_PAYMENT
                    : EvtAthleteEnrollmentStatusEnum::DISCIPLINE_ASSIGNED;

                // Fetch Attribute Names for Logging
                $attributeIds = array_keys($item['attribute_values']);
                $attributesData = \Domain\EvtEvents\Models\Attribute::whereIn('id', $attributeIds)->pluck('name', 'id');
                $attributeDetails = collect($item['attribute_values'])->mapWithKeys(function ($value, $id) use ($attributesData) {
                    $name = $attributesData->get($id, "Attribute ID {$id}");

                    return [$name => $value];
                })->toArray();

                // Call the action to create the AthleteEnrollment record
                $createAthleteEnrollmentAction = app(CreateIndividualAthleteEnrollmentAction::class);
                /** @var AthleteEnrollment $athleteEnrollment */
                $athleteEnrollment = $createAthleteEnrollmentAction->execute(
                    $this->event,
                    $this->individual->id,
                    $enrollment, // Pass the retrieved/created parent enrollment
                    $item['selected_pricing_ids']['perPerson'],
                    $item['selected_pricing_ids']['discipline'],
                    $item['selected_pricing_ids']['eventFee'],
                    $item['discipline_id'],
                    $item['attribute_values'],
                    $item['calculated_costs'],
                    $initialStatus
                );

                // Log Activity for *each* created athlete enrollment
                activity('enrollment_process')
                    ->causedBy(Auth::user())
                    ->performedOn($athleteEnrollment)
                    ->withProperties([
                        'event_id' => $this->event->id,
                        'individual_id' => $this->individual->id,
                        'discipline_id' => $item['discipline_id'],
                        'discipline_name' => $item['discipline_name'],
                        'parent_enrollment_id' => $enrollment->id,
                        'initial_status' => $initialStatus->value,
                        'item_cost' => $item['calculated_costs'],
                        'attribute_details' => $attributeDetails,
                    ])
                    ->log('Individual added discipline (' . $item['discipline_name'] . ') to enrollment.');

                $newAthleteEnrollments->push($athleteEnrollment);
            }

            // 4. Call the Finalize action (This action still needs modification)
            $finalizeAction = app(FinalizeIndividualEnrollmentAction::class);
            $result = $finalizeAction->execute(
                $this->event,
                $this->individual,
                $enrollment, // Pass the retrieved/created parent enrollment
                $newAthleteEnrollments, // Pass the collection of NEW athlete enrollments
                Auth::user()
            );

            // 5. Commit Transaction
            DB::commit();

            // 6. Log overall finalization attempt
            activity('enrollment_process')
                ->causedBy(Auth::user())
                ->performedOn($enrollment)
                ->withProperties([
                    'event_id' => $this->event->id,
                    'individual_id' => $this->individual->id,
                    'newly_added_discipline_count' => $newAthleteEnrollments->count(),
                    'finalization_result_reported_success' => $result['success'] ?? null,
                    'finalization_result_message' => $result['message'] ?? 'No message',
                    'finalization_result_document_id' => $result['document_id'] ?? null,
                    'enrollment_payment_status_after_finalize' => $enrollment->fresh()->payment_status,
                    'enrollment_total_cost_after_finalize' => $enrollment->fresh()->total_cost,
                ])
                ->log('Finalize action executed for individual registration. Added ' . $newAthleteEnrollments->count() . ' new discipline(s).');

            // 7. Handle Success/Notification based on the result from finalizeAction
            if ($result['success']) {
                // Use the document ID provided by the finalize action result
                $documentId = $result['document_id'] ?? null;
                $finalCost = $enrollment->fresh()->total_cost; // Get updated cost

                if ($finalCost > 0 && $documentId) {
                    Notification::make()
                        ->title(__('events.registration_updated_payment_required'))
                        ->body($result['message'])
                        ->actions([
                            NotificationAction::make('view_document')
                                ->label(__('events.view_payment_document'))
                                ->url(route('individual.document.show', $documentId))
                                ->button(),
                        ])
                        ->success()
                        ->send();
                } elseif ($finalCost == 0) {
                    Notification::make()
                        ->title(__('events.registration_successful'))
                        ->body($result['message'] ?: __('events.registration_complete_no_payment'))
                        ->success()
                        ->send();
                } else {
                    Notification::make()
                        ->title(__('events.registration_updated'))
                        ->body($result['message'] ?: __('events.registration_has_been_updated'))
                        ->success()
                        ->send();
                }

                // Reset component state fully
                $this->resetForm(); // Resets cart, selection, reloads confirmed/eligible

            } else {
                // Handle failure reported by finalizeAction
                Notification::make()
                    ->title(__('events.finalization_problem'))
                    ->body($result['message'] ?: __('events.issue_during_finalization'))
                    ->warning()
                    ->send();
                // Still reset and reload to show current state
                $this->resetForm();
            }
        } catch (ValidationException $e) {
            DB::rollBack();
            Log::error('Validation failed during finalization', [
                'error' => $e->getMessage(),
                'individual_id' => $this->individual->id,
                'enrollment_items' => $this->enrollmentItems,
            ]);
            activity('enrollment_error') // Use a different log channel/name for errors
                ->causedBy(Auth::user())
                ->withProperties([
                    'event_id' => $this->event->id,
                    'individual_id' => $this->individual->id,
                    'error_type' => 'ValidationException',
                    'error_message' => $e->getMessage(),
                    'enrollment_items' => $this->enrollmentItems, // Log context
                ])
                ->log('Validation error during individual enrollment finalization.');
            Notification::make()
                ->title(__('events.validation_error'))
                ->body($e->getMessage())
                ->danger()
                ->send();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to finalize registration', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(), // Keep detailed trace for server logs
                'individual_id' => $this->individual->id,
                'enrollment_items' => $this->enrollmentItems,
            ]);
            activity('enrollment_error') // Log general exceptions too
                ->causedBy(Auth::user())
                ->withProperties([
                    'event_id' => $this->event->id,
                    'individual_id' => $this->individual->id,
                    'error_type' => get_class($e),
                    'error_message' => $e->getMessage(),
                    // Avoid logging full trace here unless necessary, keep it in server logs
                    'enrollment_items' => $this->enrollmentItems,
                ])
                ->log('Exception during individual enrollment finalization.');
            Notification::make()
                ->title(__('Error'))
                ->body(__('events.unexpected_error_finalizing'))
                ->danger()
                ->send();
        } finally {
            $this->isFinalizing = false;
        }
    }

    /**
     * Ensure the athlete does not exceed the competition‑wide
     * max disciplines per athlete limit.
     *
     * – Counts:
     *     • confirmed DB enrollments (in‑race only)
     *     • items already in the in‑memory cart
     *     • the discipline currently being added
     * – Excludes:
     *     • relay / team disciplines
     *     • disciplines where the athlete chose “Out of Race” (value === 'yes')
     *
     * @return bool true  → within the limit, discipline may be added
     *              false → limit would be exceeded; $this->errorMessage is set
     */
    protected function validateDisciplineLimits(): bool
    {
        // No discipline selected → nothing to validate
        if (empty($this->selectedDiscipline)) {
            return true;
        }

        $limit = (int) ($this->event->competition->max_disciplines_per_athlete ?? 0);
        if ($limit < 1) {                         // unlimited
            return true;
        }

        $outOfRaceAction = app(
            \Domain\EvtEvents\Actions\GetDisciplineOutOfRaceAttributeAction::class
        );

        /* --------------------------------------------------------------
     * 1. Confirmed (DB) enrollments – count only “in‑race” disciplines
     * -------------------------------------------------------------- */
        $existingInRace = AthleteEnrollment::query()
            ->where('event_id', $this->event->id)
            ->where('individual_id', $this->individual->id)
            ->whereNotNull('discipline_id')
            ->with(['discipline', 'attributes'])
            ->get()
            ->filter(function (AthleteEnrollment $enrollment) use ($outOfRaceAction) {
                // Skip relay / team
                if (in_array($enrollment->discipline->enrollment_type, ['relay', 'team'])) {
                    return false;
                }

                $attr = $outOfRaceAction->execute($enrollment->discipline);
                if (! $attr) {
                    return true;                              // discipline has no OUTOFRACE attr ⇒ counts
                }

                $value = $enrollment->attributes
                    ->firstWhere('attribute_id', $attr->id)
                    ->value ?? 'no';

                return $value !== 'yes';                      // count only if NOT out‑of‑race
            })
            ->count();

        /* --------------------------------------------------------------
     * 2. Items already in the cart
     * -------------------------------------------------------------- */
        $cartInRace = collect($this->enrollmentItems)
            ->filter(function (array $item) use ($outOfRaceAction) {
                $discipline = Discipline::find($item['discipline_id']);
                if (! $discipline || in_array($discipline->enrollment_type, ['relay', 'team'])) {
                    return false;
                }

                $attr = $outOfRaceAction->execute($discipline);
                if (! $attr) {
                    return true;
                }

                $value = $item['attribute_values'][$attr->id] ?? 'no';

                return $value !== 'yes';
            })
            ->count();

        /* --------------------------------------------------------------
     * 3. Discipline currently being added
     * -------------------------------------------------------------- */
        $currentInRace = 0;
        $discipline = Discipline::find($this->selectedDiscipline);

        if ($discipline && ! in_array($discipline->enrollment_type, ['relay', 'team'])) {
            $attr = $outOfRaceAction->execute($discipline);

            if (! $attr) {
                $currentInRace = 1;                            // no OUTOFRACE attr ⇒ counts
            } else {
                $value = $this->disciplineAttributeValues[$this->individual->id][$attr->id] ?? 'no';
                $currentInRace = $value !== 'yes' ? 1 : 0;     // counts only if NOT out‑of‑race
            }
        }

        /* --------------------------------------------------------------
     * 4. Final tally and decision
     * -------------------------------------------------------------- */
        $totalAfterAdd = $existingInRace + $cartInRace + $currentInRace;

        if ($totalAfterAdd > $limit) {
            $this->errorMessage = __('events.discipline_limit_exceeded', ['limit' => $limit]);
            $this->errorMessages[] = $this->errorMessage;

            return false;
        }

        return true;
    }

    /** Helper to create temporary AthleteEnrollment for validation */
    private function createTemporaryEnrollment(int $disciplineId, array $attributeValues): AthleteEnrollment
    {
        // Find the discipline model - necessary for the out-of-race check
        $disciplineModel = Discipline::find($disciplineId);

        $enrollment = new AthleteEnrollment([
            'discipline_id' => $disciplineId,
        ]);
        // Set the discipline relationship if found
        if ($disciplineModel) {
            $enrollment->setRelation('discipline', $disciplineModel);
        }

        // Simulate the attributes relationship
        $attributes = collect($attributeValues ?? [])->map(function ($value, $id) {
            $attrModel = new \Domain\EvtEvents\Models\AthleteEnrollmentAttributes([
                'attribute_id' => $id,
                'value' => is_array($value) ? ($value['value'] ?? null) : $value, // Handle potential nested value
            ]);

            // We don't have the full Attribute model here, but attribute_id is enough for filtering
            return $attrModel;
        });

        $enrollment->setRelation('attributes', $attributes);

        return $enrollment;
    }

    /**
     * Get the current count of enrollments for a specific individual in a event, optionally filtered by discipline.
     * Needed because we cannot modify the action to return this count directly.
     */
    private function getDatabaseEnrollmentCount(?Discipline $discipline, int $eventId, Individual $individual): int
    {
        // Base query matching the action's logic if Discipline is null, dont use it
        $query = AthleteEnrollment::where('event_id', $eventId)
            ->where('individual_id', $individual->id);

        if ($discipline) {
            $query->where('discipline_id', $discipline->id);
        }

        // Default count for individual disciplines
        return $query->count();
    }

    /**
     * Initialize attribute values when a discipline is selected.
     */
    protected function initializeAttributeValues(): void
    {
        if (! empty($this->disciplineAttributes) && $this->individual) {
            if (! isset($this->disciplineAttributeValues[$this->individual->id])) {
                $this->disciplineAttributeValues[$this->individual->id] = [];
                foreach ($this->disciplineAttributes as $attributeId => $attribute) {
                    $this->disciplineAttributeValues[$this->individual->id][$attributeId] =
                        $attribute['type'] === 'OUTOFRACE' ? 'no' : ($attribute['default_value'] ?? null);
                }
            }
        }
    }

    // ... (getDisciplineAttributeValues remains useful for getting current form values) ...

    /**
     * Recalculate the displayed cost for the currently selected discipline when pricing changes.
     */
    public function updatedSelectedPricingIds($value, $key)
    {
        $this->calculateCurrentDisciplineCost();
    }

    /**
     * Handle updates when the selected discipline changes.
     * Resets form state, loads attributes and pricing for the new selection.
     */
    public function updatedSelectedDiscipline($value): void
    {
        // $value will be null if reset or becomes invalid
        $this->resetFormState(); // Clear previous attribute values, costs etc.
        $this->selectedDiscipline = $value; // Update the property
        $this->attributesKey = uniqid(); // Change the key to force DOM refresh

        if ($value) {
            // Only load data if a valid discipline ID is selected
            $this->loadPricingData();
            $this->getDisciplineAttributes();
            $this->calculateCurrentDisciplineCost();
        } else {
            // Ensure everything related to a discipline is cleared if value is null
            $this->reset(['disciplineAttributes', 'disciplineAttributeValues', 'currentDisciplineCost', 'pricingData', 'selectedPricingIds']);
        }
    }

    /**
     * Determine if the "Finalize Registrations" button should be shown.
     * Based on whether the $enrollmentItems cart has items.
     */
    protected function shouldShowFinalizeButton(): bool
    {
        // Use the flag which is updated when cart changes
        return $this->hasEnrollmentItems && ! $this->isFinalizing;
    }

    /**
     * Initiate the confirmation process for removing a confirmed enrollment.
     */
    public function requestRemoveConfirmedEnrollment(int $athleteEnrollmentId): void
    {
        $this->enrollmentToRemoveId = $athleteEnrollmentId;
        $this->showRemoveConfirmationModal = true;
    }

    /**
     * Cancel the removal process.
     */
    public function cancelRemoveConfirmedEnrollment(): void
    {
        $this->reset(['enrollmentToRemoveId', 'showRemoveConfirmationModal']);
    }

    /**
     * Execute the removal of a confirmed AthleteEnrollment record.
     */
    public function removeConfirmedEnrollment(): void
    {
        if (! $this->enrollmentToRemoveId) {
            return;
        }

        try {
            $action = app(RemoveIndividualAthleteEnrollmentAction::class);
            $result = $action->execute($this->enrollmentToRemoveId, Auth::user());

            if ($result['success']) {
                Notification::make()
                    ->title(__('events.enrollment_removed'))
                    ->body($result['message'])
                    ->success()
                    ->send();

                // Refresh component state
                $this->loadConfirmedEnrollments(); // Reload confirmed list
                $this->loadEligibleDisciplines();  // Refresh disciplines available for adding

            } else {
                Notification::make()
                    ->title(__('events.removal_failed'))
                    ->body($result['message'] ?? __('events.could_not_remove_enrollment'))
                    ->danger()
                    ->send();
            }
        } catch (Exception $e) {
            Log::error('Error removing athlete enrollment', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'athlete_enrollment_id' => $this->enrollmentToRemoveId,
                'user_id' => Auth::id(),
            ]);
            Notification::make()
                ->title(__('Error'))
                ->body(__('events.unexpected_error_removing', ['error' => $e->getMessage()]))
                ->danger()
                ->send();
        } finally {
            $this->cancelRemoveConfirmedEnrollment(); // Close modal regardless of outcome
        }
    }
}
