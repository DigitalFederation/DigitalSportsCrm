<?php

namespace Domain\Documents\Actions;

use Domain\Certifications\Models\CertificationAttributed;
use Domain\Documents\Models\Document;
use Domain\Documents\Models\DocumentDetail;

class BuildCertificationDocumentDetailAction
{
    /**
     * Build document detail for a certification purchase
     */
    public function __invoke(
        CertificationAttributed $certificationAttributed,
        Document $document,
        float $price
    ): DocumentDetail {
        $certification = $certificationAttributed->certification;

        // Build description
        $description = sprintf(
            'Certification: %s (%s)',
            $certification->name,
            $certification->acronym ?? 'N/A'
        );

        if ($certificationAttributed->holder_name) {
            $description .= ' - ' . $certificationAttributed->holder_name;
        }

        // Create document detail
        return DocumentDetail::create([
            'document_id' => $document->id,
            'quantity' => 1,
            'description' => $description,
            'unit_value' => $price,
            'total_value' => $price,
            'owner_type' => CertificationAttributed::class,
            'owner_id' => $certificationAttributed->id,
            'reference' => $certification->moloni_reference,
        ]);
    }
}
