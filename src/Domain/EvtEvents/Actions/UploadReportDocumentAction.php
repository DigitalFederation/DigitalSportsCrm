<?php

namespace Domain\EvtEvents\Actions;

use Domain\EvtEvents\Models\EventReportDocument;
use Domain\EvtEvents\Models\ChiefJudgeReport;
use Domain\EvtEvents\Models\TechnicalDelegateReport;
use Domain\Individuals\Models\Individual;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UploadReportDocumentAction
{
    /**
     * Upload a document to a report (TD or CJ)
     *
     * @param  TechnicalDelegateReport|ChiefJudgeReport  $report
     */
    public function execute(TechnicalDelegateReport|ChiefJudgeReport $report, UploadedFile $file, Individual $uploadedBy): EventReportDocument
    {
        DB::beginTransaction();

        try {
            $fileName = $file->getClientOriginalName();
            $path = (string) $file->store('report-documents/' . $report->event_id, 'local');

            $document = EventReportDocument::create([
                'documentable_type' => get_class($report),
                'documentable_id' => $report->id,
                'file_name' => $fileName,
                'file_path' => $path,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'uploaded_by' => $uploadedBy->id,
            ]);

            DB::commit();

            Log::info('Report document uploaded', [
                'report_type' => get_class($report),
                'report_id' => $report->id,
                'document_id' => $document->id,
                'file_name' => $fileName,
            ]);

            return $document;
        } catch (\Exception $e) {
            DB::rollBack();
            // Clean up the file if it was uploaded
            Storage::disk('local')->delete($path);
            Log::error('Failed to upload report document', [
                'report_id' => $report->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Delete a report document
     */
    public function delete(EventReportDocument $document): void
    {
        DB::beginTransaction();

        try {
            $path = $document->file_path;
            $document->delete();

            // Delete the actual file
            Storage::disk('local')->delete($path);

            DB::commit();

            Log::info('Report document deleted', [
                'document_id' => $document->id,
                'file_name' => $document->file_name,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete report document', [
                'document_id' => $document->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
