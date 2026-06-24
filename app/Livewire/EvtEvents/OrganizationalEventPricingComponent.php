<?php

namespace App\Livewire\EvtEvents;

use App\Enums\EvtEventEnrollmentRoleEnum;
use App\Enums\EvtEventFeeTypeEnum;
use Domain\EvtEvents\Actions\AddOrUpdatePricingAction;
use Domain\EvtEvents\DataTransferObjects\PricingData;
use Domain\EvtEvents\Models\Pricing;
use Exception;
use Livewire\Component;

class OrganizationalEventPricingComponent extends Component
{
    public $pricingTiers = [];
    public $event_id;
    public $event;
    public $disciplines = [];
    public $roles = [];
    public $errorMessage = '';
    public $successMessage = '';
    public $showModal = false;
    public $pricingTierIds = [];

    protected $listeners = ['addPricingTier'];

    public function mount($event = null)
    {
        if ($event) {
            $this->setEventId($event->id);
            $this->event = $event;
            $this->fetchDisciplines();
            $this->roles = EvtEventEnrollmentRoleEnum::toArray();
        }

    }

    private function fetchDisciplines()
    {
        if ($this->event && $this->event->competition) {
            $disciplineTemplate = $this->event->competition->disciplineTemplate;
            if ($disciplineTemplate) {
                $this->disciplines = $disciplineTemplate->disciplines()->pluck('evt_disciplines.name', 'evt_disciplines.id')->toArray();
            } else {
                $this->disciplines = [];
            }
        } else {
            $this->disciplines = [];
        }

    }

    private function fetchPricingTiers()
    {

        $existingPricingTiers = Pricing::where('event_id', $this->event_id)->get();

        $this->pricingTiers = $existingPricingTiers->isEmpty() ? [$this->emptyPricingTier()] : $existingPricingTiers->map(function ($pricing) {
            return [
                'id' => $pricing->id,
                'start_date' => $pricing->start_date->format('Y-m-d'),
                'end_date' => $pricing->end_date->format('Y-m-d'),
                'price' => $pricing->price,
                'price_type' => $pricing->price_type,
                'description' => $pricing->description ?? '',
                'discipline_id' => $pricing->discipline_id,
                'enrollment_role' => $pricing->enrollment_role,
            ];
        })->toArray();

        $this->pricingTierIds = $existingPricingTiers->pluck('id')->toArray();
    }

    public function setEventId($event_id)
    {
        $this->event_id = $event_id;
        // Fetch existing pricing tiers from the database now that we have the event_id
        $this->fetchPricingTiers();
    }

    private function emptyPricingTier()
    {
        return [
            'start_date' => '',
            'end_date' => '',
            'price' => '',
            'price_type' => EvtEventFeeTypeEnum::PER_PERSON->value,
            'description' => '',
            'discipline_id' => '',
            'enrollment_role' => EvtEventEnrollmentRoleEnum::ATHLETE->value,
        ];
    }

    public function addPricingTier()
    {
        $this->pricingTiers[] = $this->emptyPricingTier();
    }

    public function removePricingTier($index)
    {
        if (isset($this->pricingTierIds[$index])) {
            // Delete the record from the database
            $pricingId = $this->pricingTierIds[$index];
            Pricing::find($pricingId)->delete();
        }

        // Remove from the component's state
        unset($this->pricingTiers[$index]);
        unset($this->pricingTierIds[$index]);
        $this->pricingTiers = array_values($this->pricingTiers);
        $this->pricingTierIds = array_values($this->pricingTierIds);
    }

    public function savePricing(AddOrUpdatePricingAction $action)
    {

        $this->resetErrorBag(); // Reset errors
        $this->errorMessage = ''; // Reset the error message

        $this->validate([
            'pricingTiers.*.price_type' => 'required|string',
            'pricingTiers.*.start_date' => 'required|date',
            'pricingTiers.*.end_date' => 'required|date|after_or_equal:pricingTiers.*.start_date',
            'pricingTiers.*.price' => 'required|numeric|min:0',
            'pricingTiers.*.description' => 'nullable|string',
            'pricingTiers.*.discipline_id' => 'nullable|exists:evt_disciplines,id',
            'pricingTiers.*.enrollment_role' => 'required|string',
        ], [
            'pricingTiers.*.price_type.required' => 'Price type is required.',
            'pricingTiers.*.start_date.required' => 'Start date is required.',
            'pricingTiers.*.start_date.date' => 'Start date must be a valid date.',
            'pricingTiers.*.end_date.required' => 'End date is required.',
            'pricingTiers.*.end_date.date' => 'End date must be a valid date.',
            'pricingTiers.*.end_date.after_or_equal' => 'End date must be after or equal to the start date.',
            'pricingTiers.*.price.required' => 'Price is required.',
            'pricingTiers.*.price.numeric' => 'Price must be a number.',
            'pricingTiers.*.price.min' => 'Price must be at least 0.',
            'pricingTiers.*.discipline_id.exists' => 'Selected discipline does not exist.',
            'pricingTiers.*.enrollment_role.required' => 'Enrollment role is required.', // Updated validation message
            'pricingTiers.*.enrollment_role.string' => 'Enrollment role must be a string.',
        ]);

        foreach ($this->pricingTiers as $index => $tier) {

            $pricingData = new PricingData([
                'id' => $tier['id'] ?? null,
                'event_id' => $this->event_id,
                'discipline_id' => ! empty($tier['discipline_id']) ? $tier['discipline_id'] : null, // Null for organizational events
                'price_type' => $tier['price_type'],
                'target_group' => 'organization', // Target group for this pricing tier
                'start_date' => $tier['start_date'],
                'end_date' => $tier['end_date'],
                'price' => $tier['price'],
                'is_active' => true,
                'pricing_option' => null,
                'description' => $tier['description'],
                'enrollment_role' => $tier['enrollment_role'],
            ]);

            try {
                $action->execute($pricingData);
            } catch (Exception $e) {
                // Handle exception (e.g., flash message to session)
                $this->errorMessage = "Error saving pricing: {$e->getMessage()}";

                return;
            }

        }

        // Update the event fee type in the event model
        $this->successMessage = 'Pricing tiers saved successfully.';
        $this->dispatch('closeModalAndReloadPage');
    }

    public function render()
    {
        return view('livewire.evt-events.organizational-event-pricing-component', [
            'disciplines' => $this->disciplines,
            'roles' => $this->roles,
        ]);
    }
}
