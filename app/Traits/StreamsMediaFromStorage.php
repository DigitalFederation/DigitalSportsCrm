<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Trait for streaming/downloading media files from any storage disk (local or cloud).
 *
 * This trait provides methods that work with both local filesystems and cloud storage
 * like Cloudflare R2 or AWS S3, unlike direct file operations (file_exists, response()->download)
 * which only work with local files.
 */
trait StreamsMediaFromStorage
{
    /**
     * Stream a media file as a download response.
     * Works with both local and cloud storage.
     */
    protected function streamMediaDownload(Media $media, ?string $downloadFilename = null): StreamedResponse
    {
        $disk = Storage::disk($media->disk);
        $path = $media->getPathRelativeToRoot();

        if (! $disk->exists($path)) {
            abort(404, 'File not found');
        }

        $filename = $downloadFilename ?? $media->file_name;

        return response()->streamDownload(
            function () use ($disk, $path) {
                $stream = $disk->readStream($path);
                fpassthru($stream);
                if (is_resource($stream)) {
                    fclose($stream);
                }
            },
            $filename,
            [
                'Content-Type' => $media->mime_type,
                'Content-Length' => $media->size,
            ]
        );
    }

    /**
     * Stream a media file inline (for viewing in browser).
     * Works with both local and cloud storage.
     */
    protected function streamMediaInline(Media $media, string $conversionName = ''): StreamedResponse
    {
        $disk = Storage::disk($media->disk);

        if ($conversionName && $media->hasGeneratedConversion($conversionName)) {
            $path = $media->getPathRelativeToRoot($conversionName);
        } else {
            $path = $media->getPathRelativeToRoot();
        }

        if (! $disk->exists($path)) {
            if ($conversionName) {
                $path = $media->getPathRelativeToRoot();
                if (! $disk->exists($path)) {
                    abort(404, 'File not found');
                }
            } else {
                abort(404, 'File not found');
            }
        }

        $etag = '"' . md5($media->id . '-' . $media->updated_at->timestamp) . '"';

        return response()->stream(
            function () use ($disk, $path) {
                $stream = $disk->readStream($path);
                fpassthru($stream);
                if (is_resource($stream)) {
                    fclose($stream);
                }
            },
            200,
            [
                'Content-Type' => $media->mime_type,
                'Content-Disposition' => 'inline; filename="' . $media->file_name . '"',
                'Cache-Control' => 'private, max-age=86400',
                'ETag' => $etag,
            ]
        );
    }

    /**
     * Check if a media file exists on its disk.
     * Works with both local and cloud storage.
     */
    protected function mediaFileExists(Media $media): bool
    {
        $disk = Storage::disk($media->disk);

        return $disk->exists($media->getPathRelativeToRoot());
    }

    /**
     * Get the contents of a media file.
     * Works with both local and cloud storage.
     *
     * WARNING: Use with caution for large files as this loads entire file into memory.
     */
    protected function getMediaContents(Media $media): ?string
    {
        $disk = Storage::disk($media->disk);
        $path = $media->getPathRelativeToRoot();

        if (! $disk->exists($path)) {
            return null;
        }

        return $disk->get($path);
    }

    /**
     * Get a temporary local path for a media file.
     * Useful when you need a local file path (e.g., for ZipArchive).
     * Remember to delete the temp file after use!
     */
    protected function getMediaTempPath(Media $media): ?string
    {
        $disk = Storage::disk($media->disk);
        $path = $media->getPathRelativeToRoot();

        if (! $disk->exists($path)) {
            return null;
        }

        $tempPath = sys_get_temp_dir() . '/' . uniqid('media_') . '_' . $media->file_name;

        $stream = $disk->readStream($path);
        $tempFile = fopen($tempPath, 'w');
        stream_copy_to_stream($stream, $tempFile);
        fclose($tempFile);
        if (is_resource($stream)) {
            fclose($stream);
        }

        return $tempPath;
    }

    /**
     * Build a proper download filename from media.
     */
    protected function buildDownloadFilename(Media $media): string
    {
        $originalFilename = pathinfo($media->name, PATHINFO_FILENAME);
        $extension = pathinfo($media->file_name, PATHINFO_EXTENSION);

        return $originalFilename . '.' . $extension;
    }
}
