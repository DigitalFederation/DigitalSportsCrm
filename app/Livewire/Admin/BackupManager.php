<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Services\BackupService;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class BackupManager extends Component
{
    protected const RATE_LIMIT_KEY = 'backup-create';

    protected const RATE_LIMIT_SECONDS = 300;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('access backups'), 403);
    }

    public function createBackup(BackupService $backupService): void
    {
        $userId = auth()->id();
        $rateLimitKey = self::RATE_LIMIT_KEY . ':' . $userId;

        if (RateLimiter::tooManyAttempts($rateLimitKey, 1)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);

            Notification::make()
                ->title(__('backups.rate_limit_exceeded'))
                ->body(trans_choice('backups.wait_seconds', $seconds, ['seconds' => $seconds]))
                ->danger()
                ->send();

            return;
        }

        RateLimiter::hit($rateLimitKey, self::RATE_LIMIT_SECONDS);

        $backupService->createBackup();

        Notification::make()
            ->title(__('backups.backup_started'))
            ->success()
            ->send();
    }

    public function render(BackupService $backupService): View
    {
        return view('livewire.admin.backup-manager', [
            'backups' => $backupService->listBackups(),
        ]);
    }
}
