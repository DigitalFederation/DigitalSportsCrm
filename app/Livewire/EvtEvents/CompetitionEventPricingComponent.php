<?php

namespace App\Livewire\EvtEvents;

use Domain\EvtEvents\Actions\AddOrUpdatePricingAction;
use Domain\EvtEvents\DataTransferObjects\PricingData;
use Domain\EvtEvents\Models\Competition;
use Domain\EvtEvents\Models\Pricing;
use Exception;
use Livewire\Component;

class CompetitionEventPricingComponent extends Component
{
    public $competition_id;
    public $event_id;
    public $disciplines;
    public $pricingTiers = []; // Array of arrays, key is discipline_id
    public $errorMessage = '';
    protected $listeners = ['addPricingTier', 'pricingOptionChanged'];
    public $pricingTierIds = [];
    public $pricingOptions = [];
    public $pricingOptionsSelected;
    public $disablePricingOptions = false;

    public function mount($competition_id, $event_id)
    {
        $this->competition_id = $competition_id;
        $this->event_id = $event_id;

        $existingPricingTier = Pricing::where('event_id', $event_id)->first();
        if ($existingPricingTier) {
            $this->pricingOptionsSelected = $existingPricingTier->pricing_option;
            $this->disablePricingOptions = true;
        }

        // Fetch disciplines associated with the competition
        $competition = Competition::with('disciplines')->find($this->competition_id);

        // Using pluck to create an array with discipline names and their IDs
        $this->disciplines = $competition->disciplines->pluck('name', 'id');

        $this->pricingOptions = [
            'total_price' => 'Total Price for Event',
            'price_per_discipline' => 'Price per Discipline',
            'price_per_person_unique' => 'Price for Event per Unique Person',
        ];

        $this->loadPricingTiers();
    }

    public function updatedPricingOptionsSelected($value)
    {
        $this->dispatch('pricingOptionChanged', $value);
        $this->pricingOptionsSelected = $value;
        $this->pricingTiers = [];
        $this->render();

        if (
            $value == 'price_per_discipline' ||
            $value == 'price_per_person_unique' ||
            $value == 'total_price'
        ) {
            $this->addPricingTier();
        }
    }

    public function pricingOptionChanged($value)
    {
        $this->pricingOptionsSelected = $value;
    }

    private function loadPricingTiers()
    {
        $existingPricingTiers = Pricing::where('event_id', $this->event_id)->get();

        if ($existingPricingTiers->isEmpty()) {
            // $this->addPricingTier();
            $this->pricingTierIds = [];
            if ($this->pricingOptionsSelected == 'total_price' || $this->pricingOptionsSelected == 'price_per_person_unique') {
                $this->pricingTiers[] = $this->emptyTotalPriceTier();
            }
        } else {
            foreach ($existingPricingTiers as $pricing) {
                $this->pricingTiers[] = [
                    'discipline_id' => $pricing->discipline_id,
                    'start_date' => $pricing->start_date->format('Y-m-d'),
                    'end_date' => $pricing->end_date->format('Y-m-d'),
                    'price' => $pricing->price,
                    'price_type' => $pricing->price_type,
                ];
                $this->pricingTierIds[] = $pricing->id;
            }
        }
    }

    private function emptyPricingTier()
    {
        return [
            'discipline_id' => '',
            'start_date' => '',
            'end_date' => '',
            'price' => '',
            'price_type' => 'flat_fee',
        ];
    }

    private function emptyTotalPriceTier()
    {
        return [
            'start_date' => '',
            'end_date' => '',
            'price' => '',
            'price_type' => 'flat_fee',
        ];
    }

    public function addPricingTier()
    {
        // Prevent adding pricing tiers if there are no disciplines and the pricing option is 'price_per_discipline'
        if ($this->pricingOptionsSelected == 'price_per_discipline' && $this->disciplines->isEmpty()) {
            $this->errorMessage = 'Attention: Cannot add pricing tiers without disciplines.';

            return;
        }

        if ($this->pricingOptionsSelected == 'price_per_discipline') {
            $this->pricingTiers[] = $this->emptyPricingTier();
        } elseif ($this->pricingOptionsSelected == 'total_price' || $this->pricingOptionsSelected == 'price_per_person_unique') {
            $this->pricingTiers[] = $this->emptyTotalPriceTier();
        }
    }

    public function removePricingTier($index)
    {
        if (isset($this->pricingTierIds[$index])) {
            // Delete the record from the database
            $pricingId = $this->pricingTierIds[$index];
            $pricing = Pricing::find($pricingId);
            if ($pricing && ! $pricing->isReferencedInEnrollments()) {
                $pricing->delete();
                // Remove from the component's state
                unset($this->pricingTiers[$index]);
                unset($this->pricingTierIds[$index]);
                $this->pricingTiers = array_values($this->pricingTiers);
                $this->pricingTierIds = array_values($this->pricingTierIds);
            } else {
                $this->errorMessage = 'Attention: Pricing tier cannot be deleted because it is referenced in enrollments.';
            }
        }

        if (empty($this->pricingTiers)) {
            $this->disablePricingOptions = false;
        }
    }

    public function savePricing(AddOrUpdatePricingAction $action)
    {
        $this->errorMessage = ''; // Reset the error message
        $this->resetErrorBag(); // Reset error messages

        // Additional validation logic for 'price_per_discipline' option
        if ($this->pricingOptionsSelected == 'price_per_discipline' && $this->disciplines->isEmpty()) {
            $this->errorMessage = 'Attention: Cannot save pricing tiers without disciplines.';

            return;
        }

        foreach ($this->pricingTiers as $index => $tier) {

            if ($this->pricingOptionsSelected == 'total_price' || $this->pricingOptionsSelected == 'price_per_person_unique') {
                $tier['discipline_id'] = null;
            }
            $existingPricing = Pricing::where('event_id', $this->event_id)
                ->where('discipline_id', $tier['discipline_id'])
                ->where('start_date', '<=', $tier['end_date'])
                ->where('end_date', '>=', $tier['start_date'])
                ->first();

            if ($existingPricing) {
                $this->addError("pricingTiers.{$index}", 'A pricing tier already exists within the selected date range.');
                $this->errorMessage = 'Attention: A pricing tier already exists within the selected date range.';

                continue;
            }

            $validationRules = [
                "pricingTiers.{$index}.discipline_id" => 'required|exists:evt_disciplines,id',
                "pricingTiers.{$index}.start_date" => 'required|date',
                "pricingTiers.{$index}.end_date" => 'required|date|after_or_equal:pricingTiers.' . $index . '.start_date',
                "pricingTiers.{$index}.price" => 'required|numeric|min:0',
                "pricingTiers.{$index}.price_type" => 'required|in:per_person,flat_fee',
            ];
            $customMessages = [
                "pricingTiers.{$index}.discipline_id.required" => 'Please select a discipline for pricing tier ' . ($index + 1),
                "pricingTiers.{$index}.start_date.required" => 'Start date is required for pricing tier ' . ($index + 1),
                "pricingTiers.{$index}.end_date.required" => 'End date is required for pricing tier ' . ($index + 1),
                "pricingTiers.{$index}.end_date.after_or_equal" => 'End date must be after or equal to the start date for pricing tier ' . ($index + 1),
                "pricingTiers.{$index}.price.required" => 'Price is required for pricing tier ' . ($index + 1),
                "pricingTiers.{$index}.price_type.required" => 'Please select a pricing type for pricing tier ' . ($index + 1),
            ];

            if ($this->pricingOptionsSelected == 'total_price' || $this->pricingOptionsSelected == 'price_per_person_unique') {
                unset($validationRules["pricingTiers.{$index}.discipline_id"]);
            }

            // If its price_per_person_unique then we need to choose the proper price_type by default
            if ($this->pricingOptionsSelected == 'price_per_person_unique') {
                $tier['price_type'] = 'per_person';
            }

            $this->validate($validationRules, $customMessages);

            try {
                $pricingDataArray = [
                    'event_id' => $this->event_id,
                    'start_date' => $tier['start_date'],
                    'end_date' => $tier['end_date'],
                    'price' => $tier['price'],
                    'price_type' => $tier['price_type'],
                    'target_group' => 'athlete',
                    'is_active' => true,
                    'pricing_option' => $this->pricingOptionsSelected,
                ];
                // Add discipline_id only for price_per_discipline option
                if ($this->pricingOptionsSelected == 'price_per_discipline') {
                    $pricingDataArray['discipline_id'] = $tier['discipline_id'];
                }
                $pricingData = new PricingData($pricingDataArray);
                $action->execute($pricingData);
            } catch (Exception $e) {
                \Log::error('Error saving pricing: ' . $e->getMessage());
                $this->addError("pricingTiers.{$index}", $e->getMessage());
                $this->errorMessage = "Attention: Error saving pricing: {$e->getMessage()}";
            }
        }

        if (empty($this->getErrorBag()->all())) {
            $this->errorMessage = 'Success: Pricing tiers saved successfully!';
        }
    }

    public function render()
    {
        return view('livewire.evt-events.competition-event-pricing-component');
    }
}
