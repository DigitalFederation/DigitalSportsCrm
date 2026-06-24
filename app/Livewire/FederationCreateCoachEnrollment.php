<?php

namespace App\Livewire;

use Domain\EvtEvents\Models\CoachEnrollment;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\ProfessionalRole;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class FederationCreateCoachEnrollment extends Component
{
    use WithPagination;

    public $event;
    public $selectedRoles = [];
    public $selectedMembers = [];
    public $totalCost = 0;
    public $selectedMembersDetails = [];
    public $professionalRoles;
    public $search = '';
    public $showConfirmation = false;
    protected $queryString = [
        'page' => ['except' => 1],
        'search' => ['except' => ''],
    ];

    public function mount(Event $event)
    {
        $this->event = $event;
        $this->professionalRoles = ProfessionalRole::all();
    }

    public function updatedSelectedRoles()
    {
        $this->resetPage();
    }

    public function doSearch()
    {
        $this->resetPage();
    }

    public function doClearSearch()
    {
        $this->resetPage();
        $this->search = '';
    }

    public function doShowConfirmation()
    {
        if (! $this->showConfirmation) {
            $this->showConfirmation = true;
        }

    }

    // Listen for pagination page changes
    public function updating($name, $value)
    {
        if (str_starts_with($name, 'page')) {
            foreach ($this->individuals as $individual) {
                if (! in_array($individual->id, $this->selectedMembers)) {
                    $this->resetCheckbox($individual->id);
                }
            }
        }
    }

    private function resetCheckbox($individualId)
    {
        $key = array_search($individualId, $this->selectedMembers);
        if ($key !== false) {
            unset($this->selectedMembers[$key]);
        }
    }

    public function toggleMember($individualId)
    {
        $individual = Individual::find($individualId);

        if (($key = array_search($individualId, array_column($this->selectedMembersDetails, 'id'))) !== false) {
            unset($this->selectedMembersDetails[$key]);
        } else {
            $this->selectedMembersDetails[] = $individual->toArray();
        }

        $this->selectedMembers = array_column($this->selectedMembersDetails, 'id');
    }

    public function saveCoaches()
    {
        try {
            DB::beginTransaction();

            $federationId = auth()->user()->federations()->first()->id;

            foreach ($this->selectedMembers as $member) {
                $enrollment = Enrollment::create([
                    'user_id' => Individual::find($member)->user()->first()->id,
                    'event_id' => $this->event->id,
                    'enrollable_type' => Federation::class,
                    'enrollable_id' => $federationId,
                ]);

                CoachEnrollment::create([
                    'enrollment_id' => $enrollment->id,
                    'individual_id' => $member,
                    'federation_id' => $federationId,
                    'event_id' => $this->event->id,
                ]);
            }

            DB::commit();
            session()->flash('success', __('Coaches added successfully.'));

            return redirect()->route('federation.evt-events.events.coach-enrollment.index', $this->event->id);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            session()->flash('error', __('Failed to add coaches.'));

            return redirect()->route('federation.evt-events.events.coach-enrollment.index', $this->event->id);
        }
    }

    public function render()
    {
        $federationId = auth()->user()->federations()->first()->id;

        $individuals = Individual::query()
            ->whereHas('federations', function (Builder $query) use ($federationId) {
                $query->where('federation_id', $federationId);
            })
            ->whereHas('professionalRoles', function (Builder $query) {
                $query->where('role', 'COACH')
                    ->when($this->selectedRoles, function (Builder $query) {
                        $query->whereIn('id', $this->selectedRoles);
                    });
            })
            ->whereDoesntHave('coachEnrollments', function (Builder $query) {
                $query->where('event_id', $this->event->id);
            })
            ->when($this->search, function ($query) {
                $query->where(function ($subquery) {
                    $subquery->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('member_code', 'like', '%' . $this->search . '%');
                });
            })
            ->paginate(45);

        return view('livewire.federation.create-coach-enrollment', compact('individuals'));
    }
}
