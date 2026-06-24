<?php

namespace Support;

use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Spatie\Image\Image;

class UtilityMethods
{
    /**
     * Create a unique code for the Individual record
     */
    public static function generateUniqueIndividualCode(): string
    {
        $random_str = Str::random(7);
        if (Individual::where('member_code', $random_str)->count() > 0) {
            self::generateUniqueIndividualCode();
        }

        return $random_str;
    }

    public static function generateUniqueEntityCode(): string
    {
        $random_str = Str::random(7);
        if (Entity::where('member_code', $random_str)->count() > 0) {
            self::generateUniqueEntityCode();
        }

        return $random_str;
    }

    public static function generateUniqueFederationCode(): string
    {
        $random_str = Str::random(7);
        if (Federation::where('member_code', $random_str)->count() > 0) {
            self::generateUniqueFederationCode();
        }

        return $random_str;
    }

    public static function generateCertificationCmasInternationalNumber(int $year, int $federationId): string
    {
        $federation = Federation::where('id', $federationId)->with('country')->firstOrFail();

        $countryCode = $federation->country->ioc ?? 'IT';

        if (empty($year)) {
            $year = date('Y');
        }
        $uniqueString = substr(md5(uniqid(rand(), true)), 0, 6);

        return $year . $federation->member_code . $uniqueString;
    }

    public static function generateLicenseCmasInternationalNumber(int $year, string $license_code, string $federation_code): string
    {
        if (empty($year)) {
            $year = date('Y');
        }

        $uniqueString = substr(md5(uniqid(rand(), true)), 0, 6);

        return $license_code . $federation_code . $year . $uniqueString;
    }

    public static function getUniqueCountsForPaginatedCollection(LengthAwarePaginator|Collection $collection, array $columns): array
    {

        $results = [];
        foreach ($columns as $label => $column) {
            // $count = get_class($collection) == 'Illuminate\Pagination\LengthAwarePaginator' ? collect($collection->items())->unique($column)->count() : $collection->unique($column)->count();
            $uniqueValues = $collection->pluck($column)->unique();
            // $results[$label] = $count;
        }

        return $results;
    }

    /**
     * Add an image from a POST request to a media collection.
     * Resizing should be handled by conversions defined on the model.
     *
     * @param  \Illuminate\Database\Eloquent\Model&\Spatie\MediaLibrary\HasMedia  $model
     * @return \Spatie\MediaLibrary\MediaCollections\Models\Media
     */
    public static function addUploadedImageToMediaCollection(
        $model,
        string $collectionName,
        \Illuminate\Http\UploadedFile $image
    ) {
        // Generate a clean file name
        $baseName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $image->getClientOriginalExtension();
        $fileName = time() . '-' . Str::slug($baseName) . '.' . $extension;

        // Let Spatie Media Library handle the uploaded file directly
        return $model->addMedia($image)
            ->usingFileName($fileName)
            ->toMediaCollection($collectionName, 'public');
    }
}
