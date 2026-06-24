<?php

namespace App\Livewire\EventApplications\Admin;

use Domain\EventApplications\Actions\AddCommentToApplicationAction;
use Domain\EventApplications\Models\EventApplication;
use Livewire\Component;

class ApplicationComments extends Component
{
    public EventApplication $application;

    public $comment = '';

    public $is_internal = false;

    public $showForm = false;

    public function mount(EventApplication $application)
    {
        $this->application = $application;
    }

    public function rules()
    {
        return [
            'comment' => 'required|string|max:2000',
            'is_internal' => 'boolean',
        ];
    }

    public function toggleForm()
    {
        $this->showForm = ! $this->showForm;

        if (! $this->showForm) {
            $this->reset(['comment', 'is_internal']);
            $this->resetValidation();
        }
    }

    public function addComment(AddCommentToApplicationAction $action)
    {
        $this->validate();

        $action->execute(
            $this->application->id,
            $this->comment,
            $this->is_internal,
            auth()->id()
        );

        $this->reset(['comment', 'is_internal', 'showForm']);

        $this->application->refresh();

        session()->flash('success', __('event_applications.comment_added_success'));
    }

    public function render()
    {
        return view('livewire.event-applications.admin.application-comments');
    }
}
