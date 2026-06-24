<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DownloadExportExcelController extends Controller
{
    /**
     * Allowed base directories for file downloads.
     * Files outside these directories will be rejected.
     */
    private const ALLOWED_DIRECTORIES = [
        'exports/',
        'livewire-tmp/',
    ];

    public function store(Request $request)
    {
        $filePath = $request->input('filePath');
        $fileName = $request->input('fileName');

        if (! $this->isPathAllowed($filePath)) {
            abort(403, 'Access denied.');
        }

        if (! Storage::exists($filePath)) {
            abort(404, 'The requested file does not exist.');
        }

        return Storage::download($filePath, $fileName);
    }

    /**
     * Validate that the file path is within allowed directories and contains no traversal sequences.
     */
    private function isPathAllowed(?string $filePath): bool
    {
        if (empty($filePath)) {
            return false;
        }

        // Reject any path traversal sequences
        if (str_contains($filePath, '..') || str_contains($filePath, "\0")) {
            return false;
        }

        // Normalize the path and check it starts with an allowed directory
        $normalizedPath = ltrim($filePath, '/\\');

        foreach (self::ALLOWED_DIRECTORIES as $allowedDir) {
            if (str_starts_with($normalizedPath, $allowedDir)) {
                return true;
            }
        }

        return false;
    }
}
