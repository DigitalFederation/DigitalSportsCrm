<?php

declare(strict_types=1);

namespace App\Livewire\Admin\OperationsCenter;

use App\Services\SchedulerService;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class SchedulerDashboard extends Component
{
    public bool $isRunning = false;

    public ?string $runningTask = null;

    public ?string $lastOutput = null;

    public bool $showOutput = false;

    protected SchedulerService $schedulerService;

    public function boot(SchedulerService $schedulerService): void
    {
        $this->schedulerService = $schedulerService;
    }

    public function runTask(string $signature): void
    {
        $this->isRunning = true;
        $this->runningTask = $signature;

        $result = $this->schedulerService->runTask($signature);

        activity()
            ->causedBy(auth()->user())
            ->withProperties([
                'command' => $signature,
                'success' => $result['success'],
                'exit_code' => $result['exit_code'],
            ])
            ->log('Manually executed scheduled task');

        $this->isRunning = false;
        $this->runningTask = null;
        $this->lastOutput = $result['output'];
        $this->showOutput = true;

        if ($result['success']) {
            Notification::make()
                ->title(__('operations.success'))
                ->body(__('operations.task_executed_success'))
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title(__('operations.error'))
                ->body(__('operations.task_executed_failed'))
                ->danger()
                ->send();
        }
    }

    public function closeOutput(): void
    {
        $this->showOutput = false;
        $this->lastOutput = null;
    }

    public function getTasksProperty(): Collection
    {
        return $this->schedulerService->getScheduledTasks();
    }

    public function render(): View
    {
        return view('livewire.admin.operations-center.scheduler-dashboard', [
            'tasks' => $this->tasks,
        ]);
    }
}
