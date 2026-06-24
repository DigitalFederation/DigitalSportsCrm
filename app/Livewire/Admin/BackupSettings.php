<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\BackupSetting;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class BackupSettings extends Component
{
    public bool $backupEnabled = true;

    public string $backupFrequency = 'daily';

    public string $backupTime = '02:00';

    public int $retentionDays = 30;

    public int $maxStorageMb = 5000;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('access backups'), 403);

        $settings = BackupSetting::allValues();

        $this->backupEnabled = (bool) $settings['backup_enabled'];
        $this->backupFrequency = (string) $settings['backup_frequency'];
        $this->backupTime = (string) $settings['backup_time'];
        $this->retentionDays = (int) $settings['retention_days'];
        $this->maxStorageMb = (int) $settings['max_storage_mb'];
    }

    public function save(): void
    {
        $this->validate([
            'backupEnabled' => ['required', 'boolean'],
            'backupFrequency' => ['required', 'in:daily,twice_daily,every_six_hours,weekly'],
            'backupTime' => ['required', 'regex:/^([01]\d|2[0-3]):([0-5]\d)$/'],
            'retentionDays' => ['required', 'integer', 'min:1', 'max:365'],
            'maxStorageMb' => ['required', 'integer', 'min:100', 'max:50000'],
        ]);

        BackupSetting::setValue('backup_enabled', $this->backupEnabled);
        BackupSetting::setValue('backup_frequency', $this->backupFrequency);
        BackupSetting::setValue('backup_time', $this->backupTime);
        BackupSetting::setValue('retention_days', $this->retentionDays);
        BackupSetting::setValue('max_storage_mb', $this->maxStorageMb);

        Notification::make()
            ->title(__('backups.settings_saved'))
            ->success()
            ->send();
    }

    public function render(): View
    {
        return view('livewire.admin.backup-settings');
    }
}
