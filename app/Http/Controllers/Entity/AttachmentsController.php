<?php

namespace App\Http\Controllers\Entity;

use App\Http\Controllers\Controller;
use App\Models\Committee;
use App\Models\Language;
use App\Traits\StreamsMediaFromStorage;
use Domain\Attachments\Actions\GetEntityAttachmentsAction;
use Domain\Attachments\Models\Attachment;
use Domain\Attachments\Models\AttachmentCategory;
use Domain\Entities\Models\Entity;
use Domain\Users\Actions\GetUserTypeAction;
use Illuminate\Http\Request;
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
    public function index(Committee $committee, GetEntityAttachmentsAction $getAttachments, GetUserTypeAction $getUserType)
    {
        $entityId = $getUserType::execute(auth()->user())->id;
        $cacheKey = "attachments_for_entity_{$entityId}_committee_{$committee->id}";

        $attachments = Cache::remember($cacheKey, now()->addMinutes(60), function () use ($getAttachments, $entityId, $committee) {
            $baseQuery = $getAttachments->execute($entityId, $committee->id);

            // Use QueryBuilder for additional filters
            return QueryBuilder::for($baseQuery)
                ->allowedFilters([
                    AllowedFilter::scope('filter_name'),
                    AllowedFilter::scope('filter_category'),
                    AllowedFilter::scope('filter_language'),
                    AllowedFilter::scope('filter_date_start'),
                    AllowedFilter::scope('filter_date_end'),
                ])
                ->with(['category', 'language', 'licenses', 'certifications', 'media'])
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

        return view('web.entity.attachments.index', compact('attachments', 'committee', 'categories', 'languages'));
    }

    public function download(Request $request)
    {
        if (empty($request->id)) {
            return back()->with('error', 'No file selected');
        }

        // Get the entity ID for the logged-in user
        $entityId = auth()->user()->entities()->first()->id;
        if (! $entityId) {
            return back()->with('error', 'You are not authorized to download this file');
        }

        $mediaItem = Media::find($request->id);
        if (! $mediaItem) {
            return back()->with('error', 'File not found');
        }

        $downloadFilename = $this->buildDownloadFilename($mediaItem);

        // Check if the media item's parent attachment is accessible by the entity
        $attachment = $mediaItem->model()->first();
        if (! $attachment instanceof Attachment) {
            return back()->with('error', 'File not found');
        }

        // Check if attachment is for all entities or specifically for this entity
        if (
            in_array($attachment->recipient_name, ['all', 'all_entities', 'all_entities_&_individuals'], true) ||
            ($attachment->recipient_name === 'entity' && $attachment->recipient_id == $entityId) ||
            ($attachment->recipient_type === Entity::class && $attachment->recipient_id == $entityId)
        ) {
            return $this->streamMediaDownload($mediaItem, $downloadFilename);
        }

        // If none of the above conditions are met, the user is not authorized
        return back()->with('error', 'You are not authorized to download this file');
    }
}
