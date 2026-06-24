<?php

namespace App\Http\Controllers\Concerns;

use Barryvdh\DomPDF\Facade\Pdf;
use Domain\EventApplications\Actions\ApproveApplicationAction;
use Domain\EventApplications\Actions\PublishApplicationAction;
use Domain\EventApplications\Actions\RejectApplicationAction;
use Domain\EventApplications\Actions\ReturnForCorrectionAction;
use Domain\EventApplications\Actions\ValidateApplicationAction;
use Domain\EventApplications\Models\ApplicationComment;
use Domain\EventApplications\Models\ApplicationDocument;
use Domain\EventApplications\Models\EventApplication;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

trait ManagesEventApplications
{
    public function validateApplication(
        EventApplication $application,
        ValidateApplicationAction $action,
        Request $request
    ): RedirectResponse {
        try {
            $action->execute($application, (string) auth()->id(), $request->input('notes'));

            return back()->with('success', __('event_applications.application_validated_success'));
        } catch (Exception $ex) {
            Log::error('Error validating application: '.$ex->getMessage(), [
                'exception' => $ex,
                'application_id' => $application->id,
            ]);

            return back()->with('error', __('event_applications.application_validated_error'));
        }
    }

    public function returnForCorrection(
        EventApplication $application,
        ReturnForCorrectionAction $action,
        Request $request
    ): RedirectResponse {
        $request->validate([
            'notes' => 'required|string|max:1000',
        ]);

        try {
            $action->execute($application, (string) auth()->id(), $request->input('notes'));

            return back()->with('success', __('event_applications.application_returned_success'));
        } catch (Exception $ex) {
            Log::error('Error returning application: '.$ex->getMessage(), [
                'exception' => $ex,
                'application_id' => $application->id,
            ]);

            return back()->with('error', __('event_applications.application_returned_error'));
        }
    }

    public function approve(
        EventApplication $application,
        ApproveApplicationAction $action,
        Request $request
    ): RedirectResponse {
        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $action->execute($application, (string) auth()->id(), $request->input('notes'));

            return back()->with('success', __('event_applications.application_approved_success'));
        } catch (Exception $ex) {
            Log::error('Error approving application: '.$ex->getMessage(), [
                'exception' => $ex,
                'application_id' => $application->id,
            ]);

            return back()->with('error', __('event_applications.application_approved_error'));
        }
    }

    public function reject(
        EventApplication $application,
        RejectApplicationAction $action,
        Request $request
    ): RedirectResponse {
        $request->validate([
            'notes' => 'required|string|max:1000',
        ]);

        try {
            $action->execute($application, (string) auth()->id(), $request->input('notes'));

            return back()->with('success', __('event_applications.application_rejected_success'));
        } catch (Exception $ex) {
            Log::error('Error rejecting application: '.$ex->getMessage(), [
                'exception' => $ex,
                'application_id' => $application->id,
            ]);

            return back()->with('error', __('event_applications.application_rejected_error'));
        }
    }

    public function publish(
        EventApplication $application,
        PublishApplicationAction $action,
        Request $request
    ): RedirectResponse {
        try {
            $action->execute($application, (string) auth()->id());

            return back()->with('success', __('event_applications.application_published_success'));
        } catch (Exception $ex) {
            Log::error('Error publishing application: '.$ex->getMessage(), [
                'exception' => $ex,
                'application_id' => $application->id,
            ]);

            return back()->with('error', __('event_applications.application_published_error'));
        }
    }

    public function addComment(EventApplication $application, Request $request): RedirectResponse
    {
        $request->validate([
            'comment' => 'required|string|max:1000',
            'section' => 'nullable|string|max:255',
        ]);

        try {
            $application->comments()->create([
                'user_id' => auth()->id(),
                'comment' => $request->input('comment'),
                'section' => $request->input('section'),
                'is_internal' => $request->boolean('is_internal'),
            ]);

            return back()->with('success', __('event_applications.comment_added_success'));
        } catch (Exception $ex) {
            Log::error('Error adding comment: '.$ex->getMessage(), [
                'exception' => $ex,
                'application_id' => $application->id,
            ]);

            return back()->with('error', __('event_applications.comment_added_error'));
        }
    }

    public function deleteComment(EventApplication $application, ApplicationComment $comment): RedirectResponse
    {
        abort_unless($comment->application_id === $application->id, 404);

        $comment->delete();

        return back()->with('success', __('event_applications.messages.comment_deleted'));
    }

    public function export(Request $request): StreamedResponse
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, EventApplication> $applications */
        $applications = QueryBuilder::for(EventApplication::class)
            ->allowedFilters([
                AllowedFilter::exact('status_class'),
                AllowedFilter::exact('application_type'),
                AllowedFilter::exact('sport_id'),
                AllowedFilter::exact('template_id'),
            ])
            ->with(['entity', 'sport', 'template'])
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="applications_'.date('Y-m-d').'.csv"',
        ];

        return response()->stream(function () use ($applications) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'ID',
                'Event Name',
                'Event Type',
                'Entity',
                'Sport',
                'Template',
                'Status',
                'Start Date',
                'End Date',
                'Submitted At',
            ]);

            foreach ($applications as $application) {
                fputcsv($file, [
                    $application->id,
                    $application->event_name,
                    $application->event_type,
                    $application->entity->name ?? '',
                    $application->sport->name ?? '',
                    $application->template->name ?? 'Direct Submission',
                    $application->stateName(),
                    $application->start_date?->format('Y-m-d'),
                    $application->end_date?->format('Y-m-d'),
                    $application->submitted_at?->format('Y-m-d H:i'),
                ]);
            }

            fclose($file);
        }, 200, $headers);
    }

    public function exportPdf(EventApplication $application): \Illuminate\Http\Response|RedirectResponse
    {
        try {
            $application->load(['entity', 'sport', 'template', 'district', 'documents']);

            $pdf = Pdf::loadView('pdf.event-applications.application-summary', [
                'application' => $application,
                'entity' => $application->entity,
                'generatedAt' => now(),
            ])->setPaper('a4', 'portrait');

            $filename = 'candidatura_'.$application->id.'_'.now()->format('Y-m-d_His').'.pdf';

            return $pdf->download($filename);
        } catch (Exception $e) {
            Log::error('Error exporting application PDF: '.$e->getMessage(), [
                'exception' => $e,
                'application_id' => $application->id,
            ]);

            return redirect()->back()->with('error', __('event_applications.messages.application_not_found'));
        }
    }

    public function downloadDocuments(EventApplication $application): \Symfony\Component\HttpFoundation\BinaryFileResponse|RedirectResponse
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, ApplicationDocument> $documents */
        $documents = $application->documents;

        if ($documents->isEmpty()) {
            return back()->with('error', __('event_applications.no_documents_to_download'));
        }

        $disk = Storage::disk(ApplicationDocument::STORAGE_DISK);
        $zipFileName = 'application_'.$application->id.'_documents.zip';
        $zipPath = sys_get_temp_dir().'/'.$zipFileName;

        $tempFiles = [];

        try {
            $zip = new ZipArchive;
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                return back()->with('error', __('event_applications.download_documents_error'));
            }

            $usedNames = [];

            foreach ($documents as $document) {
                if (! $document->file_path || ! $disk->exists($document->file_path)) {
                    continue;
                }

                $tempPath = sys_get_temp_dir().'/'.uniqid('appdoc_').'_'.$document->file_name;
                $stream = $disk->readStream($document->file_path);
                $tempFile = fopen($tempPath, 'w');
                stream_copy_to_stream($stream, $tempFile);
                fclose($tempFile);
                if (is_resource($stream)) {
                    fclose($stream);
                }

                $zipName = $document->file_name;
                if (isset($usedNames[$zipName])) {
                    $usedNames[$zipName]++;
                    $ext = pathinfo($zipName, PATHINFO_EXTENSION);
                    $base = pathinfo($zipName, PATHINFO_FILENAME);
                    $zipName = $base.'_'.$usedNames[$zipName].'.'.$ext;
                } else {
                    $usedNames[$zipName] = 1;
                }

                $tempFiles[] = $tempPath;
                $zip->addFile($tempPath, $zipName);
            }

            if (empty($tempFiles)) {
                $zip->close();
                @unlink($zipPath);

                return back()->with('error', __('event_applications.no_documents_to_download'));
            }

            $zip->close();

            return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
        } catch (Exception $ex) {
            Log::error('Error downloading documents: '.$ex->getMessage(), [
                'exception' => $ex,
                'application_id' => $application->id,
            ]);

            return back()->with('error', __('event_applications.download_documents_error'));
        } finally {
            foreach ($tempFiles as $tempFile) {
                if (file_exists($tempFile)) {
                    @unlink($tempFile);
                }
            }
        }
    }
}
