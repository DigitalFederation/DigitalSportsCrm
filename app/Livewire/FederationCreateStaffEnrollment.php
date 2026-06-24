<?php

namespace App\Livewire;

use App\Enums\EvtEventFeeTypeEnum;
use Domain\EvtEvents\Actions\CreateEnrollmentAction;
use Domain\EvtEvents\Actions\CreateStaffEnrollmentAction;
use Domain\EvtEvents\Models\Event;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Individuals\States\ActiveIndividualFederationState;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class FederationCreateStaffEnrollment extends Component
{
    use WithPagination;

    public $page = 1;
    public $event;
    public $federation;
    public $selectedIndividuals = [];
    public $selectedIndividualDetails = [];
    public $professionalRoles;
    public $selectedProfessionalRole = null;
    public $totalCost = 0;
    public $search = '';
    public $showConfirmation = false;

    protected $queryString = [
        'page' => ['except' => 1],
    ];

    public function mount(Event $event, Federation $federation): void
    {
        $this->event = $event;
        $this->federation = $federation;
        $this->professionalRoles = ProfessionalRole::all();
        $this->selectedProfessionalRole = $this->event->professionalRoles()->pluck('professional_role_id');
        $this->calculateTotalCost();
    }

    public function doSearch()
    {
        $this->resetPage();
        $this->getEligibleIndividuals();
    }

    public function doClearSearch()
    {
        $this->resetPage();
        $this->search = '';
        $this->getEligibleIndividuals();
    }

    public function getEligibleIndividuals()
    {
        $query = Individual::query();

        // Filter for individuals belonging to the federation with an active status
        $query->whereHas('individualFederations', function ($q) {
            $q->where('federation_id', $this->federation->id)
                ->where('status_class', ActiveIndividualFederationState::class);
        });

        // Filter by staff roles
        // Optionally filter by a specific professional role
        if ($this->selectedProfessionalRole->isNotEmpty()) {
            $query->whereHas('professionalRoles', function ($q) {
                $q->whereIn('professional_role_id', $this->selectedProfessionalRole);
            });
        }

        // Add search filter
        if (! empty($this->search)) {
            $query->where(function ($subquery) {
                $subquery->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('member_code', 'like', '%' . $this->search . '%');
            });
        }

        return $query->paginate(45);
    }

    public function updateSelectedIndividuals($individual)
    {
        if (! isset($this->selectedIndividuals[$this->page])) {
            $this->selectedIndividuals[$this->page] = [];
        }
        $individual = json_decode($individual, true);

        // Check if the individual is already selected
        $existingKey = array_search($individual['id'], array_column($this->selectedIndividuals[$this->page], 'id'));

        if ($existingKey !== false) {
            unset($this->selectedIndividuals[$this->page][$existingKey]);
        } else {
            $this->selectedIndividuals[$this->page][] = $individual;
        }

        $this->calculateTotalCost();
    }

    private function calculateTotalCost()
    {
        $flattenedSelectedIndividuals = $this->getFlattenedSelectedIndividuals();

        if ($this->event->event_fee_type == EvtEventFeeTypeEnum::PER_PERSON->name) {
            $this->totalCost = count($flattenedSelectedIndividuals) * $this->event->event_fee;
        } elseif ($this->event->event_fee_type == EvtEventFeeTypeEnum::FLAT_FEE->name) {
            $this->totalCost = $this->event->event_fee;
        }
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
            $individualEnrollmentAction = new CreateStaffEnrollmentAction;

            // Create a general enrollment record
            $enrollment = $enrollmentAction->execute($this->federation, $this->event);

            // Flatten the selected individuals array
            $selectedIndividuals = array_merge(...$this->selectedIndividuals);

            foreach ($selectedIndividuals as $selected) {
                $individual = Individual::find($selected['id']);

                // Create specific individual enrollment record
                $individualEnrollment = $individualEnrollmentAction->execute(
                    $this->event,
                    $this->federation,
                    $individual,
                    $enrollment,
                    [
                        'user_id' => $individual->user_id,
                        'first_name' => $individual->name,
                        'last_name' => $individual->surname,
                        'role' => '',
                        'color_code' => '',
                        'duration' => '',
                    ]
                );

            }

            session()->flash('success', __('Individuals enrolled successfully.'));
        });

        // Post-submission logic
        $this->resetSelectedIndividuals();

        return redirect()->route('federation.evt-events.events.show', ['event' => $this->event->id]);

    }

    private function resetSelectedIndividuals()
    {
        $this->selectedIndividuals = [];
    }

    public function getFlattenedSelectedIndividuals()
    {
        return array_merge(...array_values($this->selectedIndividuals));
    }

    public function render()
    {
        $eligibleIndividuals = $this->getEligibleIndividuals();

        return view('livewire.federation.create-staff-enrollment', compact('eligibleIndividuals'));
    }

}
