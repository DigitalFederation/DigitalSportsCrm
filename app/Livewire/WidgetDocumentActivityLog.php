<?php

namespace App\Livewire;

use Domain\Documents\Models\Document;
use Illuminate\Support\Carbon;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;

class WidgetDocumentActivityLog extends Component
{
    public $documentId;

    public $activitiesByDate;

    public function mount(Document $document)
    {
        if (! $document instanceof Document) {
            throw new \Exception('Expected Document model instance');
        }
        $this->documentId = $document->id;
        $this->loadActivities();

    }

    private function loadActivities()
    {
        $activities = Activity::where('subject_id', $this->documentId)
            ->where('subject_type', Document::class)
            ->latest()
            ->get();

        $groupedActivities = $activities->reduce(function ($carry, $activity) {
            $date = Carbon::parse($activity->created_at)->format('Y-m-d');
            if (! isset($carry[$date])) {
                $carry[$date] = [];
            }
            $carry[$date][] = $activity;

            return $carry;
        }, []);

        // Keeping the activities as collections grouped by date
        $this->activitiesByDate = $groupedActivities;
    }

    public function render()
    {
        return view('livewire.widget-document-activity-log', [
            'activitiesByDate' => $this->activitiesByDate,
        ]);
    }
}
