<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DeleteExportFiles extends Command
{
    protected $signature = 'exports:cleanup';

    protected $description = 'Remove all files from the exports folder on the local disk';

    public function handle()
    {
        $disk = Storage::disk('local');
        $directory = 'exports';

        if ($disk->exists($directory)) {
            $files = $disk->files($directory);

            foreach ($files as $file) {
                $disk->delete($file);
            }

            $this->info('All files in the exports folder have been deleted.');
        } else {
            $this->info('The exports folder does not exist or is empty.');
        }
    }
}
