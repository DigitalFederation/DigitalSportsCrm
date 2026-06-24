<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventApplications\UploadDocumentRequest;
use Domain\EventApplications\Actions\UploadApplicationDocumentAction;
use Domain\EventApplications\Models\ApplicationDocument;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApplicationDocumentController extends Controller
{
    public function upload(
        UploadDocumentRequest $request,
        UploadApplicationDocumentAction $action
    ): RedirectResponse {
        try {
            $document = $action->execute([
                'application_id' => $request->input('application_id'),
                'file' => $request->file('file'),
                'document_type' => $request->input('document_type'),
            ]);

            return back()->with('success', __('event_applications.document_uploaded_success'));
        } catch (Exception $ex) {
            Log::error('Error uploading document: '.$ex->getMessage(), [
                'exception' => $ex,
                'application_id' => $request->input('application_id'),
            ]);

            return back()->with('error', __('event_applications.document_uploaded_error'));
        }
    }

    public function download(ApplicationDocument $document): StreamedResponse|RedirectResponse
    {
        $this->authorize('view', $document);

        try {
            $disk = Storage::disk(ApplicationDocument::STORAGE_DISK);

            if (! $document->file_path || ! $disk->exists($document->file_path)) {
                return back()->with('error', __('event_applications.document_not_found'));
            }

            return $disk->download($document->file_path, $document->file_name);
        } catch (Exception $ex) {
            Log::error('Error downloading document: '.$ex->getMessage(), [
                'exception' => $ex,
                'document_id' => $document->id,
            ]);

            return back()->with('error', __('event_applications.document_download_error'));
        }
    }

    public function destroy(ApplicationDocument $document): RedirectResponse
    {
        $this->authorize('delete', $document);

        try {
            $disk = Storage::disk(ApplicationDocument::STORAGE_DISK);

            if ($document->file_path && $disk->exists($document->file_path)) {
                $disk->delete($document->file_path);
            }

            $document->delete();

            return back()->with('success', __('event_applications.document_deleted_success'));
        } catch (Exception $ex) {
            Log::error('Error deleting document: '.$ex->getMessage(), [
                'exception' => $ex,
                'document_id' => $document->id,
            ]);

            return back()->with('error', __('event_applications.document_deleted_error'));
        }
    }
}
