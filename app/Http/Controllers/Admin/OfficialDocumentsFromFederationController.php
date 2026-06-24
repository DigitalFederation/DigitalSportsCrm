<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OfficialDocumentTypeEnum;
use App\Http\Controllers\Controller;
use App\Notifications\OfficialDocumentActivatedNotification;
use App\Notifications\OfficialDocumentDeletedNotification;
use Domain\OfficialDocuments\Models\OfficialDocument;
use Domain\OfficialDocuments\States\ActiveOfficialDocumentState;
use Domain\OfficialDocuments\States\RejectedOfficialDocumentState;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class OfficialDocumentsFromFederationController extends Controller
{
    public function index(): View
    {
        $officialDocumentTypes = array_keys(OfficialDocumentTypeEnum::toSortedArray([
            OfficialDocumentTypeEnum::Statutes,
            OfficialDocumentTypeEnum::GovernmentNOCRecognition,
            OfficialDocumentTypeEnum::FederationRepresentatives,
            OfficialDocumentTypeEnum::OtherDocument,
        ]));

        $official_documents = QueryBuilder::for(OfficialDocument::class)
            ->allowedFilters([
                AllowedFilter::scope('filter_type'),
                AllowedFilter::scope('filter_status'),
            ])
            ->with(['media', 'federation'])
            ->whereNotNull('federation_id')
            ->whereNull('individual_id')
            ->paginate()
            ->appends(request()->query());

        $status = [
            'pending' => 'Pending',
            'active' => 'Active',
            'rejected' => 'Rejected',
            'expired' => 'Expired',
        ];

        return view('web.admin.official_documents.index', [
            'official_document_types' => $officialDocumentTypes,
            'documents' => $official_documents,
            'status' => $status,
        ]);

    }

    public function activate(
        string $id,
        Request $request
    ): RedirectResponse {

        $federation = auth()->user()->federations()->first();

        $validated = $request->validate([
            'expire_date' => 'required|date',
        ]);

        $expireDate = $validated['expire_date'];
        $document = OfficialDocument::with('individual.user')->findOrFail($id);

        if ($document->federation_id != $federation->id ||
            $document->country_id != $federation->country_id) {
            return redirect()->back()->with('error', 'Unauthorized to activate this document.');
        }

        try {
            DB::beginTransaction();

            $document->update([
                'status_class' => ActiveOfficialDocumentState::class,
                'activated_at' => now(),
                'expiry_date' => $expireDate,
            ]);

            activity('Official Document')
                ->performedOn($document)
                ->event('approved')
                ->withProperties($document->toArray())
                ->log('Official document was approved:'.$document->name);

            $document->individual->user->notify(new OfficialDocumentActivatedNotification($document));

            DB::commit();
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage());

            return redirect()->back()->with('error', $ex->getMessage());
        }

        return redirect()->back()->with('success', 'Official Document activated with success.');
    }

    public function reject(
        string $id,
        Request $request
    ): RedirectResponse {

        // Only if the document is in pending status
        $document = OfficialDocument::with('individual.user')->findOrFail($id);
        if (! $document->state->isPending()) {
            return redirect()->back()->with('error', 'The document is not in pending status.');
        }

        try {
            DB::beginTransaction();
            $document->update([
                'status_class' => RejectedOfficialDocumentState::class,
            ]);
            DB::commit();
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage());

            return redirect()->back()->with('error', $ex->getMessage());
        }

        return redirect()->back()->with('success', 'Official Document rejected with success.');
    }

    /**
     * Download the official document
     */
    public function download(string $id)
    {
        $officialDocument = OfficialDocument::with('media', 'individual', 'federation')->findOrFail($id);
        $user = Auth::user();

        // Check if the user is the owner of the document or has the international role or belongs to the federation
        if ($officialDocument->federation_id == $user->federations()->first()->id) {
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
                ->log('Official document '.OfficialDocumentTypeEnum::toString($docType).' deleted');

            // Send notification to the individual
            $individualUser->notify(new OfficialDocumentDeletedNotification($officialDocument, 'federation'));

        } catch (Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'Official document deleted successfully');
    }
}
