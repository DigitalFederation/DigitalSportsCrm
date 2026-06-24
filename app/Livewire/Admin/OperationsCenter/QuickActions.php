<?php

declare(strict_types=1);

namespace App\Livewire\Admin\OperationsCenter;

use App\Services\OperationsCenterService;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class QuickActions extends Component
{
    public bool $isRunning = false;

    public ?string $runningAction = null;

    protected OperationsCenterService $operationsService;

    protected array $quickActions = [
        'sync:all-user-roles' => [
            'name' => 'Sync All Roles',
            'icon' => 'users',
            'color' => 'blue',
        ],
        'licenses:activate-paid' => [
            'name' => 'Activate Paid Licenses',
            'icon' => 'badge-check',
            'color' => 'green',
        ],
        'optimize:clear' => [
            'name' => 'Clear All Caches',
            'icon' => 'refresh',
            'color' => 'purple',
        ],
        'qr-code:individuals-generate' => [
            'name' => 'Generate QR Codes',
            'icon' => 'qrcode',
            'color' => 'indigo',
        ],
    ];

    protected const RATE_LIMIT_KEY = 'operations-quick-action';

    protected const RATE_LIMIT_SECONDS = 30;

    public function boot(OperationsCenterService $operationsService): void
    {
        $this->operationsService = $operationsService;
    }

    public function executeAction(string $signature): void
    {
        $userId = auth()->id();
        $rateLimitKey = self::RATE_LIMIT_KEY . ':' . $userId;

        // Check rate limit
        if (RateLimiter::tooManyAttempts($rateLimitKey, 1)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            Notification::make()
                ->title(__('operations.error'))
                ->body(__('operations.rate_limit_exceeded', ['seconds' => $seconds]))
                ->danger()
                ->send();

            return;
        }

        if (! $this->operationsService->isCommandAllowed($signature)) {
            Notification::make()
                ->title(__('operations.error'))
                ->body(__('operations.command_not_allowed'))
                ->danger()
                ->send();

            return;
        }

        $this->isRunning = true;
        $this->runningAction = $signature;

        RateLimiter::hit($rateLimitKey, self::RATE_LIMIT_SECONDS);

        $result = $this->operationsService->executeCommand($signature);

        activity()
            ->causedBy(auth()->user())
            ->withProperties([
                'command' => $signature,
                'success' => $result['success'],
                'source' => 'quick_actions',
            ])
            ->log('Executed quick action from Operations Center');

        $this->isRunning = false;
        $this->runningAction = null;

        if ($result['success']) {
            Notification::make()
                ->title(__('operations.success'))
                ->body(__('operations.action_executed_success'))
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title(__('operations.error'))
                ->body(__('operations.action_executed_failed'))
                ->danger()
                ->send();
        }
    }

    public function getActionsProperty(): array
    {
        return $this->quickActions;
    }

    public function render(): View
    {
        return view('livewire.admin.operations-center.quick-actions', [
            'actions' => $this->actions,
        ]);
    }
}
