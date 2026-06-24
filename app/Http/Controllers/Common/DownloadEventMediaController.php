<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Traits\StreamsMediaFromStorage;
use Domain\EvtEvents\Actions\FederationAllowedToSeeAction;
use Domain\EvtEvents\Actions\GetIndividualEventsAction;
use Domain\EvtEvents\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use ZipArchive;

class DownloadEventMediaController extends Controller
{
    use StreamsMediaFromStorage;

    public function store(
        Request $request,
        GetIndividualEventsAction $getIndividualEventsAction,
        FederationAllowedToSeeAction $federationAllowedToSeeAction
    ) {
        // Handle bulk download
        if ($request->has('download_all')) {
            return $this->handleBulkDownload($request, $getIndividualEventsAction, $federationAllowedToSeeAction);
        }

        // Handle single file download
        return $this->handleSingleDownload($request, $getIndividualEventsAction, $federationAllowedToSeeAction);
    }

    protected function handleBulkDownload(
        Request $request,
        GetIndividualEventsAction $getIndividualEventsAction,
        FederationAllowedToSeeAction $federationAllowedToSeeAction
    ) {
        $event = Event::findOrFail($request->route('event'));

        // Check permissions for bulk download
        if (! Auth::user()->isAdmin()) {
            if (Auth::user()->isIndividual()) {
                $allowedEvents = $getIndividualEventsAction->execute()->pluck('id');
                if (! $allowedEvents->contains($event->id)) {
                    return back()->with('error', 'You do not have permission to download these files.');
                }
            } elseif (Auth::user()->isFederation()) {
                if (! $federationAllowedToSeeAction->execute($event)) {
                    return back()->with('error', 'You do not have permission to download these files.');
                }
            }
        }

        // Create a unique temporary zip file name
        $zipFileName = 'event_' . $event->id . '_attachments_' . Str::random(8) . '.zip';
        $zipPath = storage_path('app/temp/' . $zipFileName);

        // Ensure the temp directory exists
        if (! Storage::exists('temp')) {
            Storage::makeDirectory('temp');
        }

        $tempFiles = [];

        try {
            $zip = new ZipArchive;
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                $attachments = Media::where('model_id', $event->id)
                    ->where('collection_name', 'event-general-attachments')
                    ->get();

                foreach ($attachments as $attachment) {
                    $filename = $this->buildDownloadFilename($attachment);

                    // Get temp path for cloud-compatible file access
                    $tempPath = $this->getMediaTempPath($attachment);
                    if ($tempPath) {
                        $tempFiles[] = $tempPath;
                        $zip->addFile($tempPath, $filename);
                    }
                }

                $zip->close();

                if (file_exists($zipPath)) {
                    // Clean up temp files after sending response
                    return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true)
                        ->withCallback(function () use ($tempFiles) {
                            foreach ($tempFiles as $tempFile) {
                                if (file_exists($tempFile)) {
                                    @unlink($tempFile);
                                }
                            }
                        });
                }
            }
        } finally {
            // Clean up temp files if something went wrong
            foreach ($tempFiles as $tempFile) {
                if (file_exists($tempFile)) {
                    @unlink($tempFile);
                }
            }
        }

        return back()->with('error', 'Could not create zip file');
    }

    protected function handleSingleDownload(
        Request $request,
        GetIndividualEventsAction $getIndividualEventsAction,
        FederationAllowedToSeeAction $federationAllowedToSeeAction
    ) {
        if (empty($request->id)) {
            return back()->with('error', 'No file selected');
        }

        $mediaItem = Media::find($request->id);

        if (! $mediaItem || ! $mediaItem->model) {
            return back()->with('error', 'File not found.');
        }

        $event = $mediaItem->model;
        if (! $event instanceof Event) {
            return back()->with('error', 'File not found.');
        }

        // Check permissions for single file download
        if (! Auth::user()->isAdmin()) {
            if (Auth::user()->isIndividual()) {
                $allowedEvents = $getIndividualEventsAction->execute()->pluck('id');
                if (! $allowedEvents->contains($event->id)) {
                    return back()->with('error', 'You do not have permission to download this file.');
                }
            } elseif (Auth::user()->isFederation()) {
                if (! $federationAllowedToSeeAction->execute($event)) {
                    return back()->with('error', 'You do not have permission to download this file.');
                }
            }
        }

        return $this->streamMediaDownload($mediaItem, $this->buildDownloadFilename($mediaItem));
    }
}
