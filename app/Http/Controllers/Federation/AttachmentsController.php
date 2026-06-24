<?php

namespace App\Http\Controllers\Federation;

use App\Http\Controllers\Controller;
use App\Models\Committee;
use App\Models\Language;
use App\Traits\StreamsMediaFromStorage;
use Domain\Attachments\Actions\GetFederationAttachmentsAction;
use Domain\Attachments\Models\Attachment;
use Domain\Attachments\Models\AttachmentCategory;
use Domain\EvtEvents\Models\Event;
use Domain\Federations\Models\Federation;
use Domain\Users\Actions\GetUserTypeAction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Request as RequestFacade;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class AttachmentsController extends Controller
{
    use StreamsMediaFromStorage;
    /**
     * Display a listing of the resource.
     */
    public function index(
        Committee $committee,
        GetFederationAttachmentsAction $getAttachments,
        GetUserTypeAction $getUserType
    ): View {
        $federationId = $getUserType::execute(auth()->user())->id;
        $cacheKey = "attachments_for_federation_{$federationId}_committee_{$committee->id}";

        $attachments = Cache::remember($cacheKey, now()->addMinutes(0), function () use ($getAttachments, $federationId, $committee) {
            $baseQuery = $getAttachments->execute($federationId, $committee->id);

            // Use QueryBuilder for additional filters
            $queryBuilder = QueryBuilder::for($baseQuery)
                ->allowedFilters([
                    AllowedFilter::scope('filter_name'),
                    AllowedFilter::scope('filter_category'),
                    AllowedFilter::scope('filter_language'),
                    AllowedFilter::scope('filter_date_start'),
                    AllowedFilter::scope('filter_date_end'),
                ])
                ->with(['category', 'language', 'licenses', 'certifications', 'owner', 'media'])
                ->whereHas('media')
                ->orderBy('name')
                ->get();

            // Determine the current page and number of items per page
            $page = RequestFacade::input('page', 1); // Default to page 1 if not specified
            $perPage = 15; // Set the number of items per page
            $offset = ($page - 1) * $perPage; // Calculate the offset

            // Manually paginate the results
            $pagedAttachments = new LengthAwarePaginator(
                $queryBuilder->slice($offset, $perPage), // Slice the collection for the current page
                $queryBuilder->count(), // Total count of items
                $perPage, // Items per page
                $page, // Current page
                ['path' => RequestFacade::url(), 'query' => RequestFacade::query()] // Set the path and query for pagination links
            );

            return $pagedAttachments;
        });

        $categories = Cache::remember('attachment_categories', now()->addDays(1), function () {
            return AttachmentCategory::all();
        });
        $languages = Cache::remember('attachment_languages', now()->addDays(1), function () {
            return Language::orderBy('name')->get();
        });

        return view('web.federation.attachments.index', compact('attachments', 'committee', 'categories', 'languages'));
    }

    // Download the file
    public function download(Request $request)
    {
        if (empty($request->id)) {
            return back()->with('error', 'No file selected');
        }

        // Get the federation ID for the logged-in user
        $federationId = Auth::user()->federations()->first()->id;
        if (! $federationId) {
            return back()->with('error', 'You are not authorized to download this file');
        }

        $mediaItem = Media::find($request->id);
        if (! $mediaItem) {
            return back()->with('error', 'File not found');
        }

        $downloadFilename = $this->buildDownloadFilename($mediaItem);

        // Check if the media item's parent attachment belongs to the user's federation
        $attachment = $mediaItem->model()->first();
        if ($attachment instanceof Attachment && $attachment->owner_type === Federation::class && $attachment->owner_id == $federationId) {
            // The file belongs to the federation
            return $this->streamMediaDownload($mediaItem, $downloadFilename);
        }

        if ($mediaItem->model_type == Event::class) {
            return $this->streamMediaDownload($mediaItem, $downloadFilename);
        }

        // If the attachment is not specific to a federation, check if it's for all federations
        if ($attachment instanceof Attachment && in_array($attachment->recipient_name, ['all', 'all_federations'], true)) {
            // The file is accessible to all federations
            return $this->streamMediaDownload($mediaItem, $downloadFilename);
        }

        // If none of the above conditions are met, the user is not authorized to download the file
        return back()->with('error', 'You are not authorized to download this file');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return RedirectResponse
     */
    public function destroy($id)
    {
        $federationId = auth()->user()->federations->first()->id;
        // Find the attachment by its ID
        $attachment = Attachment::findOrFail($id);
        if ($attachment->owner_type === 'Domain\Federations\Models\Federation' && $attachment->owner_id == $federationId) {
            // Delete the attachment
            $attachment->delete();

            // Invalidate cache for the specific federation and committee
            for ($i = 0; $i < 4; $i++) {
                $cacheKey = "attachments_for_federation_{$federationId}_committee_{$i}";
                Cache::forget($cacheKey);
            }

            return redirect()->back()
                ->with('success', 'Attachment deleted successfully.');
        }

        return redirect()->back()->with('error', 'Unauthorized to delete this attachment.');
    }
}
