<?php

namespace Domain\Certifications\Actions;

use Domain\Certifications\Models\Certification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class UploadCertificationView
{
    public function __invoke($file, Certification $certification)
    {
        $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $sanitizedFilename = Str::slug($filename) . '_' . time();
        $extension = $file->getClientOriginalExtension();
        $fullFilename = $sanitizedFilename . '.' . $extension;

        try {
            $manager = new ImageManager(new Driver);
            $image = $manager->read($file);

            // Define maximum dimensions for a card-like aspect ratio
            $maxWidth = 420;
            $maxHeight = 280;
            $image->cover($maxWidth, $maxHeight);
            $path = 'img/cards/' . $fullFilename;
            /*
            $image->resize(420, null, function ($constraint) {
                $constraint->aspectRatio();
            });
            */

            // Using storage facade to save the file
            Storage::disk('public')->put($path, $image->toJpeg()->toFilePointer());

            $certification->certification_view = $fullFilename;
            $certification->save();

            // Optional: return some response
            return $fullFilename;
        } catch (\Exception $e) {
            // Handle exceptions (log, notify, etc.)
            throw $e;
        }
    }

}
