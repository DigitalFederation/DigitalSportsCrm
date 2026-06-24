<?php

namespace App\Livewire\EventApplications\Entity;

use Domain\EventApplications\Models\EventApplication;
use Livewire\Component;

class ApplicationStatusTracker extends Component
{
    public EventApplication $application;

    public $states = [];
    public $currentStateIndex = 0;

    public function mount(): void
    {
        $this->loadStates();
        $this->determineCurrentState();
    }

    protected function loadStates(): void
    {
        $this->states = [
            [
                'key' => 'draft',
                'label' => __('event_applications.states.draft'),
                'icon' => 'document',
                'color' => 'gray',
            ],
            [
                'key' => 'submitted',
                'label' => __('event_applications.states.submitted'),
                'icon' => 'paper-airplane',
                'color' => 'blue',
            ],
            [
                'key' => 'approved',
                'label' => __('event_applications.states.approved'),
                'icon' => 'check-circle',
                'color' => 'green',
            ],
        ];
    }

    protected function determineCurrentState(): void
    {
        $currentState = $this->application->state->name();

        foreach ($this->states as $index => $state) {
            if ($state['key'] === $currentState) {
                $this->currentStateIndex = $index;
                break;
            }
        }

        // Special states (returned, rejected) don't follow linear progression
        if (in_array($currentState, ['returned_for_correction', 'rejected'])) {
            $this->currentStateIndex = -1;
        }
    }

    public function render()
    {
        return view('livewire.event-applications.entity.application-status-tracker');
    }
}
