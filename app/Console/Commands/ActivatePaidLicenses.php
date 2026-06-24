<?php

namespace App\Console\Commands;

use Domain\Documents\Models\DocumentDetail;
use Domain\Documents\States\PaidDocumentState;
use Domain\Licenses\Actions\ActivateLicenseAttributedAction;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\PendingLicenseAttributedState;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ActivatePaidLicenses extends Command
{
    protected $signature = 'licenses:activate-paid';
    protected $description = 'Activates all licenses that are still in pending state and associated with fully paid documents';

    public function handle()
    {
        $pendingLicenses = LicenseAttributed::where('status_class', PendingLicenseAttributedState::class)->get();
        $this->info('Found ' . $pendingLicenses->count() . ' pending licenses.');
        $activateAction = new ActivateLicenseAttributedAction;

        foreach ($pendingLicenses as $license) {
            $documentDetail = DocumentDetail::where('owner_type', LicenseAttributed::class)
                ->where('owner_id', $license->id)
                ->first();

            if ($documentDetail) {
                $document = $documentDetail->document;

                if ($document && $document->status_class === PaidDocumentState::class) {
                    try {
                        $activateAction($license, null); // Modify according to your requirements
                        $this->info("Activated license: {$license->id} associated with paid document: {$document->id}");
                    } catch (\Exception $e) {
                        Log::error("Failed to activate license {$license->id}: {$e->getMessage()}");
                        $this->error("Failed to activate license {$license->id}");
                    }
                } else {
                    $this->info("Skipping license: {$license->id} as the associated document is not fully paid.");
                }
            } else {
                $this->info("No associated document detail found for license: {$license->id}. Skipping.");
            }
        }

        $this->info('Done processing licenses.');
    }
}
