<?php

namespace Domain\Documents\Actions;

use Domain\Documents\DataTransferObject\DocumentData;
use Domain\Documents\DataTransferObject\DocumentDetailData;
use Domain\Documents\Models\Document;
use Illuminate\Database\Eloquent\Relations\Relation;

class CreateDocumentWithDetailsAction
{
    public function __invoke(
        array $detailsData,
        string $docType,
        ?string $owner_id,
        ?string $owner_type,
        ?string $notes = null
    ): ?Document {
        // Create the details collection
        $detailsCollection = collect();
        foreach ($detailsData as $detail) {
            $detailsCollection->push(DocumentDetailData::toModel($detail));
        }

        // Calculate the document total
        $calculateDocumentTotalAction = new CalculateDocumentTotalAction;
        $documentTotal = $calculateDocumentTotalAction($detailsCollection);

        // Prevent zero-value documents
        if ($documentTotal['total_value'] <= 0) {
            return null;
        }

        // Create a blank Document with the correct type
        $createDocumentAction = new CreateDocumentAction;

        // Initiate the document DTO passing only the required value
        $documentData = new DocumentData;

        $documentData->tax_value = $documentTotal['tax_value'];
        $documentData->total_value = $documentTotal['total_value'];
        $documentData->net_value = $documentTotal['net_value'];

        // TODO: Como os detalhes podem ter varios tax % como é calculada a tax_percentage do documento?
        // $documentData->tax_percentage = $details->toArray()['tax_percentage'];

        $documentData->owner_id = $owner_id;
        $documentData->owner_type = $this->normalizeMorphOwnerType($owner_type);
        $documentData->customer_name = $detailsCollection->first()?->customer_name;
        $documentData->notes = $notes;

        $document = $createDocumentAction($documentData, $docType);

        // Create the detail records
        $createDocumentDetailAction = new CreateDocumentDetailAction;

        foreach ($detailsData as $detailData) {
            $createDocumentDetailAction($document, $detailData);
        }

        activity('document')
            ->performedOn($document)
            ->event('created_with_details')
            ->log("Document {$document->number_extended} with details created");

        return $document;
    }

    private function normalizeMorphOwnerType(?string $ownerType): ?string
    {
        if ($ownerType === null) {
            return null;
        }

        $alias = array_search($ownerType, Relation::morphMap(), true);

        return $alias !== false ? $alias : $ownerType;
    }
}
