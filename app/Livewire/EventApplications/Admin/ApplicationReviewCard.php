<?php

namespace App\Livewire\EventApplications\Admin;

use Domain\EventApplications\Actions\UpdateApplicationStateAction;
use Domain\EventApplications\Models\EventApplication;
use Livewire\Component;

class ApplicationReviewCard extends Component
{
    public EventApplication $application;

    public $action = '';

    public $notes = '';

    public $showModal = false;

    public $modalTitle = '';

    public function mount(EventApplication $application)
    {
        $this->application = $application;
    }

    public function rules()
    {
        return [
            'notes' => $this->action === 'reject' || $this->action === 'return' ? 'required|string|max:2000' : 'nullable|string|max:2000',
        ];
    }

    public function openModal($action)
    {
        $this->action = $action;
        $this->notes = '';

        $this->modalTitle = match ($action) {
            'validate' => __('Validate Application'),
            'approve' => __('event_applications.actions.approve'),
            'return' => __('event_applications.actions.return_for_correction'),
            'reject' => __('event_applications.actions.reject'),
            'publish' => __('event_applications.actions.publish'),
            default => __('Update Application'),
        };

        $this->showModal = true;
        $this->resetValidation();
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['action', 'notes']);
        $this->resetValidation();
    }

    public function updateState(UpdateApplicationStateAction $updateStateAction)
    {
        $this->validate();

        try {
            $updateStateAction->execute(
                $this->application->id,
                $this->action,
                $this->notes,
                auth()->id()
            );

            $this->closeModal();
            $this->application->refresh();

            session()->flash('success', __('event_applications.application_updated_success'));

            return redirect()->route('admin.event-applications.show', $this->application);
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
            $this->closeModal();
        }
    }

    public function render()
    {
        return view('livewire.event-applications.admin.application-review-card');
    }
}
