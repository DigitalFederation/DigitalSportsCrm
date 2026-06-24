<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OfficialDocumentTypeEnum;
use App\Http\Controllers\Controller;
use Domain\Federations\Models\Federation;
use Domain\OfficialDocuments\Actions\ActivateOfficialDocumentAction;
use Domain\OfficialDocuments\Models\OfficialDocument;
use Domain\OfficialDocuments\States\RejectedOfficialDocumentState;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class OfficialDocumentsController extends Controller
{
    /**
     * list of official documents
     */
    public function index($type): View
    {
        // Sanitize type parameter (strip any surrounding quotes from URL encoding)
        $type = trim($type, '"\'');

        if (! in_array($type, ['federation', 'individual', 'entity'])) {
            abort(404);
        }

        // Build base query first
        $query = OfficialDocument::with(['media', 'federation', 'individual'])->latest();

        // Apply type-specific filtering first
        if ($type == 'federation') {
            $query->whereNotNull('federation_id')->whereNull('individual_id');

            $selectedEnumValues = [
                OfficialDocumentTypeEnum::Statutes,
                OfficialDocumentTypeEnum::GovernmentNOCRecognition,
                OfficialDocumentTypeEnum::FederationRepresentatives,
                OfficialDocumentTypeEnum::OtherDocument,
            ];
            $types = OfficialDocumentTypeEnum::toSortedArray($selectedEnumValues);
        } elseif ($type == 'individual') {
            $query->whereNotNull('individual_id')->whereNotNull('federation_id');

            $types = OfficialDocumentTypeEnum::toSortedArray(OfficialDocumentTypeEnum::individualTypes());
        } elseif ($type == 'entity') {
            $query->where('owner_type', 'entity')
                ->with('owner'); // Use the polymorphic relationship

            $selectedEnumValues = [
                OfficialDocumentTypeEnum::EntityStatutes,
                OfficialDocumentTypeEnum::EntityAccidentInsurance,
                OfficialDocumentTypeEnum::EntityLegalPersonality,
                OfficialDocumentTypeEnum::TechnicalDirectorAcceptance,
                OfficialDocumentTypeEnum::LegalRepresentativeDocument,
            ];
            $types = OfficialDocumentTypeEnum::toSortedArray($selectedEnumValues);
        }

        // Now apply QueryBuilder filters
        $official_documents = QueryBuilder::for($query)
            ->allowedFilters([
                AllowedFilter::scope('filter_type'),
                AllowedFilter::scope('filter_status'),
                AllowedFilter::scope('filter_member_number'),
                AllowedFilter::scope('filter_name'),
                AllowedFilter::scope('filter_surname'),
                AllowedFilter::scope('filter_entity_name'),
                AllowedFilter::scope('filter_entity_member_number'),
            ]);

        $documents = $official_documents->paginate()
            ->appends(request()->query());

        $status = [
            'pending' => __('official_documents.status_pending'),
            'active' => __('official_documents.status_active'),
            'rejected' => __('official_documents.status_rejected'),
            'expired' => __('official_documents.status_expired'),
        ];

        return view('web.admin.official_documents.index', [
            'official_documents' => $documents,
            'status' => $status,
            'type' => $type,
            'types' => $types,
        ]);
    }

    public function activate(string $id, Request $request, ActivateOfficialDocumentAction $activateDocument): RedirectResponse
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'expire_date' => 'nullable|date|after:start_date',
        ]);

        $document = OfficialDocument::with('individual.user', 'federation')->findOrFail($id);

        try {
            DB::beginTransaction();
            $activateDocument($document, $validated['expire_date'], $validated['start_date'] ?? null);
            DB::commit();
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage());

            return redirect()->back()->with('error', $ex->getMessage());
        }

        return redirect()->back()->with('success', __('official_documents.activated_success'));
    }

    public function edit(string $id): View
    {
        $document = OfficialDocument::with(['federation', 'individual', 'owner', 'country', 'media'])->findOrFail($id);

        // Determine document type
        $documentType = 'federation';
        if ($document->individual_id) {
            $documentType = 'individual';
        } elseif ($document->owner_type === 'entity') {
            $documentType = 'entity';
        }

        // Get appropriate document types based on the document category
        if ($documentType === 'federation') {
            $selectedEnumValues = [
                OfficialDocumentTypeEnum::Statutes,
                OfficialDocumentTypeEnum::GovernmentNOCRecognition,
                OfficialDocumentTypeEnum::FederationRepresentatives,
                OfficialDocumentTypeEnum::OtherDocument,
            ];
        } elseif ($documentType === 'individual') {
            $selectedEnumValues = OfficialDocumentTypeEnum::individualTypes();
        } else { // entity
            $selectedEnumValues = [
                OfficialDocumentTypeEnum::EntityStatutes,
                OfficialDocumentTypeEnum::EntityAccidentInsurance,
                OfficialDocumentTypeEnum::EntityLegalPersonality,
                OfficialDocumentTypeEnum::TechnicalDirectorAcceptance,
                OfficialDocumentTypeEnum::LegalRepresentativeDocument,
            ];
        }

        $types = OfficialDocumentTypeEnum::toSortedArray($selectedEnumValues);

        // Get federations for dropdown
        $federations = Federation::query()
            ->orderBy('member_code')
            ->pluck('member_code', 'id')
            ->toArray();

        // Get countries for dropdown
        $countries = \App\Models\Country::query()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();

        return view('web.admin.official_documents.edit', [
            'document' => $document,
            'documentType' => $documentType,
            'types' => $types,
            'federations' => $federations,
            'countries' => $countries,
        ]);
    }

    public function update(string $id, Request $request): RedirectResponse
    {
        $document = OfficialDocument::findOrFail($id);

        // Determine if this is an entity document
        $isEntityDocument = $document->owner_type === 'entity';

        $rules = [
            'name' => 'required|string|max:255',
            'type' => 'required|in:' . implode(',', array_column(\App\Enums\OfficialDocumentTypeEnum::cases(), 'value')),
            'country_id' => 'nullable|exists:country,id',
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after_or_equal:issue_date',
        ];

        // Only validate federation_id for non-entity documents
        if (! $isEntityDocument) {
            $rules['federation_id'] = 'nullable|exists:federation,id';
        }

        $validated = $request->validate($rules);

        // For entity documents, always use the main federation
        if ($isEntityDocument) {
            $mainFederation = Federation::where('is_default_federation', true)->first();
            if ($mainFederation) {
                $validated['federation_id'] = $mainFederation->id;
            }
        }

        try {
            DB::beginTransaction();
            $document->update($validated);
            DB::commit();

            return redirect()->route('admin.official-documents.index', $request->get('document_type', 'entity'))
                ->with('success', __('official_documents.updated_success'));
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage());

            return redirect()->back()->with('error', $ex->getMessage());
        }
    }

    public function reject(
        string $id,
        Request $request
    ): RedirectResponse {

        // Only if the document is in pending status
        $document = OfficialDocument::with('individual.user', 'federation')->findOrFail($id);
        if (! $document->state->isPending()) {
            return redirect()->back()->with('error', __('official_documents.not_pending_status'));
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

        return redirect()->back()->with('success', __('official_documents.rejected_success'));
    }

    public function destroy(string $id): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $official_document = OfficialDocument::with('media')->findOrFail($id);
            $official_document->media->each->delete();
            $official_document->delete();
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', __('official_documents.deleted_success'));
    }

    public function download(string $id)
    {
        $officialDocument = OfficialDocument::with('media', 'individual', 'federation')
            ->findOrFail($id);

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

    /**
     * Preview an official document inline (for PDFs and images)
     */
    public function preview(string $id)
    {
        $officialDocument = OfficialDocument::with('media')->findOrFail($id);

        $mediaItem = $officialDocument->media()->first();

        if (! $mediaItem instanceof Media) {
            return response(__('official_documents.file_not_found'), 404)
                ->header('Content-Type', 'text/plain');
        }

        $disk = Storage::disk($mediaItem->disk);
        $path = $mediaItem->getPathRelativeToRoot();

        if (! $disk->exists($path)) {
            return response(__('official_documents.file_not_found'), 404)
                ->header('Content-Type', 'text/plain');
        }

        $mimeType = $mediaItem->mime_type;
        $content = $disk->get($path);

        return response($content, 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $mediaItem->file_name . '"',
        ]);
    }

}
