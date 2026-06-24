<?php

namespace App\Traits;

use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait InteractsWithMediaExtend
{
    /**
     * Extends Spatie Media Library InteractsWithMedia trait to add a lastMediaObject
     *
     * @param  array  $filters
     */
    public function getLastMedia(string $collectionName = 'default', $filters = []): ?Media
    {
        $media = $this->getMedia($collectionName, $filters);

        return $media->last();
    }

    /*
    * Get the url of the image for the given conversionName
    * for last media for the given collectionName.
    * If no profile is given, return the source's url.
    */
    public function getLastMediaUrl(string $collectionName = 'default', string $conversionName = ''): string
    {
        $media = $this->getLastMedia($collectionName);

        if (! $media) {
            return $this->getFallbackMediaUrl($collectionName, $conversionName) ?: '';
        }

        if ($conversionName !== '' && ! $media->hasGeneratedConversion($conversionName)) {
            return $media->getUrl();
        }

        return $media->getUrl($conversionName);
    }
}
