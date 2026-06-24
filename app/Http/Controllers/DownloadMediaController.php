<?php

namespace App\Http\Controllers;

use App\Traits\StreamsMediaFromStorage;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DownloadMediaController extends Controller
{
    use StreamsMediaFromStorage;

    public function download(Request $request)
    {
        if (empty($request->id)) {
            return back()->with('error', 'No file selected');
        }

        $mediaItem = Media::find($request->id);
        if (! $mediaItem) {
            return back()->with('error', 'File not found');
        }

        return $this->streamMediaDownload($mediaItem, $this->buildDownloadFilename($mediaItem));
    }

    public function delete(Request $request)
    {
        if (empty($request->id)) {
            return back()->with('error', 'No file selected');
        }

        $mediaItem = Media::find($request->id);
        $mediaItem->delete();

        return back()->with('success', 'File deleted');
    }
}
