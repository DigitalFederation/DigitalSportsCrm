<?php

namespace Domain\Documents\Actions;

use Domain\Documents\DataTransferObject\DocumentData;
use Domain\Documents\Models\Document;
use Domain\Documents\Models\DocumentType;
use Domain\Documents\States\DraftDocumentState;
use Domain\Documents\States\PendingDocumentState;

class CreateDocumentAction
{
    public function __invoke(DocumentData $data, string $documentTypeCode, $is_draft = false): Document
    {
        $documentType = DocumentType::where('code', $documentTypeCode)->firstOrFail();
        $generateDocumentNumber = new GenerateDocumentNumberAction;
        $generatedNumber = $generateDocumentNumber($documentType);

        $data->number = $generatedNumber['number'];
        $data->number_pad = $generatedNumber['number_pad'];
        $data->number_year = $generatedNumber['number_year'];
        $data->number_extended = $generatedNumber['number_extended'];

        if (! $is_draft) {
            $data->status_class = PendingDocumentState::class;
        } else {
            $data->status_class = DraftDocumentState::class;
        }

        $document = new Document($data->toArray());
        $document->type()->associate($documentType);
        $document->save();

        activity('document')
            ->performedOn($document)
            ->event('created')
            ->withProperties(['type' => $documentType->code])
            ->log("Document {$document->number_extended} of type {$documentType->code} created");

        return $document;
    }
}
