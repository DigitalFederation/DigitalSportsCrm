<?php

namespace App\Http\Controllers\Entity;

use App\Enums\OfficialDocumentTypeEnum;
use App\Http\Controllers\Controller;
use Domain\OfficialDocuments\Models\OfficialDocument;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class OfficialDocumentsController extends Controller
{
    /**
     * List of official documents for the entity
     */
    public function index(): View
    {
        // Get the current entity from user session
        $entity = auth()->user()->entities()->first();

        if (! $entity) {
            abort(403, 'No entity associated with this user');
        }

        // Get federations associated with the entity
        $federations = $entity->federations;

        $entityDocumentTypes = OfficialDocumentTypeEnum::sortedByTranslation([
            OfficialDocumentTypeEnum::EntityStatutes,
            OfficialDocumentTypeEnum::EntityAccidentInsurance,
            OfficialDocumentTypeEnum::EntityLegalPersonality,
            OfficialDocumentTypeEnum::EntityLiabilityInsurance,
            OfficialDocumentTypeEnum::EntityInaugurationMinutes,
            OfficialDocumentTypeEnum::EntityOther,
        ]);

        // Get official documents for this entity
        $official_documents = OfficialDocument::where('owner_type', 'entity')
            ->where('owner_id', $entity->id)
            ->with('media', 'country', 'federation')
            ->latest()
            ->get();

        $files = $official_documents->map(function ($official_document) {
            return $official_document->getMedia('media');
        });

        return view('web.entity.official_documents.index', [
            'entity' => $entity,
            'official_documents' => $official_documents,
            'official_document_types' => $entityDocumentTypes,
            'files' => $files,
            'federations' => $federations,
        ]);
    }

    /**
     * Download an official document
     */
    public function download(string $document): RedirectResponse|\Symfony\Component\HttpFoundation\StreamedResponse
    {
        $officialDocument = OfficialDocument::with('media')->findOrFail($document);
        $user = Auth::user();
        $entity = $user->entities()->first();

        // Check if the user's entity is the owner of the document
        if (
            $officialDocument->owner_type === 'entity' &&
            (string) $officialDocument->owner_id === (string) $entity->id
        ) {

            $mediaItem = $officialDocument->media()->first();

            if (! $mediaItem instanceof Media) {
                return back()->with('error', __('official_documents.file_not_found'));
            }

            $disk = Storage::disk($mediaItem->disk);
            $path = $mediaItem->getPathRelativeToRoot();

            if (! $disk->exists($path)) {
                return back()->with('error', __('official_documents.file_not_found'));
            }

            return response()->streamDownload(function () use ($disk, $path) {
                echo $disk->get($path);
            }, $mediaItem->file_name, [
                'Content-Type' => $mediaItem->mime_type,
            ]);
        }

        abort(403, __('official_documents.unauthorized_access'));
    }

    /**
     * Delete an official document
     */
    public function destroy(string $document): RedirectResponse
    {
        $officialDocument = OfficialDocument::with('media')->findOrFail($document);
        $user = Auth::user();
        $entity = $user->entities()->first();

        // Check if the user's entity is the owner of the document
        if (
            $officialDocument->owner_type !== 'entity' ||
            (string) $officialDocument->owner_id !== (string) $entity->id
        ) {
            return back()->with('error', __('official_documents.unauthorized_delete'));
        }

        try {
            DB::beginTransaction();
            $officialDocument->media->each->delete();
            $officialDocument->delete();
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', __('official_documents.deleted_success'));
    }
}
