<?php

namespace App\Livewire;

use Domain\Memberships\Models\MembershipPlan;
use Livewire\Component;

// Adjust the model path according to your application

class PlanSearchSelect extends Component
{
    public $search = '';
    public $selectedPlans = [];
    public $plans = [];
    public function mount($selectedPlans = [])
    {
        $this->selectedPlans = $selectedPlans;
        $this->plans = MembershipPlan::all(); // Adjust this query as needed
    }

    public function updatedSearch($value)
    {
        $this->plans = MembershipPlan::where('name', 'like', '%' . $value . '%')->get(); // Adjust the query as needed
    }

    public function addPlan($planId)
    {
        if (! in_array($planId, array_column($this->selectedPlans, 'id'))) {
            $plan = MembershipPlan::find($planId);
            array_push($this->selectedPlans, ['id' => $planId, 'name' => $plan->name]);
        }
    }

    public function removePlan($index)
    {
        unset($this->selectedPlans[$index]);
        $this->selectedPlans = array_values($this->selectedPlans); // Re-index array
    }

    public function render()
    {
        return view('livewire.plan-search-select');
    }
}
