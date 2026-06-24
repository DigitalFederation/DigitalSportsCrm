<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BackupService;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupController extends Controller
{
    public function __construct(
        protected BackupService $backupService
    ) {}

    /**
     * Display the backup management page.
     */
    public function index(): View
    {
        $stats = $this->backupService->getStats();

        return view('web.admin.backups.index', compact('stats'));
    }

    /**
     * Download a backup file.
     */
    public function download(string $filename): StreamedResponse
    {
        return $this->backupService->downloadBackup($filename);
    }
}
