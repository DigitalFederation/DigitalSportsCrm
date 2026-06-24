<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\OperationsCenterService;
use App\Services\SchedulerService;
use Illuminate\Contracts\View\View;

class OperationsCenterController extends Controller
{
    public function __construct(
        protected OperationsCenterService $operationsService,
        protected SchedulerService $schedulerService
    ) {}

    /**
     * Display the main Operations Center dashboard.
     */
    public function index(): View
    {
        $queueStats = $this->operationsService->getQueueStats();
        $systemHealth = $this->operationsService->getSystemHealth();
        $scheduledTasks = $this->schedulerService->getScheduledTasks();
        $failedJobs = $this->operationsService->getFailedJobs(5);

        return view('web.admin.operations-center.index', compact(
            'queueStats',
            'systemHealth',
            'scheduledTasks',
            'failedJobs'
        ));
    }

    /**
     * Display the queue management page.
     */
    public function queue(): View
    {
        return view('web.admin.operations-center.queue');
    }

    /**
     * Display the scheduler management page.
     */
    public function scheduler(): View
    {
        return view('web.admin.operations-center.scheduler');
    }

    /**
     * Display the command center page.
     */
    public function commands(): View
    {
        return view('web.admin.operations-center.commands');
    }

    /**
     * Display the batch monitoring page.
     */
    public function batches(): View
    {
        return view('web.admin.operations-center.batches');
    }
}
