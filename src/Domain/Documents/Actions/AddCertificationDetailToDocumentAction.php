<?php

namespace Domain\Documents\Actions;

use Domain\Certifications\Models\CertificationAttributed;
use Domain\Documents\DataTransferObject\DocumentData;
use Domain\Documents\Models\Document;
use Domain\Documents\States\PendingDocumentState;
use Domain\Entities\Models\Entity;
use Domain\Individuals\Models\Individual;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AddCertificationDetailToDocumentAction
{
    public function __construct(
        private CreateDocumentAction $createDocumentAction,
        private BuildCertificationDocumentDetailAction $buildDocumentDetailAction,
        private CalculateDocumentTotalAction $calculateDocumentTotalAction
    ) {}

    public function __invoke(CertificationAttributed $certificationAttributed, float $price): void
    {
        $certificationAttributed->load(['certification', 'federation', 'entity', 'individual']);

        $owner = $certificationAttributed->entity ?? $certificationAttributed->individual;
        if (! $owner) {
            Log::error('AddCertificationDetailToDocumentAction: No owner found', [
                'certification_attributed_id' => $certificationAttributed->id,
            ]);

            return;
        }

        $batchId = $certificationAttributed->batch_id;
        $canGroupByBatch = $batchId && $certificationAttributed->entity_id;

        if ($canGroupByBatch) {
            $this->handleBatchGrouping($certificationAttributed, $owner, $price, $batchId);
        } else {
            $this->createNewDocument($certificationAttributed, $owner, $price);
        }
    }

    private function handleBatchGrouping(
        CertificationAttributed $certificationAttributed,
        Entity|Individual $owner,
        float $price,
        string $batchId
    ): void {
        $lock = Cache::lock("batch-doc-{$batchId}", 10);

        $lock->block(5, function () use ($certificationAttributed, $owner, $price, $batchId) {
            DB::transaction(function () use ($certificationAttributed, $owner, $price, $batchId) {
                $ownerMorphType = $owner->getMorphClass();

                $existingDocument = Document::query()
                    ->where('owner_type', $ownerMorphType)
                    ->where('owner_id', $owner->id)
                    ->where('status_class', PendingDocumentState::class)
                    ->whereHas('details', function ($query) use ($batchId) {
                        $query->where('owner_type', CertificationAttributed::class)
                            ->whereIn('owner_id', function ($q) use ($batchId) {
                                $q->select('id')
                                    ->from('certification_attributed')
                                    ->where('batch_id', $batchId)
                                    ->whereNull('deleted_at');
                            });
                    })
                    ->first();

                if ($existingDocument) {
                    $this->addDetailToExistingDocument($certificationAttributed, $existingDocument, $price);
                } else {
                    $this->createNewDocument($certificationAttributed, $owner, $price);
                }
            });
        });
    }

    private function addDetailToExistingDocument(
        CertificationAttributed $certificationAttributed,
        Document $document,
        float $price
    ): void {
        ($this->buildDocumentDetailAction)($certificationAttributed, $document, $price);

        $totals = ($this->calculateDocumentTotalAction)($document->details()->get());
        $document->update($totals);

        Log::info('AddCertificationDetailToDocumentAction: Detail added to existing batch document', [
            'certification_attributed_id' => $certificationAttributed->id,
            'document_id' => $document->id,
            'new_total' => $totals['total_value'],
        ]);
    }

    private function createNewDocument(
        CertificationAttributed $certificationAttributed,
        Entity|Individual $owner,
        float $price
    ): void {
        $documentData = DocumentData::fromArray([
            'owner_type' => $owner->getMorphClass(),
            'owner_id' => $owner->id,
            'total_value' => $price,
            'customer_name' => $owner->name,
        ]);

        $document = ($this->createDocumentAction)($documentData, 'ORD');

        ($this->buildDocumentDetailAction)($certificationAttributed, $document, $price);

        Log::info('AddCertificationDetailToDocumentAction: New document created', [
            'certification_attributed_id' => $certificationAttributed->id,
            'document_id' => $document->id,
            'price' => $price,
        ]);
    }
}
