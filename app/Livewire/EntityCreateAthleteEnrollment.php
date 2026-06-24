<?php

namespace App\Livewire;

use Domain\EvtEvents\Actions\CreateAthleteEnrollmentAction;
use Domain\EvtEvents\Actions\CreateEnrollmentAction;
use Domain\EvtEvents\Actions\CreateEnrollmentPaymentDocumentAction;
use Domain\EvtEvents\Actions\GetAttributesAndRulesFromDisciplineAction;
use Domain\EvtEvents\Actions\GetDisciplinesFromEventAction;
use Domain\EvtEvents\Actions\GetEligibleEntityAthletesAction;
use Domain\EvtEvents\Models\Discipline;
use Domain\EvtEvents\Models\Pricing;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Mockery\Exception;

class EntityCreateAthleteEnrollment extends Component
{
    use WithPagination;

    public $event;

    public $federation;
    public $entity;
    public $selectedIndividuals = [];

    public $selectedIndividualsByDiscipline = [];

    public $selectedIndividualIds = [];

    public $disciplines = [];

    public $disciplineModels;

    public $selectedDiscipline;

    public $disciplineAttributes = [];

    public $globalAttributes = [];

    public $individualSearchTerm;

    public $individualSearchTermValue = '';

    public $enrollmentSummary = [];

    public $inputFields = [];

    public $totalCost = 0;
    public $showConfirmation = false;

    public $pricingData = []; // To store pricing IDs

    public function mount()
    {
        $this->getDisciplinesFromEvent();
        // Initialize the array for each discipline
        foreach ($this->disciplines as $discipline) {
            $this->selectedIndividualsByDiscipline[$discipline->id] = [];
        }
    }

    public function doShowConfirmation()
    {
        if (! $this->showConfirmation) {
            $this->showConfirmation = true;
        }
    }

    public function getDisciplinesFromEvent()
    {
        $getDisciplines = new GetDisciplinesFromEventAction;
        $this->disciplines = $getDisciplines->execute($this->event);
        // Store models
        $this->disciplineModels = $this->disciplines;
    }

    public function getDisciplineAttributes()
    {
        $attributesAndRules = new GetAttributesAndRulesFromDisciplineAction;
        $allDisciplineAttributes = $attributesAndRules->execute($this->selectedDiscipline);
    }

    public function getAllEligibleAthletes()
    {
        $disciplineId = $this->selectedDiscipline ? (int) $this->selectedDiscipline : null;

        $query = app(GetEligibleEntityAthletesAction::class)->execute(
            $this->event->id,
            $this->entity->id,
            $disciplineId
        );

        // Exclude athletes already enrolled for this event (any discipline)
        $query->whereDoesntHave('athleteEnrollments', function (Builder $q) {
            $q->where('event_id', $this->event->id);
        });

        // Apply search term filter
        $query->when(! empty($this->individualSearchTermValue), function (Builder $q) {
            $q->where(function (Builder $q) {
                $q->where('name', 'like', '%' . $this->individualSearchTermValue . '%')
                    ->orWhere('surname', 'like', '%' . $this->individualSearchTermValue . '%')
                    ->orWhere('member_code', 'like', '%' . $this->individualSearchTermValue . '%');
            });
        });

        return $query->paginate();
    }

    public function findIndividuals()
    {
        // At least 3 characters to search
        if (strlen($this->individualSearchTermValue) < 3) {
            return;
        }

        $this->getAllEligibleAthletes();
    }

    public function addIndividualToSelection($individualId)
    {
        if (! in_array($individualId, $this->selectedIndividualsByDiscipline[$this->selectedDiscipline])) {
            $this->selectedIndividualsByDiscipline[$this->selectedDiscipline][] = $individualId;
            // Update selected individuals for current discipline
            $this->updateSelectedIndividuals();
        }
    }

    public function removeIndividualFromSelection($individualId)
    {
        if (($key = array_search($individualId, $this->selectedIndividualsByDiscipline[$this->selectedDiscipline])) !== false) {
            unset($this->selectedIndividualsByDiscipline[$this->selectedDiscipline][$key]);
            // Update selected individuals for current discipline
            $this->updateSelectedIndividuals();
        }
    }

    private function updateSelectedIndividuals()
    {
        $this->selectedIndividuals = Individual::whereIn('id', $this->selectedIndividualsByDiscipline[$this->selectedDiscipline])->get();
        // Dynamically create properties after updating selected individuals
        $this->initializeDynamicProperties();

        $this->calculateTotalCost();
    }

    public function updateSelectedDiscipline()
    {
        $this->resetPage();
        $this->getDisciplineAttributes();
    }

    // For the manual inputs
    protected function initializeDynamicProperties()
    {
        foreach ($this->selectedIndividuals as $individual) {
            foreach ($this->disciplineAttributes as $attribute) {

                $key = strtolower(str_replace(' ', '_', $attribute['attribute_data']['name'])) . "_{$individual->id}";
                $this->addDynamicProperty($key);
            }
        }
    }

    protected function addDynamicProperty($name)
    {
        if (! property_exists($this, $name)) {
            $this->{$name} = null;
        }
    }

    public function calculateTotalCost()
    {
        $currentDate = now(); // Get the current date

        // Fetch all applicable pricing tiers for this event
        $pricingTiers = Pricing::where('event_id', $this->event->id)
            ->where('start_date', '<=', $currentDate)
            ->where('end_date', '>=', $currentDate)
            ->get();

        // Get all selected individual IDs
        $allSelectedIndividualIds = Arr::flatten($this->selectedIndividualsByDiscipline);
        $uniqueIndividualIds = array_unique($allSelectedIndividualIds);

        foreach ($pricingTiers as $pricing) {
            switch ($pricing->pricing_option) {
                case 'total_price':
                    $this->totalCost += $pricing->price;
                    $this->pricingData[$pricing->id] = $pricing->price;
                    break;

                case 'price_per_discipline':
                    foreach ($this->selectedIndividualsByDiscipline as $disciplineId => $individualIds) {
                        if ($pricing->discipline_id == $disciplineId) {
                            $this->totalCost += count($individualIds) * $pricing->price;
                            $this->pricingData[$pricing->id] = $pricing->price;
                        }
                    }
                    break;

                case 'price_per_person_unique':
                    $this->totalCost = count($uniqueIndividualIds) * $pricing->price;
                    break;
            }

            $this->pricingData[$pricing->id] = $pricing->price;
        }
    }

    public function generatePaymentDocument()
    {
        $discipline = null;
        if ($this->selectedDiscipline) {
            $discipline = Discipline::find($this->selectedDiscipline);
        }

        // Pass the current federation id to the action
        $federation_id = $this->federation->id;
        $createEnrollmentPaymentDocumentAction = new CreateEnrollmentPaymentDocumentAction;
        $selectedIndividualsArray = $this->selectedIndividuals->toArray();

        // Call the action to generate the document
        $document = $createEnrollmentPaymentDocumentAction->execute(
            $this->event,
            $federation_id,
            $selectedIndividualsArray,
            $discipline,
            $this->totalCost
        );
    }

    public function submitAthleteEnrollment()
    {

        // Assuming you've made all necessary validations and checks:
        $createEnrollment = new CreateEnrollmentAction;
        $createAthleteEnrollment = new CreateAthleteEnrollmentAction;

        try {
            DB::beginTransaction();
            // Determine appropriate pricing_id for each enrollment
            $pricingId = $this->determinePricingId();
            $enrollment = $createEnrollment->execute($this->federation, $this->event, $pricingId);
            foreach ($this->selectedIndividualsByDiscipline as $disciplineId => $individualIds) {
                $discipline = Discipline::find($disciplineId);

                foreach ($individualIds as $individualId) {
                    // Create an enrollment for each individual in the current discipline
                    $createAthleteEnrollment->execute($this->event, $this->federation, $individualId, $discipline, $enrollment);
                }
            }
            $this->generatePaymentDocument();
            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
        }

        return redirect(route('federation.evt-events.events.athlete-enrollment.index', $this->event->id))->with('success', 'Enrollment created successfully.');
    }

    private function determinePricingId()
    {
        // Fallback to general event pricing if available
        return $this->pricingData['id'] ?? null;
    }

    public function render()
    {
        $individuals = $this->getAllEligibleAthletes();

        return view('livewire.evt-events.federation.federation-create-enrollment', ['individuals' => $individuals]);
    }
}
