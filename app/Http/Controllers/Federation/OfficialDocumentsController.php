<?php

namespace App\Http\Controllers\Federation;

use App\Enums\OfficialDocumentTypeEnum;
use App\Http\Controllers\Controller;
use App\Notifications\OfficialDocumentDeletedNotification;
use Domain\OfficialDocuments\Models\OfficialDocument;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class OfficialDocumentsController extends Controller
{
    /**
     * list of official documents
     */
    public function index(): View
    {

        $officialDocumentTypes = OfficialDocumentTypeEnum::toSortedArray([
            OfficialDocumentTypeEnum::Statutes,
            OfficialDocumentTypeEnum::GovernmentNOCRecognition,
            OfficialDocumentTypeEnum::FederationRepresentatives,
            OfficialDocumentTypeEnum::OtherDocument,
        ]);

        $federation = auth()->user()->federations()->first();

        $official_documents = QueryBuilder::for(OfficialDocument::class)
            ->allowedFilters([
                AllowedFilter::scope('committee'),
                AllowedFilter::scope('filter_type'),
                AllowedFilter::scope('filter_status'),
            ])
            ->with(['media', 'individual', 'federation'])
            ->where('federation_id', $federation->id)
            ->whereNull('individual_id')
            ->paginate()
            ->appends(request()->query());

        $status = [
            'pending' => 'Pending',
            'active' => 'Active',
            'rejected' => 'Rejected',
            'expired' => 'Expired',
        ];

        return view('web.federation.official_documents.index', [
            'official_document_types' => $officialDocumentTypes,
            'documents' => $official_documents,
            'status' => $status,
            'federation' => $federation,
        ]);
    }

    /**
     * Download the official document
     */
    public function download(string $id)
    {
        $officialDocument = OfficialDocument::with('media', 'individual', 'federation')->findOrFail($id);
        $federation = Auth::user()->federations()->first();

        // Check if the user is the owner of the document or has the international role or belongs to the federation
        if ($officialDocument->federation_id == $federation->id) {
            $mediaItem = $officialDocument->media()->first();

            if (! $mediaItem instanceof Media) {
                return back()->with('error', 'File not found');
            }

            $disk = Storage::disk($mediaItem->disk);
            $path = $mediaItem->getPathRelativeToRoot();

            if (! $disk->exists($path)) {
                return back()->with('error', 'File not found');
            }

            return response()->streamDownload(function () use ($disk, $path) {
                echo $disk->get($path);
            }, $mediaItem->file_name, [
                'Content-Type' => $mediaItem->mime_type,
            ]);
        }

        abort(403, 'Unauthorized access to the file.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $officialDocument = OfficialDocument::with('media')->findOrFail($id);
        $user = Auth::user();

        if ($officialDocument->federation_id != $user->federations()->first()->id) {
            return back()->with('error', 'Unauthorized to delete this document.');
        }
        $docType = $officialDocument->type;
        $individualUser = $officialDocument->individual->user;

        try {
            DB::beginTransaction();
            $officialDocument->media->each->delete();
            $officialDocument->delete();
            DB::commit();

            activity()
                ->causedBy($user)
                ->performedOn($officialDocument)
                ->log('Official document ' . OfficialDocumentTypeEnum::toString($docType) . ' deleted');

            // Send notification to the individual
            $individualUser->notify(new OfficialDocumentDeletedNotification($officialDocument, 'federation'));
        } catch (Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'Official document deleted successfully');
    }
}
