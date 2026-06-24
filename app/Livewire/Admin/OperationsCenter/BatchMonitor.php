<?php

declare(strict_types=1);

namespace App\Livewire\Admin\OperationsCenter;

use App\Services\OperationsCenterService;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class BatchMonitor extends Component
{
    public string $activeTab = 'active';

    protected OperationsCenterService $operationsService;

    public function boot(OperationsCenterService $operationsService): void
    {
        $this->operationsService = $operationsService;
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function cancelBatch(string $batchId): void
    {
        $result = $this->operationsService->cancelBatch($batchId);

        if ($result) {
            activity()
                ->causedBy(auth()->user())
                ->withProperties(['batch_id' => $batchId])
                ->log('Cancelled job batch');

            Notification::make()
                ->title(__('operations.success'))
                ->body(__('operations.batch_cancelled'))
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title(__('operations.error'))
                ->body(__('operations.batch_cancel_failed'))
                ->danger()
                ->send();
        }
    }

    public function getActiveBatchesProperty(): Collection
    {
        return $this->operationsService->getActiveBatches();
    }

    public function getCompletedBatchesProperty(): Collection
    {
        return $this->operationsService->getCompletedBatches();
    }

    public function render(): View
    {
        return view('livewire.admin.operations-center.batch-monitor', [
            'activeBatches' => $this->activeBatches,
            'completedBatches' => $this->completedBatches,
        ]);
    }
}
