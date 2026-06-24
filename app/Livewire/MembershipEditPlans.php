<?php

namespace App\Livewire;

use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class MembershipEditPlans extends Component
{
    public Collection $plans;

    public array $selectedPlans = [];

    public function mount()
    {
        $this->selectedPlans = $this->plans->pluck('id')->toArray();
    }

    public function removePlan($id)
    {
        $this->plans = $this->plans->reject(fn ($plan) => $plan->id == $id);
        $this->selectedPlans = $this->plans->pluck('id')->toArray();
    }

    public function render()
    {
        return view('livewire.membership-edit-plans');
    }
}
