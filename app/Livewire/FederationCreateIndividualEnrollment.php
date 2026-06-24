<?php

namespace App\Livewire;

use App\Enums\EvtEventFeeTypeEnum;
use Domain\EvtEvents\Actions\CreateEnrollmentAction;
use Domain\EvtEvents\Actions\CreateIndividualEnrollmentAction;
use Domain\EvtEvents\Actions\CreateIndividualEnrollmentOrderAction;
use Domain\EvtEvents\Actions\GetAttributesFromEventAction;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\Pricing;
use Domain\EvtEvents\Services\EnrollmentsCostCalculationService;
use Domain\EvtEvents\States\ActiveIndividualEnrollmentState;
use Domain\EvtEvents\States\PendingIndividualEnrollmentState;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Individuals\States\ActiveIndividualFederationState;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class FederationCreateIndividualEnrollment extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;
    // use WithPagination;

    public $page = 1;
    public $event;
    public $federation;
    public $selectedIndividuals = [];
    public $selectedIndividualDetails = [];

    public $professionalRoles;
    public $selectedProfessionalRole = null;
    public $totalCost = 0;
    public $costBreakdown = [];
    public $search = '';

    public $pricingData = []; // To store pricing IDs

    public $eventAttributes = [];
    public $attributeValues = [];
    public $showConfirmation = false;
    public $pricingTiersOptions = [];
    public $selectedPricingTierId = null;
    public $individualPricingTiers = []; // Stores pricing tier IDs indexed by individual IDs

    public $redirectTo = null;

    protected $queryString = [
        'page' => ['except' => 1],
    ];

    public function mount(Event $event, Federation $federation)
    {

        $this->event = $event;
        $this->federation = $federation;
        $this->professionalRoles = ProfessionalRole::all();
        $this->selectedProfessionalRole = $this->event->professionalRoles()->pluck('professional_role_id');

        $getAttributesFromEventAction = new GetAttributesFromEventAction;

        $this->eventAttributes = $getAttributesFromEventAction->execute($event->id);

        // Load pricing options for the event
        $this->pricingTiersOptions = Pricing::where('event_id', $event->id)
            ->get()
            ->mapWithKeys(function ($pricing) {
                // Store all necessary details for each pricing tier
                return [$pricing->id => [
                    'price_type' => $pricing->price_type,
                    'price' => $pricing->price,
                    'start_date' => $pricing->start_date,
                    'end_date' => $pricing->end_date,
                    'description' => $pricing->description,
                ]];
            });

        // Automatically select a pricing tier if only one is available
        $this->selectedPricingTierId = $this->pricingTiersOptions->keys()->first();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getEligibleIndividuals())
            ->columns([
                TextColumn::make('full_name')
                    ->searchable(['name', 'surname'])
                    ->label('Name'),
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
            ->filters([
            ]);
    }

    public function getEligibleIndividuals()
    {
        $query = Individual::query()
            ->with('individualEnrollments')
            ->whereHas('individualFederations', function ($q) {
                $q->where('federation_id', $this->federation->id)
                    ->where('status_class', ActiveIndividualFederationState::class);
            });

        if ($this->selectedProfessionalRole->isNotEmpty()) {
            $query->whereHas('professionalRoles', function ($q) {
                $q->whereIn('professional_role_id', $this->selectedProfessionalRole);
            });
        }

        return $query; // Just return the query, not execute it
    }

    public function updateSelectedIndividuals(Collection $individuals)
    {
        // Reset the selected individuals array
        $this->selectedIndividuals = [];
        // This loop will check if selected individuals are already in the list or not and update accordingly

        foreach ($individuals as $individual) {
            $this->selectedIndividuals[] = [
                'id' => $individual->id,
                'name' => $individual->full_name,
                'member_code' => $individual->member_code,
            ];
            // Initialize with default pricing tier or null if not available
            $this->individualPricingTiers[$individual->id] = $this->pricingTiersOptions->keys()->first() ?? null;
        }

        $this->calculateTotalCost();
    }

    public function updatedSelectedPricingTierId()
    {
        $this->calculateTotalCost();
    }

    public function generatePaymentDocument()
    {
        // Pass the current federation id to the action
        $this->calculateTotalCost();
        $federation_id = $this->federation->id;
        $costCalculationService = app(EnrollmentsCostCalculationService::class);
        $createEnrollmentPaymentDocumentAction = new CreateIndividualEnrollmentOrderAction($costCalculationService);

        $unitCost = $this->totalCost / count($this->selectedIndividuals);

        // Make sure the document is generated only if the total cost is greater than 0
        if ($this->totalCost > 0) {

            $enrollment = Enrollment::where('id', $this->selectedIndividuals[0]['main_enrollment_id'])->firstOrFail();

            // Get selected individuals as Collection
            $selectedIndividualsCollection = new Collection(
                Individual::whereIn('id', collect($this->selectedIndividuals)->pluck('id'))
                    ->get()
                    ->map(function ($individual) {
                        return [
                            'id' => $individual->id,
                            'name' => $individual->name,
                            'surname' => $individual->surname,
                            'member_code' => $individual->member_code,
                        ];
                    })
            );

            // Call the action to generate the document
            $document = $createEnrollmentPaymentDocumentAction->execute(
                $this->event,
                $enrollment,
                $federation_id,
                Federation::class,
                $selectedIndividualsCollection,
                $this->individualPricingTiers
            );

            // Redirect the user to the document detail page if the document has been successfully created
            if ($document) {
                $this->redirectTo = route('federation.document.show', ['id' => $document->id]);
            }

        }

    }

    public function calculateTotalCost()
    {
        $this->costBreakdown = $this->calculateCosts();
        $this->totalCost = array_sum($this->calculateCosts());
    }

    public function calculateCosts(): array
    {
        $this->totalCost = 0;
        $breakdown = [];
        $flatFeeApplied = false;

        foreach ($this->selectedIndividuals as $selected) {
            $pricingId = $this->individualPricingTiers[$selected['id']] ?? null;
            if ($pricingId && isset($this->pricingTiersOptions[$pricingId])) {
                $pricing = $this->pricingTiersOptions[$pricingId];
                switch ($pricing['price_type']) {
                    case EvtEventFeeTypeEnum::PER_PERSON->value:
                        $this->totalCost += $pricing['price'];
                        $breakdown[] = [
                            'type' => 'Per Person Fee',
                            'cost' => $pricing['price'],
                        ];
                        break;
                    case EvtEventFeeTypeEnum::FLAT_FEE->value:
                        if (! $flatFeeApplied) {
                            $this->totalCost += $pricing['price'];
                            $breakdown[] = [
                                'type' => 'Flat Fee',
                                'cost' => $pricing['price'],
                            ];
                            $flatFeeApplied = true;
                        }
                        break;
                }
            }
        }

        return $breakdown;
    }

    public function doShowConfirmation()
    {
        if (! $this->showConfirmation) {
            $this->showConfirmation = true;
        }

    }

    public function submitEnrollment()
    {

        DB::transaction(function () {
            $enrollmentAction = new CreateEnrollmentAction;
            $individualEnrollmentAction = new CreateIndividualEnrollmentAction;
            // Create a general enrollment record
            $enrollment = $enrollmentAction->execute($this->federation, $this->event);

            foreach ($this->selectedIndividuals as $index => $selected) {

                $individual = Individual::find($selected['id']);
                // Determine the enrollment status
                $statusClass = $this->determineEnrollmentStatus();
                // $pricingId = $this->individualPricingTiers[$selected['id']] ?? null;

                // Create specific individual enrollment record
                $individualEnrollment = $individualEnrollmentAction->execute(
                    $this->event,
                    $this->federation,
                    $individual,
                    $enrollment,
                    $statusClass,
                    $this->attributeValues,
                    $this->selectedPricingTierId  // Pass the pricing ID
                );

                // Collect newly created enrollment IDs
                $this->selectedIndividuals[$index]['individual_enrollment_id'] = $individualEnrollment->id;
                $this->selectedIndividuals[$index]['main_enrollment_id'] = $enrollment->id;
            }

            // Generate payment document only if the event has a fee
            if ($this->totalCost > 0) {
                // $this->generatePaymentDocument();
            }
        });

        // Check if a redirect has been set and return it
        if ($this->redirectTo) {
            return redirect($this->redirectTo);
        }

        // Post-submission logic
        $this->resetSelectedIndividuals();

        Notification::make()
            ->title(__('Individuals enrolled successfully.'))
            ->success()
            ->send();

        return redirect()->route('federation.evt-events.events.waiting-list.index', ['event' => $this->event->id]);
        // return redirect()->route('federation.evt-events.events.show', ['event' => $this->event->id]);

    }

    private function determineEnrollmentStatus()
    {
        $selectedPricing = Pricing::findOrFail($this->selectedPricingTierId);

        return match ($selectedPricing->price_type) {
            EvtEventFeeTypeEnum::FREE->value => ActiveIndividualEnrollmentState::class,
            EvtEventFeeTypeEnum::FLAT_FEE->value,
            EvtEventFeeTypeEnum::PER_PERSON->value => PendingIndividualEnrollmentState::class,
            default => PendingIndividualEnrollmentState::class,
        };
    }

    private function resetSelectedIndividuals()
    {
        $this->selectedIndividuals = [];
    }

    public function render()
    {
        $eligibleIndividuals = $this->getEligibleIndividuals();

        return view('livewire.federation.create-individual-enrollment', compact('eligibleIndividuals'));
    }

}
