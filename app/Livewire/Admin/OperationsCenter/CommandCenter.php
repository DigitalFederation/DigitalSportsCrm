<?php

declare(strict_types=1);

namespace App\Livewire\Admin\OperationsCenter;

use App\Services\OperationsCenterService;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class CommandCenter extends Component
{
    public string $search = '';

    public ?string $selectedCategory = null;

    public bool $isRunning = false;

    public ?string $runningCommand = null;

    public ?string $lastOutput = null;

    public bool $showOutput = false;

    protected OperationsCenterService $operationsService;

    protected const RATE_LIMIT_KEY = 'operations-command-execution';

    protected const RATE_LIMIT_SECONDS = 30;

    public function boot(OperationsCenterService $operationsService): void
    {
        $this->operationsService = $operationsService;
    }

    public function selectCategory(?string $category): void
    {
        $this->selectedCategory = $category;
    }

    public function executeCommand(string $signature): void
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
        $this->runningCommand = $signature;

        // Hit the rate limiter
        RateLimiter::hit($rateLimitKey, self::RATE_LIMIT_SECONDS);

        $result = $this->operationsService->executeCommand($signature);

        // Log to activity log
        activity()
            ->causedBy(auth()->user())
            ->withProperties([
                'command' => $signature,
                'success' => $result['success'],
                'exit_code' => $result['exit_code'],
            ])
            ->log('Executed command from Operations Center');

        // Store last execution time
        Cache::put('operations_last_command_' . md5($signature), Carbon::now(), now()->addDays(7));

        $this->isRunning = false;
        $this->runningCommand = null;
        $this->lastOutput = $result['output'];
        $this->showOutput = true;

        if ($result['success']) {
            Notification::make()
                ->title(__('operations.success'))
                ->body(__('operations.command_executed_success'))
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title(__('operations.error'))
                ->body(__('operations.command_executed_failed'))
                ->danger()
                ->send();
        }
    }

    public function closeOutput(): void
    {
        $this->showOutput = false;
        $this->lastOutput = null;
    }

    public function getCommandsProperty(): array
    {
        $categories = $this->operationsService->getCommandsByCategory();

        if ($this->selectedCategory) {
            $categories = array_filter($categories, fn ($key) => $key === $this->selectedCategory, ARRAY_FILTER_USE_KEY);
        }

        if ($this->search) {
            foreach ($categories as $key => $category) {
                $categories[$key]['commands'] = array_filter($category['commands'], function ($config, $signature) {
                    return str_contains(strtolower($signature), strtolower($this->search)) ||
                           str_contains(strtolower($config['name']), strtolower($this->search)) ||
                           str_contains(strtolower($config['description']), strtolower($this->search));
                }, ARRAY_FILTER_USE_BOTH);
            }
            $categories = array_filter($categories, fn ($cat) => ! empty($cat['commands']));
        }

        return $categories;
    }

    public function getCategoriesProperty(): array
    {
        return $this->operationsService->getCommandsByCategory();
    }

    public function getLastExecutionTime(string $signature): ?Carbon
    {
        return Cache::get('operations_last_command_' . md5($signature));
    }

    public function render(): View
    {
        return view('livewire.admin.operations-center.command-center', [
            'commands' => $this->commands,
            'categories' => $this->categories,
        ]);
    }
}
