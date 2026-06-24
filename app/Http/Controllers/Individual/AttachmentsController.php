<?php

namespace App\Http\Controllers\Individual;

use App\Http\Controllers\Controller;
use App\Models\Committee;
use App\Models\Language;
use App\Traits\StreamsMediaFromStorage;
use Domain\Attachments\Actions\GetIndividualAttachmentsAction;
use Domain\Attachments\Models\Attachment;
use Domain\Attachments\Models\AttachmentCategory;
use Domain\Individuals\Models\Individual;
use Domain\Users\Actions\GetUserTypeAction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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
        GetIndividualAttachmentsAction $getAttachments,
        GetUserTypeAction $getUserType
    ): View {
        // Get user Individual id
        $individualId = $getUserType::execute(auth()->user())->id;
        $cacheKey = "attachments_for_individual_{$individualId}_committee_{$committee->id}";

        $attachments = Cache::remember($cacheKey, now()->addMinutes(0), function () use ($getAttachments, $individualId, $committee) {
            $baseQuery = $getAttachments->execute($individualId, $committee->id);

            // Use QueryBuilder for additional filters
            return QueryBuilder::for($baseQuery)
                ->allowedFilters([
                    AllowedFilter::scope('filter_name'),
                    AllowedFilter::scope('filter_category'),
                    AllowedFilter::scope('filter_language'),
                    AllowedFilter::scope('filter_date_start'),
                    AllowedFilter::scope('filter_date_end'),
                ])
                ->with(['category', 'language', 'licenses', 'certifications', 'media', 'owner'])
                ->whereHas('media')
                ->orderBy('name')
                ->paginate()
                ->appends(request()->query());
        });

        $categories = Cache::remember('attachment_categories', now()->addDays(1), function () {
            return AttachmentCategory::all();
        });
        $languages = Cache::remember('attachment_languages', now()->addDays(1), function () {
            return Language::orderBy('name')->get();
        });

        return view('web.individual.attachments.index', compact('attachments', 'committee', 'categories', 'languages'));
    }

    public function create(?Committee $committee = null): View
    {
        $categories = Cache::remember('attachment_categories', now()->addDays(1), function () {
            return AttachmentCategory::all();
        });

        $languages = Cache::remember('attachment_languages', now()->addDays(1), function () {
            return Language::orderBy('name')->get();
        });

        return view('web.individual.attachments.create', compact('committee', 'categories', 'languages'));
    }

    public function download(Request $request)
    {
        if (empty($request->id)) {
            return back()->with('error', 'No file selected');
        }

        // Get the individual ID for the logged-in user
        $individualId = Auth::user()->individuals()->first()->id;
        $mediaItem = Media::find($request->id);
        if (! $mediaItem) {
            return back()->with('error', 'File not found');
        }

        $downloadFilename = $this->buildDownloadFilename($mediaItem);

        // Check if the media item's parent attachment is accessible by the individual
        $attachment = $mediaItem->model()->first();
        if (! $attachment instanceof Attachment) {
            return back()->with('error', 'File not found');
        }

        if ($attachment->owner_type === Individual::class && $attachment->owner_id == $individualId) {
            // The file belongs to the individual
            return $this->streamMediaDownload($mediaItem, $downloadFilename);
        }

        // Check if the attachment is for all individuals or all entities & individuals
        if (in_array($attachment->recipient_name, ['all', 'all_individuals', 'all_entities_&_individuals', 'individual'], true)) {
            // The file is accessible to all individuals
            return $this->streamMediaDownload($mediaItem, $downloadFilename);
        }

        // If none of the above conditions are met, the user is not authorized to download the file
        return back()->with('error', 'You are not authorized to download this file');
    }
}
