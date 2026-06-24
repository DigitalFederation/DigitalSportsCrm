<?php

namespace App\Livewire\Widgets;

use App\Enums\UserGroupEnum;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

class ActivityLog extends Component
{
    use WithPagination;

    public $selectedLog = null;

    public int $perPage = 5;

    protected $listeners = ['closeModal'];

    public function render()
    {
        try {
            $logs = $this->findLogs();

            return view('livewire.widgets.activity-log', compact('logs'));
        } catch (Exception $e) {
            Log::error($e->getMessage());

            return view('livewire.widgets.404')->with('title', 'Activity Log');
        }
    }

    public function findLogs()
    {
        $logsQuery = auth()->user()->group_id === UserGroupEnum::ADMIN->value
            ? Activity::with('causer')->whereIn('causer_id', $this->getAdminUserIds())
            : Activity::with('causer')->where('causer_id', auth()->id());

        return $logsQuery->orderBy('created_at', 'desc')->paginate($this->perPage);
    }

    private function getAdminUserIds()
    {
        return User::where('group_id', UserGroupEnum::ADMIN->value)->pluck('id');
    }

    public function isAdmin(): bool
    {
        return auth()->user()->group_id === UserGroupEnum::ADMIN->value;
    }

    public function showDetails($logId)
    {
        $this->selectedLog = Activity::find($logId);
        $this->dispatch('open-modal');
    }

    public function closeModal()
    {
        $this->selectedLog = null;
        $this->dispatch('close-modal');
    }

    /**
     * Get a user-friendly, translated description for the activity log entry
     */
    public function getFormattedDescription(Activity $log): string
    {
        $event = $this->translateEvent($log->event);
        $subjectType = $this->translateSubjectType($log->subject_type);

        return "{$event} {$subjectType}";
    }

    /**
     * Translate event type to user-friendly text
     */
    private function translateEvent(?string $event): string
    {
        return match ($event) {
            'created' => __('activity.created'),
            'updated' => __('activity.updated'),
            'deleted' => __('activity.deleted'),
            default => __('activity.action_performed'),
        };
    }

    /**
     * Translate subject type (model) to user-friendly text
     */
    private function translateSubjectType(?string $subjectType): string
    {
        if (empty($subjectType)) {
            return __('activity.record');
        }

        $className = class_basename($subjectType);

        return match ($className) {
            'Document' => __('activity.subject.document'),
            'Insurance' => __('activity.subject.insurance'),
            'Affiliation' => __('activity.subject.affiliation'),
            'MemberSubscription' => __('activity.subject.subscription'),
            'License', 'LicenseAttributed' => __('activity.subject.license'),
            'Certification', 'CertificationAttributed' => __('activity.subject.certification'),
            'Individual' => __('activity.subject.profile'),
            'Entity' => __('activity.subject.entity'),
            'Payment' => __('activity.subject.payment'),
            default => __('activity.record'),
        };
    }

    private function formatFieldName($key)
    {
        return match ($key) {
            'status', 'status_class' => __('Status'),
            'total_value' => __('Amount'),
            'is_self_request' => __('Self Requested'),
            default => __(ucfirst(str_replace('_', ' ', $key)))
        };
    }

    public function formatStateClass($stateClass)
    {
        if (empty($stateClass)) {
            return '';
        }

        // Get the class basename (e.g., "ActiveLicenseAttributedState")
        $className = class_basename($stateClass);

        // Remove common suffixes
        $name = str_replace(['State', 'Attributed'], '', $className);

        // Split by uppercase letters
        $words = preg_split('/(?=[A-Z])/', $name, -1, PREG_SPLIT_NO_EMPTY);

        // Join with spaces and translate
        return __(implode(' ', $words));
    }

    public function getStatusBadgeColor($status)
    {
        return match (Str::lower($status)) {
            'active' => 'success',
            'pending' => 'warning',
            'inactive', 'expired' => 'danger',
            default => 'info'
        };
    }
}
