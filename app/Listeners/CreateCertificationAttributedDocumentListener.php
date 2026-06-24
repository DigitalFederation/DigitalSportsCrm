<?php

namespace App\Listeners;

use App\Events\CertificationAttributedCreatedEvent;
use Domain\Documents\Actions\AddCertificationDetailToDocumentAction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class CreateCertificationAttributedDocumentListener implements ShouldQueue
{
    public function __construct(
        private AddCertificationDetailToDocumentAction $addCertificationDetailToDocumentAction
    ) {}

    public function handle(CertificationAttributedCreatedEvent $event): void
    {
        $certificationAttributed = $event->certificationAttributed;
        $price = $event->price;

        if ($price <= 0) {
            Log::info('CreateCertificationAttributedDocumentListener: Skipping document creation for free certification', [
                'certification_attributed_id' => $certificationAttributed->id,
                'price' => $price,
            ]);

            return;
        }

        try {
            ($this->addCertificationDetailToDocumentAction)($certificationAttributed, $price);

            Log::info('CreateCertificationAttributedDocumentListener: Document processing completed', [
                'certification_attributed_id' => $certificationAttributed->id,
                'price' => $price,
            ]);
        } catch (\Exception $e) {
            Log::error('CreateCertificationAttributedDocumentListener: Failed to create document', [
                'certification_attributed_id' => $certificationAttributed->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
