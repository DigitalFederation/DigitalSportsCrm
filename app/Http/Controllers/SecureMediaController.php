<?php

namespace App\Http\Controllers;

use App\Traits\StreamsMediaFromStorage;
use Domain\Individuals\Models\Individual;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\Response;

class SecureMediaController extends Controller
{
    use StreamsMediaFromStorage;

    /**
     * Serve a protected profile image
     */
    public function serveProfileImage(Request $request, string $individualId, string $mediaId): Response
    {
        $media = $this->resolveAuthorizedMedia($individualId, $mediaId);

        if ($this->isNotModified($request, $media)) {
            return response('', 304);
        }

        return $this->streamMediaInline($media);
    }

    /**
     * Serve a profile thumbnail
     */
    public function serveProfileThumbnail(Request $request, string $individualId, string $mediaId): Response
    {
        $media = $this->resolveAuthorizedMedia($individualId, $mediaId);

        if ($this->isNotModified($request, $media)) {
            return response('', 304);
        }

        return $this->streamMediaInline($media, 'thumb');
    }

    /**
     * Resolve the individual and media, checking authorization.
     */
    protected function resolveAuthorizedMedia(string $individualId, string $mediaId): Media
    {
        $individual = Individual::findOrFail($individualId);

        if (! $this->canViewProfileImage($individual)) {
            abort(403, 'Unauthorized access to profile image');
        }

        $media = $individual->getMedia('profile')->where('id', (int) $mediaId)->first();

        if (! $media) {
            abort(404, 'Profile image not found');
        }

        return $media;
    }

    /**
     * Check if the client already has the current version (ETag match).
     */
    protected function isNotModified(Request $request, Media $media): bool
    {
        $etag = '"' . md5($media->id . '-' . $media->updated_at->timestamp) . '"';

        return $request->header('If-None-Match') === $etag;
    }

    /**
     * Check if the current user can view the profile image
     */
    protected function canViewProfileImage(Individual $individual): bool
    {
        // Allow public access for coaches visible in the public registry
        if ($individual->visible_in_coach_registry) {
            return true;
        }

        // Allow public access for individuals with active certifications
        // This supports public certification verification (QR code scanning)
        if ($this->hasPublicCertification($individual)) {
            return true;
        }

        // Any authenticated user can view profile images
        return auth()->check();
    }

    /**
     * Check if the individual has any active public certifications
     * This allows their profile image to be shown on public certification verification pages
     */
    protected function hasPublicCertification(Individual $individual): bool
    {
        return $individual->certificationsAttributed()
            ->where('status_class', \Domain\Certifications\States\ActiveCertificationAttributedState::class)
            ->exists();
    }
}
