<?php

namespace App\Livewire\EventApplications\Admin;

use Domain\EventApplications\Models\ApplicationTemplate;
use Domain\EventApplications\Models\EventApplication;
use Livewire\Component;
use Livewire\WithPagination;

class ApplicationsList extends Component
{
    use WithPagination;

    public $search = '';

    public $status = '';

    public $application_type = '';

    public $event_type = '';

    public $template_id = '';

    public $date_from = '';

    public $date_to = '';

    public $perPage = 15;

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'application_type' => ['except' => ''],
        'event_type' => ['except' => ''],
        'template_id' => ['except' => ''],
        'date_from' => ['except' => ''],
        'date_to' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function updatingApplicationType()
    {
        $this->resetPage();
    }

    public function updatingEventType()
    {
        $this->resetPage();
    }

    public function updatingTemplateId()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->reset(['search', 'status', 'application_type', 'event_type', 'template_id', 'date_from', 'date_to']);
        $this->resetPage();
    }

    public function render()
    {
        $applications = EventApplication::query()
            ->with(['entity', 'template', 'sport'])
            ->when($this->search, function ($query) {
                $query->where('event_name', 'like', '%'.$this->search.'%')
                    ->orWhereHas('entity', function ($q) {
                        $q->where('name', 'like', '%'.$this->search.'%');
                    });
            })
            ->when($this->status, function ($query) {
                $stateMap = [
                    'draft' => \Domain\EventApplications\States\DraftApplicationState::class,
                    'submitted' => \Domain\EventApplications\States\SubmittedApplicationState::class,
                    'in_validation' => \Domain\EventApplications\States\InValidationApplicationState::class,
                    'returned_for_correction' => \Domain\EventApplications\States\ReturnedForCorrectionApplicationState::class,
                    'approved' => \Domain\EventApplications\States\ApprovedApplicationState::class,
                    'rejected' => \Domain\EventApplications\States\RejectedApplicationState::class,
                    'published' => \Domain\EventApplications\States\PublishedApplicationState::class,
                ];

                if (isset($stateMap[$this->status])) {
                    $query->where('status_class', $stateMap[$this->status]);
                }
            })
            ->when($this->application_type, function ($query) {
                $query->where('application_type', $this->application_type);
            })
            ->when($this->event_type, function ($query) {
                $query->where('event_type', $this->event_type);
            })
            ->when($this->template_id, function ($query) {
                $query->where('template_id', $this->template_id);
            })
            ->when($this->date_from, function ($query) {
                $query->whereDate('submitted_at', '>=', $this->date_from);
            })
            ->when($this->date_to, function ($query) {
                $query->whereDate('submitted_at', '<=', $this->date_to);
            })
            ->latest()
            ->paginate($this->perPage);

        $templates = ApplicationTemplate::all();

        return view('livewire.event-applications.admin.applications-list', [
            'applications' => $applications,
            'templates' => $templates,
        ]);
    }
}
