<?php

namespace App\Console\Commands;

use App\Jobs\EntityQrCodeGenerate;
use Domain\Entities\Models\Entity;
use Illuminate\Console\Command;

class GenerateEntitiesQrCode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qr-code:entities-generate {--force : Regenerate all QR codes, even if they already exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a qr code for all entities who have not yet been generated';

    /**
     * Execute the console command.
     *
     * @throws \Throwable
     */
    public function handle(): int
    {
        $query = Entity::query();

        if (! $this->option('force')) {
            $query->where(function ($q) {
                $q->whereNull('qrcode_path')
                    ->orWhere('qrcode_path', '');
            });
        }

        $entities = $query->get();

        if ($entities->isEmpty()) {
            $this->info('No entities found requiring QR codes.');

            return 0;
        }

        $this->info("Processing {$entities->count()} entities...");

        foreach ($entities as $entity) {
            EntityQrCodeGenerate::dispatch($entity);
        }

        $this->info('QR code generation jobs queued successfully.');

        return 0;
    }
}
