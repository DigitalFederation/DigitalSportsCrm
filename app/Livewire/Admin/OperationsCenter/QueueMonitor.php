<?php

declare(strict_types=1);

namespace App\Livewire\Admin\OperationsCenter;

use App\Services\OperationsCenterService;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class QueueMonitor extends Component
{
    public string $activeTab = 'pending';

    public string $search = '';

    public ?string $selectedJobUuid = null;

    public ?string $selectedJobException = null;

    protected OperationsCenterService $operationsService;

    public function boot(OperationsCenterService $operationsService): void
    {
        $this->operationsService = $operationsService;
    }

    public function mount(): void
    {
        $this->activeTab = 'pending';
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->search = '';
    }

    public function retryJob(string $uuid): void
    {
        $result = $this->operationsService->retryJob($uuid);

        if ($result) {
            activity()
                ->causedBy(auth()->user())
                ->withProperties(['uuid' => $uuid])
                ->log('Retried failed job');

            Notification::make()
                ->title(__('operations.success'))
                ->body(__('operations.job_retry_success'))
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title(__('operations.error'))
                ->body(__('operations.job_retry_failed'))
                ->danger()
                ->send();
        }
    }

    public function deleteJob(string $uuid): void
    {
        $result = $this->operationsService->deleteFailedJob($uuid);

        if ($result) {
            activity()
                ->causedBy(auth()->user())
                ->withProperties(['uuid' => $uuid])
                ->log('Deleted failed job');

            Notification::make()
                ->title(__('operations.success'))
                ->body(__('operations.job_deleted'))
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title(__('operations.error'))
                ->body(__('operations.job_delete_failed'))
                ->danger()
                ->send();
        }
    }

    public function purgeAllFailed(): void
    {
        $count = $this->operationsService->purgeFailedJobs();

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['count' => $count])
            ->log('Purged all failed jobs');

        Notification::make()
            ->title(__('operations.success'))
            ->body(__('operations.jobs_purged', ['count' => $count]))
            ->success()
            ->send();
    }

    public function showJobDetails(string $uuid, string $exception): void
    {
        $this->selectedJobUuid = $uuid;
        $this->selectedJobException = $exception;
    }

    public function closeJobDetails(): void
    {
        $this->selectedJobUuid = null;
        $this->selectedJobException = null;
    }

    public function getPendingJobsProperty(): Collection
    {
        $jobs = $this->operationsService->getPendingJobs();

        if ($this->search) {
            $jobs = $jobs->filter(function ($job) {
                return str_contains(strtolower($job->job_class), strtolower($this->search)) ||
                       str_contains(strtolower($job->queue), strtolower($this->search));
            });
        }

        return $jobs;
    }

    public function getFailedJobsProperty(): Collection
    {
        $jobs = $this->operationsService->getFailedJobs();

        if ($this->search) {
            $jobs = $jobs->filter(function ($job) {
                return str_contains(strtolower($job->job_class), strtolower($this->search)) ||
                       str_contains(strtolower($job->queue), strtolower($this->search)) ||
                       str_contains(strtolower($job->exception), strtolower($this->search));
            });
        }

        return $jobs;
    }

    public function getStatsProperty(): array
    {
        return $this->operationsService->getQueueStats();
    }

    public function render(): View
    {
        return view('livewire.admin.operations-center.queue-monitor', [
            'pendingJobs' => $this->pendingJobs,
            'failedJobs' => $this->failedJobs,
            'stats' => $this->stats,
        ]);
    }
}
