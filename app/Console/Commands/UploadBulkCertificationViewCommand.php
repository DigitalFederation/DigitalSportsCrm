<?php

namespace App\Console\Commands;

use Domain\Certifications\Models\Certification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class UploadBulkCertificationViewCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'certification:upload-views {folder : Folder containing certification card images}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upload many certification views at once';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $folderPath = $this->argument('folder');
        $files = File::files($folderPath);

        foreach ($files as $file) {
            $filename = pathinfo($file, PATHINFO_FILENAME);
            $filenameSlug = Str::slug($filename);

            // Get all certifications, convert their name to a slug and check for a match
            $certifications = Certification::all();

            foreach ($certifications as $certification) {
                $isSimilar = Str::slug($certification->name) == $filenameSlug;

                if ($isSimilar) {
                    // Create Image from file
                    $img = Image::make($file);

                    // Resize the image to a width of 420 and constrain aspect ratio (auto height)
                    $img->resize(420, null, function ($constraint) {
                        $constraint->aspectRatio();
                    });

                    $path = 'img/cards/'.$filenameSlug.'.jpg';
                    Storage::disk('public')->put($path, (string) $img->encode('jpg'));

                    $certification->certification_view = basename($path);
                    $certification->save();

                    $this->info("Image for {$certification->name} has been uploaded successfully");
                    break;
                }
            }
        }
    }
}
