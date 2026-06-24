<?php

namespace App\Livewire;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;

class WidgetActivityLog extends Component
{
    public $subject;

    public $activitiesByDate;

    public $loadType; // 'lazy' or 'poll'

    public function mount($subject, $loadType = 'lazy')
    {
        // Handle both single model and collection
        if ($subject instanceof Collection && $subject->isNotEmpty()) {
            $subject = $subject->first();
        }

        if (! $subject instanceof Model) {
            throw new \Exception('Expected a single model instance or a non-empty collection');
        }

        $this->subject = $subject;
        $this->loadType = $loadType;

        if ($this->loadType === 'lazy') {
            $this->activitiesByDate = [];
        } else {
            $this->loadActivities();
        }
    }

    public function loadActivities()
    {
        $activities = Activity::where(function ($query) {
            $query->where('subject_id', $this->subject->getKey())
                ->where('subject_type', get_class($this->subject));
        })
            ->orWhere(function ($query) {
                $query->where('log_name', 'CertificationAttributed')
                    ->whereJsonContains('properties->id', $this->subject->id);
            })
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

        $this->activitiesByDate = $groupedActivities;
    }

    public function render()
    {
        if ($this->loadType === 'poll') {
            $this->loadActivities();
        }

        return view('livewire.widget-activity-log', [
            'activitiesByDate' => $this->activitiesByDate,
        ]);
    }
}
