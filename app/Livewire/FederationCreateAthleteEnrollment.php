<?php

namespace App\Livewire;

use Domain\EvtEvents\Actions\CreateAthleteEnrollmentAction;
use Domain\EvtEvents\Actions\CreateEnrollmentAction;
use Domain\EvtEvents\Actions\GetAttributesAndRulesFromDisciplineAction;
use Domain\EvtEvents\Actions\GetDisciplinesFromEventAction;
use Domain\EvtEvents\Actions\GetEligibleAthletesAction;
use Domain\EvtEvents\Models\Discipline;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\Pricing;
use Domain\Federations\Models\Federation;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Mockery\Exception;

class FederationCreateAthleteEnrollment extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;
    // use WithPagination;

    public $page = 1;
    public $event;
    public $federation;
    public $selectedIndividuals = [];
    public $selectedIndividualsByDiscipline = [];
    public $selectedIndividualIds = [];
    public $disciplines = [];
    public $disciplineModels;
    public $selectedDiscipline;
    public $disciplineAttributes = [];
    public $disciplineAttributeValues = [];
    public $globalAttributes = [];
    public $individualSearchTerm;
    public $individualSearchTermValue = '';
    public $enrollmentSummary = [];
    public $inputFields = [];
    public $totalCost = 0;
    public $showConfirmation = false;
    public $pricingData = []; // To store pricing IDs
    public $displayedIndividuals = [];

    protected $queryString = [
        'page' => ['except' => 1],
    ];

    protected $listeners = ['refreshTable' => 'refreshTable'];

    public function mount(Event $event, Federation $federation)
    {

        $this->event = $event;
        $this->federation = $federation;
        $this->getDisciplinesFromEvent();

        // Initialize the array for each discipline
        foreach ($this->disciplines as $discipline) {
            if (! isset($this->selectedIndividualsByDiscipline[$discipline->id])) {
                $this->selectedIndividualsByDiscipline[$discipline->id] = [];
            }
        }

    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getAllEligibleAthletes())
            ->columns([
                TextColumn::make('full_name')
                    ->searchable(['name', 'surname'])
                    ->label('Name'),
                TextColumn::make('birthdate')
                    ->label('Birthdate'),
                TextColumn::make('gender')
                    ->label('Gender'),
                TextColumn::make('member_code')
                    ->searchable()
                    ->label('International Code'),
            ])
            ->bulkActions([
                BulkAction::make('updateSelectedIndividuals')
                    ->label('Select Individual(s)')
                    ->color('primary')
                    ->size('xl')
                    ->action(fn (Collection $records) => $this->updateSelectedIndividuals($records)),
            ])
            ->filters([]);
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
        $this->disciplineAttributes = $attributesAndRules->execute($this->selectedDiscipline);
    }

    private function initializeAttributeValues()
    {
        foreach ($this->selectedIndividuals as $individual) {
            foreach ($this->disciplineAttributes as $attribute) {
                if (! isset($this->disciplineAttributeValues[$individual['id']][$attribute['attribute_data']['id']])) {
                    $this->disciplineAttributeValues[$individual['id']][$attribute['attribute_data']['id']] = $attribute['attribute_data']['default_value'] ?? null;
                }
            }
        }
    }

    public function getAllEligibleAthletes()
    {
        $getEligibleAthletesAction = new GetEligibleAthletesAction;

        return $getEligibleAthletesAction->execute($this->event->id, $this->federation->id, $this->selectedDiscipline);
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
        // Fetch from component
        $selectedIndividual = collect($this->displayedIndividuals[$this->selectedDiscipline])->firstWhere('id', $individualId);
        $this->selectedIndividualsByDiscipline[$this->selectedDiscipline][] = $selectedIndividual;

        $this->calculateTotalCost();
    }

    public function removeIndividualFromSelection($individualId)
    {
        // Filter out the individual with the matching ID
        $this->selectedIndividualsByDiscipline[$this->selectedDiscipline] = array_filter(
            $this->selectedIndividualsByDiscipline[$this->selectedDiscipline],
            function ($individual) use ($individualId) {
                return $individual['id'] != $individualId;
            }
        );

        // Re-index the array to maintain array structure
        $this->selectedIndividualsByDiscipline[$this->selectedDiscipline] = array_values($this->selectedIndividualsByDiscipline[$this->selectedDiscipline]);

        $this->calculateTotalCost();
    }

    private function updateSelectedIndividuals(Collection $individuals)
    {
        // Reset the selected individuals array
        $this->selectedIndividuals = [];

        // This loop will check if selected individuals are already in the list or not and update accordingly
        foreach ($individuals as $individual) {
            if (! isset($this->disciplineAttributeValues[$individual->id])) {
                $this->disciplineAttributeValues[$individual->id] = [];
            }
            $this->initializeAttributeValues();
            $this->selectedIndividuals[] = [
                'id' => $individual->id,
                'name' => $individual->full_name,
                'member_code' => $individual->member_code,
            ];

        }

    }

    public function updateSelectedDiscipline()
    {
        $this->resetPage();
        $this->getDisciplineAttributes();
        $this->resetTable();
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
        $this->totalCost = 0;

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
            if ($pricing->price == null || $pricing->price == 0) {
                continue;
            }
            $this->pricingData[$pricing->id] = $pricing->price;
        }
    }

    public function submitAthleteEnrollment()
    {
        $createEnrollment = new CreateEnrollmentAction;
        $createAthleteEnrollment = new CreateAthleteEnrollmentAction;

        if (empty($this->selectedDiscipline)) {
            return false;
        }

        try {
            DB::beginTransaction();
            // Main enrollment process
            $enrollment = $createEnrollment->execute($this->federation, $this->event);

            foreach ($this->selectedIndividuals as $selectedIndividual) {
                $createAthleteEnrollment->execute(
                    $this->event,
                    $this->federation,
                    $selectedIndividual['id'],
                    $this->selectedDiscipline,
                    $enrollment,
                    $this->disciplineAttributeValues[$selectedIndividual['id']] ?? []
                );
            }

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
        }

        return redirect(route('federation.evt-events.pending-enrollments.index'))->with('success', 'Athlete enrollment created and is pending confirmation.');
    }

    /**
     * Reset the Filament table.
     */
    protected function refreshTable()
    {
        $this->table->query($this->getAllEligibleAthletes());
    }

    private function determinePricingId()
    {
        // Fallback to general event pricing if available
        return $this->pricingData['id'] ?? null;
    }

    public function render()
    {
        // $individuals = $this->getAllEligibleAthletes();
        $selectedDisciplineModel = Discipline::find($this->selectedDiscipline);

        return view('livewire.evt-events.federation.federation-create-enrollment', [
            'selectedDisciplineModel' => $selectedDisciplineModel,
        ]);
    }
}
