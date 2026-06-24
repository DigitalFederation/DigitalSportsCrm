<?php

namespace App\Http\Controllers\Federation;

use App\Enums\OfficialDocumentTypeEnum;
use App\Http\Controllers\Controller;
use App\Notifications\OfficialDocumentActivatedNotification;
use App\Notifications\OfficialDocumentDeletedNotification;
use Exception;
use Domain\Individuals\Models\Individual;
use Domain\OfficialDocuments\Models\OfficialDocument;
use Domain\OfficialDocuments\States\ActiveOfficialDocumentState;
use Domain\OfficialDocuments\States\PendingOfficialDocumentState;
use Domain\OfficialDocuments\States\RejectedOfficialDocumentState;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class OfficialDocumentsFromIndividualController extends Controller
{
    /**
     * list of official documents
     */
    public function index(Request $request): View
    {
        $federation = auth()->user()->federations()->first();

        $official_documents = QueryBuilder::for(OfficialDocument::class)
            ->allowedFilters([
                AllowedFilter::scope('committee'),
                AllowedFilter::scope('filter_type'),
                AllowedFilter::scope('filter_status'),
            ])
            ->with(['media', 'individual', 'federation'])
            ->whereNotNull('individual_id');

        // If is a local federation, show only the documents of the federation
        if ($federation->is_local) {
            $official_documents->where('federation_id', $federation->id);
        } else {
            $official_documents->where('country_id', $federation->country_id);
        }
        // TODO: We use this to show the documents of the federation but we should use the filter above ??
        $official_documents->where('federation_id', $federation->id);

        $documents = $official_documents->paginate()
            ->appends(request()->query());

        $committee = isset(request()->query('filter')['committee']) ? request()->query('filter')['committee'] : null;

        // For filtering the Document Type
        $official_documents_types = OfficialDocumentTypeEnum::getKeysByCommittee(strtoupper($committee));
        $types = array_combine(
            $official_documents_types,
            array_map(
                fn ($key) => OfficialDocumentTypeEnum::toString(OfficialDocumentTypeEnum::from($key)),
                $official_documents_types
            )
        );

        $status = [
            'pending' => 'Pending',
            'active' => 'Active',
            'rejected' => 'Rejected',
            'expired' => 'Expired',
        ];

        return view('web.federation.official_documents.individual.index', compact('documents', 'committee', 'types', 'status'));
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

        if (
            $document->federation_id != $federation->id &&
            $document->country_id != $federation->country_id
        ) {
            return redirect()->back()->with('error', __('official_documents.unauthorized_activate'));
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
                ->log('Official document was approved:' . $document->name);

            if (! empty($document->individual->user)) {
                $document->individual->user->notify(new OfficialDocumentActivatedNotification($document));
            }

            DB::commit();
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage());

            return redirect()->back()->with('error', $ex->getMessage());
        }

        return redirect()->back()->with('success', __('official_documents.activated_success'));
    }

    public function reject(
        string $id,
        Request $request
    ): RedirectResponse {

        $federation = auth()->user()->federations()->first();

        // Only if the document is in pending status
        $document = OfficialDocument::with('individual.user')->findOrFail($id);

        // Security: Verify document belongs to this federation
        if (
            $document->federation_id !== $federation->id &&
            $document->country_id !== $federation->country_id
        ) {
            return redirect()->back()->with('error', __('official_documents.unauthorized_reject'));
        }

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

    /**
     * Download the official document
     */
    public function download(string $id)
    {
        $officialDocument = OfficialDocument::with('media', 'individual', 'federation')->findOrFail($id);
        $user = Auth::user();

        // Check if the federation is the owner of the document
        if (
            $officialDocument->federation_id == $user->federations()->first()->id ||
            $officialDocument->country_id == $user->federations()->first()->country_id
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

            // Get the original filename without extension
            $originalFilename = pathinfo($mediaItem->name, PATHINFO_FILENAME);
            // Get the stored file extension
            $extension = pathinfo($mediaItem->file_name, PATHINFO_EXTENSION);
            // Combine them
            $downloadFilename = $originalFilename . '.' . $extension;

            return response()->streamDownload(function () use ($disk, $path) {
                echo $disk->get($path);
            }, $downloadFilename, [
                'Content-Type' => $mediaItem->mime_type,
            ]);
        }

        abort(403, __('official_documents.unauthorized_access'));
    }

    public function destroy(string $id): RedirectResponse
    {
        try {
            DB::beginTransaction();

            // Load the document with its relationships
            $officialDocument = OfficialDocument::with(['media', 'individual.user'])->findOrFail($id);
            $federation = auth()->user()->federations()->first();

            // Simplified authorization check
            $hasAccess = $federation->is_local
                ? $officialDocument->federation_id === $federation->id
                : $officialDocument->country_id === $federation->country_id;

            if (! $hasAccess) {
                DB::rollBack();

                return redirect()->back()->with('error', __('official_documents.unauthorized_delete'));
            }

            // Store all necessary data before deletion
            $documentData = [
                'id' => $officialDocument->id,
                'type' => $officialDocument->type->value,
                'individual_id' => $officialDocument->individual_id,
                'federation_id' => $officialDocument->federation_id,
                'country_id' => $officialDocument->country_id,
            ];
            $individualUser = $officialDocument->individual?->user;

            // 1. First detach/delete media files
            $mediaItems = $officialDocument->media()->get();
            foreach ($mediaItems as $mediaItem) {
                if (! $mediaItem instanceof Media) {
                    continue;
                }

                try {
                    $mediaItem->delete();
                } catch (Exception $e) {
                    Log::warning("Failed to delete media item {$mediaItem->id}: " . $e->getMessage());
                }
            }

            // 2. Delete the document itself
            $officialDocument->delete();

            DB::commit();

            // 3. Log the activity using stored data
            activity()
                ->causedBy(auth()->user())
                ->event('deleted')
                ->withProperties($documentData)
                ->log('Official document ' . $documentData['type'] . ' deleted');

            // 4. Send notification with stored data if we have a user to notify
            if ($individualUser) {
                $individualUser->notify(
                    new OfficialDocumentDeletedNotification($documentData, 'federation')
                );
            }

            return redirect()->back()->with('success', __('official_documents.deleted_success'));
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error deleting official document: ' . $e->getMessage());

            return redirect()->back()->with('error', __('official_documents.error_deleting', ['message' => $e->getMessage()]));
        }
    }

    public function create(): View
    {
        $federation = auth()->user()->federations()->first();

        // Cache individuals for 1 hour with additional fields
        $individuals = Cache::remember('federation_individuals_' . $federation->id, 3600, function () use ($federation) {
            return Individual::whereHas('federations', function ($query) use ($federation) {
                $query->where('federation.id', $federation->id);
            })
                ->select('id', 'member_code', 'birthdate', 'gender', 'name', 'surname')
                ->get()
                ->map(function ($individual) {
                    return [
                        'id' => $individual->id,
                        'display_name' => sprintf(
                            '%s | %s | %s | %s %s',
                            $individual->member_code ?? 'N/A',
                            $individual->birthdate ? \Carbon\Carbon::parse($individual->birthdate)->format('Y-m-d') : 'N/A',
                            $individual->gender ?? 'N/A',
                            $individual->name,
                            $individual->surname
                        ),
                    ];
                });
        });

        // Get document types with proper labels
        $documentTypes = collect(OfficialDocumentTypeEnum::cases())
            ->filter(function ($type) {
                // Exclude federation-specific document types
                return ! in_array($type, [
                    OfficialDocumentTypeEnum::Statutes,
                    OfficialDocumentTypeEnum::GovernmentNOCRecognition,
                    OfficialDocumentTypeEnum::FederationRepresentatives,
                    OfficialDocumentTypeEnum::OtherDocument,
                ]);
            })
            ->mapWithKeys(function ($type) {
                return [$type->value => OfficialDocumentTypeEnum::toString($type)];
            });

        return view('web.federation.official_documents.individual.create', [
            'individuals' => $individuals,
            'documentTypes' => $documentTypes,
            'federation' => $federation,
        ]);
    }

    /**
     * Update document dates
     */
    public function updateDates(string $id): RedirectResponse
    {
        $officialDocument = OfficialDocument::findOrFail($id);
        $user = Auth::user();

        if ($officialDocument->federation_id != $user->federations()->first()->id) {
            return back()->with('error', __('official_documents.unauthorized_update'));
        }

        $validated = request()->validate([
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after:issue_date',
        ]);

        try {
            DB::beginTransaction();

            // Check if the document is in pending state and issue date is being updated
            if (
                $officialDocument->status_class === PendingOfficialDocumentState::class &&
                isset($validated['issue_date']) &&
                $validated['issue_date']
            ) {
                // Update with dates and set to active
                $officialDocument->update(array_merge($validated, [
                    'status_class' => ActiveOfficialDocumentState::class,
                    'activated_at' => now(),
                ]));

                // Notify the user if available
                if (! empty($officialDocument->individual->user)) {
                    $officialDocument->individual->user->notify(
                        new OfficialDocumentActivatedNotification($officialDocument)
                    );
                }
            } else {
                // Only update dates without changing status
                $officialDocument->update($validated);
            }

            DB::commit();

            activity()
                ->causedBy($user)
                ->performedOn($officialDocument)
                ->log('Official document dates updated');
        } catch (Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', __('official_documents.dates_updated_success'));
    }
}
