<?php

declare(strict_types=1);

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupService
{
    protected string $disk = 'r2-backups';

    /**
     * List all backups sorted newest-first.
     *
     * @return Collection<int, array{name: string, size: int, date: Carbon}>
     */
    public function listBackups(): Collection
    {
        $disk = Storage::disk($this->disk);
        $files = $disk->files('', true);

        return collect($files)
            ->filter(fn (string $file) => str_ends_with($file, '.zip'))
            ->map(fn (string $file) => [
                'name' => basename($file),
                'path' => $file,
                'size' => $disk->size($file),
                'date' => Carbon::createFromTimestamp($disk->lastModified($file)),
            ])
            ->sortByDesc('date')
            ->values();
    }

    /**
     * Queue a new database-only backup.
     */
    public function createBackup(): void
    {
        dispatch(function () {
            \Artisan::call('backup:run', ['--only-db' => true]);
        })->onQueue('default');
    }

    /**
     * Stream a backup file for download.
     */
    public function downloadBackup(string $filename): StreamedResponse
    {
        $disk = Storage::disk($this->disk);

        $path = $this->findBackupPath($filename);

        if (! $path || ! $disk->exists($path)) {
            abort(404, __('backups.file_not_found'));
        }

        return $disk->download($path, $filename);
    }

    /**
     * Get backup statistics.
     *
     * @return array{total_count: int, total_size: int, last_backup: ?Carbon}
     */
    public function getStats(): array
    {
        $backups = $this->listBackups();

        return [
            'total_count' => $backups->count(),
            'total_size' => $backups->sum('size'),
            'last_backup' => $backups->first()['date'] ?? null,
        ];
    }

    /**
     * Find the full path of a backup file by filename.
     */
    protected function findBackupPath(string $filename): ?string
    {
        $disk = Storage::disk($this->disk);
        $files = $disk->files('', true);

        return collect($files)
            ->first(fn (string $file) => basename($file) === $filename);
    }
}
